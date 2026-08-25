<?php
declare(strict_types=1);

namespace Mariah\Controllers;

use Mariah\Core\Database;
use Mariah\Core\Request;
use Mariah\Core\Response;
use Mariah\Repositories\AuditLogRepository;

final class AuditLogController
{
    private AuditLogRepository $logs;

    public function __construct()
    {
        $this->logs = new AuditLogRepository();
    }

    public function index(Request $request): never
    {
        $result = $this->logs->paginate($request);
        Response::json($result['rows'], 200, $result['meta']);
    }

    /** Filter dropdown values, drawn from what the log actually contains. */
    public function filters(Request $request): never
    {
        $options = $this->logs->filterOptions();

        $options['users'] = Database::fetchAll(
            "SELECT DISTINCT u.id, CONCAT(u.first_name, ' ', u.last_name) AS name
               FROM audit_logs a
               JOIN users u ON u.id = a.user_id
              ORDER BY name"
        );

        Response::json($options);
    }
}
