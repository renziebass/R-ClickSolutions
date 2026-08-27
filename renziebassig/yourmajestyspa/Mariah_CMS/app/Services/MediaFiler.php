<?php
declare(strict_types=1);

namespace Mariah\Services;

use Mariah\Core\Database;
use Mariah\Core\Env;
use Mariah\Core\Logger;

/**
 * Files photos into their module folder, on disk and in the database.
 *
 * The rule is first use wins: a photo lands in "unsorted" when it is uploaded,
 * and moves once — into the folder of whatever content record attaches it
 * first. Later uses leave it alone, so a photo shared by a service and a
 * promotion has one home rather than ping-ponging between two, and its URL
 * stays stable after the first move.
 *
 * Nothing here throws. Filing runs after the content record is already saved
 * and committed, so a failed rename must not turn a successful save into a
 * 500 — it is logged, the database is left describing where the file actually
 * still is, and the live image keeps rendering from its old path.
 */
final class MediaFiler
{
    /** Everything a move needs to decide and record itself. */
    private const COLUMNS = 'id, file_name, file_path, file_url, folder, created_at';

    /**
     * Files a photo into $folder, unless it has already been filed somewhere.
     *
     * @param ?int $mediaId null (a cleared image field) is a no-op
     */
    public static function file(?int $mediaId, ?string $folder): void
    {
        if ($mediaId === null || $mediaId < 1 || !MediaFolders::isValid($folder)) {
            return;
        }

        try {
            $media = self::row($mediaId);

            // First use wins — already filed, so it stays where it is.
            if ($media === null
                || $media['folder'] !== MediaFolders::UNSORTED
                || !self::isManagedUpload($media)) {
                return;
            }

            self::relocate($media, (string) $folder);
        } catch (\Throwable $e) {
            Logger::error($e, ['media_id' => $mediaId, 'folder' => $folder]);
        }
    }

    /**
     * Moves a photo into $folder regardless of where it is now — the manual
     * override behind "Move to folder".
     *
     * @return ?string null on success, otherwise a reason to show the operator
     */
    public static function moveTo(int $mediaId, string $folder): ?string
    {
        if (!MediaFolders::isValid($folder)) {
            return 'That is not a media library folder.';
        }

        try {
            $media = self::row($mediaId);

            if ($media === null) {
                return 'That image no longer exists.';
            }

            if (!self::isManagedUpload($media)) {
                return 'This image is part of the website\'s built-in artwork rather than an '
                     . 'upload, so it does not live in a folder and cannot be moved.';
            }

            return self::relocate($media, $folder)
                ? null
                : 'The file could not be moved on the server, so it has been left where it is. '
                  . 'Please try again, or ask your administrator to check the error log.';
        } catch (\Throwable $e) {
            Logger::error($e, ['media_id' => $mediaId, 'folder' => $folder]);
            return 'Something went wrong while moving the image. It has been left where it is.';
        }
    }

    /**
     * Brings every row's file on disk in line with its folder column.
     *
     * This is what migrates a library that predates folders: the migration
     * stamps each row with a folder, and this walks the files over to match.
     * Idempotent — a second run finds nothing to do.
     *
     * @return array{moved: int, skipped: int, failed: int}
     */
    public static function reorganize(): array
    {
        $rows = Database::fetchAll(
            'SELECT ' . self::COLUMNS . ' FROM media WHERE deleted_at IS NULL'
        );

        $moved = 0;
        $failed = 0;
        $skipped = 0;

        foreach ($rows as $media) {
            $folder = MediaFolders::normalize($media['folder']);

            // Nothing to do, or nothing this tool owns.
            if ($media['file_path'] === self::targetPath($media, $folder)
                || !self::isManagedUpload($media)) {
                $skipped++;
                continue;
            }

            try {
                if (self::relocate($media, $folder, false)) {
                    $moved++;
                } else {
                    $failed++;
                }
            } catch (\Throwable $e) {
                Logger::error($e, ['media_id' => (int) $media['id'], 'folder' => $folder]);
                $failed++;
            }
        }

        if ($moved > 0) {
            AuditLogger::record(
                'reorganized',
                'media',
                null,
                "Reorganised the media library into folders ({$moved} file(s) moved)",
                ['moved' => $moved, 'failed' => $failed, 'already_filed' => $skipped]
            );
        }

        return ['moved' => $moved, 'skipped' => $skipped, 'failed' => $failed];
    }

