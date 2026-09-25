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
        $isManager = $currentUser && ($currentUser->inGroup('manager') || $isAdmin);

        // Fetch accessible projects
        if ($isManager) {
            $data['projects'] = $projectModel->where('deleted_at', null)->orderBy('name', 'ASC')->findAll();
        } else {
            $data['projects'] = $projectModel->where('user_id', $this->userId)
                                              ->where('deleted_at', null)
                                              ->orderBy('name', 'ASC')
                                              ->findAll();
        }

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

        // Base time log builder
        $logsQuery = $timeModel->select('time_logs.*, projects.name as project_name, projects.slug as project_slug, projects.color as project_color')
            ->join('projects', 'projects.id = time_logs.project_id', 'left')
            ->where('time_logs.user_id', $this->userId);

        if ($projectId) {
            $logsQuery->where('time_logs.project_id', $projectId);
        }

        // Stats
        $todayQuery = $db->table('time_logs')
            ->selectSum('duration')
            ->where('user_id', $this->userId)
            ->where('DATE(start_time)', date('Y-m-d'));
        if ($projectId) {
            $todayQuery->where('project_id', $projectId);
        }
        $todayDuration = $todayQuery->get()->getRow()->duration ?? 0;
        $data['todayTime'] = round($todayDuration / 3600, 1);

        $weekQuery = $db->table('time_logs')
            ->selectSum('duration')
            ->where('user_id', $this->userId)
            ->where('start_time >=', date('Y-m-d', strtotime('monday this week')));
        if ($projectId) {
            $weekQuery->where('project_id', $projectId);
        }
        $weekDuration = $weekQuery->get()->getRow()->duration ?? 0;
        $data['weekTime'] = round($weekDuration / 3600, 1);

        $monthQuery = $db->table('time_logs')
            ->selectSum('duration')
            ->where('user_id', $this->userId)
            ->where('start_time >=', date('Y-m-01'));
        if ($projectId) {
            $monthQuery->where('project_id', $projectId);
        }
        $monthDuration = $monthQuery->get()->getRow()->duration ?? 0;
        $data['monthTime'] = round($monthDuration / 3600, 1);

        $data['avgDaily'] = 0;
        $daysQuery = $db->table('time_logs')
            ->select('COUNT(DISTINCT DATE(start_time)) as days')
            ->where('user_id', $this->userId);
        if ($projectId) {
            $daysQuery->where('project_id', $projectId);
        }
        $daysLogged = $daysQuery->get()->getRow()->days ?? 1;

        $totalSecondsQuery = $db->table('time_logs')
            ->selectSum('duration')
            ->where('user_id', $this->userId);
        if ($projectId) {
            $totalSecondsQuery->where('project_id', $projectId);
        }
        $totalSeconds = $totalSecondsQuery->get()->getRow()->duration ?? 0;

        if ($daysLogged > 0) {
            $data['avgDaily'] = round(($totalSeconds / $daysLogged) / 3600, 1);
        }

        // Paginated Time Entries
        $data['time_logs'] = $logsQuery->orderBy('time_logs.start_time', 'DESC')
            ->paginate(10, 'time_logs');
        $data['pager'] = $timeModel->pager;

        // Project Time Breakdown
        $breakdownQuery = $db->table('time_logs')
            ->select('projects.name, projects.color, SUM(time_logs.duration) as total_duration')
            ->join('projects', 'projects.id = time_logs.project_id', 'left')
            ->where('time_logs.user_id', $this->userId)
            ->groupBy('time_logs.project_id')
            ->orderBy('total_duration', 'DESC');
        
        $totalBreakdown = $breakdownQuery->countAllResults(false);
        $pageBreakdown = $this->request->getVar('page_breakdown') ?? 1;
        $data['project_breakdown'] = $breakdownQuery->get(5, ($pageBreakdown - 1) * 5)->getResultArray();
        $data['breakdown_total_pages'] = ceil($totalBreakdown / 5);
        $data['breakdown_current_page'] = $pageBreakdown;
        $data['total_all_duration'] = array_sum(array_column($data['project_breakdown'], 'total_duration')) ?: 1;

        return view('user/time', $data);
    }

    public function logManual()
    {
        $projectId = $this->request->getPost('project_id');
        $taskName = trim($this->request->getPost('task_name') ?? 'Work session');
        $date = $this->request->getPost('date') ?: date('Y-m-d');
        $durationHours = (float)($this->request->getPost('duration') ?? 1.0);
        $notes = $this->request->getPost('notes');

        $timeModel = new TimeLogModel();
        $startTime = $date . ' ' . date('H:i:s');
        $durationSeconds = max(60, (int)round($durationHours * 3600));

        $timeModel->insert([
            'user_id'    => $this->userId,
            'project_id' => $projectId ? (int)$projectId : null,
            'task_name'  => $taskName,
            'start_time' => $startTime,
            'end_time'   => date('Y-m-d H:i:s', strtotime($startTime) + $durationSeconds),
            'duration'   => $durationSeconds,
            'notes'      => $notes
        ]);

        $redirectUrl = site_url('time');
        if ($projectId) {
            $proj = (new ProjectModel())->find($projectId);
            $slug = $proj['slug'] ?? $projectId;
            $redirectUrl = site_url('projects/time/' . $slug);
        }
        return redirect()->to($redirectUrl)->with('success', 'Time log recorded successfully (' . $durationHours . ' hrs).');
    }
}
