<?php
declare(strict_types=1);

namespace Mariah\Services;

use Mariah\Core\Auth;
use Mariah\Core\Database;
use Mariah\Core\Env;
use Mariah\Core\HttpException;
use Mariah\Core\Logger;

/**
 * Image upload and storage.
 *
 * A file is accepted only if it passes all three checks: extension allowlist,
 * real MIME sniffed by finfo, and a successful getimagesize(). Any one alone
 * is bypassable. Stored names are random, so a crafted filename cannot control
 * the path, and storage/uploads/.htaccess disables script execution.
 */
final class MediaService
{
    private const ALLOWED = [
        'image/jpeg' => 'jpg',
        'image/pjpeg'=> 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

    /** @param array $file one entry from $_FILES */
    public static function store(array $file, ?string $altText = null, ?string $title = null): array
    {
        self::assertUploadOk($file);

        $maxBytes = Env::int('UPLOAD_MAX_BYTES', 5_242_880);
        if ($file['size'] > $maxBytes) {
            $mb = round($maxBytes / 1_048_576, 1);
            throw HttpException::validation(
                ['file' => "Image must be {$mb} MB or smaller."],
                'That image is too large.'
            );
        }

        $tmpPath = $file['tmp_name'];
        if (!is_uploaded_file($tmpPath)) {
            throw HttpException::badRequest('Upload could not be verified.');
        }

        // 1. Extension allowlist.
        $originalName = (string) ($file['name'] ?? 'upload');
        $extension    = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if (!in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            throw HttpException::validation(
                ['file' => 'Only JPG, PNG and WEBP images are allowed.'],
                'Unsupported file type.'
            );
        }

        // 2. Real MIME type, sniffed from content.
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime  = (string) $finfo->file($tmpPath);
        if (!isset(self::ALLOWED[$mime])) {
            throw HttpException::validation(
                ['file' => 'That file is not a valid JPG, PNG or WEBP image.'],
                'Unsupported file type.'
            );
        }

        // 3. It must actually decode as an image.
        $dimensions = @getimagesize($tmpPath);
        if ($dimensions === false) {
            throw HttpException::validation(
                ['file' => 'That file could not be read as an image.'],
                'Unsupported file type.'
            );
        }

        [$width, $height] = $dimensions;

        // Canonical extension from the sniffed MIME, not the submitted name.
        $safeExtension = self::ALLOWED[$mime];
        $fileName      = date('Ymd-His') . '-' . bin2hex(random_bytes(8)) . '.' . $safeExtension;

        $relativeDir = date('Y/m');
        $absoluteDir = self::storageRoot() . '/' . $relativeDir;

        if (!is_dir($absoluteDir) && !@mkdir($absoluteDir, 0775, true) && !is_dir($absoluteDir)) {
            throw new \RuntimeException("Could not create upload directory: {$absoluteDir}");
        }

        $relativePath = $relativeDir . '/' . $fileName;
        $absolutePath = $absoluteDir . '/' . $fileName;

        if (!move_uploaded_file($tmpPath, $absolutePath)) {
            throw new \RuntimeException("Could not move upload to {$absolutePath}");
        }
        @chmod($absolutePath, 0644);

        $fileUrl = rtrim(Env::string('STORAGE_URL', ''), '/') . '/' . $relativePath;

        Database::run(
            'INSERT INTO media
                (file_name, original_name, file_path, file_url, mime_type,
                 file_size, width, height, alt_text, title, uploaded_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $fileName,
                mb_substr($originalName, 0, 255),
                $relativePath,
                $fileUrl,
                $mime,
                (int) $file['size'],
                (int) $width,
                (int) $height,
                $altText !== null ? mb_substr($altText, 0, 255) : null,
                $title !== null ? mb_substr($title, 0, 190) : null,
                Auth::id(),
            ]
        );

        $id = Database::insertId();

        AuditLogger::record(
            'uploaded',
            'media',
            $id,
            "Uploaded image \"{$originalName}\"",
            ['file_name' => $fileName, 'size' => (int) $file['size'], 'mime' => $mime]
        );

        return self::find($id) ?? throw new \RuntimeException('Media row vanished after insert.');
    }

    private static function assertUploadOk(array $file): void
    {
        $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;

        if ($error === UPLOAD_ERR_OK) {
            return;
        }

        $message = match ($error) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'That image is larger than the server allows.',
            UPLOAD_ERR_PARTIAL    => 'The upload was interrupted. Please try again.',
            UPLOAD_ERR_NO_FILE    => 'Please choose an image to upload.',
            UPLOAD_ERR_NO_TMP_DIR,
            UPLOAD_ERR_CANT_WRITE => 'The server could not save the image. Please contact your administrator.',
            default               => 'The image could not be uploaded.',
        };

        throw HttpException::validation(['file' => $message], $message);
    }

