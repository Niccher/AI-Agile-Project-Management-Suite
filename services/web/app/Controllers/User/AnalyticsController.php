<?php

namespace App\Controllers\User;

use App\Models\ProjectModel;

class AnalyticsController extends BaseUserController
{
    public function index($projectIdentifier = null)
    {
        $db = \Config\Database::connect();
        $projectModel = new ProjectModel();
        $isAdmin = auth()->user() && auth()->user()->inGroup('admin', 'manager');
        
        $selectedProject = null;
        if ($projectIdentifier !== null) {
            $selectedProject = $projectModel->findByIdentifier($projectIdentifier, $this->userId, $isAdmin);
        }

        // Total projects count
        $pQuery = $isAdmin ? (new ProjectModel()) : (new ProjectModel())->where('user_id', $this->userId);
        $totalProjects = $pQuery->countAllResults();

        $compQuery = $isAdmin ? (new ProjectModel())->where('status', 'completed') : (new ProjectModel())->where('user_id', $this->userId)->where('status', 'completed');
        $completedProjects = $compQuery->countAllResults();

        // Completion rate
        $completionRate = $totalProjects > 0 ? round(($completedProjects / $totalProjects) * 100) : 0;

        // Hours logged
        $tlQuery = $db->table('time_logs')->selectSum('duration');
        if (!$isAdmin) {
            $tlQuery->where('user_id', $this->userId);
        }
        $totalSeconds = $tlQuery->get()->getRow()->duration ?? 0;
        $totalHours = round($totalSeconds / 3600, 1);

        // Avg daily
        $daysQuery = $db->table('time_logs')->select('COUNT(DISTINCT DATE(start_time)) as days');
        if (!$isAdmin) {
            $daysQuery->where('user_id', $this->userId);
        }
        $daysLogged = $daysQuery->get()->getRow()->days ?? 1;
        $avgDaily = $daysLogged > 0 ? round($totalHours / $daysLogged, 1) : 0;

        // Monthly trends (last 6 months)
        $monthlyTrends = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-{$i} months"));
            $monthStart = $month . '-01';
            $monthEnd = date('Y-m-t', strtotime($monthStart));
            
            $started = $projectModel->where('user_id', $this->userId)
                ->where('created_at >=', $monthStart)
                ->where('created_at <=', $monthEnd . ' 23:59:59')
                ->countAllResults();
            
            $done = $projectModel->where('user_id', $this->userId)
                ->where('updated_at >=', $monthStart)
                ->where('updated_at <=', $monthEnd . ' 23:59:59')
                ->where('status', 'completed')
                ->countAllResults();

            $monthlyTrends[] = [
                'month' => date('M', strtotime($monthStart)),
                'started' => $started,
                'completed' => $done,
            ];
        }

        // Project health distribution
        $good = $projectModel->where('user_id', $this->userId)->where('status', 'in_progress')->where('progress >=', 50)->countAllResults();
        $warning = $projectModel->where('user_id', $this->userId)->where('status', 'in_progress')->where('progress <', 50)->where('progress >', 0)->countAllResults();
        $danger = $projectModel->where('user_id', $this->userId)->whereIn('status', ['planning', 'on_hold'])->countAllResults();
        $archived = $projectModel->where('user_id', $this->userId)->where('is_archived', 1)->countAllResults();
        $healthTotal = max($good + $warning + $danger + $archived, 1);

        // Time distribution by project
        $timeDistribution = $db->table('time_logs')
            ->select('projects.name, projects.color, COALESCE(SUM(time_logs.duration), 0) as total_duration')
            ->join('projects', 'projects.id = time_logs.project_id', 'left')
            ->where('time_logs.user_id', $this->userId)
            ->groupBy('time_logs.project_id')
            ->orderBy('total_duration', 'DESC')
            ->limit(5)
            ->get()->getResultArray();

        $allTimeTotal = max(array_sum(array_column($timeDistribution, 'total_duration')), 1);

        // Activity heatmap data - last 30 days
        $heatmapData = [];
        for ($i = 29; $i >= 0; $i--) {
            $day = date('Y-m-d', strtotime("-{$i} days"));
            $dayStart = $day . ' 00:00:00';
            $dayEnd   = $day . ' 23:59:59';
            $activityCount = 0;
            
            $tlBuilder = $db->table('time_logs')
                ->where('start_time >=', $dayStart)
                ->where('start_time <=', $dayEnd);
            if (!$isAdmin) {
                $tlBuilder->where('user_id', $this->userId);
            }
            $activityCount += $tlBuilder->countAllResults();
            
            $noteBuilder = $db->table('notes')
                ->where('created_at >=', $dayStart)
                ->where('created_at <=', $dayEnd);
            if (!$isAdmin) {
                $noteBuilder->where('user_id', $this->userId);
            }
            $activityCount += $noteBuilder->countAllResults();

            $projBuilder = $db->table('projects')
                ->where('updated_at >=', $dayStart)
                ->where('updated_at <=', $dayEnd);
            if (!$isAdmin) {
                $projBuilder->where('user_id', $this->userId);
            }
            $activityCount += $projBuilder->countAllResults();

            $heatmapData[] = [
                'date' => $day,
                'count' => min($activityCount, 4),
            ];
        }

