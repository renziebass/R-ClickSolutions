<?php
declare(strict_types=1);

namespace Mariah\Services;

use Mariah\Core\HttpException;

/**
 * The catalogue of every site setting: its type, default, label, help text,
 * validation and whether the browser may see it.
 *
 * This file is the single source of truth, exactly as config/permissions.php
 * is for permissions. Adding a setting is a one-line edit here — no migration,
 * no controller change — and it appears in the Settings form automatically.
 *
 * A class of statics rather than a returned array, because it is read on most
 * requests (so it should autoload rather than be require()d per call) and its
 * help text interpolates constants from ServiceCsvSchema.
 */
final class SettingsSchema
{
    /**
     * key => {group, label, help, type, default, rules, public}
     *
     * `type`   string | url | bool | int — the only thing coerce()/serialise() read.
     * `public` whether the value may be sent to the browser in /auth/me.
     *          Nothing without it is ever emitted to a client or written into
     *          an audit-log value. No setting is secret today; the flag has to
     *          exist before the first one is, not after.
     *
     * @return array<string, array{group:string,label:string,help:string,type:string,default:mixed,rules:string,public:bool}>
     */
    public static function definitions(): array
    {
        return [
            'services_import_sheet_url' => [
                'group'   => 'Service import',
                'label'   => 'Google Sheets template link',
                'help'    => 'The blank template staff copy into their own Google Drive. '
                           . 'Paste the Share link. Leave blank to hide the button.',
                'type'    => 'url',
                'default' => '',
                'rules'   => 'nullable|string|max:500',
                'public'  => true,
            ],

            'services_import_url_enabled' => [
                'group'   => 'Service import',
                'label'   => 'Allow importing directly from a Google Sheets link',
                'help'    => 'When off, staff download the sheet as a CSV and upload it instead. '
                           . 'Turn this off if this server cannot reach Google.',
                'type'    => 'bool',
                'default' => true,
                'rules'   => 'nullable|bool',
                'public'  => true,
            ],
        ];
    }

    /** @return string[] */
    public static function keys(): array
    {
        return array_keys(self::definitions());
    }

    public static function has(string $key): bool
    {
        return array_key_exists($key, self::definitions());
    }

    /** @return array<string, mixed> key => coerced default */
    public static function defaults(): array
    {
        $defaults = [];

        foreach (self::definitions() as $key => $definition) {
            $defaults[$key] = $definition['default'];
        }

        return $defaults;
    }

    /**
     * Validation rules for just the keys being submitted, so a partial update
     * validates only what it carries.
     *
     * @param string[] $onlyKeys
     */
    public static function rules(array $onlyKeys): array
    {
        $rules = [];

        foreach (self::definitions() as $key => $definition) {
            if (in_array($key, $onlyKeys, true)) {
                $rules[$key] = $definition['rules'];
            }
        }

        return $rules;
    }

    public static function labels(): array
    {
        $labels = [];

        foreach (self::definitions() as $key => $definition) {
            $labels[$key] = $definition['label'];
        }

        return $labels;
    }

    /** @return string[] the keys the browser may see */
    public static function publicKeys(): array
    {
        $keys = [];

        foreach (self::definitions() as $key => $definition) {
            if ($definition['public']) {
                $keys[] = $key;
            }
        }

        return $keys;
    }

    /** Settings grouped for the form, in declaration order. */
    public static function groups(): array
    {
        $groups = [];

        foreach (self::definitions() as $key => $definition) {
            $groups[$definition['group']][] = $key;
        }

        return $groups;
    }

    // -----------------------------------------------------------------
    // Type handling — the only two places a `type` is interpreted
    // -----------------------------------------------------------------

    /** Stored text (or null) into the PHP value the application uses. */
    public static function coerce(string $key, mixed $raw): mixed
    {
        $definition = self::definitions()[$key] ?? null;

        if ($definition === null) {
            return null;
        }

        if ($raw === null) {
            return $definition['default'];
        }

        return match ($definition['type']) {
            'bool'  => in_array(strtolower((string) $raw), ['1', 'true', 'yes', 'on'], true),
            'int'   => (int) $raw,
            default => (string) $raw,
        };
    }

    /**
     * The PHP value back into stored text.
     *
     * Validator turns a present-but-empty nullable field into null, so a
     * cleared URL arrives here as null. Mapping it to '' rather than storing
     * NULL keeps coerce() free of a special case in both directions.
     */
    public static function serialise(string $key, mixed $value): string
    {
        $definition = self::definitions()[$key] ?? null;

        if ($definition === null) {
            return '';
        }

        return match ($definition['type']) {
            'bool'  => $value ? '1' : '0',
            'int'   => (string) (int) $value,
            default => $value === null ? '' : (string) $value,
        };
    }

    // -----------------------------------------------------------------
    // Format rules the Validator cannot express
    // -----------------------------------------------------------------

    /**
     * Throws HttpException::validation keyed by the offending setting, so the
     * Settings form paints the message on the right input.
     */
    public static function assertValid(array $clean): void
    {
        if (array_key_exists('services_import_sheet_url', $clean)) {
            $url = trim((string) ($clean['services_import_sheet_url'] ?? ''));

            // Blank is valid and means "hide the button".
            if ($url !== '') {
                $reason = GoogleSheetUrl::explainRejection($url);

                if ($reason !== null) {
                    throw HttpException::validation(['services_import_sheet_url' => $reason]);
                }
            }
        }
    }
}
