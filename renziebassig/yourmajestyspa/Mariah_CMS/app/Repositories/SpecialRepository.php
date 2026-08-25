<?php
declare(strict_types=1);

namespace Mariah\Repositories;

use Mariah\Core\Database;
use Mariah\Core\Request;
use Mariah\Services\ScheduleResolver;

final class SpecialRepository extends BaseRepository
{
    protected string $table  = 'specials';
    protected string $entity = 'special';
    protected string $alias  = 'sp';

    protected array $fillable = [
        'title', 'slug', 'description', 'media_id', 'badge_label',
        'price', 'price_display', 'compare_at_price', 'booking_url',
        'start_date', 'end_date', 'status', 'featured', 'display_order',
    ];

    protected array $sortable = [
        'title'         => 'sp.title',
        'price'         => 'sp.price',
        'status'        => 'sp.status',
        'start_date'    => 'sp.start_date',
        'end_date'      => 'sp.end_date',
        'featured'      => 'sp.featured',
        'display_order' => 'sp.display_order',
        'updated_at'    => 'sp.updated_at',
        'created_at'    => 'sp.created_at',
        'id'            => 'sp.id',
    ];

    protected array $searchable = ['sp.title', 'sp.description'];

    protected string $defaultSort      = 'display_order';
    protected string $defaultDirection = 'ASC';

    protected function listSelect(): string
    {
        return 'sp.*, m.file_url AS image_url, m.alt_text AS image_alt';
    }

    protected function listJoins(): string
    {
        return 'LEFT JOIN media m ON m.id = sp.media_id AND m.deleted_at IS NULL';
    }

    protected function listFilters(Request $request): array
    {
        $conditions = [];
        $bindings   = [];

        $state = (string) $request->q('state', '');
        switch ($state) {
            case 'draft':
                $conditions[] = "sp.status = 'draft'";
                break;
            case 'inactive':
                $conditions[] = "sp.status = 'archived'";
                break;
            case 'scheduled':
                $conditions[] = "sp.status = 'published' AND sp.start_date IS NOT NULL AND sp.start_date > CURDATE()";
                break;
            case 'expired':
                $conditions[] = "sp.status = 'published' AND sp.end_date IS NOT NULL AND sp.end_date < CURDATE()";
                break;
            case 'active':
                $conditions[] = "sp.status = 'published'
                                 AND (sp.start_date IS NULL OR sp.start_date <= CURDATE())
                                 AND (sp.end_date   IS NULL OR sp.end_date   >= CURDATE())";
                break;
        }

        $featured = $request->qBool('featured');
        if ($featured !== null) {
            $conditions[] = 'sp.featured = ?';
            $bindings[]   = $featured ? 1 : 0;
        }

        if (($from = $request->q('date_from')) !== null) {
            $conditions[] = '(sp.end_date IS NULL OR sp.end_date >= ?)';
            $bindings[]   = substr((string) $from, 0, 10);
        }
        if (($to = $request->q('date_to')) !== null) {
            $conditions[] = '(sp.start_date IS NULL OR sp.start_date <= ?)';
            $bindings[]   = substr((string) $to, 0, 10);
        }

        return [$conditions, $bindings];
    }

    protected function decorate(array $row): array
    {
        $row['id']               = (int) $row['id'];
        $row['featured']         = (bool) $row['featured'];
        $row['price']            = $row['price'] === null ? null : (float) $row['price'];
        $row['compare_at_price'] = $row['compare_at_price'] === null ? null : (float) $row['compare_at_price'];

        $row = ScheduleResolver::decorate($row);
        $row['effective_status_label'] = ScheduleResolver::label($row['effective_status']);

        $row['price_label'] = $row['price_display']
            ?: ($row['price'] !== null ? ServiceRepository::formatMoney((float) $row['price']) : null);
        $row['compare_at_label'] = $row['compare_at_price'] !== null
            ? ServiceRepository::formatMoney((float) $row['compare_at_price'])
            : null;

        return $row;
    }

    public function stats(): array
    {
        $row = Database::fetchOne(
            "SELECT
                COUNT(*) AS total,
                SUM(status = 'published'
                    AND (start_date IS NULL OR start_date <= CURDATE())
                    AND (end_date   IS NULL OR end_date   >= CURDATE())) AS active,
                SUM(status = 'published' AND start_date IS NOT NULL AND start_date > CURDATE()) AS upcoming,
                SUM(status = 'published' AND end_date IS NOT NULL AND end_date < CURDATE())     AS expired
             FROM specials
            WHERE deleted_at IS NULL"
        ) ?? [];

        return [
            'total'    => (int) ($row['total'] ?? 0),
            'active'   => (int) ($row['active'] ?? 0),
            'upcoming' => (int) ($row['upcoming'] ?? 0),
            'expired'  => (int) ($row['expired'] ?? 0),
        ];
    }
}
