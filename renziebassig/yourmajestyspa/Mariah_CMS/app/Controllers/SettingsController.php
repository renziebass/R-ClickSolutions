<?php
declare(strict_types=1);

namespace Mariah\Controllers;

use Mariah\Core\Auth;
use Mariah\Core\HttpException;
use Mariah\Core\Request;
use Mariah\Core\Response;
use Mariah\Core\Validator;
use Mariah\Repositories\SettingsRepository;
use Mariah\Services\AuditLogger;
use Mariah\Services\SettingsSchema;

/**
 * Site settings — the CMS's own configuration, editable in the browser.
 *
 * Not a ResourceController: there is no collection, no id, no soft delete and
 * no status. One record that is really a bag of keys.
 */
final class SettingsController
{
    /** GET /settings — the whole form description, so the client holds no copy of the registry. */
    public function index(Request $request): never
    {
        Response::json($this->payload());
    }

    /** PUT /settings — a partial or whole update. */
    public function update(Request $request): never
    {
        $body = $request->body();

        // An unknown key is a typo, and silently discarding it would let
        // someone believe a value saved. applyErrors() finds no matching input
        // and banners it, which is the right outcome.
        foreach (array_keys($body) as $key) {
            if (!SettingsSchema::has((string) $key)) {
                throw HttpException::validation([
                    (string) $key => '"' . $key . '" is not a known setting.',
                ]);
            }
        }

        if ($body === []) {
            throw HttpException::badRequest('No settings were supplied.');
        }

        $clean = Validator::make($body)->validate(
            SettingsSchema::rules(array_keys($body)),
            SettingsSchema::labels()
        );

        SettingsSchema::assertValid($clean);

        $changes = SettingsRepository::put($clean);

        if ($changes !== []) {
            AuditLogger::record(
                'updated',
                'setting',
                null,
                'Changed site settings: ' . implode(', ', array_keys($changes)),
                $this->loggableChanges($changes)
            );
        }

        Response::json($this->payload());
    }

    // -----------------------------------------------------------------

    /** The same shape from both endpoints, so the client refreshes from one payload. */
    private function payload(): array
    {
        $values = SettingsRepository::all();
        $groups = [];

        foreach (SettingsSchema::groups() as $label => $keys) {
            $settings = [];

            foreach ($keys as $key) {
                $definition = SettingsSchema::definitions()[$key];

                $settings[] = [
                    'key'   => $key,
                    'label' => $definition['label'],
                    'help'  => $definition['help'],
                    'type'  => $definition['type'],
                    'value' => $values[$key] ?? null,
                ];
            }

            $groups[] = ['label' => $label, 'settings' => $settings];
        }

        return [
            'groups' => $groups,
            'values' => $values,
            // Computed server-side so the client never has to guess, and so a
            // read-only role gets a form it can see but not submit.
            'can_edit' => Auth::can('settings.edit'),
            // Fed straight into session.config by the client, so the same tab
            // sees a new value without waiting for a reload.
            'values_public' => SettingsRepository::publicValues(),
        ];
    }

    /**
     * Audit metadata. A non-public setting is recorded by name only — its
     * value never reaches the log, which is readable by anyone with
     * audit_logs.view.
     */
    private function loggableChanges(array $changes): array
    {
        $public   = SettingsSchema::publicKeys();
        $loggable = [];

        foreach ($changes as $key => $change) {
            $loggable[$key] = in_array($key, $public, true) ? $change : '(hidden)';
        }

        return ['changes' => $loggable];
    }
}
