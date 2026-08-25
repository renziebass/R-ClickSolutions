<?php
declare(strict_types=1);

namespace Mariah\Repositories;

use Mariah\Core\Auth;
use Mariah\Core\Database;
use Mariah\Core\HttpException;
use Mariah\Core\Paginator;
use Mariah\Core\Request;

/**
 * Shared CRUD behaviour: soft deletes, authorship stamping, search, filtering,
 * safe sorting and pagination.
 *
 * Column and sort names never come from raw input — they are resolved through
 * per-repository allowlists, so no user-supplied string ever reaches SQL as an
 * identifier. Values always travel as bound parameters.
 */
abstract class BaseRepository
{
    /** Physical table name. */
    protected string $table;

    /** Entity name used in audit-log lines, e.g. "service". */
    protected string $entity;

    /** Columns that may be written by the API. */
    protected array $fillable = [];

    /** input sort key => qualified SQL column. */
    protected array $sortable = ['id' => 'id'];

    /** Columns included in a LIKE search. */
    protected array $searchable = [];

    protected string $defaultSort      = 'id';
    protected string $defaultDirection = 'DESC';

    /** Table alias used in list queries. */
    protected string $alias = 't';

    /** Some tables (roles, audit_logs) are not soft-deletable. */
    protected bool $softDeletes = true;

    public function table(): string  { return $this->table; }
    public function entity(): string { return $this->entity; }

    /** Columns this repository is allowed to write — used when duplicating. */
    public function fillable(): array { return $this->fillable; }

    // ---------------------------------------------------------------
    // Reading
    // ---------------------------------------------------------------

    /** SELECT clause for list queries. Overridden to add joins/derived cols. */
    protected function listSelect(): string
    {
        return "{$this->alias}.*";
    }

    /** JOINs appended after the FROM clause. */
    protected function listJoins(): string
    {
        return '';
    }

    /**
     * Extra WHERE fragments from request filters.
     * @return array{0: string[], 1: array} [conditions, bindings]
     */
    protected function listFilters(Request $request): array
    {
        return [[], []];
    }

    /** Post-processes each row before it leaves the repository. */
    protected function decorate(array $row): array
    {
        return $row;
    }

    /**
     * Paginated, searched, filtered and sorted list for the admin tables.
     * `?deleted=only|with` exposes the recycle bin.
     */
    public function paginate(Request $request): array
    {
        $a          = $this->alias;
        $conditions = [];
        $bindings   = [];

        // --- soft-delete scope ---
        if ($this->softDeletes) {
            $deleted = strtolower((string) $request->q('deleted', ''));
            if ($deleted === 'only') {
                $conditions[] = "{$a}.deleted_at IS NOT NULL";
            } elseif ($deleted !== 'with') {
                $conditions[] = "{$a}.deleted_at IS NULL";
            }
        }

        // --- free-text search ---
        $search = trim((string) $request->q('search', ''));
        if ($search !== '' && $this->searchable !== []) {
            $parts = [];
            foreach ($this->searchable as $column) {
                $parts[]    = "{$column} LIKE ?";
                $bindings[] = '%' . $this->escapeLike($search) . '%';
            }
            $conditions[] = '(' . implode(' OR ', $parts) . ')';
        }

        // --- repository-specific filters ---
        [$extraConditions, $extraBindings] = $this->listFilters($request);
        $conditions = array_merge($conditions, $extraConditions);
        $bindings   = array_merge($bindings, $extraBindings);

        $where = $conditions === [] ? '' : ' WHERE ' . implode(' AND ', $conditions);
        $joins = $this->listJoins();

        $total = (int) Database::fetchValue(
            "SELECT COUNT(*) FROM `{$this->table}` {$a} {$joins}{$where}",
            $bindings
        );

        $paginator = Paginator::fromRequest($request);
        $orderBy   = $this->resolveSort($request);

        // No GROUP BY: every listJoins() is a many-to-one LEFT JOIN on a primary
        // key, so rows are never multiplied. Grouping here would also break
        // under MySQL's ONLY_FULL_GROUP_BY, which is on by default in 8.0.
        // LIMIT/OFFSET are cast ints, never interpolated strings.
        $rows = Database::fetchAll(
            "SELECT {$this->listSelect()}
               FROM `{$this->table}` {$a} {$joins}{$where}
              ORDER BY {$orderBy}
              LIMIT {$paginator->perPage} OFFSET {$paginator->offset()}",
            $bindings
        );

        return [
            'rows' => array_map([$this, 'decorate'], $rows),
            'meta' => $paginator->meta($total),
        ];
    }

