<?php
declare(strict_types=1);

namespace Mariah\Controllers;

use Mariah\Core\Database;
use Mariah\Core\HttpException;
use Mariah\Core\Request;
use Mariah\Core\Response;
use Mariah\Repositories\BaseRepository;
use Mariah\Repositories\BrandRepository;
use Mariah\Repositories\ProductCategoryRepository;
use Mariah\Repositories\ProductRepository;

final class ProductController extends ResourceController
{
    private ProductRepository $products;

    public function __construct()
    {
        $this->products = new ProductRepository();
    }

    protected function repository(): BaseRepository { return $this->products; }
    protected function label(): string              { return 'Product'; }
    protected function entityType(): string         { return 'product'; }

    protected function rules(bool $isUpdate): array
    {
        $required = $isUpdate ? '' : 'required|';

        return [
            'brand_id'         => 'nullable|int|min:1',
            'category_id'      => 'nullable|int|min:1',
            'name'             => $required . 'string|min:2|max:190',
            'slug'             => 'nullable|string|max:190',
            'description'      => 'nullable|string|max:20000',
            'price'            => $required . 'numeric|min:0|max:100000',
            'compare_at_price' => 'nullable|numeric|min:0|max:100000',
            'media_id'         => 'nullable|int|min:0',
            'icon_key'         => 'nullable|string|max:40',
            'badge_label'      => 'nullable|string|max:60',
            'status'           => 'nullable|in:active,inactive',
            'featured'         => 'nullable|bool',
            'display_order'    => 'nullable|int|min:0',
        ];
    }

    protected function fieldLabels(): array
    {
        return [
            'brand_id'         => 'Brand',
            'category_id'      => 'Product category',
            'name'             => 'Product name',
            'price'            => 'Price',
            'compare_at_price' => 'Compare-at price',
            'badge_label'      => 'Badge',
            'media_id'         => 'Image',
        ];
    }

    protected function prepare(array $data, Request $request, ?array $existing): array
    {
        $data = $this->resolveMediaId($data);

        foreach ([
            'brand_id'    => ['product_brands', 'Please choose a valid brand.'],
            'category_id' => ['product_categories', 'Please choose a valid product category.'],
        ] as $field => [$table, $message]) {
            if (!array_key_exists($field, $data) || $data[$field] === null) {
                continue;
            }

            $exists = Database::fetchValue(
                "SELECT 1 FROM `{$table}` WHERE id = ? AND deleted_at IS NULL",
                [(int) $data[$field]]
            );

            if ($exists === null) {
                throw HttpException::validation([$field => $message]);
            }
        }

        $price   = $data['price']            ?? ($existing['price'] ?? null);
        $compare = $data['compare_at_price'] ?? ($existing['compare_at_price'] ?? null);

        if ($price !== null && $compare !== null && (float) $compare <= (float) $price) {
            throw HttpException::validation([
                'compare_at_price' => 'The compare-at price must be higher than the selling price.',
            ]);
        }

        return $data;
    }

    public function formOptions(Request $request): never
    {
        Response::json([
            'brands'     => (new BrandRepository())->options(),
            'categories' => (new ProductCategoryRepository())->options(),
            'icons'      => [
                ['key' => 'i-pad',    'label' => 'Pads'],
                ['key' => 'i-bottle', 'label' => 'Bottle / toner'],
                ['key' => 'i-pump',   'label' => 'Pump / cleanser'],
                ['key' => 'i-jar',    'label' => 'Jar / cream'],
            ],
        ]);
    }
}
