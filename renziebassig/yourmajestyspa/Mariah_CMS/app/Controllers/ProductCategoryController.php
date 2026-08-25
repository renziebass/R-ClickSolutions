<?php
declare(strict_types=1);

namespace Mariah\Controllers;

use Mariah\Core\HttpException;
use Mariah\Core\Request;
use Mariah\Core\Response;
use Mariah\Repositories\BaseRepository;
use Mariah\Repositories\ProductCategoryRepository;

final class ProductCategoryController extends ResourceController
{
    private ProductCategoryRepository $categories;

    public function __construct()
    {
        $this->categories = new ProductCategoryRepository();
    }

    protected function repository(): BaseRepository { return $this->categories; }
    protected function label(): string              { return 'Product category'; }
    protected function entityType(): string         { return 'product_category'; }

    protected function rules(bool $isUpdate): array
    {
        $required = $isUpdate ? '' : 'required|';

        return [
            'name'          => $required . 'string|min:2|max:120',
            'slug'          => 'nullable|string|max:190',
            'description'   => 'nullable|string|max:500',
            'status'        => 'nullable|in:active,inactive',
            'display_order' => 'nullable|int|min:0',
        ];
    }

    protected function assertDeletable(array $row): void
    {
        $count = $this->categories->productCount((int) $row['id']);

        if ($count > 0) {
            throw HttpException::conflict(
                "\"{$row['name']}\" still contains {$count} product(s). Move or delete them first."
            );
        }
    }

    public function options(Request $request): never
    {
        Response::json($this->categories->options());
    }
}
