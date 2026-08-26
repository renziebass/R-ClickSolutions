<?php
declare(strict_types=1);

namespace Mariah\Controllers;

use Mariah\Core\HttpException;
use Mariah\Core\Request;
use Mariah\Core\Response;
use Mariah\Repositories\BaseRepository;
use Mariah\Repositories\BlogCategoryRepository;

final class BlogCategoryController extends ResourceController
{
    private BlogCategoryRepository $categories;

    public function __construct()
    {
        $this->categories = new BlogCategoryRepository();
    }

    protected function repository(): BaseRepository { return $this->categories; }
    protected function label(): string              { return 'Blog topic'; }
    protected function entityType(): string         { return 'blog_category'; }

    protected function rules(bool $isUpdate): array
    {
        $required = $isUpdate ? '' : 'required|';

        return [
            'name'          => $required . 'string|min:2|max:120',
            'slug'          => 'nullable|string|max:190',
            'description'   => 'nullable|string|max:2000',
            'status'        => 'nullable|in:active,inactive',
            'display_order' => 'nullable|int|min:0',
        ];
    }

    protected function fieldLabels(): array
    {
        return ['name' => 'Topic name'];
    }

    /**
     * A topic holding posts must not be removed — the posts would lose the
     * filter they are reached by on the website with no obvious cause.
     */
    protected function assertDeletable(array $row): void
    {
        $count = $this->categories->postCount((int) $row['id']);

        if ($count > 0) {
            throw HttpException::conflict(
                "\"{$row['name']}\" still holds {$count} post(s). Move them to another topic first."
            );
        }
    }

    public function options(Request $request): never
    {
        Response::json($this->categories->options());
    }
}
