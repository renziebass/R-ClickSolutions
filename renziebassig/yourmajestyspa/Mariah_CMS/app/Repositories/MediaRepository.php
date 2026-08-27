<?php
declare(strict_types=1);

namespace Mariah\Repositories;

use Mariah\Core\Database;
use Mariah\Core\Request;
use Mariah\Services\MediaFolders;

final class MediaRepository extends BaseRepository
{
    protected string $table  = 'media';
    protected string $entity = 'image';
    protected string $alias  = 'm';

    // folder is not fillable: changing it moves a file on disk, so it goes
    // through MediaFiler::moveTo() rather than a bare column write.
    protected array $fillable = ['alt_text', 'title'];

    protected array $sortable = [
        'name'       => 'm.original_name',
        'size'       => 'm.file_size',
        'folder'     => 'm.folder',
        'created_at' => 'm.created_at',
        'id'         => 'm.id',
    ];

    protected array $searchable = ['m.original_name', 'm.alt_text', 'm.title'];

    protected string $defaultSort      = 'created_at';
    protected string $defaultDirection = 'DESC';

    protected function listSelect(): string
    {
        return "m.*, CONCAT(u.first_name, ' ', u.last_name) AS uploaded_by_name";
    }

    protected function listJoins(): string
    {
        return 'LEFT JOIN users u ON u.id = m.uploaded_by';
    }

    protected function listFilters(Request $request): array
    {
        $conditions = [];
        $bindings   = [];

        $mime = (string) $request->q('mime', '');
        if ($mime !== '' && in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            $conditions[] = 'm.mime_type = ?';
            $bindings[]   = $mime;
        }

        // An unrecognised slug is ignored rather than returning nothing, so a
        // stale bookmark shows the library instead of an empty grid.
        $folder = (string) $request->q('folder', '');
        if (MediaFolders::isValid($folder)) {
            $conditions[] = 'm.folder = ?';
            $bindings[]   = $folder;
        }

        return [$conditions, $bindings];
    }

    /**
     * Usage counts for the current page, injected by paginate()/find() so
     * decorate() never issues a query of its own. Counting per row would mean
     * eight queries per image — 480 for a 60-image page.
     *
     * @var array<int, int>
     */
    private array $usageCounts = [];

    protected function decorate(array $row): array
    {
        $id = (int) $row['id'];

        $row['id']           = $id;
        $row['file_size']    = (int) $row['file_size'];
        $row['width']        = $row['width']  === null ? null : (int) $row['width'];
        $row['height']       = $row['height'] === null ? null : (int) $row['height'];
        $row['size_label']   = self::formatBytes((int) $row['file_size']);
        $row['usage_count']  = $this->usageCounts[$id] ?? 0;
        $row['folder']       = MediaFolders::normalize($row['folder'] ?? null);
        $row['folder_label'] = MediaFolders::label($row['folder']);

        return $row;
    }

    public function paginate(Request $request): array
    {
        $result = parent::paginate($request);

        // Resolve usage for exactly the ids on this page, then fill it in.
        $ids = array_map(static fn (array $row): int => (int) $row['id'], $result['rows']);
        $this->usageCounts = self::usageCountsFor($ids);

        $result['rows'] = array_map(function (array $row): array {
            $row['usage_count'] = $this->usageCounts[(int) $row['id']] ?? 0;
            return $row;
        }, $result['rows']);

        return $result;
    }

    public function find(int $id, bool $withDeleted = false): ?array
    {
        $this->usageCounts = self::usageCountsFor([$id]);
        return parent::find($id, $withDeleted);
    }

    /**
     * One query per referencing table for the whole page, rather than per row.
     *
     * @param int[] $ids
     * @return array<int, int>
     */
    public static function usageCountsFor(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if ($ids === []) {
            return [];
        }

        $placeholders = implode(', ', array_fill(0, count($ids), '?'));
        $counts       = array_fill_keys($ids, 0);

        $tables = [
            'services', 'service_categories', 'promotions', 'specials',
            'products', 'product_brands', 'gift_cards', 'blog_posts',
        ];

        foreach ($tables as $table) {
            // $table comes from this fixed list, never from input.
            $rows = Database::fetchAll(
                "SELECT media_id, COUNT(*) AS n FROM `{$table}`
                  WHERE media_id IN ({$placeholders}) AND deleted_at IS NULL
                  GROUP BY media_id",
                $ids
            );

            foreach ($rows as $row) {
                $counts[(int) $row['media_id']] += (int) $row['n'];
            }
        }

        $galleryRows = Database::fetchAll(
            "SELECT media_id, COUNT(*) AS n FROM service_images
              WHERE media_id IN ({$placeholders})
              GROUP BY media_id",
            $ids
        );

        foreach ($galleryRows as $row) {
            $counts[(int) $row['media_id']] += (int) $row['n'];
        }

        return $counts;
    }

    public static function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        }
        if ($bytes < 1_048_576) {
            return round($bytes / 1024, 1) . ' KB';
        }
        return round($bytes / 1_048_576, 2) . ' MB';
    }

    /**
     * Every folder with its photo count, in the order MediaFolders declares —
     * including the empty ones, so the library's folder strip does not
     * rearrange itself as photos move around.
     *
     * @return array<int, array{slug: string, label: string, count: int}>
     */
    public function folders(): array
    {
        $counts = [];
        foreach (Database::fetchAll(
            'SELECT folder, COUNT(*) AS n FROM media WHERE deleted_at IS NULL GROUP BY folder'
        ) as $row) {
            $counts[(string) $row['folder']] = (int) $row['n'];
        }

        $folders = [];
        foreach (MediaFolders::all() as $slug => $label) {
            $folders[] = [
                'slug'  => $slug,
                'label' => $label,
                'count' => $counts[$slug] ?? 0,
            ];
        }

        return $folders;
    }

    public function stats(): array
    {
        $row = Database::fetchOne(
            'SELECT COUNT(*) AS total, COALESCE(SUM(file_size), 0) AS bytes
               FROM media WHERE deleted_at IS NULL'
        ) ?? [];

        return [
            'total'       => (int) ($row['total'] ?? 0),
            'total_bytes' => (int) ($row['bytes'] ?? 0),
            'total_label' => self::formatBytes((int) ($row['bytes'] ?? 0)),
        ];
    }
}
