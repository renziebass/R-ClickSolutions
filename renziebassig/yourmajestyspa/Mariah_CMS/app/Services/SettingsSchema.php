<?php
declare(strict_types=1);

namespace Mariah\Services;

use Mariah\Core\Clock;
use Mariah\Core\Database;
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
     * key => {group, label, help, type, default, rules, public, options?, super_admin?}
     *
     * `type`   string | url | bool | int | select — the only thing
     *          coerce()/serialise() read. `select` needs no branch in either:
     *          it round-trips as a string exactly as `url` does, and differs
     *          only in how the form renders it.
     * `public` whether the value may be sent to the browser in /auth/me.
     *          Nothing without it is ever emitted to a client or written into
     *          an audit-log value. No setting is secret today; the flag has to
     *          exist before the first one is, not after.
     * `options` {value,label} pairs for a `select`. Optional everywhere else.
     * `super_admin` restricts *editing* to a Super Admin. Everyone holding
     *          settings.view still sees the value; only the write is refused.
     *
     * @return array<string, array{group:string,label:string,help:string,type:string,default:mixed,rules:string,public:bool,options?:mixed,super_admin?:bool}>
     */
    public static function definitions(): array
    {
        return [
            'site_timezone' => [
                'group'       => 'Regional',
                'label'       => 'Timezone',
                'help'        => 'The clock the whole CMS runs on. Audit log times, '
                               . 'scheduled blog posts, and promotion and special start '
                               . 'and end dates all follow it.',
                'type'        => 'select',
                // Declared as a callable, not an array: definitions() is called
                // several times on most requests and building ~420 labelled
                // zones is pure waste on every request that never opens the
                // Settings screen. optionsFor() resolves it where it is needed.
                'options'     => [self::class, 'timezoneOptions'],
                'default'     => Clock::FALLBACK,
                'rules'       => 'required|string|max:64',
                // Not a secret, and being public is what keeps the audit entry
                // readable: loggableChanges() records a non-public value as
                // "(hidden)", which would be useless for a timezone change.
                'public'      => true,
                'super_admin' => true,
            ],

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

            'services_import_rules' => [
                'group'   => 'Service import',
                'label'   => 'Import column rules',
                'help'    => 'Which CSV columns must be filled, and what a blank cell '
                           . 'falls back to on a new service.',
                'type'    => 'json',
                'default' => '{}',
                'rules'   => 'nullable|string|max:8000',
                'public'  => false,
                // Edited on the Import screen, which is the only place that
                // knows the column list. A one-line text input full of JSON
                // would be worse than not showing it at all.
                'hidden'  => true,
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

    /**
     * Every IANA zone as {value,label} pairs for the Settings dropdown.
     *
     * Memoised because definitions() is called by defaults(), rules(), labels(),
     * groups() and has() — several times per request — and this list is roughly
     * 420 entries. Labels carry the current UTC offset, since "Asia/Manila"
     * alone is not much help to someone choosing between two plausible zones.
     *
     * @return array<int, array{value:string, label:string}>
     */
    public static function timezoneOptions(): array
    {
        static $options = null;

        if ($options !== null) {
            return $options;
        }

        $options = [];

        foreach (timezone_identifiers_list() as $zone) {
            $options[] = [
                'value' => $zone,
                'label' => str_replace('_', ' ', $zone) . ' (UTC' . Clock::utcOffset($zone) . ')',
            ];
        }

        return $options;
    }

    /** @return string[] */
    public static function keys(): array
    {
        return array_keys(self::definitions());
    }

    /** Whether editing this setting is restricted to a Super Admin. */
    public static function isSuperAdminOnly(string $key): bool
    {
        return (bool) (self::definitions()[$key]['super_admin'] ?? false);
    }

    /**
     * A setting's choices, resolving the lazy callable form. Empty for every
     * type that has none.
     *
     * @return array<int, array{value:string, label:string}>
     */
    public static function optionsFor(string $key): array
    {
        $options = self::definitions()[$key]['options'] ?? [];

        return is_callable($options) ? $options() : $options;
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

    /**
     * Settings grouped for the form, in declaration order.
     *
     * Hidden settings are omitted: they are real settings, saved and audited
     * through the same endpoint, but they have a purpose-built editor
     * elsewhere and would only render here as an unusable text box.
     */
    public static function groups(): array
    {
        $groups = [];

        foreach (self::definitions() as $key => $definition) {
            if (self::isHidden($key)) {
                continue;
            }

            $groups[$definition['group']][] = $key;
        }

        return $groups;
    }

    /** Whether this setting is edited somewhere other than the Settings form. */
    public static function isHidden(string $key): bool
    {
        return (bool) (self::definitions()[$key]['hidden'] ?? false);
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
        if (array_key_exists(ServiceCsvSchema::RULES_SETTING, $clean)) {
            self::assertImportRules((string) ($clean[ServiceCsvSchema::RULES_SETTING] ?? ''));
        }

        if (array_key_exists('site_timezone', $clean)) {
            $zone = trim((string) ($clean['site_timezone'] ?? ''));

            // Checked against the live IANA list rather than an in: rule —
            // 420 identifiers do not belong in a rule string.
            if (!Clock::isValid($zone)) {
                throw HttpException::validation([
                    'site_timezone' => '"' . $zone . '" is not a recognised timezone. '
                        . 'Choose one from the list.',
                ]);
            }
        }

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

    /**
     * Checks the per-column import rules before they are stored.
     *
     * Every default is run through the same coercion the import will apply to
     * it, so "not a number" in the price default is refused here rather than
     * surfacing as 500 identical row errors on the next import.
     */
    private static function assertImportRules(string $json): void
    {
        $key = ServiceCsvSchema::RULES_SETTING;

        if (trim($json) === '') {
            return;   // cleared; the base contract applies
        }

        $config = json_decode($json, true);

        if (!is_array($config)) {
            throw HttpException::validation([
                $key => 'The import rules could not be read. Reset them and try again.',
            ]);
        }

        $known  = ServiceCsvSchema::columnKeys();
        $types  = ServiceCsvSchema::normalisableColumns();
        $errors = [];

        foreach ($config as $column => $rule) {
            if (!is_string($column) || !in_array($column, $known, true)) {
                $errors[] = '"' . ServiceCsvSchema::clip($column) . '" is not an import column.';
                continue;
            }

            if (!is_array($rule)) {
                $errors[] = 'The rule for "' . $column . '" is malformed.';
                continue;
            }

            $default = $rule['default'] ?? null;

            if ($default === null || trim((string) $default) === '') {
                continue;
            }

            if (in_array($column, ServiceCsvSchema::NO_DEFAULT, true)) {
                $errors[] = '"' . $column . '" identifies which service a row belongs to, '
                    . 'so it cannot have a default.';
                continue;
            }

            // `category` is resolved against the live category list rather
            // than coerced, so it is checked separately.
            if ($column === 'category') {
                $exists = Database::fetchValue(
                    'SELECT 1 FROM service_categories WHERE (name = ? OR slug = ?) AND deleted_at IS NULL',
                    [trim((string) $default), trim((string) $default)]
                );

                if ($exists === null) {
                    $errors[] = 'There is no category named "'
                        . ServiceCsvSchema::clip($default) . '".';
                }

                continue;
            }

            if (!isset($types[$column])) {
                continue;   // free text, nothing to coerce
            }

            $warnings = [];
            $result   = ServiceCsvSchema::normalise(
                $column,
                $types[$column],
                trim((string) $default),
                $warnings
            );

            if ($result instanceof \RuntimeException) {
                $errors[] = 'Default for "' . $column . '": ' . $result->getMessage();
            } elseif ($warnings !== []) {
                $errors[] = 'Default for "' . $column . '": ' . $warnings[0];
            }
        }

        if ($errors !== []) {
            throw HttpException::validation([$key => implode(' ', $errors)]);
        }
    }
}
