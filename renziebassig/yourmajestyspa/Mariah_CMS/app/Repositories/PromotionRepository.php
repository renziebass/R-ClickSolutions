<?php
declare(strict_types=1);

namespace Mariah\Repositories;

use Mariah\Core\Auth;
use Mariah\Core\Database;
use Mariah\Core\Request;
use Mariah\Services\ScheduleResolver;

final class PromotionRepository extends BaseRepository
{
    protected string $table  = 'promotions';
    protected string $entity = 'promotion';
    protected string $alias  = 'p';

    protected array $fillable = [
        'title', 'slug', 'description', 'media_id',
        'discount_type', 'discount_value', 'original_price', 'promo_price',
        'badge_label', 'booking_url', 'start_date', 'end_date',
        'status', 'featured', 'display_order',
    ];

    protected array $sortable = [
        'title'         => 'p.title',
        'status'        => 'p.status',
        'start_date'    => 'p.start_date',
        'end_date'      => 'p.end_date',
        'featured'      => 'p.featured',
        'display_order' => 'p.display_order',
        'updated_at'    => 'p.updated_at',
        'created_at'    => 'p.created_at',
        'id'            => 'p.id',
    ];

    protected array $searchable = ['p.title', 'p.description'];

    protected string $defaultSort      = 'display_order';
    protected string $defaultDirection = 'ASC';

    protected function listSelect(): string
    {
        return 'p.*, m.file_url AS image_url, m.alt_text AS image_alt';
    }

    protected function listJoins(): string
    {
        return 'LEFT JOIN media m ON m.id = p.media_id AND m.deleted_at IS NULL';
    }

    protected function listFilters(Request $request): array
    {
        $conditions = [];
        $bindings   = [];

        // Filtering by the DERIVED state, expressed in SQL so pagination counts
        // stay correct — filtering in PHP after LIMIT would give wrong totals.
        $state = (string) $request->q('state', '');
        switch ($state) {
            case 'draft':
                $conditions[] = "p.status = 'draft'";
                break;
            case 'inactive':
                $conditions[] = "p.status = 'archived'";
                break;
            case 'scheduled':
                $conditions[] = "p.status = 'published' AND p.start_date IS NOT NULL AND p.start_date > CURDATE()";
                break;
            case 'expired':
                $conditions[] = "p.status = 'published' AND p.end_date IS NOT NULL AND p.end_date < CURDATE()";
                break;
            case 'active':
                $conditions[] = "p.status = 'published'
                                 AND (p.start_date IS NULL OR p.start_date <= CURDATE())
                                 AND (p.end_date   IS NULL OR p.end_date   >= CURDATE())";
                break;
        }

        $featured = $request->qBool('featured');
        if ($featured !== null) {
            $conditions[] = 'p.featured = ?';
            $bindings[]   = $featured ? 1 : 0;
        }

        if (($from = $request->q('date_from')) !== null) {
            $conditions[] = '(p.end_date IS NULL OR p.end_date >= ?)';
            $bindings[]   = substr((string) $from, 0, 10);
        }
        if (($to = $request->q('date_to')) !== null) {
            $conditions[] = '(p.start_date IS NULL OR p.start_date <= ?)';
            $bindings[]   = substr((string) $to, 0, 10);
        }

        return [$conditions, $bindings];
    }

    protected function decorate(array $row): array
    {
        $row['id']             = (int) $row['id'];
        $row['featured']       = (bool) $row['featured'];
        $row['discount_value'] = (float) $row['discount_value'];

        $row = ScheduleResolver::decorate($row);
        $row['effective_status_label'] = ScheduleResolver::label($row['effective_status']);
        $row['discount_label']         = self::discountLabel($row);

        return $row;
    }

    public static function discountLabel(array $row): string
    {
        return match ($row['discount_type']) {
            'percentage'    => rtrim(rtrim(number_format((float) $row['discount_value'], 2, '.', ''), '0'), '.') . '% off',
            'fixed'         => '$' . rtrim(rtrim(number_format((float) $row['discount_value'], 2, '.', ''), '0'), '.') . ' off',
            'special_price' => $row['promo_price'] !== null
                ? '$' . rtrim(rtrim(number_format((float) $row['promo_price'], 2, '.', ''), '0'), '.')
                : 'Special price',
            default         => '',
        };
    }

    /** Service ids this promotion applies to. */
    public function serviceIds(int $promotionId): array
    {
        return array_map(
            'intval',
            array_column(
                Database::fetchAll(
                    'SELECT service_id FROM promotion_services WHERE promotion_id = ?',
                    [$promotionId]
                ),
                'service_id'
            )
        );
    }

    /** @param int[] $serviceIds */
    public function syncServices(int $promotionId, array $serviceIds): void
    {
        Database::transaction(function () use ($promotionId, $serviceIds): void {
            Database::run('DELETE FROM promotion_services WHERE promotion_id = ?', [$promotionId]);

            foreach (array_unique(array_map('intval', $serviceIds)) as $serviceId) {
                if ($serviceId <= 0) {
                    continue;
                }
                // Ignore ids that no longer resolve to a live service.
                $exists = Database::fetchValue(
                    'SELECT 1 FROM services WHERE id = ? AND deleted_at IS NULL',
                    [$serviceId]
                );
                if ($exists === null) {
                    continue;
                }

                Database::run(
                    'INSERT INTO promotion_services (promotion_id, service_id) VALUES (?, ?)',
                    [$promotionId, $serviceId]
                );
            }
        });
    }

    public function stats(): array
    {
        $row = Database::fetchOne(
            "SELECT
                COUNT(*) AS total,
                SUM(status = 'draft') AS draft,
                SUM(status = 'published'
                    AND (start_date IS NULL OR start_date <= CURDATE())
                    AND (end_date   IS NULL OR end_date   >= CURDATE())) AS active,
                SUM(status = 'published' AND start_date IS NOT NULL AND start_date > CURDATE()) AS scheduled,
                SUM(status = 'published' AND end_date IS NOT NULL AND end_date < CURDATE())     AS expired
             FROM promotions
            WHERE deleted_at IS NULL"
        ) ?? [];

        return [
            'total'     => (int) ($row['total'] ?? 0),
            'draft'     => (int) ($row['draft'] ?? 0),
            'active'    => (int) ($row['active'] ?? 0),
            'scheduled' => (int) ($row['scheduled'] ?? 0),
            'expired'   => (int) ($row['expired'] ?? 0),
        ];
    }

    public function recent(int $limit = 5): array
    {
        $limit = max(1, min(20, $limit));

        return array_map(
            [ScheduleResolver::class, 'decorate'],
            Database::fetchAll(
                "SELECT p.id, p.title, p.slug, p.status, p.start_date, p.end_date, p.created_at,
                        CONCAT(u.first_name, ' ', u.last_name) AS created_by_name
                   FROM promotions p
                   LEFT JOIN users u ON u.id = p.created_by
                  WHERE p.deleted_at IS NULL
                  ORDER BY p.created_at DESC
                  LIMIT {$limit}"
            )
        );
    }
}
