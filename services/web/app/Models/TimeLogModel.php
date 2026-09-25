<?php

namespace App\Models;

use CodeIgniter\Model;

class TimeLogModel extends Model
{
    protected $table            = 'time_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id', 'project_id', 'task_name', 'start_time', 
        'end_time', 'duration', 'is_billable', 'hourly_rate', 'notes'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get recent logs for a user
     */
    public function getRecentLogs(int $userId, int $limit = 5)
    {
        return $this->select('time_logs.*, projects.name as project_name, projects.color as project_color, projects.slug as project_slug')
                    ->join('projects', 'projects.id = time_logs.project_id', 'left')
                    ->where('time_logs.user_id', $userId)
                    ->orderBy('time_logs.start_time', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * Get filtered logs with project and user details
     */
    public function getFilteredLogs(int $userId, bool $isAdmin, array $filters = []): array
    {
        $builder = $this->select('time_logs.*, projects.name as project_name, projects.slug as project_slug, projects.color as project_color, users.username as user_name')
                        ->join('projects', 'projects.id = time_logs.project_id', 'left')
                        ->join('users', 'users.id = time_logs.user_id', 'left');

        if (! $isAdmin) {
            $builder->where('time_logs.user_id', $userId);
        } elseif (! empty($filters['user_id'])) {
            $builder->where('time_logs.user_id', (int) $filters['user_id']);
        }

        if (! empty($filters['project_id'])) {
            $builder->where('time_logs.project_id', (int) $filters['project_id']);
        }

        if (! empty($filters['from_date'])) {
            $builder->where('DATE(time_logs.start_time) >=', $filters['from_date']);
        }

        if (! empty($filters['to_date'])) {
            $builder->where('DATE(time_logs.start_time) <=', $filters['to_date']);
        }

        if (isset($filters['is_billable']) && $filters['is_billable'] !== '' && $filters['is_billable'] !== 'all') {
            $builder->where('time_logs.is_billable', (int) $filters['is_billable']);
        }

        return $builder->orderBy('time_logs.start_time', 'DESC')->findAll();
    }

    /**
     * Compute summary statistics from filtered logs
     */
    public function getSummaryStats(array $logs): array
    {
        $totalSeconds = 0;
        $billableSeconds = 0;
        $totalAmount = 0.00;
        $byProject = [];
        $byUser = [];

        foreach ($logs as $log) {
            $dur = (int) ($log['duration'] ?? 0);
            $isBillable = (int) ($log['is_billable'] ?? 1) === 1;
            $rate = (float) ($log['hourly_rate'] ?? 50.00);

            $totalSeconds += $dur;
            if ($isBillable) {
                $billableSeconds += $dur;
                $totalAmount += ($dur / 3600) * $rate;
            }

            // Group by project
            $pName = $log['project_name'] ?: 'General / Unassigned';
            if (! isset($byProject[$pName])) {
                $byProject[$pName] = [
                    'name'            => $pName,
                    'color'           => $log['project_color'] ?: '#727cf5',
                    'duration'        => 0,
                    'billable_amount' => 0.00,
                ];
            }
            $byProject[$pName]['duration'] += $dur;
            if ($isBillable) {
                $byProject[$pName]['billable_amount'] += ($dur / 3600) * $rate;
            }

            // Group by user
            $uName = $log['user_name'] ?: 'Unknown User';
            if (! isset($byUser[$uName])) {
                $byUser[$uName] = [
                    'name'     => $uName,
                    'duration' => 0,
                ];
            }
            $byUser[$uName]['duration'] += $dur;
        }

        return [
            'total_seconds'      => $totalSeconds,
            'total_hours'        => round($totalSeconds / 3600, 2),
            'billable_seconds'   => $billableSeconds,
            'billable_hours'     => round($billableSeconds / 3600, 2),
            'non_billable_hours' => round(($totalSeconds - $billableSeconds) / 3600, 2),
            'total_amount'       => round($totalAmount, 2),
            'by_project'         => $byProject,
            'by_user'            => $byUser,
        ];
    }
}
