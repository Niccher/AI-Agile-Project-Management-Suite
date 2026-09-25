<?php

namespace App\Controllers\Manager;

use App\Controllers\BaseController;
use App\Models\TaskModel;
use App\Models\ProjectModel;

class TeamDashboardController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        
        // 1. Overall Team Stats
        $taskModel = new TaskModel();
        $totalTasks = $taskModel->countAllResults();
        $approvedTasks = $taskModel->whereIn('status', ['approved', 'done'])->countAllResults();
        $pendingTasks = $taskModel->whereIn('status', ['in_progress', 'review'])->countAllResults();
        
        // 2. Performance Leaderboard (Rank workers by approved/completed tasks)
        $leaderboard = [];
        try {
            $leaderboardQuery = $db->query("
                SELECT 
                    u.id as user_id,
                    COALESCE(
                        NULLIF(TRIM(CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, ''))), ''),
                        u.username,
                        'Team Member'
                    ) as username,
                    COUNT(t.id) as completed_tasks
                FROM users u
                LEFT JOIN tasks t ON u.id = t.assigned_to AND t.status IN ('approved', 'done')
                GROUP BY u.id, u.first_name, u.last_name, u.username
                ORDER BY completed_tasks DESC
                LIMIT 10
            ");
            $leaderboard = $leaderboardQuery->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', 'Team leaderboard query error: ' . $e->getMessage());
        }

        return view('manager/team/index', [
            'totalTasks' => $totalTasks,
            'approvedTasks' => $approvedTasks,
            'pendingTasks' => $pendingTasks,
            'leaderboard' => $leaderboard
        ]);
    }
}
