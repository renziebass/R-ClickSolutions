<?php
declare(strict_types=1);

namespace Mariah\Controllers;

use Mariah\Core\HttpException;
use Mariah\Core\Request;
use Mariah\Core\Response;
use Mariah\Repositories\BaseRepository;
use Mariah\Repositories\BrandRepository;

final class BrandController extends ResourceController
{
    private BrandRepository $brands;

    public function __construct()
    {
        $this->brands = new BrandRepository();
    }

    protected function repository(): BaseRepository { return $this->brands; }
    protected function label(): string              { return 'Brand'; }
    protected function entityType(): string         { return 'brand'; }

    protected function rules(bool $isUpdate): array
    {
        $required = $isUpdate ? '' : 'required|';

        return [
            'name'          => $required . 'string|min:2|max:120',
            'slug'          => 'nullable|string|max:190',
            'tagline'       => 'nullable|string|max:190',
            'media_id'      => 'nullable|int|min:0',
            'status'        => 'nullable|in:active,inactive',
            'display_order' => 'nullable|int|min:0',
        ];
    }

    protected function fieldLabels(): array
    {
        return ['name' => 'Brand name', 'media_id' => 'Logo'];
    }

    protected function prepare(array $data, Request $request, ?array $existing): array
    {
        return $this->resolveMediaId($data);
    }

    protected function assertDeletable(array $row): void
    {
        $count = $this->brands->productCount((int) $row['id']);

        if ($count > 0) {
            throw HttpException::conflict(
                "\"{$row['name']}\" still has {$count} product(s). Reassign or delete them first."
            );
        }
    }

    public function options(Request $request): never
    {
        Response::json($this->brands->options());
    }
}
