<?php
declare(strict_types=1);

namespace Mariah\Repositories;

use Mariah\Core\Database;
use Mariah\Core\Request;

final class BlogCategoryRepository extends BaseRepository
{
    protected string $table  = 'blog_categories';
    protected string $entity = 'blog topic';
    protected string $alias  = 'bc';

    protected array $fillable = ['name', 'slug', 'description', 'status', 'display_order'];

    protected array $sortable = [
        'name'          => 'bc.name',
        'status'        => 'bc.status',
        'display_order' => 'bc.display_order',
        'updated_at'    => 'bc.updated_at',
        'id'            => 'bc.id',
    ];

    protected array $searchable = ['bc.name', 'bc.description'];

    protected string $defaultSort      = 'display_order';
    protected string $defaultDirection = 'ASC';

    protected function listSelect(): string
    {
        return 'bc.*,
                (SELECT COUNT(*) FROM blog_posts bp
                  WHERE bp.category_id = bc.id AND bp.deleted_at IS NULL) AS posts_count';
    }

    protected function listFilters(Request $request): array
    {
        $status = (string) $request->q('status', '');
        if (in_array($status, ['active', 'inactive'], true)) {
            return [['bc.status = ?'], [$status]];
        }
        return [[], []];
    }

    protected function decorate(array $row): array
    {
        $row['id']          = (int) $row['id'];
        $row['posts_count'] = (int) ($row['posts_count'] ?? 0);
        return $row;
    }

    public function postCount(int $id): int
    {
        return (int) Database::fetchValue(
            'SELECT COUNT(*) FROM blog_posts WHERE category_id = ? AND deleted_at IS NULL',
            [$id]
        );
    }

    public function options(): array
    {
        return Database::fetchAll(
            'SELECT id, name, slug, status FROM blog_categories
              WHERE deleted_at IS NULL ORDER BY display_order ASC, name ASC'
        );
    }
}
