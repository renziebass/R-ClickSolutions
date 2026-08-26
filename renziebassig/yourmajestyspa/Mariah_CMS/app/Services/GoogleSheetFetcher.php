<?php
declare(strict_types=1);

namespace Mariah\Services;

use Mariah\Core\Env;
use Mariah\Core\HttpException;

/**
 * Fetches one Google Sheet tab as CSV.
 *
 * This is the ONLY outbound request the application makes, and it is
 * deliberately not a general-purpose HTTP client: a reusable HttpClient would
 * invite calls to arbitrary hosts and turn a narrow, hardened path into a
 * broad one.
 *
 * The URL is never passed through from the operator. GoogleSheetUrl extracts a
 * document id matching [A-Za-z0-9-_]{20,120} and a numeric gid, and this class
 * requests a hardcoded docs.google.com template built from those two fragments.
 * That, rather than any host blocklist, is what closes SSRF here.
 *
 * Every failure throws HttpException::validation(['file' => ...]), because a
 * fetch failure is a file-level failure — so it rides the exact channel the
 * import screen already renders, with no client changes.
 */
final class GoogleSheetFetcher
{
    private const CONNECT_TIMEOUT = 5;
    private const TOTAL_TIMEOUT   = 20;
    private const MAX_REDIRECTS   = 5;

    /** Appended to every failure: there is always a way to get the data in. */
    private const FALLBACK = ' In Google Sheets choose File → Download → '
                           . 'Comma-separated values (.csv), then upload that file here.';

    /**
     * @return array{bytes: string, name: string, url: string}
     */
    public static function fetchCsv(string $sourceUrl): array
    {
        // No allow_url_fopen fallback: a stream wrapper offers no streaming
        // size abort, no protocol restriction and no separate connect timeout,
        // which is three of the four controls below. cURL or an honest message.
        if (!extension_loaded('curl')) {
            throw HttpException::validation(['file' =>
                'This server cannot fetch a Google Sheet: the PHP cURL extension is not installed.'
                . self::FALLBACK]);
        }

        $reason = GoogleSheetUrl::explainRejection($sourceUrl);

        if ($reason !== null) {
            throw HttpException::validation(['file' => $reason]);
        }

        $exportUrl = GoogleSheetUrl::csvExportUrl($sourceUrl);

        if ($exportUrl === null) {
            throw HttpException::validation(['file' =>
                'That does not look like a Google Sheets link.']);
        }

        $maxBytes = Env::int('SERVICE_IMPORT_MAX_BYTES', ServiceCsvSchema::MAX_BYTES);

        $buffer      = '';
        $overflow    = false;
        $disposition = '';
        $contentType = '';

        $handle = curl_init($exportUrl);

        curl_setopt_array($handle, [
            CURLOPT_FOLLOWLOCATION => true,     // /export legitimately redirects
            CURLOPT_MAXREDIRS      => self::MAX_REDIRECTS,
            CURLOPT_CONNECTTIMEOUT => self::CONNECT_TIMEOUT,
            CURLOPT_TIMEOUT        => self::TOTAL_TIMEOUT,
            // Explicitly on. smoke.php disables verification, which is right
            // for a CLI tool hitting a staging cert and wrong here, where the
            // entire trust model is "this is really Google".
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_USERAGENT      => 'Mariah_CMS service importer',
            // Transparent gzip, so the cap below measures decompressed bytes —
            // the number that actually matters.
            CURLOPT_ENCODING       => '',

            CURLOPT_HEADERFUNCTION => static function ($ch, string $header)
                use (&$disposition, &$contentType): int {
                $lower = strtolower($header);

                if (str_starts_with($lower, 'content-disposition:')) {
                    $disposition = trim(substr($header, 20));
                }
                if (str_starts_with($lower, 'content-type:')) {
                    $contentType = strtolower(trim(substr($header, 13)));
                }

                return strlen($header);
            },

            // Returning a short count aborts the transfer mid-flight, so an
            // oversized sheet is never fully buffered. Content-Length would be
            // cheaper but Google rarely sends it on a dynamic export, so this
            // callback is what actually enforces the cap.
            CURLOPT_WRITEFUNCTION => static function ($ch, string $chunk)
                use (&$buffer, &$overflow, $maxBytes): int {
                $buffer .= $chunk;

                if (strlen($buffer) > $maxBytes) {
                    $overflow = true;
                    return 0;
                }

                return strlen($chunk);
            },
        ]);

        // Confine every hop to HTTPS, so a redirect can never reach file://,
        // ftp:// or a plaintext internal address. Guarded because the
        // constants are soft-deprecated on libcurl >= 7.85.
        if (defined('CURLPROTO_HTTPS')) {
            curl_setopt($handle, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);
            curl_setopt($handle, CURLOPT_REDIR_PROTOCOLS, CURLPROTO_HTTPS);
        }

        curl_exec($handle);

        $errno       = curl_errno($handle);
        $status      = (int) curl_getinfo($handle, CURLINFO_HTTP_CODE);
        $effectiveUrl = (string) curl_getinfo($handle, CURLINFO_EFFECTIVE_URL);

        curl_close($handle);

        if ($overflow) {
            $mb = round($maxBytes / 1048576, 1);
            throw HttpException::validation(['file' =>
                "That sheet is larger than the {$mb} MB limit. Split it and import in parts."]);
        }

        if ($errno !== 0) {
            throw HttpException::validation(['file' => self::transportMessage($errno)]);
        }

        if ($status !== 200) {
            throw HttpException::validation(['file' => self::statusMessage($status)]);
        }

        // The case that will actually happen: a sheet that is not shared
        // publicly frequently returns HTTP 200 with Google's sign-in HTML, so
        // a status check alone is not enough. Without this the operator gets
        // "missing required columns … the columns found were: <!DOCTYPE html".
        if (self::looksLikeSignIn($buffer, $contentType, $effectiveUrl)) {
            throw HttpException::validation(['file' =>
                'Google returned a sign-in page instead of the sheet, which means it is not shared '
                . 'publicly. Open the sheet, choose Share → General access → "Anyone with the link", '
                . 'set the role to Viewer, then try again. Anyone holding the link will then be able '
                . 'to read the sheet.']);
        }

        if (trim($buffer) === '') {
            throw HttpException::validation(['file' =>
                'That sheet tab has no rows. Check the link points at the tab holding your services — '
                . 'that is the #gid= at the end of the URL.']);
        }

        return [
            'bytes' => $buffer,
            'name'  => self::filenameFrom($disposition),
            'url'   => $exportUrl,
        ];
    }

