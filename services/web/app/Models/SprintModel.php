<?php

namespace App\Models;

use CodeIgniter\Model;

class SprintModel extends Model
{
    protected $table            = 'sprints';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'project_id',
        'name',
        'goal',
        'status',
        'start_date',
        'end_date',
        'total_points',
        'completed_points',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get active sprint for a project
     */
    public function getActiveSprint(int $projectId)
    {
        return $this->where('project_id', $projectId)
                    ->where('status', 'active')
                    ->first();
    }

    /**
     * Recalculate story points for a sprint
     */
    public function recalculatePoints(int $sprintId): array
    {
        $db = \Config\Database::connect();
        $tasks = $db->table('tasks')
                    ->select('status, story_points')
                    ->where('sprint_id', $sprintId)
                    ->get()
                    ->getResultArray();

        $total = 0;
        $completed = 0;

        foreach ($tasks as $t) {
            $pts = (int)($t['story_points'] ?? 0);
            $total += $pts;
            if (in_array($t['status'], ['done', 'approved'])) {
                $completed += $pts;
            }
        }

        $this->update($sprintId, [
            'total_points'     => $total,
            'completed_points' => $completed
        ]);

        return [
            'total_points'     => $total,
            'completed_points' => $completed,
            'remaining_points' => max(0, $total - $completed)
        ];
    }
}
