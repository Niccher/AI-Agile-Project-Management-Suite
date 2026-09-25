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
        $totalProjects = $projectModel->where('user_id', $this->userId)->countAllResults();
        $completedProjects = $projectModel->where('user_id', $this->userId)->where('status', 'completed')->countAllResults();

        // Completion rate
        $completionRate = $totalProjects > 0 ? round(($completedProjects / $totalProjects) * 100) : 0;

        // Hours logged
        $totalSeconds = $db->table('time_logs')
            ->selectSum('duration')
            ->where('user_id', $this->userId)
            ->get()->getRow()->duration ?? 0;
        $totalHours = round($totalSeconds / 3600, 1);

        // Avg daily
        $daysLogged = $db->table('time_logs')
            ->select('COUNT(DISTINCT DATE(start_time)) as days')
            ->where('user_id', $this->userId)
            ->get()->getRow()->days ?? 1;
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
            $activityCount = 0;
            
            $activityCount += $db->table('time_logs')
                ->where('user_id', $this->userId)
                ->where('DATE(start_time)', $day)
                ->countAllResults();
            
            $activityCount += $db->table('notes')
                ->where('user_id', $this->userId)
                ->where('DATE(created_at)', $day)
                ->countAllResults();

            $activityCount += $db->table('projects')
                ->where('user_id', $this->userId)
                ->where('DATE(updated_at)', $day)
                ->countAllResults();

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

        $data = [
            'user'              => $this->currentUser,
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
