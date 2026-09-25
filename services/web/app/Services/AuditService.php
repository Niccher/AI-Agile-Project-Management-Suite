<?php

namespace App\Services;

use App\Models\AuditLogModel;
use CodeIgniter\HTTP\IncomingRequest;

class AuditService
{
    public static function record(string $action, string $entityType, int $entityId, ?array $oldValues = null, ?array $newValues = null)
    {
        $auditModel = new AuditLogModel();
        
        $request = \Config\Services::request();
        $ipAddress = $request instanceof IncomingRequest ? $request->getIPAddress() : 'cli';

        $data = [
            'user_id'     => auth()->id(),
            'action'      => $action,
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
            'old_values'  => $oldValues ? json_encode($oldValues) : null,
            'new_values'  => $newValues ? json_encode($newValues) : null,
            'ip_address'  => $ipAddress,
            'created_at'  => date('Y-m-d H:i:s'),
        ];

        return $auditModel->insert($data);
    }
}
