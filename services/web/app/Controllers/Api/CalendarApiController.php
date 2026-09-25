<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\EventModel;
use App\Models\NoteModel;
use App\Models\TimeLogModel;

class CalendarApiController extends BaseController
{
    public function index()
    {
        $userId = auth()->id();
        $db = \Config\Database::connect();
        $events = [];

        $projectIdFilter = $this->request->getGet('project_id');

        // 1. Manual Events
        $eventModel = new EventModel();
        $builder = $eventModel->where('user_id', $userId);
        $manualEvents = $builder->findAll();
        foreach ($manualEvents as $event) {
            $events[] = [
                'id'            => 'event_' . $event['id'],
                'title'         => $event['title'],
                'start'         => $event['start_time'],
                'end'           => $event['end_time'],
                'color'         => $event['color'] ?: '#727cf5',
                'allDay'        => (bool)$event['is_all_day'],
                'extendedProps' => [
                    'type'        => 'manual',
                    'description' => $event['description'],
                    'dbId'        => $event['id'],
                    'icon'        => 'fa-calendar-day'
                ]
            ];
        }

        // 2. Sprint Date Bands (Agile Sprints)
        $sprintBuilder = $db->table('sprints')
            ->select('sprints.*, projects.name as project_name, projects.slug as project_slug, projects.color as project_color')
            ->join('projects', 'projects.id = sprints.project_id')
            ->where('projects.user_id', $userId)
            ->where('sprints.start_date IS NOT NULL')
            ->where('sprints.end_date IS NOT NULL');

        if (!empty($projectIdFilter)) {
            $sprintBuilder->where('sprints.project_id', (int)$projectIdFilter);
        }

        $sprints = $sprintBuilder->get()->getResultArray();
        foreach ($sprints as $sp) {
            $statusBadge = strtoupper($sp['status']);
            $events[] = [
                'id'            => 'sprint_' . $sp['id'],
                'title'         => "⚡ SPRINT: {$sp['name']} ({$statusBadge})",
                'start'         => $sp['start_date'],
                'end'           => $sp['end_date'],
                'color'         => $sp['status'] === 'active' ? '#727cf5' : ($sp['status'] === 'completed' ? '#0acf97' : '#6c757d'),
                'allDay'        => true,
                'extendedProps' => [
                    'type'         => 'sprint',
                    'status'       => $sp['status'],
                    'goal'         => $sp['goal'],
                    'total_points' => $sp['total_points'],
                    'project_name' => $sp['project_name'],
                    'url'          => site_url('projects/sprints/' . ($sp['project_slug'] ?: $sp['project_id'])),
                    'icon'         => 'fa-running'
                ]
            ];
        }

        // 3. Task Due Dates
        $taskBuilder = $db->table('tasks')
            ->select('tasks.*, projects.name as project_name, projects.slug as project_slug, projects.color as project_color')
            ->join('projects', 'projects.id = tasks.project_id', 'left')
            ->where('tasks.user_id', $userId)
            ->where('tasks.due_date IS NOT NULL');

        if (!empty($projectIdFilter)) {
            $taskBuilder->where('tasks.project_id', (int)$projectIdFilter);
        }

        $tasks = $taskBuilder->get()->getResultArray();
        foreach ($tasks as $task) {
            $isDone = in_array($task['status'], ['done', 'approved']);
            
            $taskColor = '#39afd1'; // default info
            if ($isDone) {
                $taskColor = '#0acf97'; // green
            } elseif ($task['priority'] === 'critical') {
                $taskColor = '#fa5c7c'; // red
            } elseif ($task['priority'] === 'high') {
                $taskColor = '#ffbc00'; // amber
            }

            $pointsStr = !empty($task['story_points']) ? " [{$task['story_points']} pts]" : '';
            $statusLabel = strtoupper(str_replace('_', ' ', $task['status']));

            $events[] = [
                'id'            => 'task_' . $task['id'],
                'title'         => "✓ TASK: {$task['title']}{$pointsStr}",
                'start'         => $task['due_date'],
                'color'         => $taskColor,
                'allDay'        => true,
                'extendedProps' => [
                    'type'         => 'task',
                    'status'       => $task['status'],
                    'priority'     => $task['priority'],
                    'description'  => $task['description'] ?: 'No description provided.',
                    'project_name' => $task['project_name'] ?: 'Workspace Task',
                    'dbId'         => $task['id'],
                    'url'          => !empty($task['project_slug']) ? site_url('projects/kanban/' . $task['project_slug']) : site_url('kanban'),
                    'icon'         => 'fa-tasks'
                ]
            ];
        }

        // 4. Project Target Completion Dates
        $projBuilder = $db->table('projects')
            ->where('user_id', $userId)
            ->where('due_date IS NOT NULL');

        if (!empty($projectIdFilter)) {
            $projBuilder->where('id', (int)$projectIdFilter);
        }

        $projects = $projBuilder->get()->getResultArray();
        foreach ($projects as $project) {
            $events[] = [
                'id'            => 'project_' . $project['id'],
                'title'         => '🎯 PROJECT DUE: ' . $project['name'],
                'start'         => $project['due_date'],
                'color'         => $project['color'] ?: '#6366f1',
                'allDay'        => true,
                'extendedProps' => [
                    'type'         => 'project',
                    'description'  => 'Project milestone deadline: ' . $project['name'],
                    'dbId'         => $project['id'],
                    'url'          => site_url('projects/view/' . ($project['slug'] ?: $project['id'])),
                    'icon'         => 'fa-project-diagram'
                ]
            ];
        }

        // 5. Milestones
        $msBuilder = $db->table('project_milestones')
            ->select('project_milestones.*, projects.name as project_name, projects.slug as project_slug, projects.color as project_color')
            ->join('projects', 'projects.id = project_milestones.project_id')
            ->where('projects.user_id', $userId);

        if (!empty($projectIdFilter)) {
            $msBuilder->where('project_milestones.project_id', (int)$projectIdFilter);
        }

        $milestones = $msBuilder->get()->getResultArray();
        foreach ($milestones as $ms) {
            if (!empty($ms['due_date']) && $ms['status'] !== 'completed') {
                $events[] = [
                    'id'            => 'ms_due_' . $ms['id'],
                    'title'         => '🚩 MILESTONE: ' . $ms['name'],
                    'start'         => $ms['due_date'],
                    'color'         => '#f59e0b',
                    'allDay'        => true,
                    'extendedProps' => [
                        'type'         => 'milestone',
                        'description'  => 'Deadline for "' . $ms['name'] . '" in ' . $ms['project_name'],
                        'dbId'         => $ms['id'],
                        'icon'         => 'fa-flag'
                    ]
                ];
            }
        }

        // 6. Time Logs
        $timeModel = new TimeLogModel();
        $timeBuilder = $timeModel->where('user_id', $userId);
        if (!empty($projectIdFilter)) {
            $timeBuilder->where('project_id', (int)$projectIdFilter);
        }
        $logs = $timeBuilder->findAll();
        foreach ($logs as $log) {
            $events[] = [
                'id'            => 'time_' . $log['id'],
                'title'         => '⏱ TIME: ' . $log['task_name'],
                'start'         => $log['start_time'],
                'end'           => $log['end_time'],
                'color'         => '#8b5cf6',
                'extendedProps' => [
                    'type'        => 'time_log',
                    'description' => 'Time logged: ' . $log['task_name'] . ($log['notes'] ? ' - ' . $log['notes'] : ''),
                    'dbId'        => $log['id'],
                    'icon'        => 'fa-clock'
                ]
            ];
        }

        return $this->response->setJSON($events);
    }
}
