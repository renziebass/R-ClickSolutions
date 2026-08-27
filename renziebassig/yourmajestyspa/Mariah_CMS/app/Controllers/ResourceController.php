<?php
declare(strict_types=1);

namespace Mariah\Controllers;

use Mariah\Core\Database;
use Mariah\Core\HttpException;
use Mariah\Core\Request;
use Mariah\Core\Response;
use Mariah\Core\Slug;
use Mariah\Core\Validator;
use Mariah\Repositories\BaseRepository;
use Mariah\Services\AuditLogger;
use Mariah\Services\HtmlSanitizer;
use Mariah\Services\MediaFiler;
use Mariah\Services\MediaService;

/**
 * Shared CRUD flow for every content resource: list, show, create, update,
 * soft-delete, restore, status toggle and duplicate — each with validation,
 * slug handling and audit logging.
 *
 * Subclasses declare their rules and any entity-specific behaviour via hooks.
 */
abstract class ResourceController
{
    abstract protected function repository(): BaseRepository;

    /** Human label for messages, e.g. "Service". */
    abstract protected function label(): string;

    /** Audit entity_type, e.g. "service". */
    abstract protected function entityType(): string;

    /** Validation rules. $isUpdate relaxes `required` into optional. */
    abstract protected function rules(bool $isUpdate): array;

    /** Friendly field labels for validation messages. */
    protected function fieldLabels(): array
    {
        return [];
    }

    /** Column holding the display name — used for slugs and audit text. */
    protected function titleColumn(): string
    {
        return 'name';
    }

    /** Whether this resource maintains a slug column. */
    protected function hasSlug(): bool
    {
        return true;
    }

    /** Status values accepted by PATCH /:id/status. */
    protected function statusValues(): array
    {
        return ['active', 'inactive'];
    }

    /** Last chance to adjust the validated payload before it is written. */
    protected function prepare(array $data, Request $request, ?array $existing): array
    {
        return $data;
    }

    /**
     * Columns holding operator-authored HTML from the rich text editor.
     *
     * Listed here rather than sanitised inside each prepare() so that a
     * resource cannot acquire a rich field and quietly forget to clean it —
     * store() and update() both run this before prepare() is ever called.
     *
     * @return string[]
     */
    protected function richTextFields(): array
    {
        return [];
    }

    /** Runs inside the same transaction as the write (relations, etc.). */
    protected function afterSave(int $id, array $data, Request $request, bool $isUpdate): void
    {
    }

    /**
     * Media library folder this resource files its images into, e.g. "services".
     * Null means this resource carries no image.
     */
    protected function mediaFolder(): ?string
    {
        return null;
    }

    /**
     * Runs after the write has committed, for effects a transaction cannot
     * take back.
     *
     * Filing a photo moves a file on disk, so it belongs here rather than in
     * afterSave(): a rollback would leave the file in its new folder with no
     * record saying so. By the time this runs the record is safely stored, and
     * MediaFiler swallows its own failures, so an unfilable photo costs the
     * operator nothing.
     */
    protected function afterCommit(int $id, array $data, Request $request, bool $isUpdate): void
    {
        $folder = $this->mediaFolder();

        // array_key_exists, not isset: a partial update that never mentions
        // media_id must leave the existing photo's folder alone, while an
        // explicit null (image cleared) is a no-op MediaFiler handles.
        if ($folder !== null && array_key_exists('media_id', $data)) {
            MediaFiler::file(
                $data['media_id'] === null ? null : (int) $data['media_id'],
                $folder
            );
        }
    }

    /**
     * Copies whatever duplicate() could not: child rows, join tables, anything
     * outside the fillable column list. Without this a copy silently loses its
     * relations, which is the worst way to lose them.
     */
    protected function afterDuplicate(int $newId, int $sourceId): void
    {
    }

    /** Throws if the record must not be deleted (e.g. still referenced). */
    protected function assertDeletable(array $row): void
    {
    }

    /** Extra payload merged into the show response. */
    protected function showExtras(array $row): array
    {
        return [];
    }

    // -----------------------------------------------------------------
    // Endpoints
    // -----------------------------------------------------------------

    public function index(Request $request): never
    {
        $result = $this->repository()->paginate($request);
        Response::json($result['rows'], 200, $result['meta']);
    }

    public function show(Request $request, array $args): never
    {
        $row = $this->repository()->findOrFail($this->idFrom($args), true);
        Response::json(array_merge($row, $this->showExtras($row)));
    }

