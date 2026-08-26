<?php
declare(strict_types=1);

namespace Mariah\Repositories;

use Mariah\Core\Database;
use Mariah\Core\Request;

final class ServiceRepository extends BaseRepository
{
    protected string $table  = 'services';
    protected string $entity = 'service';
    protected string $alias  = 's';

    protected array $fillable = [
        'category_id', 'name', 'slug', 'short_description', 'description',
        'price', 'price_display', 'promo_price',
        'duration_minutes', 'duration_display',
        'icon_key', 'booking_url', 'media_id',
        'status', 'featured', 'most_loved_rank', 'display_order',
    ];

    protected array $sortable = [
        'name'          => 's.name',
        'price'         => 's.price',
        'duration'      => 's.duration_minutes',
        'category'      => 'c.name',
        'status'        => 's.status',
        'featured'      => 's.featured',
        'display_order' => 's.display_order',
        'updated_at'    => 's.updated_at',
        'created_at'    => 's.created_at',
        'id'            => 's.id',
    ];

    protected array $searchable = ['s.name', 's.short_description', 's.description'];

    protected string $defaultSort      = 'display_order';
    protected string $defaultDirection = 'ASC';

    protected function listSelect(): string
    {
        return 's.*,
                c.name AS category_name,
                c.slug AS category_slug,
                m.file_url AS image_url,
                m.alt_text AS image_alt';
    }

    protected function listJoins(): string
    {
        return 'LEFT JOIN service_categories c ON c.id = s.category_id
                LEFT JOIN media m ON m.id = s.media_id AND m.deleted_at IS NULL';
    }

    protected function listFilters(Request $request): array
    {
        $conditions = [];
        $bindings   = [];

        if (($categoryId = $request->q('category_id')) !== null && is_numeric($categoryId)) {
            $conditions[] = 's.category_id = ?';
            $bindings[]   = (int) $categoryId;
        }

        $status = (string) $request->q('status', '');
        if (in_array($status, ['active', 'inactive'], true)) {
            $conditions[] = 's.status = ?';
            $bindings[]   = $status;
        }

        $featured = $request->qBool('featured');
        if ($featured !== null) {
            $conditions[] = 's.featured = ?';
            $bindings[]   = $featured ? 1 : 0;
        }

        return [$conditions, $bindings];
    }

    protected function decorate(array $row): array
    {
        $row['id']              = (int) $row['id'];
        $row['category_id']     = (int) $row['category_id'];
        $row['featured']        = (bool) $row['featured'];
        $row['price']           = (float) $row['price'];
        $row['promo_price']     = $row['promo_price'] === null ? null : (float) $row['promo_price'];
        $row['most_loved_rank'] = $row['most_loved_rank'] === null ? null : (int) $row['most_loved_rank'];

        // What the public page should print, display override winning.
        $row['price_label']    = $row['price_display'] ?: self::formatMoney((float) $row['price']);
        $row['duration_label'] = $row['duration_display']
            ?: ($row['duration_minutes'] !== null ? $row['duration_minutes'] . ' min' : null);

        return $row;
    }

    public static function formatMoney(float $amount): string
    {
        return '$' . rtrim(rtrim(number_format($amount, 2, '.', ','), '0'), '.');
    }

    /**
     * `most_loved_rank` is a 1-3 podium: assigning a rank must clear it from
     * whichever service held it, otherwise the public page renders duplicates.
     */
    public function claimMostLovedRank(?int $rank, int $serviceId): void
    {
        if ($rank === null) {
            return;
        }

        Database::run(
            'UPDATE services SET most_loved_rank = NULL WHERE most_loved_rank = ? AND id <> ?',
            [$rank, $serviceId]
        );
    }

    /** Gallery images, primary first. */
    public function images(int $serviceId): array
    {
        return Database::fetchAll(
            'SELECT si.id, si.media_id, si.display_order, si.is_primary,
                    COALESCE(si.alt_text, m.alt_text) AS alt_text,
                    m.file_url, m.file_name, m.width, m.height
               FROM service_images si
               JOIN media m ON m.id = si.media_id
              WHERE si.service_id = ? AND m.deleted_at IS NULL
              ORDER BY si.is_primary DESC, si.display_order ASC, si.id ASC',
            [$serviceId]
        );
    }