        // Insights
        $insights = [];
        if ($completionRate >= 50) {
            $insights[] = [
                'type' => 'positive',
                'icon' => 'fa-arrow-up',
                'color' => 'text-success',
                'title' => 'Productivity Increase',
                'message' => "Your completion rate is {$completionRate}%. Keep up the momentum!",
            ];
        } else {
            $insights[] = [
                'type' => 'warning',
                'icon' => 'fa-clock',
                'color' => 'text-warning',
                'title' => 'Time Distribution',
                'message' => 'Consider breaking down projects into smaller, achievable milestones.',
            ];
        }

        $overdueCount = $db->table('projects')
            ->where('user_id', $this->userId)
            ->where('due_date <', date('Y-m-d'))
            ->where('status !=', 'completed')
            ->countAllResults();
        
        if ($overdueCount > 0) {
            $insights[] = [
                'type' => 'danger',
                'icon' => 'fa-exclamation-triangle',
                'color' => 'text-danger',
                'title' => 'Overdue Projects',
                'message' => "You have {$overdueCount} overdue project(s) that need attention.",
            ];
        }

        $insights[] = [
            'type' => 'info',
            'icon' => 'fa-calendar',
            'color' => 'text-info',
            'title' => 'Consistency',
            'message' => $daysLogged > 0 ? "You've logged time on {$daysLogged} different days. " . ($avgDaily > 0 ? "Average {$avgDaily}h/day." : '') : 'Start tracking time to see consistency insights.',
        ];

        // Completed this month
        $monthStart = date('Y-m-01');
        $monthEnd = date('Y-m-t 23:59:59');
        $completedThisMonth = $projectModel->where('user_id', $this->userId)
            ->where('status', 'completed')
            ->where('updated_at >=', $monthStart)
            ->where('updated_at <=', $monthEnd)
            ->findAll();

        // Stalled projects (no update in 14+ days)
        $stalledProjects = $projectModel->where('user_id', $this->userId)
            ->where('status', 'in_progress')
            ->where('updated_at <', date('Y-m-d', strtotime('-14 days')))
            ->where('is_archived', 0)
            ->findAll();

        $stalledTasks = [];
        foreach ($stalledProjects as $sp) {
            $stalledTasks[] = esc($sp['name']) . ' has been inactive for ' . round((time() - strtotime($sp['updated_at'])) / 86400) . ' days';
        }

        // Recent notes completed
        $recentDone = [];
        $doneNotes = $db->table('notes')
            ->where('user_id', $this->userId)
            ->where('is_completed', 1)
            ->where('is_deleted', 0)
            ->orderBy('updated_at', 'DESC')
            ->limit(5)
            ->get()->getResultArray();
        foreach ($doneNotes as $dn) {
            $recentDone[] = esc($dn['title']);
        }

