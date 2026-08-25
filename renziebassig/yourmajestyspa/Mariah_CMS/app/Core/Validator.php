<?php
declare(strict_types=1);

namespace Mariah\Core;

/**
 * Field validator producing user-facing messages keyed by field name, which
 * the SPA maps straight onto form inputs.
 *
 * Rules: required, string, int, numeric, bool, email, url, date, min:n, max:n,
 *        between:a,b, in:a,b,c, slug, nullable
 */
final class Validator
{
    private array $errors = [];
    private array $clean  = [];

    public function __construct(private array $data) {}

    public static function make(array $data): self
    {
        return new self($data);
    }

    /** @param array<string, string> $rules field => 'rule|rule:arg' */
    public function validate(array $rules, array $labels = []): array
    {
        foreach ($rules as $field => $ruleString) {
            $rules_    = explode('|', $ruleString);
            $label     = $labels[$field] ?? $this->humanize($field);
            $nullable  = in_array('nullable', $rules_, true);
            $value     = $this->data[$field] ?? null;

            if (is_string($value)) {
                $value = trim($value);
            }

            $isEmpty = $value === null || $value === '';

            if ($isEmpty) {
                if (in_array('required', $rules_, true)) {
                    $this->errors[$field] = "{$label} is required.";
                    continue;
                }
                if ($nullable || !array_key_exists($field, $this->data)) {
                    // Absent optional field: only carry through an explicit null.
                    if (array_key_exists($field, $this->data)) {
                        $this->clean[$field] = null;
                    }
                    continue;
                }
                $this->clean[$field] = null;
                continue;
            }

            foreach ($rules_ as $rule) {
                if ($rule === '' || $rule === 'required' || $rule === 'nullable') {
                    continue;
                }
                [$name, $arg] = array_pad(explode(':', $rule, 2), 2, null);

                $result = $this->applyRule($name, $arg, $value, $label);
                if ($result !== null) {
                    $this->errors[$field] = $result;
                    continue 2;
                }
            }

            $this->clean[$field] = $this->cast($rules_, $value);
        }

        if ($this->errors !== []) {
            throw HttpException::validation($this->errors);
        }

        return $this->clean;
    }

    private function applyRule(string $name, ?string $arg, mixed $value, string $label): ?string
    {
        switch ($name) {
            case 'string':
                return is_scalar($value) ? null : "{$label} must be text.";

            case 'int':
                return (is_int($value) || (is_string($value) && preg_match('/^-?\d+$/', $value)))
                    ? null : "{$label} must be a whole number.";

            case 'numeric':
                return is_numeric($value) ? null : "{$label} must be a number.";

            case 'bool':
                return in_array($value, [true, false, 0, 1, '0', '1', 'true', 'false'], true)
                    ? null : "{$label} must be true or false.";

            case 'email':
                return filter_var((string) $value, FILTER_VALIDATE_EMAIL)
                    ? null : "{$label} must be a valid email address.";

            case 'url':
                return filter_var((string) $value, FILTER_VALIDATE_URL)
                    && preg_match('#^https?://#i', (string) $value)
                    ? null : "{$label} must be a valid http(s) URL.";

            case 'date':
                return $this->isDate((string) $value)
                    ? null : "{$label} must be a valid date (YYYY-MM-DD).";

            case 'slug':
                return preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', (string) $value)
                    ? null : "{$label} may contain only lowercase letters, numbers and hyphens.";

            case 'min':
                if (is_numeric($value) && !is_string($value)) {
                    return $value >= (float) $arg ? null : "{$label} must be at least {$arg}.";
                }
                return mb_strlen((string) $value) >= (int) $arg
                    ? null : "{$label} must be at least {$arg} characters.";

            case 'max':
                if (is_numeric($value) && !is_string($value)) {
                    return $value <= (float) $arg ? null : "{$label} must be at most {$arg}.";
                }
                return mb_strlen((string) $value) <= (int) $arg
                    ? null : "{$label} must be {$arg} characters or fewer.";

            case 'between':
                [$lo, $hi] = array_pad(explode(',', (string) $arg), 2, '0');
                return ((float) $value >= (float) $lo && (float) $value <= (float) $hi)
                    ? null : "{$label} must be between {$lo} and {$hi}.";

            case 'in':
                $allowed = explode(',', (string) $arg);
                return in_array((string) $value, $allowed, true)
                    ? null : "{$label} must be one of: " . implode(', ', $allowed) . '.';

            default:
                return null;
        }
    }

    private function cast(array $rules, mixed $value): mixed
    {
        if (in_array('int', $rules, true)) {
            return (int) $value;
        }
        if (in_array('numeric', $rules, true)) {
            return (float) $value;
        }
        if (in_array('bool', $rules, true)) {
            return in_array($value, [true, 1, '1', 'true'], true) ? 1 : 0;
        }
        return $value;
    }

    private function isDate(string $v): bool
    {
        $d = \DateTimeImmutable::createFromFormat('Y-m-d', substr($v, 0, 10));
        return $d !== false && $d->format('Y-m-d') === substr($v, 0, 10);
    }

    private function humanize(string $field): string
    {
        return ucfirst(str_replace('_', ' ', $field));
    }
}