    /**
     * Replaces the gallery with the given media ids. The first becomes primary
     * and is mirrored onto services.media_id so listing queries avoid a join.
     *
     * @param int[] $mediaIds
     */
    public function syncImages(int $serviceId, array $mediaIds, ?string $altText = null): void
    {
        Database::transaction(function () use ($serviceId, $mediaIds, $altText): void {
            Database::run('DELETE FROM service_images WHERE service_id = ?', [$serviceId]);

            $order = 0;
            foreach (array_values(array_unique(array_map('intval', $mediaIds))) as $mediaId) {
                if ($mediaId <= 0) {
                    continue;
                }

                Database::run(
                    'INSERT INTO service_images
                        (service_id, media_id, alt_text, display_order, is_primary, uploaded_by)
                     VALUES (?, ?, ?, ?, ?, ?)',
                    [
                        $serviceId,
                        $mediaId,
                        $order === 0 ? $altText : null,
                        $order,
                        $order === 0 ? 1 : 0,
                        \Mariah\Core\Auth::id(),
                    ]
                );
                $order++;
            }

            $primary = Database::fetchValue(
                'SELECT media_id FROM service_images
                  WHERE service_id = ? ORDER BY is_primary DESC, display_order ASC LIMIT 1',
                [$serviceId]
            );

            Database::run('UPDATE services SET media_id = ? WHERE id = ?', [$primary, $serviceId]);
        });
    }

    /** Dashboard counters. */
    /**
     * Every service in a batch of slugs, keyed by slug, in one query.
     *
     * Soft-deleted rows are included on purpose: `uq_services_slug` spans them,
     * so an importer that ignored them would collide on insert. Resolving 500
     * slugs one at a time is the difference between a two-second import and a
     * timeout on shared hosting.
     *
     * @param string[] $slugs
     * @return array<string, array> slug => row
     */
    public function findBySlugs(array $slugs): array
    {
        $slugs = array_values(array_unique(array_filter($slugs)));

        if ($slugs === []) {
            return [];
        }

        $placeholders = implode(', ', array_fill(0, count($slugs), '?'));

        $rows = Database::fetchAll(
            "SELECT * FROM services WHERE slug IN ({$placeholders})",
            $slugs
        );

        $bySlug = [];
        foreach ($rows as $row) {
            $bySlug[(string) $row['slug']] = $row;
        }

        return $bySlug;
    }

    /**
     * Which service currently holds each Most Loved rank, so an import can warn
     * before taking one away.
     *
     * @return array<int, array{id:int, name:string}> rank => holder
     */
    public function mostLovedHolders(): array
    {
        $rows = Database::fetchAll(
            'SELECT id, name, most_loved_rank
               FROM services
              WHERE most_loved_rank IS NOT NULL AND deleted_at IS NULL'
        );

        $holders = [];
        foreach ($rows as $row) {
            $holders[(int) $row['most_loved_rank']] = [
                'id'   => (int) $row['id'],
                'name' => (string) $row['name'],
            ];
        }

        return $holders;
    }

    public function stats(): array
    {
        $row = Database::fetchOne(
            "SELECT
                COUNT(*)                                          AS total,
                SUM(status = 'active')                            AS active,
                SUM(status = 'inactive')                          AS inactive,
                SUM(featured = 1 AND status = 'active')           AS featured
             FROM services
            WHERE deleted_at IS NULL"
        ) ?? [];

        return [
            'total'    => (int) ($row['total'] ?? 0),
            'active'   => (int) ($row['active'] ?? 0),
            'inactive' => (int) ($row['inactive'] ?? 0),
            'featured' => (int) ($row['featured'] ?? 0),
        ];
    }

    public function recentlyUpdated(int $limit = 5): array
    {
        $limit = max(1, min(20, $limit));

        return Database::fetchAll(
            "SELECT s.id, s.name, s.slug, s.status, s.price, s.price_display, s.updated_at,
                    c.name AS category_name,
                    CONCAT(u.first_name, ' ', u.last_name) AS updated_by_name
               FROM services s
               LEFT JOIN service_categories c ON c.id = s.category_id
               LEFT JOIN users u ON u.id = s.updated_by
              WHERE s.deleted_at IS NULL
              ORDER BY s.updated_at DESC
              LIMIT {$limit}"
        );
    }
}
