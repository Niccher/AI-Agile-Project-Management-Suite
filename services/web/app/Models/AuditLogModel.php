<?php

namespace App\Models;

use CodeIgniter\Model;

class AuditLogModel extends Model
{
    protected $table            = 'audit_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id', 'action', 'entity_type', 'entity_id', 
        'old_values', 'new_values', 'ip_address', 'created_at'
    ];

    // Dates
    protected $useTimestamps = false; // We set created_at manually
}