    public function store(Request $request): never
    {
        $repository = $this->repository();

        $data = Validator::make($request->body())
            ->validate($this->rules(false), $this->fieldLabels());

        $data = $this->sanitizeRichText($data);
        $data = $this->prepare($data, $request, null);

        if ($this->hasSlug()) {
            $source       = $data['slug'] ?? ($data[$this->titleColumn()] ?? '');
            $data['slug'] = Slug::unique($repository->table(), (string) $source);
        }

        if (!array_key_exists('display_order', $data) || $data['display_order'] === null) {
            $data['display_order'] = $repository->nextDisplayOrder();
        }

        $id = Database::transaction(function () use ($repository, $data, $request): int {
            $newId = $repository->create($data);
            $this->afterSave($newId, $data, $request, false);
            return $newId;
        });

        $this->afterCommit($id, $data, $request, false);

        $row   = $repository->findOrFail($id);
        $title = $this->titleOf($row);

        AuditLogger::record('created', $this->entityType(), $id, "Created {$this->label()} \"{$title}\"");

        Response::created(array_merge($row, $this->showExtras($row)));
    }

    public function update(Request $request, array $args): never
    {
        $repository = $this->repository();
        $id         = $this->idFrom($args);
        $before     = $repository->findOrFail($id, true);

        $data = Validator::make($request->body())
            ->validate($this->rules(true), $this->fieldLabels());

        $data = $this->sanitizeRichText($data);
        $data = $this->prepare($data, $request, $before);

        // Only re-slug when the client explicitly sent one, or the title moved.
        if ($this->hasSlug()) {
            $titleColumn = $this->titleColumn();

            if (array_key_exists('slug', $data) && $data['slug'] !== null && $data['slug'] !== '') {
                $data['slug'] = Slug::unique($repository->table(), (string) $data['slug'], $id);
            } elseif (array_key_exists($titleColumn, $data)
                   && (string) $data[$titleColumn] !== (string) ($before[$titleColumn] ?? '')) {
                $data['slug'] = Slug::unique($repository->table(), (string) $data[$titleColumn], $id);
            } else {
                unset($data['slug']);
            }
        }

        Database::transaction(function () use ($repository, $id, $data, $request): void {
            $repository->update($id, $data);
            $this->afterSave($id, $data, $request, true);
        });

        $this->afterCommit($id, $data, $request, true);

        // Read back after filing, so the response carries the photo's new URL
        // rather than the one it had a moment ago.
        $after = $repository->findOrFail($id, true);
        $title = $this->titleOf($after);

        AuditLogger::record(
            'updated',
            $this->entityType(),
            $id,
            "Updated {$this->label()} \"{$title}\"",
            AuditLogger::diff($before, $after)
        );

        Response::json(array_merge($after, $this->showExtras($after)));
    }

    public function destroy(Request $request, array $args): never
    {
        $repository = $this->repository();
        $id         = $this->idFrom($args);
        $row        = $repository->findOrFail($id);

        $this->assertDeletable($row);

        $repository->softDelete($id);

        $title = $this->titleOf($row);
        AuditLogger::record('deleted', $this->entityType(), $id, "Deleted {$this->label()} \"{$title}\"");

        Response::json([
            'id'      => $id,
            'deleted' => true,
            'message' => "{$this->label()} \"{$title}\" was moved to deleted items and can be restored.",
        ]);
    }

    public function restore(Request $request, array $args): never
    {
        $repository = $this->repository();
        $id         = $this->idFrom($args);
        $row        = $repository->findOrFail($id, true);

        if (($row['deleted_at'] ?? null) === null) {
            throw HttpException::conflict("That {$this->entityType()} is not deleted.");
        }

        $repository->restore($id);

        $title = $this->titleOf($row);
        AuditLogger::record('restored', $this->entityType(), $id, "Restored {$this->label()} \"{$title}\"");

        Response::json($repository->findOrFail($id));
    }

    public function setStatus(Request $request, array $args): never
    {
        $repository = $this->repository();
        $id         = $this->idFrom($args);
        $row        = $repository->findOrFail($id);

        $allowed = $this->statusValues();
        $status  = (string) $request->input('status', '');

        if (!in_array($status, $allowed, true)) {
            throw HttpException::validation(
                ['status' => 'Status must be one of: ' . implode(', ', $allowed) . '.']
            );
        }

        $repository->setStatus($id, $status);

        $title  = $this->titleOf($row);
        $action = in_array($status, ['active', 'published'], true) ? 'activated' : 'deactivated';

        AuditLogger::record(
            $action,
            $this->entityType(),
            $id,
            ucfirst($action) . " {$this->label()} \"{$title}\"",
            ['from' => $row['status'], 'to' => $status]
        );

        Response::json($repository->findOrFail($id));
    }

