<?php

namespace App\Controllers\User;

use App\Models\ProjectModel;
use App\Models\EventModel;

class CalendarController extends BaseUserController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $projectModel = new ProjectModel();

        $userId = (int)$this->userId;
        $currentUser = $this->currentUser;
        $isAdmin = $currentUser && ($currentUser->inGroup('admin') || $currentUser->inGroup('superadmin'));
        $isManager = $currentUser && $currentUser->inGroup('manager');

        $data['user'] = $currentUser;
        if ($isAdmin || $isManager) {
            $data['projects'] = $projectModel->findAll();
        } else {
            $data['projects'] = $projectModel->where('user_id', $userId)->findAll();
        }

        // --- 1. Stats Calculation ---
        $now = date('Y-m-d H:i:s');
        $monthStart = date('Y-m-01 00:00:00');
        $monthEnd = date('Y-m-t 23:59:59');

        // Manual Events Count
        $evQuery = $db->table('calendar_events')
            ->where('start_time >=', $monthStart)
            ->where('start_time <=', $monthEnd);
        if (!$isAdmin && !$isManager) {
            $evQuery->where('user_id', $userId);
        }
        $data['total_events'] = $evQuery->countAllResults();

        // Projects Due this month
        $pQuery = $db->table('projects')
            ->where('due_date >=', $monthStart)
            ->where('due_date <=', $monthEnd);
        if (!$isAdmin && !$isManager) {
            $pQuery->where('user_id', $userId);
        }
        $data['total_events'] += $pQuery->countAllResults();

        // Milestones Due this month
        $mQuery = $db->table('project_milestones')
            ->join('projects', 'projects.id = project_milestones.project_id')
            ->where('project_milestones.due_date >=', $monthStart)
            ->where('project_milestones.due_date <=', $monthEnd);
        if (!$isAdmin && !$isManager) {
            $mQuery->where('projects.user_id', $userId);
        }
        $data['total_events'] += $mQuery->countAllResults();

        // Tasks Due this month
        $tQuery = $db->table('tasks')
            ->where('due_date >=', $monthStart)
            ->where('due_date <=', $monthEnd);
        if (!$isAdmin && !$isManager) {
            $tQuery->groupStart()
                   ->where('user_id', $userId)
                   ->orWhere('assigned_to', $userId)
                   ->groupEnd();
        }
        $data['total_events'] += $tQuery->countAllResults();

        // Overdue count (Projects & Tasks past due_date and not completed)
        $pOverdue = $db->table('projects')
            ->where('due_date <', date('Y-m-d'))
            ->where('status !=', 'completed');
        if (!$isAdmin && !$isManager) {
            $pOverdue->where('user_id', $userId);
        }
        $data['overdue_count'] = $pOverdue->countAllResults();

        $tOverdue = $db->table('tasks')
            ->where('due_date <', date('Y-m-d'))
            ->whereNotIn('status', ['done', 'approved']);
        if (!$isAdmin && !$isManager) {
            $tOverdue->groupStart()
                     ->where('user_id', $userId)
                     ->orWhere('assigned_to', $userId)
                     ->groupEnd();
        }
        $data['overdue_count'] += $tOverdue->countAllResults();

        // Completed Stats
        $pComp = $db->table('projects')->where('status', 'completed');
        if (!$isAdmin && !$isManager) { $pComp->where('user_id', $userId); }
        $data['completed_count'] = $pComp->countAllResults();

        $tComp = $db->table('tasks')->whereIn('status', ['done', 'approved']);
        if (!$isAdmin && !$isManager) {
            $tComp->groupStart()->where('user_id', $userId)->orWhere('assigned_to', $userId)->groupEnd();
        }
        $data['completed_count'] += $tComp->countAllResults();

        $data['completed_count'] += $db->table('notes')->where('user_id', $userId)->where('is_completed', 1)->countAllResults();

        // Pending Stats
        $pPend = $db->table('projects')->whereIn('status', ['planning', 'in_progress', 'on_hold']);
        if (!$isAdmin && !$isManager) { $pPend->where('user_id', $userId); }
        $data['pending_count'] = $pPend->countAllResults();

        $tPend = $db->table('tasks')->whereNotIn('status', ['done', 'approved']);
        if (!$isAdmin && !$isManager) {
            $tPend->groupStart()->where('user_id', $userId)->orWhere('assigned_to', $userId)->groupEnd();
        }
        $data['pending_count'] += $tPend->countAllResults();

        $data['pending_count'] += $db->table('notes')->where('user_id', $userId)->where('is_completed', 0)->where('is_deleted', 0)->countAllResults();

        // --- 2. Upcoming Events List (Paginated) ---
        $upcoming = [];

        // Tasks
        $taskBuilder = $db->table('tasks')
            ->select('tasks.*, projects.name as project_name, projects.color as project_color')
            ->join('projects', 'projects.id = tasks.project_id', 'left')
            ->where('tasks.due_date >=', date('Y-m-d'))
            ->orderBy('tasks.due_date', 'ASC');
        if (!$isAdmin && !$isManager) {
            $taskBuilder->groupStart()
                        ->where('tasks.user_id', $userId)
                        ->orWhere('tasks.assigned_to', $userId)
                        ->groupEnd();
        }
        $upcomingTasks = $taskBuilder->get()->getResultArray();
        foreach ($upcomingTasks as $t) {
            $upcoming[] = [
                'date' => $t['due_date'],
                'title' => $t['title'],
                'desc' => 'Task Deadline (' . ucfirst($t['priority']) . ')',
                'project' => $t['project_name'] ?: 'Workspace',
                'color' => $t['project_color'] ?: '#39afd1',
                'type' => 'task',
                'icon' => 'fa-tasks'
            ];
        }

        // Projects
        $projBuilder = $db->table('projects')
            ->where('due_date >=', date('Y-m-d'))
            ->orderBy('due_date', 'ASC');
        if (!$isAdmin && !$isManager) {
            $projBuilder->where('user_id', $userId);
        }
        $upcomingProjects = $projBuilder->get()->getResultArray();
        foreach ($upcomingProjects as $p) {
            $upcoming[] = [
                'date' => $p['due_date'],
                'title' => $p['name'],
                'desc' => 'Project Deadline',
                'project' => $p['name'],
                'color' => $p['color'] ?: '#6366f1',
                'type' => 'project',
                'icon' => 'fa-project-diagram'
            ];
        }

        // Manual Events
        $manualBuilder = $db->table('calendar_events')
            ->where('start_time >=', $now)
            ->orderBy('start_time', 'ASC');
        if (!$isAdmin && !$isManager) {
            $manualBuilder->where('user_id', $userId);
        }
        $upcomingManual = $manualBuilder->get()->getResultArray();
        foreach ($upcomingManual as $e) {
            $upcoming[] = [
                'date' => date('Y-m-d', strtotime($e['start_time'])),
                'title' => $e['title'],
                'desc' => $e['description'] ?: 'Event',
                'project' => 'Personal',
                'color' => $e['color'] ?: '#727cf5',
                'type' => 'event',
                'icon' => 'fa-calendar-day'
            ];
        }

        usort($upcoming, function($a, $b) {
            return strtotime($a['date']) - strtotime($b['date']);
        });

        // Simple custom pagination logic for combined array
        $page = (int)($this->request->getVar('page_upcoming') ?? 1);
        $perPage = 5;
        $totalItems = count($upcoming);
        $data['upcoming_events'] = array_slice($upcoming, ($page - 1) * $perPage, $perPage);
        $data['upcoming_pager'] = service('pager');
        $data['upcoming_total_pages'] = (int)ceil($totalItems / $perPage);
        $data['upcoming_current_page'] = $page;

        // --- 3. Project Distribution ---
        $distBuilder = $db->table('calendar_events')
            ->select('projects.name, projects.color, COUNT(calendar_events.id) as count')
            ->join('projects', 'projects.id = calendar_events.project_id', 'left');
        if (!$isAdmin && !$isManager) {
            $distBuilder->where('calendar_events.user_id', $userId);
        }
        $data['distribution'] = $distBuilder->groupBy('calendar_events.project_id')
            ->get()->getResultArray();

        return view('user/calendar', $data);
    }

    public function storeEvent()
    {
        $eventModel = new EventModel();
        $data = $this->request->getPost();
        $data['user_id'] = $this->userId;

        if (empty($data['project_id'])) {
            $data['project_id'] = null;
        }

        if ($eventModel->insert($data)) {
            return redirect()->to('/calendar')->with('message', 'Event added successfully.');
        }

        return redirect()->back()->withInput()->with('errors', $eventModel->errors());
    }

    public function updateEvent($id = null)
    {
        $eventModel = new EventModel();
        
        $event = $eventModel->where('id', $id)->where('user_id', $this->userId)->first();
        if (!$event) {
            return redirect()->to('/calendar')->with('error', 'Event not found.');
        }

        $data = $this->request->getPost();
        if (empty($data['project_id'])) {
            $data['project_id'] = null;
        }

        if ($eventModel->update($id, $data)) {
            return redirect()->to('/calendar')->with('message', 'Event updated successfully.');
        }

        return redirect()->back()->withInput()->with('errors', $eventModel->errors());
    }

    public function deleteEvent($id = null)
    {
        $eventModel = new EventModel();

        $event = $eventModel->where('id', $id)->where('user_id', $this->userId)->first();
        if (!$event) {
            return redirect()->to('/calendar')->with('error', 'Event not found.');
        }

        $eventModel->delete($id);
        return redirect()->to('/calendar')->with('message', 'Event deleted.');
    }
}
