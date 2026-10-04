<?php

namespace App\Controllers\User;

use App\Models\ProjectModel;
use App\Models\TimeLogModel;

class TimeTrackerController extends BaseUserController
{
    public function index($projectIdentifier = null)
    {
        $db = \Config\Database::connect();
        $projectModel = new ProjectModel();
        $timeModel = new TimeLogModel();

        $currentUser = auth()->user();
        $isAdmin = $currentUser && $currentUser->inGroup('admin');
        $isManager = $currentUser && ($currentUser->inGroup('manager') || $isAdmin || is_solo_mode());

        // Scope & User Filter
        $scope = $this->request->getVar('scope') ?? ($isManager ? 'team' : 'me');
        $selectedUserIdVar = $this->request->getVar('user_id');
        $selectedUserId = ($selectedUserIdVar !== null && $selectedUserIdVar !== '') ? (int)$selectedUserIdVar : null;

        if (!$isManager) {
            $scope = 'me';
            $selectedUserId = $this->userId;
        }

        // Fetch accessible projects
        $projects = $projectModel->getAccessibleProjects($this->userId, $isManager);
        $data['projects'] = $projects;

        $projectId = null;
        $activeProject = null;
        if ($projectIdentifier !== null) {
            $activeProject = $projectModel->findByIdentifier($projectIdentifier, $this->userId, $isManager);
            if ($activeProject) {
                $projectId = (int)$activeProject['id'];
            }
        }

        $data['selectedProjectId'] = $projectId;
        $data['activeProject'] = $activeProject;
        $data['user'] = $this->currentUser;
        $data['isManager'] = $isManager;
        $data['scope'] = $scope;
        $data['selectedUserId'] = $selectedUserId;

        // Fetch team members for manager filtering
        $hasFirstName = $db->fieldExists('first_name', 'users');
        $userNameSelect = $hasFirstName 
            ? "COALESCE(NULLIF(TRIM(CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, ''))), ''), u.username, 'User') as user_display_name"
            : "COALESCE(u.username, 'User') as user_display_name";

        $teamMembers = [];
        try {
            $teamMembers = $db->table('users u')
                ->select("u.id, u.username, {$userNameSelect}, u.email")
                ->orderBy('user_display_name', 'ASC')
                ->get()->getResultArray();
        } catch (\Throwable $e) {}
        $data['teamMembers'] = $teamMembers;

        // Helper closure to apply user filtering to query builders
        $applyUserFilter = function($query, $userCol = 'user_id') use ($scope, $selectedUserId, $isManager) {
            if ($selectedUserId) {
                $query->where($userCol, $selectedUserId);
            } elseif (!$isManager || $scope === 'me') {
                $query->where($userCol, $this->userId);
            }
            return $query;
        };

        // Base time log builder
        $logsQuery = $timeModel->select("time_logs.*, {$userNameSelect}, u.username as user_username, projects.name as project_name, projects.slug as project_slug, projects.color as project_color")
            ->join('projects', 'projects.id = time_logs.project_id', 'left')
            ->join('users u', 'u.id = time_logs.user_id', 'left');

        $applyUserFilter($logsQuery, 'time_logs.user_id');

        if ($projectId) {
            $logsQuery->where('time_logs.project_id', $projectId);
        }

        // 1. Stats Calculation
        $todayQuery = $db->table('time_logs')
            ->selectSum('duration')
            ->where('DATE(start_time)', date('Y-m-d'));
        $applyUserFilter($todayQuery, 'user_id');
        if ($projectId) {
            $todayQuery->where('project_id', $projectId);
        }
        $todayDuration = $todayQuery->get()->getRow()->duration ?? 0;
        $data['todayTime'] = round($todayDuration / 3600, 1);
        $data['todayMinutes'] = round($todayDuration / 60);

        $weekQuery = $db->table('time_logs')
            ->selectSum('duration')
            ->where('start_time >=', date('Y-m-d', strtotime('monday this week')));
        $applyUserFilter($weekQuery, 'user_id');
        if ($projectId) {
            $weekQuery->where('project_id', $projectId);
        }
        $weekDuration = $weekQuery->get()->getRow()->duration ?? 0;
        $data['weekTime'] = round($weekDuration / 3600, 1);

        $monthQuery = $db->table('time_logs')
            ->selectSum('duration')
            ->where('start_time >=', date('Y-m-01'));
        $applyUserFilter($monthQuery, 'user_id');
        if ($projectId) {
            $monthQuery->where('project_id', $projectId);
        }
        $monthDuration = $monthQuery->get()->getRow()->duration ?? 0;
        $data['monthTime'] = round($monthDuration / 3600, 1);

        $data['avgDaily'] = 0;
        $daysQuery = $db->table('time_logs')
            ->select('COUNT(DISTINCT DATE(start_time)) as days');
        $applyUserFilter($daysQuery, 'user_id');
        if ($projectId) {
            $daysQuery->where('project_id', $projectId);
        }
        $daysLogged = $daysQuery->get()->getRow()->days ?? 1;

        $totalSecondsQuery = $db->table('time_logs')
            ->selectSum('duration');
        $applyUserFilter($totalSecondsQuery, 'user_id');
        if ($projectId) {
            $totalSecondsQuery->where('project_id', $projectId);
        }
        $totalSeconds = $totalSecondsQuery->get()->getRow()->duration ?? 0;
        $data['totalHours'] = round($totalSeconds / 3600, 1);

        if ($daysLogged > 0) {
            $data['avgDaily'] = round(($totalSeconds / $daysLogged) / 3600, 1);
        }

        // Billable vs Non-Billable calculation
        $hasBillable = $db->fieldExists('is_billable', 'time_logs');
        $billableSeconds = 0;
        if ($hasBillable) {
            $billableQuery = $db->table('time_logs')
                ->selectSum('duration')
                ->where('is_billable', 1);
            $applyUserFilter($billableQuery, 'user_id');
            if ($projectId) {
                $billableQuery->where('project_id', $projectId);
            }
            $billableSeconds = $billableQuery->get()->getRow()->duration ?? 0;
        }
        $data['billableHours'] = round($billableSeconds / 3600, 1);
        $data['billableRate'] = $totalSeconds > 0 ? round(($billableSeconds / $totalSeconds) * 100) : 100;

        // Total log count
        $countQuery = $db->table('time_logs');
        $applyUserFilter($countQuery, 'user_id');
        if ($projectId) {
            $countQuery->where('project_id', $projectId);
        }
        $data['totalLogsCount'] = $countQuery->countAllResults();

        // 2. 7-Day Velocity & Effort Trend Chart
        $trendLabels = [];
        $trendHours = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-{$i} days"));
            $dLabel = date('D (m/d)', strtotime("-{$i} days"));
            $trendLabels[] = $dLabel;

            $dQuery = $db->table('time_logs')
                ->selectSum('duration')
                ->where('DATE(start_time)', $d);
            $applyUserFilter($dQuery, 'user_id');
            if ($projectId) {
                $dQuery->where('project_id', $projectId);
            }
            $dSec = $dQuery->get()->getRow()->duration ?? 0;
            $trendHours[] = round($dSec / 3600, 2);
        }
        $data['trendLabels'] = $trendLabels;
        $data['trendHours'] = $trendHours;