    /**
     * Copies a record as an inactive draft so staff can adapt it safely.
     * Relations are intentionally not copied — that is handled per resource.
     */
    public function duplicate(Request $request, array $args): never
    {
        $repository = $this->repository();
        $id         = $this->idFrom($args);
        $source     = $repository->findOrFail($id);

        $titleColumn = $this->titleColumn();
        $copy        = $this->copyableFields($source);

        $copy[$titleColumn]    = mb_substr((string) $source[$titleColumn] . ' (Copy)', 0, 190);
        $copy['status']        = $this->statusValues()[1] ?? 'inactive';
        $copy['display_order'] = $repository->nextDisplayOrder();

        if (array_key_exists('featured', $copy)) {
            $copy['featured'] = 0;
        }
        if (array_key_exists('most_loved_rank', $copy)) {
            $copy['most_loved_rank'] = null;   // the podium holds one service per rank
        }
        if (array_key_exists('published_at', $copy)) {
            $copy['published_at'] = null;      // a copy is a new post, not a re-dated old one
        }
        if ($this->hasSlug()) {
            $copy['slug'] = Slug::unique($repository->table(), (string) $copy[$titleColumn]);
        }

        $newId = $repository->create($copy);

        $this->afterDuplicate($newId, $id);

        $title = $this->titleOf($repository->findOrFail($newId));

        AuditLogger::record(
            'duplicated',
            $this->entityType(),
            $newId,
            "Duplicated {$this->label()} \"{$source[$titleColumn]}\" as \"{$title}\"",
            ['source_id' => $id]
        );

        Response::created($repository->findOrFail($newId));
    }

    // -----------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------

    /**
     * Reduces every rich text field to the allowlisted subset before anything
     * else touches it.
     *
     * The length is re-checked afterwards because Validator measured what was
     * submitted, and sanitising can add characters — a link gains
     * `rel="noopener noreferrer" target="_blank"` it did not arrive with. The
     * cap is what protects the column, so it has to apply to what is stored.
     */
    private function sanitizeRichText(array $data): array
    {
        $fields = $this->richTextFields();

        if ($fields === []) {
            return $data;
        }

        $rules  = $this->rules(true);
        $labels = $this->fieldLabels();

        foreach ($fields as $field) {
            if (!array_key_exists($field, $data)) {
                continue;   // a partial update that does not touch this column
            }

            $clean = HtmlSanitizer::clean($data[$field] === null ? '' : (string) $data[$field]);

            // Cleared in the editor means cleared in the database, not an
            // empty paragraph stored forever.
            $data[$field] = $clean === '' ? null : $clean;

            if ($clean === '') {
                continue;
            }

            $max = self::maxLengthOf($rules[$field] ?? '');

            if ($max !== null && mb_strlen($clean) > $max) {
                throw HttpException::validation([
                    $field => ($labels[$field] ?? $field)
                        . ' is too long once formatting is included — '
                        . number_format(mb_strlen($clean)) . ' of '
                        . number_format($max) . ' characters. Try shortening it.',
                ]);
            }
        }

        return $data;
    }

    /** The `max:N` value out of a Validator rule string, if it carries one. */
    private static function maxLengthOf(string $rules): ?int
    {
        return preg_match('/(?:^|\|)max:(\d+)/', $rules, $m) === 1 ? (int) $m[1] : null;
    }

    protected function idFrom(array $args): int
    {
        $id = $args['id'] ?? '';

        if (!is_string($id) || !preg_match('/^\d+$/', $id) || (int) $id < 1) {
            throw HttpException::badRequest('Invalid record id.');
        }

        return (int) $id;
    }

    protected function titleOf(array $row): string
    {
        return (string) ($row[$this->titleColumn()] ?? ('#' . ($row['id'] ?? '?')));
    }

    /** Source row reduced to the columns this resource is allowed to write. */
    private function copyableFields(array $source): array
    {
        $copy = [];

        foreach ($this->repository()->fillable() as $column) {
            if (array_key_exists($column, $source)) {
                $copy[$column] = $source[$column];
            }
        }

        unset($copy['password_hash']);

        return $copy;
    }

    /** Validates and normalises a media_id coming from a form. */
    protected function resolveMediaId(array $data): array
    {
        if (array_key_exists('media_id', $data)) {
            $data['media_id'] = MediaService::assertExists(
                $data['media_id'] === null || $data['media_id'] === '' ? null : (int) $data['media_id']
            );
        }
        return $data;
    }
}
