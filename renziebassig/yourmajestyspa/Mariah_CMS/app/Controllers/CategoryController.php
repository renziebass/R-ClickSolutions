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

    protected function rules(bool $isUpdate): array
    {
        $required = $isUpdate ? '' : 'required|';

        return [
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
        return ['name' => 'Category name', 'media_id' => 'Image'];
    }

    protected function prepare(array $data, Request $request, ?array $existing): array
    {
        return $this->resolveMediaId($data);
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
    }

    public function options(Request $request): never
    {
        Response::json($this->categories->options());
    }
}