    public static function find(int $id): ?array
    {
        return Database::fetchOne(
            'SELECT m.*, CONCAT(u.first_name, " ", u.last_name) AS uploaded_by_name
               FROM media m
               LEFT JOIN users u ON u.id = m.uploaded_by
              WHERE m.id = ? AND m.deleted_at IS NULL',
            [$id]
        );
    }

    /** Counts every content row still pointing at this image. */
    public static function usageCount(int $mediaId): int
    {
        $total = 0;

        foreach ([
            'services', 'service_categories', 'promotions', 'specials',
            'products', 'product_brands', 'gift_cards',
        ] as $table) {
            $total += (int) Database::fetchValue(
                "SELECT COUNT(*) FROM `{$table}` WHERE media_id = ? AND deleted_at IS NULL",
                [$mediaId]
            );
        }

        $total += (int) Database::fetchValue(
            'SELECT COUNT(*) FROM service_images WHERE media_id = ?',
            [$mediaId]
        );

        return $total;
    }

    /**
     * Soft-deletes an image and removes the file from disk. Refuses while the
     * image is still referenced — silently blanking a live page is worse than
     * an explicit error.
     */
    public static function delete(int $id): void
    {
        $media = self::find($id);
        if ($media === null) {
            throw HttpException::notFound('That image no longer exists.');
        }

        $usage = self::usageCount($id);
        if ($usage > 0) {
            throw HttpException::conflict(
                "This image is still used by {$usage} item(s). Remove it from those items first."
            );
        }

        Database::run(
            'UPDATE media SET deleted_at = NOW(), deleted_by = ? WHERE id = ?',
            [Auth::id(), $id]
        );

        $absolute = self::storageRoot() . '/' . $media['file_path'];
        if (is_file($absolute) && !@unlink($absolute)) {
            // The DB row is already gone; a stray file is a cleanup issue, not
            // a request failure.
            Logger::warn('Could not delete media file from disk', ['path' => $absolute, 'media_id' => $id]);
        }

        AuditLogger::record(
            'deleted',
            'media',
            $id,
            "Deleted image \"{$media['original_name']}\""
        );
    }

    public static function storageRoot(): string
    {
        $configured = Env::string('STORAGE_PATH', 'storage/uploads');

        // Absolute path (POSIX or Windows) is used as-is.
        if (str_starts_with($configured, '/') || preg_match('#^[A-Za-z]:[\\\\/]#', $configured)) {
            return rtrim($configured, '/\\');
        }

        return MARIAH_ROOT . '/' . trim($configured, '/');
    }

    /** Verifies a media id exists, for foreign-key fields on content forms. */
    public static function assertExists(?int $mediaId): ?int
    {
        if ($mediaId === null || $mediaId === 0) {
            return null;
        }

        $exists = Database::fetchValue(
            'SELECT 1 FROM media WHERE id = ? AND deleted_at IS NULL',
            [$mediaId]
        );

        if ($exists === null) {
            throw HttpException::validation(['media_id' => 'The selected image no longer exists.']);
        }

        return $mediaId;
    }
}
