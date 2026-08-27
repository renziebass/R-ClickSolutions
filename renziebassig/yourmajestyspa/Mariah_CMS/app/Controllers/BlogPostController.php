<?php
declare(strict_types=1);

namespace Mariah\Controllers;

use Mariah\Core\Database;
use Mariah\Core\HttpException;
use Mariah\Core\Request;
use Mariah\Repositories\BaseRepository;
use Mariah\Repositories\BlogPostRepository;

final class BlogPostController extends ResourceController
{
    private BlogPostRepository $posts;

    public function __construct()
    {
        $this->posts = new BlogPostRepository();
    }

    protected function repository(): BaseRepository { return $this->posts; }
    protected function label(): string              { return 'Blog post'; }
    protected function entityType(): string         { return 'blog_post'; }
    protected function titleColumn(): string        { return 'title'; }
    protected function mediaFolder(): ?string       { return 'blog'; }

    protected function statusValues(): array
    {
        return ['published', 'draft', 'archived'];
    }

    protected function rules(bool $isUpdate): array
    {
        $required = $isUpdate ? '' : 'required|';

        return [
            'title'            => $required . 'string|min:2|max:190',
            'slug'             => 'nullable|string|max:190',
            'category_id'      => 'nullable|int|min:0',
            'excerpt'          => 'nullable|string|max:500',
            'content'          => $required . 'string|min:20|max:120000',
            'media_id'         => 'nullable|int|min:0',
            'author_name'      => 'nullable|string|max:120',
            'read_minutes'     => 'nullable|int|min:0|max:600',
            'tags'             => 'nullable|string|max:255',
            'meta_title'       => 'nullable|string|max:190',
            'meta_description' => 'nullable|string|max:300',
            'status'           => 'nullable|in:draft,published,archived',
            'published_at'     => 'nullable|string|max:25',
            'featured'         => 'nullable|bool',
            'display_order'    => 'nullable|int|min:0',
        ];
    }

    /** The post body. excerpt and meta_description stay plain — markup in an SEO description is worse than useless. */
    protected function richTextFields(): array
    {
        return ['content'];
    }

    protected function fieldLabels(): array
    {
        return [
            'title'            => 'Post title',
            'category_id'      => 'Topic',
            'excerpt'          => 'Excerpt',
            'content'          => 'Post content',
            'author_name'      => 'Author',
            'read_minutes'     => 'Reading time',
            'tags'             => 'Tags',
            'meta_title'       => 'SEO title',
            'meta_description' => 'SEO description',
            'published_at'     => 'Publish date',
            'media_id'         => 'Cover image',
        ];
    }

    protected function prepare(array $data, Request $request, ?array $existing): array
    {
        $data = $this->resolveMediaId($data);
        $data = $this->resolveCategoryId($data);

        if (array_key_exists('published_at', $data)) {
            $data['published_at'] = self::normaliseDateTime($data['published_at']);
        }

        $status    = $data['status']       ?? ($existing['status'] ?? 'draft');
        $publishAt = array_key_exists('published_at', $data)
            ? $data['published_at']
            : ($existing['published_at'] ?? null);

        // A post published with no date would sort to the bottom of the site
        // forever, so the moment of publishing becomes its date.
        if ($status === 'published' && ($publishAt === null || $publishAt === '')) {
            $data['published_at'] = date('Y-m-d H:i:s');
        }

        $content = $data['content'] ?? ($existing['content'] ?? null);

        // Both of these are conveniences, never overrides: what the editor
        // typed wins, and a field the caller did not send is left alone.
        if (!self::stillSet($data, $existing, 'excerpt')) {
            $data['excerpt'] = BlogPostRepository::deriveExcerpt($content);
        }

        if (!self::stillSet($data, $existing, 'read_minutes')) {
            $data['read_minutes'] = BlogPostRepository::estimateReadMinutes($content);
        }

        if (array_key_exists('tags', $data) && $data['tags'] !== null) {
            $data['tags'] = implode(', ', BlogPostRepository::splitTags((string) $data['tags'])) ?: null;
        }

        return $data;
    }

    /**
     * Whether a field will hold a real value after this write: either the
     * caller sent one, or it was left out of a partial update and the stored
     * row already has one.
     */
    private static function stillSet(array $data, ?array $existing, string $field): bool
    {
        $value = array_key_exists($field, $data)
            ? $data[$field]
            : ($existing[$field] ?? null);

        // "0" covers a reading time the driver hands back as a string.
        return $value !== null && $value !== '' && $value !== 0 && $value !== '0';
    }

    private function resolveCategoryId(array $data): array
    {
        if (!array_key_exists('category_id', $data)) {
            return $data;
        }

        if ($data['category_id'] === null || $data['category_id'] === '' || (int) $data['category_id'] === 0) {
            $data['category_id'] = null;
            return $data;
        }

        $id     = (int) $data['category_id'];
        $exists = Database::fetchValue(
            'SELECT id FROM blog_categories WHERE id = ? AND deleted_at IS NULL',
            [$id]
        );

        if ($exists === null) {
            throw HttpException::validation([
                'category_id' => 'That topic no longer exists. Pick another one.',
            ]);
        }

        $data['category_id'] = $id;

        return $data;
    }

    /**
     * Accepts what the browser's datetime-local and date inputs send
     * ("2026-08-25T14:30", "2026-08-25") and stores MySQL DATETIME.
     */
    private static function normaliseDateTime(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $text = str_replace('T', ' ', trim((string) $value));

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $text)) {
            return $text . ' 00:00:00';
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $text)) {
            return $text . ':00';
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $text)) {
            return $text;
        }

        throw HttpException::validation([
            'published_at' => 'Enter a valid publish date and time.',
        ]);
    }
}
