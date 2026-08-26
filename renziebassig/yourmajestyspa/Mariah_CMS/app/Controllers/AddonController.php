<?php
declare(strict_types=1);

namespace Mariah\Controllers;

use Mariah\Core\Request;
use Mariah\Repositories\AddonRepository;
use Mariah\Repositories\BaseRepository;
use Mariah\Services\ServiceCsvSchema;

/**
 * Per-category add-ons. A plain resource — no slug, because an add-on is only
 * ever listed alongside its category and never addressed by URL.
 */
final class AddonController extends ResourceController
{
    private AddonRepository $addons;

    public function __construct()
    {
        $this->addons = new AddonRepository();
    }

    protected function repository(): BaseRepository { return $this->addons; }
    protected function label(): string              { return 'Add-on'; }
    protected function entityType(): string         { return 'addon'; }
    protected function hasSlug(): bool              { return false; }

    protected function rules(bool $isUpdate): array
    {
        $required = $isUpdate ? '' : 'required|';

        return [
            'category_id'      => $required . 'int|min:1',
            'name'             => $required . 'string|min:2|max:150',
            'description'      => 'nullable|string|max:500',
            'price'            => $required . 'numeric|min:0|max:100000',
            'duration_minutes' => 'nullable|int|min:0|max:1440',
            'status'           => 'nullable|in:active,inactive',
            'display_order'    => 'nullable|int|min:0',
        ];
    }

    protected function fieldLabels(): array
    {
        return [
            'category_id'      => 'Category',
            'name'             => 'Add-on name',
            'price'            => 'Additional price',
            'duration_minutes' => 'Extra time',
        ];
    }

    protected function prepare(array $data, Request $request, ?array $existing): array
    {
        if (array_key_exists('category_id', $data)) {
            // Reused so an add-on cannot be attached to a category the service
            // form would reject.
            ServiceCsvSchema::assertCategoryExists((int) $data['category_id']);
        }

        return $data;
    }
}
