<?php
declare(strict_types=1);

namespace Mariah\Services;

/**
 * Reads a Google Sheets link and rebuilds the two URLs the CMS needs from it.
 *
 * Pure — no I/O, no network. Every output is assembled from the document id and
 * the tab gid, each matched against a tight character class, against a
 * hardcoded docs.google.com template. Nothing an operator typed is ever used
 * as a URL directly.
 *
 * That is the whole SSRF defence for the fetcher, and it is far stronger than
 * a host blocklist: the only attacker-influenced input that reaches the
 * network is an opaque id of [A-Za-z0-9-_] and a run of digits.
 */
final class GoogleSheetUrl
{
    /** The document id in .../spreadsheets/d/<ID>/... */
    private const ID_PATTERN =
        '#^https?://docs\.google\.com/spreadsheets/d/([A-Za-z0-9\-_]{20,120})(?:[/?\#]|$)#i';

    /** "Publish to web" links use a different id space and must not be confused with the above. */
    private const PUBLISHED_PATTERN = '#^https?://docs\.google\.com/spreadsheets/d/e/#i';

    public static function spreadsheetId(string $url): ?string
    {
        $url = trim($url);

        // Checked first: a published link also contains "/spreadsheets/d/",
        // and /copy 404s for it, so it needs its own answer rather than
        // silently matching the id pattern.
        if (preg_match(self::PUBLISHED_PATTERN, $url) === 1) {
            return null;
        }

        return preg_match(self::ID_PATTERN, $url, $matches) === 1 ? $matches[1] : null;
    }

    /** The tab id. Covers "#gid=", "?gid=" and "&gid=" in one pass. */
    public static function gid(string $url): string
    {
        return preg_match('/[#?&]gid=(\d{1,15})/', trim($url), $matches) === 1
            ? $matches[1]
            : '0';
    }

    /**
     * The "make a copy" link. Google prompts the visitor to copy the whole
     * workbook into their own Drive, so the gid is deliberately not carried.
     */
    public static function copyUrl(string $url): ?string
    {
        $id = self::spreadsheetId($url);

        return $id === null ? null : 'https://docs.google.com/spreadsheets/d/' . $id . '/copy';
    }

    /** The CSV export of one tab. */
    public static function csvExportUrl(string $url): ?string
    {
        $id = self::spreadsheetId($url);

        return $id === null
            ? null
            : 'https://docs.google.com/spreadsheets/d/' . $id . '/export?format=csv&gid=' . self::gid($url);
    }

    /** Whether a link is the "Publish to web" form rather than a Share link. */
    public static function isPublishedToWeb(string $url): bool
    {
        return preg_match(self::PUBLISHED_PATTERN, trim($url)) === 1;
    }

    /**
     * Null when the link is usable, otherwise the reason in the operator's
     * terms. Three distinct messages, because "invalid link" would leave
     * someone re-pasting the same wrong thing.
     */
    public static function explainRejection(string $url): ?string
    {
        $url = trim($url);

        if ($url === '') {
            return null;
        }

        if (self::isPublishedToWeb($url)) {
            return 'That is a "Publish to web" link. Open the sheet, use Share → Copy link instead, '
                 . 'and paste that.';
        }

        if (preg_match('#^https?://docs\.google\.com/spreadsheets/#i', $url) !== 1) {
            return 'That is not a Google Sheets link. It should start with '
                 . 'https://docs.google.com/spreadsheets/d/';
        }

        if (self::spreadsheetId($url) === null) {
            return 'That link is missing the sheet id. Use Share → Copy link and paste the whole link.';
        }

        return null;
    }
}