    // -----------------------------------------------------------------
    // Internals
    // -----------------------------------------------------------------

    private static function row(int $mediaId): ?array
    {
        return Database::fetchOne(
            'SELECT ' . self::COLUMNS . ' FROM media WHERE id = ? AND deleted_at IS NULL',
            [$mediaId]
        );
    }

    /**
     * Whether this row describes a file this CMS actually stores.
     *
     * The demo seed registers the website's own asset files as media rows so
     * content can point at them, with a file_url outside STORAGE_URL and no
     * file under the storage root at all. Those are references, not uploads —
     * moving one would break the image and gain nothing, so folders simply do
     * not apply to them.
     */
    private static function isManagedUpload(array $media): bool
    {
        $storageUrl = rtrim(Env::string('STORAGE_URL', ''), '/');

        return $storageUrl !== ''
            && str_starts_with((string) $media['file_url'], $storageUrl . '/');
    }

    /**
     * Where this photo belongs once filed into $folder.
     *
     * The YYYY/MM shard is carried across rather than recomputed from today,
     * so a photo keeps its upload date on disk and no single directory grows
     * without bound.
     */
    private static function targetPath(array $media, string $folder): string
    {
        $path = (string) $media['file_path'];

        if (preg_match('#(\d{4}/\d{2})/[^/]+$#', $path, $matches) === 1) {
            $shard = $matches[1];
        } else {
            $timestamp = strtotime((string) ($media['created_at'] ?? '')) ?: time();
            $shard     = date('Y/m', $timestamp);
        }

        return $folder . '/' . $shard . '/' . $media['file_name'];
    }

    /**
     * Performs the move and records the new location.
     *
     * The database is only written once the file is confirmed to be at the new
     * path — a row pointing somewhere the file is not would break every page
     * showing the image.
     */
    private static function relocate(array $media, string $folder, bool $audit = true): bool
    {
        $id           = (int) $media['id'];
        $currentPath  = (string) $media['file_path'];
        $relativePath = self::targetPath($media, $folder);

        if ($relativePath === $currentPath) {
            // Already in the right place; the column may still be stale.
            if (($media['folder'] ?? null) !== $folder) {
                Database::run('UPDATE media SET folder = ? WHERE id = ?', [$folder, $id]);
            }
            return true;
        }

        $root         = MediaService::storageRoot();
        $source       = $root . '/' . $currentPath;
        $destination  = $root . '/' . $relativePath;
        $absoluteDir  = dirname($destination);

        if (!is_dir($absoluteDir) && !@mkdir($absoluteDir, 0775, true) && !is_dir($absoluteDir)) {
            Logger::warn('Could not create media folder', ['dir' => $absoluteDir, 'media_id' => $id]);
            return false;
        }

        if (!is_file($source)) {
            // An interrupted earlier move can leave the file already at the
            // destination with the row still describing the old path. Anything
            // else is a file that is simply gone, and moving nothing would only
            // point the row at a second missing location.
            if (!is_file($destination)) {
                Logger::warn('Media file missing on disk, not filed', [
                    'media_id' => $id,
                    'path'     => $currentPath,
                ]);
                return false;
            }
        } elseif (!@rename($source, $destination)) {
            Logger::warn('Could not move media file into its folder', [
                'media_id' => $id,
                'from'     => $currentPath,
                'to'       => $relativePath,
            ]);
            return false;
        }

        $fileUrl = rtrim(Env::string('STORAGE_URL', ''), '/') . '/' . $relativePath;

        Database::run(
            'UPDATE media SET folder = ?, file_path = ?, file_url = ? WHERE id = ?',
            [$folder, $relativePath, $fileUrl, $id]
        );

        if ($audit) {
            AuditLogger::record(
                'filed',
                'media',
                $id,
                'Filed image into "' . MediaFolders::label($folder) . '"',
                ['from' => $currentPath, 'to' => $relativePath]
            );
        }

        return true;
    }
}
