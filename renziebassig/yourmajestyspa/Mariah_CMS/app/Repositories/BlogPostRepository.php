<?php
declare(strict_types=1);

namespace Mariah\Repositories;

use Mariah\Core\Database;
use Mariah\Core\Request;
use Mariah\Services\ScheduleResolver;

final class BlogPostRepository extends BaseRepository
{
    protected string $table  = 'blog_posts';
    protected string $entity = 'blog post';
    protected string $alias  = 'bp';

    protected array $fillable = [
        'category_id', 'title', 'slug', 'excerpt', 'content', 'media_id',
        'author_name', 'read_minutes', 'tags', 'meta_title', 'meta_description',
        'status', 'published_at', 'featured', 'display_order',
    ];

    protected array $sortable = [
        'title'         => 'bp.title',
        'status'        => 'bp.status',
        'published_at'  => 'bp.published_at',
        'featured'      => 'bp.featured',
        'display_order' => 'bp.display_order',
        'updated_at'    => 'bp.updated_at',
        'created_at'    => 'bp.created_at',
        'id'            => 'bp.id',
    ];

    protected array $searchable = ['bp.title', 'bp.excerpt', 'bp.content', 'bp.tags'];

    // Newest first: an editor opening the Blog screen is looking for the most
    // recent post, not the one sitting lowest in a hand-set order.
    protected string $defaultSort      = 'published_at';
    protected string $defaultDirection = 'DESC';

    protected function listSelect(): string
    {
        return 'bp.*, c.name AS category_name, c.slug AS category_slug,
                m.file_url AS image_url, m.alt_text AS image_alt';
    }

    protected function listJoins(): string
    {
        return 'LEFT JOIN blog_categories c ON c.id = bp.category_id AND c.deleted_at IS NULL
                LEFT JOIN media m ON m.id = bp.media_id AND m.deleted_at IS NULL';
    }

    protected function listFilters(Request $request): array
    {
        $conditions = [];
        $bindings   = [];

        $state = (string) $request->q('state', '');
        switch ($state) {
            case 'draft':
                $conditions[] = "bp.status = 'draft'";
                break;
            case 'inactive':
                $conditions[] = "bp.status = 'archived'";
                break;
            case 'scheduled':
                $conditions[] = "bp.status = 'published'
                                 AND bp.published_at IS NOT NULL AND bp.published_at > NOW()";
                break;
            case 'active':
                $conditions[] = "bp.status = 'published'
                                 AND (bp.published_at IS NULL OR bp.published_at <= NOW())";
                break;
        }

        $categoryId = $request->q('category_id');
        if ($categoryId !== null && $categoryId !== '') {
            $conditions[] = 'bp.category_id = ?';
            $bindings[]   = (int) $categoryId;
        }

        $featured = $request->qBool('featured');
        if ($featured !== null) {
            $conditions[] = 'bp.featured = ?';
            $bindings[]   = $featured ? 1 : 0;
        }

        $from = $request->q('date_from');
        if ($from !== null && $from !== '') {
            $conditions[] = '(bp.published_at IS NULL OR bp.published_at >= ?)';
            $bindings[]   = substr((string) $from, 0, 10) . ' 00:00:00';
        }

        $to = $request->q('date_to');
        if ($to !== null && $to !== '') {
            $conditions[] = '(bp.published_at IS NULL OR bp.published_at <= ?)';
            $bindings[]   = substr((string) $to, 0, 10) . ' 23:59:59';
        }

        return [$conditions, $bindings];
    }

    protected function decorate(array $row): array
    {
        $row['id']          = (int) $row['id'];
        $row['featured']    = (bool) $row['featured'];
        $row['category_id'] = $row['category_id'] === null ? null : (int) $row['category_id'];
        $row['media_id']    = $row['media_id'] === null ? null : (int) $row['media_id'];

        $row['read_minutes'] = $row['read_minutes'] === null ? null : (int) $row['read_minutes'];
        $row['read_label']   = $row['read_minutes'] === null
            ? null
            : $row['read_minutes'] . ' min read';

        $row['effective_status'] = ScheduleResolver::resolvePublished(
            $row['status'] ?? null,
            $row['published_at'] ?? null
        );
        $row['effective_status_label'] = ScheduleResolver::label($row['effective_status']);

        $row['tag_list'] = self::splitTags($row['tags'] ?? null);

        return $row;
    }

    /** "Self care, Facials , " becomes ["Self care", "Facials"]. */
    public static function splitTags(?string $tags): array
    {
        if ($tags === null || trim($tags) === '') {
            return [];
        }

        return array_values(array_filter(
            array_map('trim', explode(',', $tags)),
            static fn (string $tag): bool => $tag !== ''
        ));
    }

    /**
     * Reading time at 200 words per minute — the rate publishers commonly use
     * for general-audience prose. Never less than a minute.
     */
    public static function estimateReadMinutes(?string $content): ?int
    {
        if ($content === null || trim($content) === '') {
            return null;
        }

        $words = preg_split('/\s+/u', trim($content), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return max(1, (int) ceil(count($words) / 200));
    }

    /** First paragraph of a post, trimmed to a card-sized teaser. */
    public static function deriveExcerpt(?string $content, int $limit = 220): ?string
    {
        if ($content === null || trim($content) === '') {
            return null;
        }

        $paragraphs = preg_split('/\R{2,}/u', trim($content)) ?: [];
        $paragraph  = trim((string) preg_replace('/\s+/u', ' ', (string) ($paragraphs[0] ?? '')));

        if ($paragraph === '' || mb_strlen($paragraph) <= $limit) {
            return $paragraph === '' ? null : $paragraph;
        }

        $cut   = mb_substr($paragraph, 0, $limit);
        $space = mb_strrpos($cut, ' ');

        return rtrim($space === false ? $cut : mb_substr($cut, 0, $space), ' ,.;:') . '…';
    }

    public function stats(): array
    {
        $row = Database::fetchOne(
            "SELECT
                COUNT(*) AS total,
                SUM(status = 'published' AND (published_at IS NULL OR published_at <= NOW())) AS published,
                SUM(status = 'published' AND published_at IS NOT NULL AND published_at > NOW()) AS scheduled,
                SUM(status = 'draft')    AS draft,
                SUM(status = 'archived') AS archived
             FROM blog_posts
            WHERE deleted_at IS NULL"
        ) ?? [];

        return [
            'total'     => (int) ($row['total'] ?? 0),
            'published' => (int) ($row['published'] ?? 0),
            'scheduled' => (int) ($row['scheduled'] ?? 0),
            'draft'     => (int) ($row['draft'] ?? 0),
            'archived'  => (int) ($row['archived'] ?? 0),
        ];
    }

    public function recent(int $limit = 5): array
    {
        $limit = max(1, min(20, $limit));

        return Database::fetchAll(
            "SELECT bp.id, bp.title, bp.slug, bp.status, bp.published_at, bp.updated_at,
                    c.name AS category_name,
                    CONCAT(u.first_name, ' ', u.last_name) AS updated_by_name
               FROM blog_posts bp
               LEFT JOIN blog_categories c ON c.id = bp.category_id
               LEFT JOIN users u ON u.id = bp.updated_by
              WHERE bp.deleted_at IS NULL
              ORDER BY bp.updated_at DESC
              LIMIT {$limit}"
        );
    }
}
