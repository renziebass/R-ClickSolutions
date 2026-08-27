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
use Mariah\Services\MediaFiler;
use Mariah\Services\MediaFolders;
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

    /** The folder list with counts, for the library's folder navigation. */
    public function folders(Request $request): never
    {
        Response::json($this->media->folders());
    }

    /**
     * multipart/form-data upload: field "file", optional alt_text, title and
     * folder.
     *
     * An upload started from a content form names its folder, so the photo
     * lands where it belongs and never needs moving. A plain library upload
     * names none and goes to "unsorted", to be filed when something uses it.
     */
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
        $folder  = $request->input('folder');

        $record = MediaService::store(
            $_FILES['file'],
            is_string($altText) && $altText !== '' ? $altText : null,
            is_string($title) && $title !== '' ? $title : null,
            is_string($folder) && MediaFolders::isValid($folder) ? $folder : null
        );

        Response::created($this->media->findOrFail((int) $record['id']));
    }

    /**
     * Files every unsorted photo by what uses it, then walks the files on disk
     * to match.
     *
     * This is what reorganises a library uploaded before folders existed, and
     * the repair when filing has drifted. Idempotent, so a second run reports
     * nothing to do.
     */
    public function reorganize(Request $request): never
    {
        $result = MediaFiler::reorganize();

        $parts = [];
        if ($result['filed'] > 0) {
            $parts[] = "filed {$result['filed']} image(s) by where they are used";
        }
        if ($result['moved'] > 0) {
            $parts[] = "moved {$result['moved']} file(s) on the server";
        }

        $message = $parts === []
            ? 'Every image is already in the right folder.'
            : ucfirst(implode(' and ', $parts)) . '.';

        if ($result['failed'] > 0) {
            $message .= " {$result['failed']} file(s) could not be moved and were left where they are.";
        }

        Response::json($result + ['message' => $message]);
    }

    /**
     * Metadata and folder — replacing the binary means uploading a new image.
     *
     * A folder change is the manual override on the first-use-wins rule, for
     * the photo that filed itself somewhere unhelpful. It moves the file, so
     * it goes through MediaFiler rather than the repository's column write.
     */
    public function update(Request $request, array $args): never
    {
        $id     = $this->idFrom($args);
        $before = $this->media->findOrFail($id);

        $data = Validator::make($request->body())->validate([
            'alt_text' => 'nullable|string|max:255',
            'title'    => 'nullable|string|max:190',
            'folder'   => 'nullable|string|max:40',
        ], ['alt_text' => 'Alt text']);

        $folder = $data['folder'] ?? null;
        unset($data['folder']);

        if ($folder !== null && $folder !== '' && $folder !== $before['folder']) {
            if (!MediaFolders::isValid($folder)) {
                throw HttpException::validation(['folder' => 'That is not a media library folder.']);
            }

            $reason = MediaFiler::moveTo($id, $folder);
            if ($reason !== null) {
                throw HttpException::conflict($reason);
            }
        }

        $this->media->update($id, $data);

        $after = $this->media->findOrFail($id);

        AuditLogger::record(
            'updated',
            'media',
            $id,
            "Updated image details for \"{$after['original_name']}\"",
            // file_path/file_url move with the folder, and folder_label is
            // derived from it — one "folder" line says all four.
            AuditLogger::diff($before, $after, [
                'usage_count', 'size_label', 'folder_label', 'file_path', 'file_url',
            ])
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
            'blog_posts'         => ['blog post',        'title'],
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
