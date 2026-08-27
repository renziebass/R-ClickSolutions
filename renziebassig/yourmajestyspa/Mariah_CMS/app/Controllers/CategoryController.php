<?php
declare(strict_types=1);

namespace Mariah\Controllers;

use Mariah\Core\HttpException;
use Mariah\Core\Request;
use Mariah\Core\Response;
use Mariah\Repositories\BaseRepository;
use Mariah\Repositories\CategoryRepository;

final class CategoryController extends ResourceController
{
    private CategoryRepository $categories;

    public function __construct()
    {
        $this->categories = new CategoryRepository();
    }

    protected function repository(): BaseRepository { return $this->categories; }
    protected function label(): string              { return 'Category'; }
    protected function entityType(): string         { return 'category'; }
    protected function mediaFolder(): ?string       { return 'categories'; }

    protected function rules(bool $isUpdate): array
    {
        $required = $isUpdate ? '' : 'required|';

        return [
            'parent_id'     => 'nullable|int|min:0',
            'name'          => $required . 'string|min:2|max:120',
            'slug'          => 'nullable|string|max:190',
            'description'   => 'nullable|string|max:2000',
            'icon_key'      => 'nullable|string|max:40',
            'media_id'      => 'nullable|int|min:0',
            'status'        => 'nullable|in:active,inactive',
            'display_order' => 'nullable|int|min:0',
        ];
    }

    protected function fieldLabels(): array
    {
        return [
            'name'      => 'Category name',
            'media_id'  => 'Image',
            'parent_id' => 'Parent category',
        ];
    }

    protected function prepare(array $data, Request $request, ?array $existing): array
    {
        $data = $this->resolveMediaId($data);

        if (array_key_exists('parent_id', $data)) {
            // 0 arrives from a cleared <select>; both mean "top level".
            $data['parent_id'] = ((int) $data['parent_id']) ?: null;

            if ($data['parent_id'] !== null) {
                $this->assertUsableParent((int) $data['parent_id'], $existing);
            }
        }

        return $data;
    }

    /**
     * The hierarchy is deliberately two levels deep. Anything deeper has no
     * way to render — the public site draws top-level categories as tabs and
     * sub-categories as headings inside them, and there is no third place to
     * put a grandchild.
     */
    private function assertUsableParent(int $parentId, ?array $existing): void
    {
        $selfId = $existing === null ? null : (int) $existing['id'];

        if ($selfId !== null && $parentId === $selfId) {
            throw HttpException::validation([
                'parent_id' => 'A category cannot be its own parent.',
            ]);
        }

        $parent = $this->categories->find($parentId);

        if ($parent === null) {
            throw HttpException::validation([
                'parent_id' => 'That parent category no longer exists.',
            ]);
        }

        if ($parent['parent_id'] !== null) {
            throw HttpException::validation([
                'parent_id' => "\"{$parent['name']}\" is already a sub-category. "
                    . 'Categories can only be nested one level deep.',
            ]);
        }

        // Moving a parent under someone else would push its own children to a
        // third level without ever touching those child records.
        if ($selfId !== null && $this->categories->childCount($selfId) > 0) {
            throw HttpException::validation([
                'parent_id' => 'This category has sub-categories of its own, so it cannot '
                    . 'become a sub-category. Move or remove them first.',
            ]);
        }
    }

    /**
     * A category holding services must not be removed — the services would
     * vanish from the public site with no obvious cause.
     */
    protected function assertDeletable(array $row): void
    {
        $count = $this->categories->serviceCount((int) $row['id']);

        if ($count > 0) {
            throw HttpException::conflict(
                "\"{$row['name']}\" still contains {$count} service(s). Move or delete them first."
            );
        }

        $children = $this->categories->childCount((int) $row['id']);

        if ($children > 0) {
            $noun = $children === 1 ? 'sub-category' : 'sub-categories';
            throw HttpException::conflict(
                "\"{$row['name']}\" still has {$children} {$noun}. Move or delete them first."
            );
        }
    }

    public function options(Request $request): never
    {
        Response::json($this->categories->options());
    }
}
