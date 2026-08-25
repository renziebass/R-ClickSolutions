<?php
declare(strict_types=1);

namespace Mariah\Controllers;

use Mariah\Core\Database;
use Mariah\Core\HttpException;
use Mariah\Core\Request;
use Mariah\Core\Response;
use Mariah\Core\Validator;
use Mariah\Repositories\MediaRepository;
use Mariah\Services\AuditLogger;
use Mariah\Services\MediaService;

final class MediaController
{
    private MediaRepository $media;

    public function __construct()
    {
        $this->media = new MediaRepository();
    }

    public function index(Request $request): never
    {
        $result = $this->media->paginate($request);
        Response::json($result['rows'], 200, $result['meta']);
    }

    public function show(Request $request, array $args): never
    {
        Response::json($this->media->findOrFail($this->idFrom($args)));
    }

    /** multipart/form-data upload: field "file", optional alt_text and title. */
    public function store(Request $request): never
    {
        if (!isset($_FILES['file'])) {
            throw HttpException::validation(
                ['file' => 'Please choose an image to upload.'],
                'No file was received.'
            );
        }

        $altText = $request->input('alt_text');
        $title   = $request->input('title');

        $record = MediaService::store(
            $_FILES['file'],
            is_string($altText) && $altText !== '' ? $altText : null,
            is_string($title) && $title !== '' ? $title : null
        );

        Response::created($this->media->findOrFail((int) $record['id']));
    }

    /** Metadata only — replacing the binary means uploading a new image. */
    public function update(Request $request, array $args): never
    {
        $id     = $this->idFrom($args);
        $before = $this->media->findOrFail($id);

        $data = Validator::make($request->body())->validate([
            'alt_text' => 'nullable|string|max:255',
            'title'    => 'nullable|string|max:190',
        ], ['alt_text' => 'Alt text']);

        $this->media->update($id, $data);

        $after = $this->media->findOrFail($id);

        AuditLogger::record(
            'updated',
            'media',
            $id,
            "Updated image details for \"{$after['original_name']}\"",
            AuditLogger::diff($before, $after, ['usage_count', 'size_label'])
        );

        Response::json($after);
    }

    public function destroy(Request $request, array $args): never
    {
        $id = $this->idFrom($args);

        // MediaService refuses while the image is still referenced, and removes
        // the file from disk once the row is soft-deleted.
        MediaService::delete($id);

        Response::json([
            'id'      => $id,
            'deleted' => true,
            'message' => 'The image was deleted.',
        ]);
    }

    /** Where an image is used, so staff can see what a delete would break. */
    public function usage(Request $request, array $args): never
    {
        $id = $this->idFrom($args);
        $this->media->findOrFail($id);

        $usage = [];

        foreach ([
            'services'           => ['service',          'name'],
            'service_categories' => ['category',         'name'],
            'promotions'         => ['promotion',        'title'],
            'specials'           => ['special',          'title'],
            'products'           => ['product',          'name'],
            'product_brands'     => ['brand',            'name'],
            'gift_cards'         => ['gift card',        'title'],
        ] as $table => [$type, $titleColumn]) {
            $rows = Database::fetchAll(
                "SELECT id, `{$titleColumn}` AS title FROM `{$table}`
                  WHERE media_id = ? AND deleted_at IS NULL",
                [$id]
            );

            foreach ($rows as $row) {
                $usage[] = [
                    'type'  => $type,
                    'id'    => (int) $row['id'],
                    'title' => $row['title'],
                ];
            }
        }

        $galleryRows = Database::fetchAll(
            'SELECT s.id, s.name AS title
               FROM service_images si
               JOIN services s ON s.id = si.service_id
              WHERE si.media_id = ? AND s.deleted_at IS NULL',
            [$id]
        );

        foreach ($galleryRows as $row) {
            $usage[] = [
                'type'  => 'service gallery',
                'id'    => (int) $row['id'],
                'title' => $row['title'],
            ];
        }

        Response::json(['count' => count($usage), 'items' => $usage]);
    }

    private function idFrom(array $args): int
    {
        $id = $args['id'] ?? '';

        if (!is_string($id) || !preg_match('/^\d+$/', $id) || (int) $id < 1) {
            throw HttpException::badRequest('Invalid image id.');
        }

        return (int) $id;
    }
}