    /** Resolves ?sort= and ?direction= through the allowlist. */
    protected function resolveSort(Request $request): string
    {
        $key    = (string) $request->q('sort', $this->defaultSort);
        $column = $this->sortable[$key] ?? $this->sortable[$this->defaultSort] ?? "{$this->alias}.id";

        $direction = strtoupper((string) $request->q('direction', $this->defaultDirection));
        $direction = $direction === 'ASC' ? 'ASC' : 'DESC';

        // Stable tiebreak so pagination never repeats or skips rows.
        return "{$column} {$direction}, {$this->alias}.id DESC";
    }

    public function find(int $id, bool $withDeleted = false): ?array
    {
        $sql = "SELECT {$this->listSelect()}
                  FROM `{$this->table}` {$this->alias} {$this->listJoins()}
                 WHERE {$this->alias}.id = ?";

        if ($this->softDeletes && !$withDeleted) {
            $sql .= " AND {$this->alias}.deleted_at IS NULL";
        }

        $row = Database::fetchOne($sql . ' LIMIT 1', [$id]);

        return $row === null ? null : $this->decorate($row);
    }

    public function findOrFail(int $id, bool $withDeleted = false): array
    {
        $row = $this->find($id, $withDeleted);
        if ($row === null) {
            throw HttpException::notFound(
                'That ' . $this->entity . ' was not found. It may have been deleted.'
            );
        }
        return $row;
    }

    // ---------------------------------------------------------------
    // Writing
    // ---------------------------------------------------------------

    /** @return int new id */
    public function create(array $data): int
    {
        $data = $this->onlyFillable($data);

        if ($this->hasColumn('created_by')) {
            $data['created_by'] = Auth::id();
            $data['updated_by'] = Auth::id();
        }

        if ($data === []) {
            throw HttpException::badRequest('No values were supplied.');
        }

        $columns      = array_keys($data);
        $placeholders = implode(', ', array_fill(0, count($columns), '?'));
        $columnList   = '`' . implode('`, `', $columns) . '`';

        Database::run(
            "INSERT INTO `{$this->table}` ({$columnList}) VALUES ({$placeholders})",
            array_values($data)
        );

        return Database::insertId();
    }

    public function update(int $id, array $data): void
    {
        $data = $this->onlyFillable($data);

        if ($this->hasColumn('updated_by')) {
            $data['updated_by'] = Auth::id();
        }

        if ($data === []) {
            return;
        }

        $assignments = implode(', ', array_map(
            static fn (string $c): string => "`{$c}` = ?",
            array_keys($data)
        ));

        $values   = array_values($data);
        $values[] = $id;

        Database::run("UPDATE `{$this->table}` SET {$assignments} WHERE id = ?", $values);
    }

    public function softDelete(int $id): void
    {
        Database::run(
            "UPDATE `{$this->table}` SET deleted_at = NOW(), deleted_by = ? WHERE id = ?",
            [Auth::id(), $id]
        );
    }

    public function restore(int $id): void
    {
        Database::run(
            "UPDATE `{$this->table}` SET deleted_at = NULL, deleted_by = NULL WHERE id = ?",
            [$id]
        );
    }

    public function setStatus(int $id, string $status): void
    {
        $update = ['status' => $status];
        if ($this->hasColumn('updated_by')) {
            $update['updated_by'] = Auth::id();
        }

        $assignments = implode(', ', array_map(
            static fn (string $c): string => "`{$c}` = ?",
            array_keys($update)
        ));

        $values   = array_values($update);
        $values[] = $id;

        Database::run("UPDATE `{$this->table}` SET {$assignments} WHERE id = ?", $values);
    }

    /** Highest display_order + 1, so new records land at the end of the list. */
    public function nextDisplayOrder(): int
    {
        $max = Database::fetchValue("SELECT MAX(display_order) FROM `{$this->table}`");
        return $max === null ? 0 : ((int) $max) + 1;
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    protected function onlyFillable(array $data): array
    {
        return array_intersect_key($data, array_flip($this->fillable));
    }

    protected function hasColumn(string $column): bool
    {
        static $cache = [];

        $key = $this->table;
        if (!isset($cache[$key])) {
            $cache[$key] = array_column(
                Database::fetchAll("SHOW COLUMNS FROM `{$this->table}`"),
                'Field'
            );
        }

        return in_array($column, $cache[$key], true);
    }

    /** LIKE wildcards in user input must be literal, not operators. */
    protected function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $value);
    }
}
