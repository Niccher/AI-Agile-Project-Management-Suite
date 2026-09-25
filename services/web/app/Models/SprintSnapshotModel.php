<?php

namespace App\Models;

use CodeIgniter\Model;

class SprintSnapshotModel extends Model
{
    protected $table            = 'sprint_snapshots';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'sprint_id',
        'snapshot_date',
        'remaining_points',
        'completed_points',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = '';
}
