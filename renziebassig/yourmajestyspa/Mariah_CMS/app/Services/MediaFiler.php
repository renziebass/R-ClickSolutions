<?php
declare(strict_types=1);

namespace Mariah\Services;

use Mariah\Core\Database;
use Mariah\Core\Env;
use Mariah\Core\Logger;

/**
 * Files photos into their module folder.
 *
 * The rule is first use wins: a photo lands in "unsorted" when it is uploaded,
 * and files once — into the folder of whatever content record attaches it
 * first. Later uses leave it alone, so a photo shared by a service and a
 * promotion has one home rather than ping-ponging between two, and its URL
 * stays stable after the first move.
 *
 * Filing is two things, and they are deliberately separable:
 *
 *   * the folder recorded against the row, which is what the library shows and
 *     filters by. Every photo gets this.
 *   * the file moved on disk into storage/uploads/{folder}/, which only
 *     applies to files this CMS actually stores. The demo seed registers the
 *     website's own artwork in `assets/` as media rows so content can point at
 *     it; those have nothing under the storage root to move. They are still
 *     service photos or category photos, so they still get a folder — they
 *     just keep their file where it is.
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
     * Where a photo's folder comes from when it is derived from usage, as
     * [folder, join] pairs applied in order.
     *
     * Order is the first-use-wins tie-break for a photo used in more than one
     * place, and it matches migration 011's backfill. The join fragments are
     * fixed strings in this file, never input.
     */
    private const USAGE_SOURCES = [
        ['services',   'JOIN services s ON s.media_id = m.id AND s.deleted_at IS NULL'],
        ['services',   'JOIN service_images si ON si.media_id = m.id
                        JOIN services s ON s.id = si.service_id AND s.deleted_at IS NULL'],
        ['categories', 'JOIN service_categories c ON c.media_id = m.id AND c.deleted_at IS NULL'],
        ['promotions', 'JOIN promotions p ON p.media_id = m.id AND p.deleted_at IS NULL'],
        ['specials',   'JOIN specials sp ON sp.media_id = m.id AND sp.deleted_at IS NULL'],
        ['blog',       'JOIN blog_posts b ON b.media_id = m.id AND b.deleted_at IS NULL'],
        ['brands',     'JOIN product_brands pb ON pb.media_id = m.id AND pb.deleted_at IS NULL'],
        ['gift-cards', 'JOIN gift_cards g ON g.media_id = m.id AND g.deleted_at IS NULL'],
        ['products',   'JOIN products pr ON pr.media_id = m.id AND pr.deleted_at IS NULL'],
    ];

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
            if ($media === null || $media['folder'] !== MediaFolders::UNSORTED) {
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
     * Gives every unsorted photo the folder its current usage implies.
     *
     * Migration 011 runs the same statements once, but only catches what was
     * already attached when it ran — on an install seeded afterwards, or one
     * where the backfill did not land, everything stays in Unsorted with no
     * way back. Running it again from here costs nine statements and makes the
     * library self-healing rather than dependent on a one-shot backfill.
     *
     * Only `unsorted` rows are touched, so this can never overrule a folder
     * somebody chose by hand.
     *
     * @return int rows filed
     */
    public static function fileUnsortedByUsage(): int
    {
        $filed = 0;

        foreach (self::USAGE_SOURCES as [$folder, $join]) {
            $filed += Database::run(
                "UPDATE media m {$join}
                    SET m.folder = ?
                  WHERE m.folder = ? AND m.deleted_at IS NULL",
                [$folder, MediaFolders::UNSORTED]
            )->rowCount();
        }

        return $filed;
    }

    /**
     * Brings the whole library in line: folders derived from usage, then files
     * walked to match their folder.
     *
     * This is what migrates a library that predates folders, and the repair
     * for one where filing has drifted. Idempotent — a second run finds
     * nothing to do.
     *
     * @return array{filed: int, moved: int, skipped: int, failed: int}
     */
    public static function reorganize(): array
    {
        $filed = self::fileUnsortedByUsage();

        $rows = Database::fetchAll(
            'SELECT ' . self::COLUMNS . ' FROM media WHERE deleted_at IS NULL'
        );

        $moved = 0;
        $failed = 0;
        $skipped = 0;

        foreach ($rows as $media) {
            $folder = MediaFolders::normalize($media['folder']);

            // Nothing to move: already in place, or no file of ours to move.
            if (!self::isManagedUpload($media)
                || $media['file_path'] === self::targetPath($media, $folder)) {
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

        if ($filed > 0 || $moved > 0) {
            AuditLogger::record(
                'reorganized',
                'media',
                null,
                "Reorganised the media library into folders ({$filed} filed, {$moved} file(s) moved)",
                ['filed' => $filed, 'moved' => $moved, 'failed' => $failed, 'unchanged' => $skipped]
            );
        }

        return ['filed' => $filed, 'moved' => $moved, 'skipped' => $skipped, 'failed' => $failed];
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
     * Whether this row's bytes are a file this CMS stores under STORAGE_PATH.
     *
     * False for the seeded website artwork, whose file_url points into the
     * public site's assets/ folder. Those rows still get a folder; there is
     * simply no file of ours to move.
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
     * Records the folder, moving the file to match where there is one to move.
     *
     * The move comes first and the row is written only once the file is
     * confirmed at its new path — a row pointing somewhere the file is not
     * would break every page showing the image.
     */
    private static function relocate(array $media, string $folder, bool $audit = true): bool
    {
        $id          = (int) $media['id'];
        $currentPath = (string) $media['file_path'];

        // Not our file to move — record the folder and leave the bytes alone.
        if (!self::isManagedUpload($media)) {
            self::stamp($id, (string) $media['folder'], $folder, $audit);
            return true;
        }

        $relativePath = self::targetPath($media, $folder);

        if ($relativePath === $currentPath) {
            self::stamp($id, (string) $media['folder'], $folder, $audit);
            return true;
        }

        $root        = MediaService::storageRoot();
        $source      = $root . '/' . $currentPath;
        $destination = $root . '/' . $relativePath;
        $absoluteDir = dirname($destination);

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
                Logger::warn('Media file missing on disk, folder recorded but file not moved', [
                    'media_id' => $id,
                    'path'     => $currentPath,
                ]);
                self::stamp($id, (string) $media['folder'], $folder, $audit);
                return true;
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

    /** Records the folder alone, for a photo with no file of ours to move. */
    private static function stamp(int $id, string $from, string $folder, bool $audit): void
    {
        if ($from === $folder) {
            return;
        }

        Database::run('UPDATE media SET folder = ? WHERE id = ?', [$folder, $id]);

        if ($audit) {
            AuditLogger::record(
                'filed',
                'media',
                $id,
                'Filed image into "' . MediaFolders::label($folder) . '"',
                ['from_folder' => $from, 'to_folder' => $folder]
            );
        }
    }
}