        // Team Work & Staff Performance Roster (For Admin & Manager Viewers)
        $teamRoster = [];
        if ($isAdmin) {
            try {
                $users = $db->table('users')
                    ->select('users.id, users.username, users.first_name, users.last_name, users.active')
                    ->get()->getResultArray();

                $thirtyDaysAgo = date('Y-m-d H:i:s', strtotime('-30 days'));

                foreach ($users as $u) {
                    $uId = (int)$u['id'];

                    // Tasks stats
                    $totalAssigned = (int)$db->table('tasks')->where('assigned_to', $uId)->countAllResults();
                    $completedTasks = (int)$db->table('tasks')->where('assigned_to', $uId)->whereIn('status', ['approved', 'done'])->countAllResults();
                    $inProgressTasks = (int)$db->table('tasks')->where('assigned_to', $uId)->where('status', 'in_progress')->countAllResults();
                    $reviewTasks = (int)$db->table('tasks')->where('assigned_to', $uId)->where('status', 'review')->countAllResults();
                    $overdueTasks = (int)$db->table('tasks')
                        ->where('assigned_to', $uId)
                        ->where('due_date <', date('Y-m-d'))
                        ->whereNotIn('status', ['approved', 'done'])
                        ->countAllResults();

                    // Time logs
                    $timeLog30d = $db->table('time_logs')
                        ->select('COALESCE(SUM(duration), 0) as total_sec')
                        ->where('user_id', $uId)
                        ->where('start_time >=', $thirtyDaysAgo)
                        ->get()->getRowArray();
                    $loggedHours30d = round(((int)($timeLog30d['total_sec'] ?? 0)) / 3600, 1);

                    $timeLogAll = $db->table('time_logs')
                        ->select('COALESCE(SUM(duration), 0) as total_sec')
                        ->where('user_id', $uId)
                        ->get()->getRowArray();
                    $loggedHoursAll = round(((int)($timeLogAll['total_sec'] ?? 0)) / 3600, 1);

                    // User Group / Role
                    $groupRow = $db->table('auth_groups_users')->where('user_id', $uId)->get()->getRowArray();
                    $groupName = $groupRow['group'] ?? 'user';

                    // Exclude idle admins/managers who have 0 tasks and 0 logged hours
                    $isManagerOrAdmin = in_array($groupName, ['admin', 'manager', 'superadmin'], true);
                    $hasWork = ($totalAssigned > 0 || $loggedHoursAll > 0 || $inProgressTasks > 0);

                    if ($isManagerOrAdmin && !$hasWork) {
                        continue; // Skip idle admin / manager without work
                    }

                    // Active projects worked on
                    $activeProjects = $db->table('tasks')
                        ->select('projects.name')
                        ->join('projects', 'projects.id = tasks.project_id', 'left')
                        ->where('tasks.assigned_to', $uId)
                        ->distinct()
                        ->limit(3)
                        ->get()->getResultArray();
                    $projectNames = array_filter(array_column($activeProjects, 'name'));

                    $displayName = trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? '')) ?: ($u['username'] ?? 'User');
                    $compPct = $totalAssigned > 0 ? round(($completedTasks / $totalAssigned) * 100) : ($loggedHours30d > 0 ? 100 : 0);

                    $teamRoster[] = [
                        'user_id'           => $uId,
                        'display_name'      => $displayName,
                        'username'          => $u['username'],
                        'role'              => ucfirst($groupName),
                        'is_manager'        => $isManagerOrAdmin,
                        'total_assigned'    => $totalAssigned,
                        'completed_tasks'   => $completedTasks,
                        'in_progress_tasks' => $inProgressTasks,
                        'review_tasks'      => $reviewTasks,
                        'overdue_tasks'     => $overdueTasks,
                        'logged_hours_30d'  => $loggedHours30d,
                        'logged_hours_all'  => $loggedHoursAll,
                        'project_names'     => $projectNames,
                        'completion_rate'   => $compPct,
                    ];
                }

                // Sort by logged_hours_30d DESC or completed_tasks DESC
                usort($teamRoster, function($a, $b) {
                    if ($b['logged_hours_30d'] === $a['logged_hours_30d']) {
                        return $b['completed_tasks'] <=> $a['completed_tasks'];
                    }
                    return ($b['logged_hours_30d'] > $a['logged_hours_30d']) ? 1 : -1;
                });
            } catch (\Throwable $e) {
                log_message('error', 'Team roster analytics query error: ' . $e->getMessage());
            }
        }

        $data = [
            'user'              => $this->currentUser,
            'isAdmin'           => $isAdmin,
            'isTeamView'        => $isAdmin,
            'teamRoster'        => $teamRoster,
            'totalProjects'     => $totalProjects,
            'completionRate'    => $completionRate,
            'totalHours'        => $totalHours,
            'avgDaily'          => $avgDaily,
            'monthlyTrends'     => $monthlyTrends,
            'good'              => $good,
            'warningCount'      => $warning,
            'dangerCount'       => $danger,
            'archivedCount'     => $archived,
            'healthTotal'       => $healthTotal,
            'goodPct'           => round(($good / $healthTotal) * 100),
            'warningPct'        => round(($warning / $healthTotal) * 100),
            'dangerPct'         => round(($danger / $healthTotal) * 100),
            'archivedPct'       => round(($archived / $healthTotal) * 100),
            'timeDistribution'  => $timeDistribution,
            'allTimeTotal'      => $allTimeTotal,
            'heatmapData'       => $heatmapData,
            'insights'          => $insights,
            'completedThisMonth' => $completedThisMonth,
            'stalledTasks'      => $stalledTasks,
            'recentDone'        => $recentDone,
            'thisMonthStarted'  => $projectModel->where('user_id', $this->userId)
                ->where('created_at >=', $monthStart)
                ->where('created_at <=', $monthEnd)
                ->countAllResults(),
            'projectsCount'     => $totalProjects,
        ];

        return view('user/analytics', $data);
    }
}