        // 3. Project Time Breakdown
        $breakdownQuery = $db->table('time_logs')
            ->select('COALESCE(projects.name, "General") as name, COALESCE(projects.color, "#727cf5") as color, SUM(time_logs.duration) as total_duration, COUNT(time_logs.id) as sessions_count')
            ->join('projects', 'projects.id = time_logs.project_id', 'left');
        $applyUserFilter($breakdownQuery, 'time_logs.user_id');
        if ($projectId) {
            $breakdownQuery->where('time_logs.project_id', $projectId);
        }
        $breakdownQuery->groupBy('time_logs.project_id')->orderBy('total_duration', 'DESC');
        
        $projectBreakdowns = $breakdownQuery->get()->getResultArray();
        $chartProjectLabels = [];
        $chartProjectHours = [];
        $chartProjectColors = [];
        foreach ($projectBreakdowns as &$pb) {
            $pbSecs = (int)($pb['total_duration'] ?? 0);
            $pbHrs = round($pbSecs / 3600, 1);
            $pb['hours'] = $pbHrs;
            $chartProjectLabels[] = $pb['name'];
            $chartProjectHours[] = $pbHrs;
            $chartProjectColors[] = $pb['color'] ?: '#727cf5';
        }
        unset($pb);

        $data['project_breakdown'] = $projectBreakdowns;
        $data['chartProjectLabels'] = $chartProjectLabels;
        $data['chartProjectHours'] = $chartProjectHours;
        $data['chartProjectColors'] = $chartProjectColors;
        $data['total_all_duration'] = array_sum(array_column($projectBreakdowns, 'total_duration')) ?: 1;

