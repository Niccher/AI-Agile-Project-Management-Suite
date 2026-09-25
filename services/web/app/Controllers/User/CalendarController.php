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
        
        $data['user'] = $this->currentUser;
        $data['projects'] = $projectModel->where('user_id', $this->userId)->findAll();
        
        // --- 1. Stats Calculation ---
        $now = date('Y-m-d H:i:s');
        $monthStart = date('Y-m-01 00:00:00');
        $monthEnd = date('Y-m-t 23:59:59');

        // Manual Events Count
        $data['total_events'] = $db->table('calendar_events')
            ->where('user_id', $this->userId)
            ->where('start_time >=', $monthStart)
            ->where('start_time <=', $monthEnd)
            ->countAllResults();

        // Plus Projects Due this month
        $data['total_events'] += $db->table('projects')
            ->where('user_id', $this->userId)
            ->where('due_date >=', $monthStart)
            ->where('due_date <=', $monthEnd)
            ->countAllResults();

        // Plus Milestones Due this month
        $data['total_events'] += $db->table('project_milestones')
            ->join('projects', 'projects.id = project_milestones.project_id')
            ->where('projects.user_id', $this->userId)
            ->where('project_milestones.due_date >=', $monthStart)
            ->where('project_milestones.due_date <=', $monthEnd)
            ->countAllResults();

        // Overdue count (Projects & Milestones past due_date and not completed)
        $data['overdue_count'] = $db->table('projects')
            ->where('user_id', $this->userId)
            ->where('due_date <', date('Y-m-d'))
            ->where('status !=', 'completed')
            ->countAllResults();

        // Completed Stats
        $data['completed_count'] = $db->table('projects')->where('user_id', $this->userId)->where('status', 'completed')->countAllResults();
        $data['completed_count'] += $db->table('project_milestones')->join('projects', 'projects.id = project_milestones.project_id')->where('projects.user_id', $this->userId)->where('project_milestones.status', 'completed')->countAllResults();
        $data['completed_count'] += $db->table('notes')->where('user_id', $this->userId)->where('is_completed', 1)->countAllResults();

        // Pending Stats
        $data['pending_count'] = $db->table('projects')->where('user_id', $this->userId)->whereIn('status', ['planning', 'in_progress', 'on_hold'])->countAllResults();
        $data['pending_count'] += $db->table('project_milestones')->join('projects', 'projects.id = project_milestones.project_id')->where('projects.user_id', $this->userId)->where('project_milestones.status !=', 'completed')->countAllResults();
        $data['pending_count'] += $db->table('notes')->where('user_id', $this->userId)->where('is_completed', 0)->where('is_deleted', 0)->countAllResults();

        // --- 2. Upcoming Events List (Paginated) ---
        $upcoming = [];
        
        // Projects
        $upcomingProjects = $db->table('projects')
            ->where('user_id', $this->userId)
            ->where('due_date >=', date('Y-m-d'))
            ->orderBy('due_date', 'ASC')
            ->get()->getResultArray();
        foreach ($upcomingProjects as $p) {
            $upcoming[] = [
                'date' => $p['due_date'],
                'title' => $p['name'],
                'desc' => 'Project Deadline',
                'project' => $p['name'],
                'color' => $p['color'],
                'type' => 'project',
                'icon' => 'fa-project-diagram'
            ];
        }

        // Manual
        $upcomingManual = $db->table('calendar_events')
            ->where('user_id', $this->userId)
            ->where('start_time >=', $now)
            ->orderBy('start_time', 'ASC')
            ->get()->getResultArray();
        foreach ($upcomingManual as $e) {
            $upcoming[] = [
                'date' => date('Y-m-d', strtotime($e['start_time'])),
                'title' => $e['title'],
                'desc' => $e['description'],
                'project' => 'Personal',
                'color' => $e['color'],
                'type' => 'event',
                'icon' => 'fa-calendar-day'
            ];
        }

        usort($upcoming, function($a, $b) {
            return strtotime($a['date']) - strtotime($b['date']);
        });

        // Simple custom pagination logic for combined array
        $page = $this->request->getVar('page_upcoming') ?? 1;
        $perPage = 5;
        $totalItems = count($upcoming);
        $data['upcoming_events'] = array_slice($upcoming, ($page - 1) * $perPage, $perPage);
        $data['upcoming_pager'] = service('pager');
        $data['upcoming_total_pages'] = ceil($totalItems / $perPage);
        $data['upcoming_current_page'] = $page;

        // --- 3. Project Distribution ---
        $data['distribution'] = $db->table('calendar_events')
            ->select('projects.name, projects.color, COUNT(calendar_events.id) as count')
            ->join('projects', 'projects.id = calendar_events.project_id', 'left')
            ->where('calendar_events.user_id', $this->userId)
            ->groupBy('calendar_events.project_id')
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