    /** Named specifically, because "could not fetch" tells an operator nothing. */
    private static function transportMessage(int $errno): string
    {
        $message = match ($errno) {
            CURLE_COULDNT_RESOLVE_HOST =>
                'This server could not look up docs.google.com. Its DNS or outbound network access '
                . 'is blocked by the host.',
            CURLE_COULDNT_CONNECT =>
                'This server could not connect to Google. Outbound HTTPS is most likely blocked by '
                . 'your hosting plan.',
            CURLE_OPERATION_TIMEOUTED =>
                'The request to Google timed out after ' . self::TOTAL_TIMEOUT . ' seconds.',
            CURLE_SSL_CONNECT_ERROR, CURLE_SSL_CACERT =>
                "This server could not verify Google's certificate. Its CA bundle may be out of "
                . 'date — contact your host.',
            default => 'The sheet could not be fetched (cURL error ' . $errno . ').',
        };

        return $message . self::FALLBACK;
    }

    private static function statusMessage(int $status): string
    {
        $message = match (true) {
            $status === 401 || $status === 403 =>
                'Google refused access to that sheet. Choose Share → General access → '
                . '"Anyone with the link" → Viewer, then try again.',
            $status === 404 =>
                'No Google Sheet was found at that link. Check the link, or that the sheet has not '
                . 'been deleted.',
            $status === 429 =>
                'Google is rate-limiting this server. Wait a minute and try again.',
            $status >= 500 =>
                'Google returned an error (HTTP ' . $status . '). Try again shortly.',
            default =>
                'Google returned HTTP ' . $status . ' for that sheet.',
        };

        return $message . self::FALLBACK;
    }

    /** Any one of the three signals is enough. */
    private static function looksLikeSignIn(string $body, string $contentType, string $effectiveUrl): bool
    {
        if (str_starts_with($contentType, 'text/html')) {
            return true;
        }

        if (str_contains($effectiveUrl, 'accounts.google.com')) {
            return true;
        }

        $head = strtolower(ltrim(substr($body, 0, 512)));

        return str_starts_with($head, '<!doctype html') || str_starts_with($head, '<html');
    }

    /**
     * The sheet's real title from Content-Disposition, so the audit trail reads
     * "Imported 12 services from "Spa menu - Sheet1.csv"" rather than something
     * generic that would be useless once two sheets exist.
     */
    private static function filenameFrom(string $disposition): string
    {
        if ($disposition !== '') {
            // RFC 5987 form first: filename*=UTF-8''Spa%20menu.csv
            if (preg_match("/filename\*=UTF-8''([^;]+)/i", $disposition, $m) === 1) {
                $decoded = urldecode(trim($m[1]));
                if ($decoded !== '') {
                    return $decoded;
                }
            }

            if (preg_match('/filename="?([^";]+)"?/i', $disposition, $m) === 1) {
                $name = trim($m[1]);
                if ($name !== '') {
                    return $name;
                }
            }
        }

        return 'Google Sheet';
    }
}