        // 4. Team Members Time Leaderboard (for Manager/Admin)
        $userBreakdowns = [];
        if ($isManager) {
            try {
                $userBreakdownQuery = $db->table('time_logs')
                    ->select("time_logs.user_id, {$userNameSelect}, u.username, SUM(time_logs.duration) as total_duration, COUNT(time_logs.id) as sessions_count")
                    ->join('users u', 'u.id = time_logs.user_id', 'left');
                if ($projectId) {
                    $userBreakdownQuery->where('time_logs.project_id', $projectId);
                }
                $userBreakdowns = $userBreakdownQuery->groupBy('time_logs.user_id')
                    ->orderBy('total_duration', 'DESC')
                    ->get()->getResultArray();

                foreach ($userBreakdowns as &$ub) {
                    $ubSecs = (int)($ub['total_duration'] ?? 0);
                    $ub['hours'] = round($ubSecs / 3600, 1);
                }
                unset($ub);
            } catch (\Throwable $e) {}
        }
        $data['user_breakdown'] = $userBreakdowns;

        // 5. Time Entries Pagination
        $data['time_logs'] = $logsQuery->orderBy('time_logs.start_time', 'DESC')
            ->paginate(15, 'time_logs');
        $data['pager'] = $timeModel->pager;

        return view('user/time', $data);
    }

    public function logManual()
    {
        $input = $this->request->getPost();
        if (empty($input)) {
            try {
                $input = $this->request->getJSON(true) ?: [];
            } catch (\Throwable $e) {
                $input = [];
            }
        }
        
        $projectId = !empty($input['project_id']) ? (int)$input['project_id'] : ($this->request->getVar('project_id') ? (int)$this->request->getVar('project_id') : null);
        $taskName = trim($input['task_name'] ?? $this->request->getVar('task_name') ?? 'Work session');
        $date = !empty($input['date']) ? $input['date'] : ($this->request->getVar('date') ?: date('Y-m-d'));
        $durationHours = (float)($input['duration'] ?? $this->request->getVar('duration') ?? 1.0);
        $notes = $input['notes'] ?? $this->request->getVar('notes') ?? '';
        $isBillable = isset($input['is_billable']) ? (int)$input['is_billable'] : (isset($_POST['is_billable']) ? 1 : 1);

        // Allow Admin/Manager to assign log to another user
        $targetUserId = $this->userId;
        $currentUser = auth()->user();
        $isManager = $currentUser && ($currentUser->inGroup('manager') || $currentUser->inGroup('admin') || is_solo_mode());
        if ($isManager && !empty($input['user_id'])) {
            $targetUserId = (int)$input['user_id'];
        }

        if (empty($taskName)) {
            $taskName = 'Work session';
        }

        $timeModel = new TimeLogModel();
        $startTime = $date . ' ' . date('H:i:s');
        $durationSeconds = max(60, (int)round($durationHours * 3600));

        $dataToInsert = [
            'user_id'    => $targetUserId,
            'project_id' => $projectId,
            'task_name'  => $taskName,
            'start_time' => $startTime,
            'end_time'   => date('Y-m-d H:i:s', strtotime($startTime) + $durationSeconds),
            'duration'   => $durationSeconds,
            'notes'      => $notes
        ];

        $db = \Config\Database::connect();
        if ($db->fieldExists('is_billable', 'time_logs')) {
            $dataToInsert['is_billable'] = $isBillable;
        }

        $insertId = $timeModel->insert($dataToInsert);

        $projectModel = new ProjectModel();
        $projectName = 'General';
        $projectColor = '#3e60d5';
        $projectSlug = '';
        if ($projectId) {
            $proj = $projectModel->find($projectId);
            if ($proj) {
                $projectName = $proj['name'] ?? 'General';
                $projectColor = $proj['color'] ?? '#3e60d5';
                $projectSlug = $proj['slug'] ?? (string)$projectId;
            }
        }

        if ($this->request->isAJAX() || $this->request->header('Accept')?->getValue() === 'application/json' || str_contains($this->request->header('Content-Type')?->getValue() ?? '', 'json')) {
            $newLog = $timeModel->find($insertId);
            return $this->response->setJSON([
                'success'       => true,
                'status'        => 'success',
                'message'       => 'Time log recorded successfully (' . round($durationHours, 2) . ' hrs).',
                'log'           => $newLog,
                'project_name'  => $projectName,
                'project_color' => $projectColor,
                'project_slug'  => $projectSlug,
                'duration'      => $durationHours,
            ]);
        }

        $redirectUrl = site_url('time');
        if ($projectId && !empty($projectSlug)) {
            $redirectUrl = site_url('projects/time/' . $projectSlug);
        }
        return redirect()->to($redirectUrl)->with('success', 'Time log recorded successfully (' . round($durationHours, 2) . ' hrs).');
    }
}
