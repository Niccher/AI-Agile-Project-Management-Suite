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
        $totalTasks = (new TaskModel())->countAllResults();
        $approvedTasks = (new TaskModel())->whereIn('status', ['approved', 'done'])->countAllResults();
        $pendingTasks = (new TaskModel())->whereIn('status', ['todo', 'in_progress', 'review', 'blocked'])->countAllResults();
        $completionRate = $totalTasks > 0 ? round(($approvedTasks / $totalTasks) * 100) : 0;
        $pendingRate = $totalTasks > 0 ? round(($pendingTasks / $totalTasks) * 100) : 0;
        
        // 2. Performance Leaderboard (Rank workers by approved/completed tasks)
        $leaderboard = [];
        $hasFirstName = $db->fieldExists('first_name', 'users');
        $nameSelect = $hasFirstName 
            ? "COALESCE(NULLIF(TRIM(CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, ''))), ''), u.username, 'Team Member') as display_name"
            : "COALESCE(u.username, 'Team Member') as display_name";
        $groupBy = $hasFirstName ? "u.id, u.first_name, u.last_name, u.username" : "u.id, u.username";
        $ownerSelect = $hasFirstName 
            ? "COALESCE(NULLIF(TRIM(CONCAT(u.first_name, ' ', u.last_name)), ''), u.username) as owner_name"
            : "u.username as owner_name";

        try {
            $leaderboardQuery = $db->query("
                SELECT 
                    u.id as user_id,
                    {$nameSelect},
                    u.username,
                    COUNT(CASE WHEN t.status IN ('approved', 'done') THEN 1 END) as completed_tasks,
                    COUNT(t.id) as total_tasks
                FROM users u
                LEFT JOIN tasks t ON (t.assigned_to = u.id OR (t.assigned_to IS NULL AND t.user_id = u.id))
                GROUP BY {$groupBy}
                ORDER BY completed_tasks DESC, total_tasks DESC
                LIMIT 10
            ");
            $leaderboard = $leaderboardQuery->getResultArray();
            foreach ($leaderboard as &$entry) {
                $t = (int)($entry['total_tasks'] ?? 0);
                $c = (int)($entry['completed_tasks'] ?? 0);
                $entry['completion_rate'] = $t > 0 ? round(($c / $t) * 100) : 0;
                $entry['active_tasks'] = max(0, $t - $c);
            }
            unset($entry);
        } catch (\Throwable $e) {
            log_message('error', 'Team leaderboard query error: ' . $e->getMessage());
        }

        // 3. Projects with Assigned Users Roster
        $projectsWithMembers = [];
        try {
            $projects = $db->table('projects')
                ->select("projects.*, {$ownerSelect}")
                ->join('users u', 'u.id = projects.user_id', 'left')
                ->orderBy('projects.id', 'DESC')
                ->get()->getResultArray();

            foreach ($projects as $proj) {
                // Fetch members working on tasks in this project or owning it
                $members = $db->query("
                    SELECT 
                        u.id as user_id,
                        {$nameSelect},
                        u.username,
                        COUNT(t.id) as task_count,
                        COUNT(CASE WHEN t.status IN ('approved', 'done') THEN 1 END) as completed_task_count
                    FROM users u
                    JOIN tasks t ON (t.assigned_to = u.id OR (t.assigned_to IS NULL AND t.user_id = u.id))
                    WHERE t.project_id = ?
                    GROUP BY {$groupBy}
                    ORDER BY completed_task_count DESC, task_count DESC
                ", [$proj['id']])->getResultArray();

                // If owner is not in members (e.g. no tasks assigned directly to owner), add owner as lead
                $ownerInMembers = false;
                foreach ($members as $m) {
                    if ((int)$m['user_id'] === (int)$proj['user_id']) {
                        $ownerInMembers = true;
                        break;
                    }
                }

                if (!$ownerInMembers && !empty($proj['user_id'])) {
                    $ownerRow = $db->table('users')->where('id', $proj['user_id'])->get()->getRowArray();
                    if ($ownerRow) {
                        $dName = trim(($ownerRow['first_name'] ?? '') . ' ' . ($ownerRow['last_name'] ?? ''));
                        array_unshift($members, [
                            'user_id' => $ownerRow['id'],
                            'display_name' => $dName ?: ($ownerRow['username'] ?? 'Project Lead'),
                            'username' => $ownerRow['username'] ?? 'owner',
                            'task_count' => 0,
                            'completed_task_count' => 0,
                            'is_lead' => true,
                        ]);
                    }
                }

                $totalProjTasks = (int)$db->table('tasks')->where('project_id', $proj['id'])->countAllResults();
                $doneProjTasks = (int)$db->table('tasks')->where('project_id', $proj['id'])->whereIn('status', ['approved', 'done'])->countAllResults();
                $progressPct = $totalProjTasks > 0 ? round(($doneProjTasks / $totalProjTasks) * 100) : 0;

                $proj['members'] = $members;
                $proj['total_tasks'] = $totalProjTasks;
                $proj['done_tasks'] = $doneProjTasks;
                $proj['progress_pct'] = $progressPct;
                $projectsWithMembers[] = $proj;
            }
        } catch (\Throwable $e) {
            log_message('error', 'Projects team roster query error: ' . $e->getMessage());
        }

        return view('manager/team/index', [
            'totalTasks' => $totalTasks,
            'approvedTasks' => $approvedTasks,
            'pendingTasks' => $pendingTasks,
            'completionRate' => $completionRate,
            'pendingRate' => $pendingRate,
            'leaderboard' => $leaderboard,
            'projectsWithMembers' => $projectsWithMembers
        ]);
    }
}
