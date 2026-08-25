<?php
declare(strict_types=1);

namespace Mariah\Repositories;

use Mariah\Core\Database;
use Mariah\Core\Request;

final class AuditLogRepository extends BaseRepository
{
    protected string $table  = 'audit_logs';
    protected string $entity = 'audit log entry';
    protected string $alias  = 'a';
    protected bool $softDeletes = false;   // the audit trail is append-only

    protected array $fillable = [];        // written only via AuditLogger

    protected array $sortable = [
        'created_at'  => 'a.created_at',
        'action'      => 'a.action',
        'entity_type' => 'a.entity_type',
        'user'        => 'a.user_label',
        'id'          => 'a.id',
    ];

    protected array $searchable = ['a.description', 'a.user_label', 'a.entity_type'];

    protected string $defaultSort      = 'created_at';
    protected string $defaultDirection = 'DESC';

    protected function listSelect(): string
    {
        return "a.*, CONCAT(u.first_name, ' ', u.last_name) AS current_user_name, u.email AS user_email";
    }

    protected function listJoins(): string
    {
        return 'LEFT JOIN users u ON u.id = a.user_id';
    }

    protected function listFilters(Request $request): array
    {
        $conditions = [];
        $bindings   = [];

        if (($userId = $request->q('user_id')) !== null && is_numeric($userId)) {
            $conditions[] = 'a.user_id = ?';
            $bindings[]   = (int) $userId;
        }

        if (($action = $request->q('action')) !== null) {
            $conditions[] = 'a.action = ?';
            $bindings[]   = (string) $action;
        }

        if (($entityType = $request->q('entity_type')) !== null) {
            $conditions[] = 'a.entity_type = ?';
            $bindings[]   = (string) $entityType;
        }

        if (($from = $request->q('date_from')) !== null) {
            $conditions[] = 'a.created_at >= ?';
            $bindings[]   = substr((string) $from, 0, 10) . ' 00:00:00';
        }

        if (($to = $request->q('date_to')) !== null) {
            $conditions[] = 'a.created_at <= ?';
            $bindings[]   = substr((string) $to, 0, 10) . ' 23:59:59';
        }

        return [$conditions, $bindings];
    }

    protected function decorate(array $row): array
    {
        $row['id']        = (int) $row['id'];
        $row['user_id']   = $row['user_id'] === null ? null : (int) $row['user_id'];
        $row['entity_id'] = $row['entity_id'] === null ? null : (int) $row['entity_id'];

        // The account may have been renamed or deleted since the entry was written.
        $row['actor'] = $row['current_user_name'] ?: ($row['user_label'] ?: 'System');

        if (is_string($row['metadata']) && $row['metadata'] !== '') {
            $decoded = json_decode($row['metadata'], true);
            $row['metadata'] = is_array($decoded) ? $decoded : null;
        }

        unset($row['current_user_name']);

        return $row;
    }

    public function recent(int $limit = 8): array
    {
        $limit = max(1, min(50, $limit));

        return array_map(
            [$this, 'decorate'],
            Database::fetchAll(
                "SELECT a.*, CONCAT(u.first_name, ' ', u.last_name) AS current_user_name,
                        u.email AS user_email
                   FROM audit_logs a
                   LEFT JOIN users u ON u.id = a.user_id
                  ORDER BY a.created_at DESC, a.id DESC
                  LIMIT {$limit}"
            )
        );
    }

    /** Distinct values, so the UI filters offer only what actually exists. */
    public function filterOptions(): array
    {
        return [
            'actions'      => array_column(
                Database::fetchAll('SELECT DISTINCT action FROM audit_logs ORDER BY action'),
                'action'
            ),
            'entity_types' => array_column(
                Database::fetchAll('SELECT DISTINCT entity_type FROM audit_logs ORDER BY entity_type'),
                'entity_type'
            ),
        ];
    }
}
