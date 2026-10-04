<?php

namespace App\Controllers\User;

use App\Models\ProjectModel;
use App\Models\TaskModel;
use App\Models\SprintModel;
use App\Models\TimeLogModel;
use App\Models\UserModel;
use App\Models\AuditLogModel;

class MyDashboardController extends BaseUserController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $projectModel = new ProjectModel();
        $taskModel    = new TaskModel();
        $sprintModel  = new SprintModel();
        $timeLogModel = new TimeLogModel();

        $isAdminOrManager = false;
        if ($this->currentUser) {
            $isAdminOrManager = $this->currentUser->inGroup('admin', 'manager') || is_solo_mode();
        }

        // 1. Projects with Health Scoring
        $projects = [];
        try {
            $projects = $projectModel->getProjectsWithHealth($this->userId, $isAdminOrManager);
        } catch (\Throwable $e) {
            log_message('error', 'Dashboard getProjectsWithHealth error: ' . $e->getMessage());
            $projects = $projectModel->findAll();
        }

        // 2. Project counts
        $totalProjects = count($projects);
        $activeProjects = 0;
        $completedProjects = 0;
        $planningProjects = 0;
        foreach ($projects as $p) {
            $st = $p['status'] ?? 'in_progress';
            if ($st === 'completed') $completedProjects++;
            elseif ($st === 'in_progress') $activeProjects++;
            else $planningProjects++;
        }

        // 3. Sprint & Task Metrics across accessible scope
        $taskSelect = $isAdminOrManager 
            ? $taskModel->select('tasks.*, projects.name as project_name, projects.slug as project_slug, projects.color as project_color')
                        ->join('projects', 'projects.id = tasks.project_id', 'left')
            : $taskModel->select('tasks.*, projects.name as project_name, projects.slug as project_slug, projects.color as project_color')
                        ->join('projects', 'projects.id = tasks.project_id', 'left')
                        ->groupStart()
                            ->where('tasks.user_id', $this->userId)
                            ->orWhere('tasks.assigned_to', $this->userId)
                            ->orWhere('projects.user_id', $this->userId)
                        ->groupEnd();

        $allTasks = $taskSelect->orderBy('tasks.id', 'DESC')->findAll(100);

        $totalTasks = count($allTasks);
        $doneTasks = 0;
        $inProgressTasks = 0;
        $reviewTasks = 0;
        $todoTasks = 0;
        $blockedTasks = 0;
        $overdueTasks = 0;
        $now = time();

        $statusBreakdown = [
            'Todo'        => 0,
            'In Progress' => 0,
            'In Review'   => 0,
            'Completed'   => 0,
            'Blocked'     => 0,
        ];

        foreach ($allTasks as $t) {
            $st = $t['status'] ?? 'todo';
            if ($st === 'done' || $st === 'approved') {
                $doneTasks++;
                $statusBreakdown['Completed']++;
            } elseif ($st === 'in_progress') {
                $inProgressTasks++;
                $statusBreakdown['In Progress']++;
            } elseif ($st === 'review') {
                $reviewTasks++;
                $statusBreakdown['In Review']++;
            } elseif ($st === 'blocked') {
                $blockedTasks++;
                $statusBreakdown['Blocked']++;
            } else {
                $todoTasks++;
                $statusBreakdown['Todo']++;
            }

            if (!empty($t['due_date']) && strtotime($t['due_date']) < $now && !in_array($st, ['done', 'approved'])) {
                $overdueTasks++;
            }
        }

        $completionRate = $totalTasks > 0 ? round(($doneTasks / $totalTasks) * 100) : 0;

        // 4. My Action Items (Tasks specifically assigned to or created by current user)
        $myTasks = [];
        foreach ($allTasks as $t) {
            if ((int)($t['assigned_to'] ?? 0) === $this->userId || (int)($t['user_id'] ?? 0) === $this->userId || $isAdminOrManager) {
                if (!in_array($t['status'] ?? '', ['done', 'approved'])) {
                    $myTasks[] = $t;
                }
            }
        }
        // Limit action items to 6
        $myTasks = array_slice($myTasks, 0, 6);

        // 5. Active Sprints
        $activeSprints = [];
        try {
            $activeSprints = $db->table('sprints')
                ->select('sprints.*, projects.name as project_name, projects.slug as project_slug, projects.color as project_color')
                ->join('projects', 'projects.id = sprints.project_id', 'left')
                ->where('sprints.status', 'active')
                ->orderBy('sprints.end_date', 'ASC')
                ->get()->getResultArray();

            foreach ($activeSprints as &$sp) {
                $spTasks = $taskModel->where('sprint_id', $sp['id'])->findAll();
                $spTotal = count($spTasks);
                $spDone = 0;
                foreach ($spTasks as $spt) {
                    if (in_array($spt['status'] ?? '', ['done', 'approved'])) $spDone++;
                }
                $sp['total_tasks'] = $spTotal;
                $sp['done_tasks'] = $spDone;
                $sp['progress'] = $spTotal > 0 ? round(($spDone / $spTotal) * 100) : 0;
                
                $daysRemaining = 0;
                if (!empty($sp['end_date'])) {
                    $diff = strtotime($sp['end_date']) - time();
                    $daysRemaining = max(0, ceil($diff / 86400));
                }
                $sp['days_remaining'] = $daysRemaining;
            }
            unset($sp);
        } catch (\Throwable $e) {
            log_message('error', 'Dashboard sprints query error: ' . $e->getMessage());
        }

        // 6. Time Logged Effort
        $weeklyHours = 0;
        $totalHours = 0;
        try {
            $startOfWeek = date('Y-m-d 00:00:00', strtotime('monday this week'));
            $weekLogs = $timeLogModel->where('user_id', $this->userId)
                                     ->where('start_time >=', $startOfWeek)
                                     ->findAll();
            $totalSecs = 0;
            foreach ($weekLogs as $wl) {
                $totalSecs += (int)($wl['duration'] ?? 0);
            }
            $weeklyHours = round($totalSecs / 3600, 1);

            $allTimeLogs = $timeLogModel->where('user_id', $this->userId)->findAll();
            $allSecs = 0;
            foreach ($allTimeLogs as $atl) {
                $allSecs += (int)($atl['duration'] ?? 0);
            }
            $totalHours = round($allSecs / 3600, 1);
        } catch (\Throwable $e) {
            // fallback
        }

        // 7. 7-Day Velocity & Workload Chart
        $velocityTrend = [
            'labels'    => [],
            'completed' => [],
            'created'   => [],
        ];
        for ($i = 6; $i >= 0; $i--) {
            $day = date('Y-m-d', strtotime("-{$i} days"));
            $dayLabel = date('D (m/d)', strtotime("-{$i} days"));
            $velocityTrend['labels'][] = $dayLabel;

            $createdCount = 0;
            $completedCount = 0;
            foreach ($allTasks as $t) {
                if (!empty($t['created_at']) && substr($t['created_at'], 0, 10) === $day) {
                    $createdCount++;
                }
                if (!empty($t['updated_at']) && substr($t['updated_at'], 0, 10) === $day && in_array($t['status'] ?? '', ['done', 'approved'])) {
                    $completedCount++;
                }
            }
            $velocityTrend['created'][] = $createdCount;
            $velocityTrend['completed'][] = $completedCount;
        }

        // 8. Recent Activity Stream
        $recentActivity = [];
        try {
            $auditLogs = (new AuditLogModel())->orderBy('created_at', 'DESC')->limit(8)->findAll();
            foreach ($auditLogs as $al) {
                $recentActivity[] = [
                    'title'       => ucfirst(str_replace('_', ' ', $al['action'] ?? 'activity')) . ' on ' . ucfirst($al['entity_type'] ?? 'item'),
                    'description' => 'User ID #' . ($al['user_id'] ?? 'System') . ' performed ' . ($al['action'] ?? 'action'),
                    'time'        => $al['created_at'] ?? date('Y-m-d H:i:s'),
                    'icon'        => 'mdi-information-outline',
                    'color'       => 'primary'
                ];
            }
        } catch (\Throwable $e) {}

        if (empty($recentActivity)) {
            foreach (array_slice($allTasks, 0, 6) as $rt) {
                $isDone = in_array($rt['status'] ?? '', ['done', 'approved']);
                $recentActivity[] = [
                    'title'       => 'Task: ' . ($rt['title'] ?? 'Task'),
                    'description' => 'Status: ' . ucfirst(str_replace('_', ' ', $rt['status'] ?? 'todo')) . ' in ' . ($rt['project_name'] ?? 'Project'),
                    'time'        => $rt['updated_at'] ?? $rt['created_at'] ?? date('Y-m-d H:i:s'),
                    'icon'        => $isDone ? 'mdi-check-circle' : 'mdi-clock-outline',
                    'color'       => $isDone ? 'success' : 'info'
                ];
            }
        }

        // 9. AI Agile Insights
        $aiInsights = [];
        if ($blockedTasks > 0) {
            $aiInsights[] = [
                'type'    => 'danger',
                'icon'    => 'mdi-alert-octagon',
                'title'   => "{$blockedTasks} Blocked Task(s)",
                'message' => 'There are blocked tasks requiring immediate unblocking to protect sprint delivery.'
            ];
        }
        if ($overdueTasks > 0) {
            $aiInsights[] = [
                'type'    => 'warning',
                'icon'    => 'mdi-clock-alert',
                'title'   => "{$overdueTasks} Overdue Task(s)",
                'message' => 'Target completion deadlines have lapsed. Review resource allocation.'
            ];
        }
        if ($completionRate >= 60 && $totalTasks > 0) {
            $aiInsights[] = [
                'type'    => 'success',
                'icon'    => 'mdi-rocket-launch',
                'title'   => "Velocity Peak ({$completionRate}%)",
                'message' => 'Sprint velocity and completion velocity are operating ahead of average cycle times.'
            ];
        } else {
            $aiInsights[] = [
                'type'    => 'info',
                'icon'    => 'mdi-chart-line',
                'title'   => "Active Pipeline ({$inProgressTasks} In Flight)",
                'message' => 'Work in progress is flowing across agile sprint swimlanes.'
            ];
        }

        $data = [
            'user'               => $this->currentUser,
            'isAdminOrManager'   => $isAdminOrManager,
            'stats'              => [
                'total_projects'     => $totalProjects,
                'active_projects'    => $activeProjects,
                'completed_projects' => $completedProjects,
                'planning_projects'  => $planningProjects,
                'total_tasks'        => $totalTasks,
                'completed_tasks'    => $doneTasks,
                'in_progress_tasks'  => $inProgressTasks,
                'review_tasks'       => $reviewTasks,
                'todo_tasks'         => $todoTasks,
                'blocked_tasks'      => $blockedTasks,
                'overdue_tasks'      => $overdueTasks,
                'completion_rate'    => $completionRate,
                'weekly_hours'       => $weeklyHours,
                'total_hours'        => $totalHours,
                'active_sprints'     => count($activeSprints),
            ],
            'statusBreakdown'    => $statusBreakdown,
            'velocityTrend'      => $velocityTrend,
            'projects'           => array_slice($projects, 0, 6),
            'myTasks'            => $myTasks,
            'activeSprints'      => $activeSprints,
            'recentActivity'     => $recentActivity,
            'aiInsights'         => $aiInsights,
        ];

        return view('user/home', $data);
    }
}
