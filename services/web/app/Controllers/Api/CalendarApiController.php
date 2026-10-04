<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\EventModel;

class CalendarApiController extends BaseController
{
    public function index()
    {
        $userId = (int)auth()->id();
        $currentUser = auth()->user();
        $isAdmin = $currentUser && ($currentUser->inGroup('admin') || $currentUser->inGroup('superadmin'));
        $isManager = $currentUser && $currentUser->inGroup('manager');

        $db = \Config\Database::connect();
        $events = [];

        $projectIdFilter = $this->request->getGet('project_id');

        // 1. Personal Manual Events
        try {
            $eventModel = new EventModel();
            $builder = $eventModel->where('user_id', $userId);
            $manualEvents = $builder->findAll();
            foreach ($manualEvents as $event) {
                if (!empty($event['start_time'])) {
                    $events[] = [
                        'id'            => 'event_' . $event['id'],
                        'title'         => $event['title'],
                        'start'         => $event['start_time'],
                        'end'           => $event['end_time'] ?? null,
                        'color'         => $event['color'] ?: '#727cf5',
                        'allDay'        => (bool)($event['is_all_day'] ?? false),
                        'extendedProps' => [
                            'type'        => 'manual',
                            'description' => $event['description'] ?? '',
                            'dbId'        => $event['id'],
                            'icon'        => 'fa-calendar-day'
                        ]
                    ];
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'Calendar manual events error: ' . $e->getMessage());
        }

        // 2. Agile Sprints
        try {
            if ($db->tableExists('sprints')) {
                $sprintBuilder = $db->table('sprints')
                    ->select('sprints.*, projects.name as project_name, projects.slug as project_slug, projects.color as project_color')
                    ->join('projects', 'projects.id = sprints.project_id', 'left')
                    ->where('sprints.start_date IS NOT NULL')
                    ->where('sprints.start_date >', '1970-01-01');

                if (!$isAdmin && !$isManager) {
                    $sprintBuilder->where('projects.user_id', $userId);
                }

                if (!empty($projectIdFilter)) {
                    $sprintBuilder->where('sprints.project_id', (int)$projectIdFilter);
                }

                $sprints = $sprintBuilder->get()->getResultArray();
                foreach ($sprints as $sp) {
                    $statusBadge = strtoupper($sp['status'] ?? 'PLANNED');
                    $events[] = [
                        'id'            => 'sprint_' . $sp['id'],
                        'title'         => "⚡ SPRINT: {$sp['name']} ({$statusBadge})",
                        'start'         => $sp['start_date'],
                        'end'           => $sp['end_date'] ?? null,
                        'color'         => ($sp['status'] ?? '') === 'active' ? '#727cf5' : (($sp['status'] ?? '') === 'completed' ? '#0acf97' : '#6c757d'),
                        'allDay'        => true,
                        'extendedProps' => [
                            'type'         => 'sprint',
                            'status'       => $sp['status'] ?? 'planned',
                            'goal'         => $sp['goal'] ?? '',
                            'total_points' => $sp['total_points'] ?? 0,
                            'project_name' => $sp['project_name'] ?? 'Project Sprint',
                            'url'          => site_url('projects/sprints/' . ($sp['project_slug'] ?: ($sp['project_id'] ?? ''))),
                            'icon'         => 'fa-running'
                        ]
                    ];
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'Calendar sprints error: ' . $e->getMessage());
        }

        // 3. Tasks & Deadlines
        try {
            if ($db->tableExists('tasks')) {
                $hasFirstName = $db->fieldExists('first_name', 'users');
                $assigneeSelect = $hasFirstName 
                    ? 'COALESCE(NULLIF(TRIM(CONCAT(assignee.first_name, " ", assignee.last_name)), ""), assignee.username, "Unassigned") as assignee_name'
                    : 'COALESCE(assignee.username, "Unassigned") as assignee_name';
                $creatorSelect = $hasFirstName 
                    ? 'COALESCE(NULLIF(TRIM(CONCAT(creator.first_name, " ", creator.last_name)), ""), creator.username, "Unknown") as creator_name'
                    : 'COALESCE(creator.username, "Unknown") as creator_name';

                $taskBuilder = $db->table('tasks')
                    ->select("tasks.*, projects.name as project_name, projects.slug as project_slug, projects.color as project_color, {$assigneeSelect}, {$creatorSelect}")
                    ->join('projects', 'projects.id = tasks.project_id', 'left')
                    ->join('users assignee', 'assignee.id = tasks.assigned_to', 'left')
                    ->join('users creator', 'creator.id = tasks.user_id', 'left')
                    ->where('tasks.due_date IS NOT NULL')
                    ->where('tasks.due_date >', '1970-01-01');

                if (!$isAdmin && !$isManager) {
                    $taskBuilder->groupStart()
                                ->where('tasks.user_id', $userId)
                                ->orWhere('tasks.assigned_to', $userId)
                                ->groupEnd();
                }

                if (!empty($projectIdFilter)) {
                    $taskBuilder->where('tasks.project_id', (int)$projectIdFilter);
                }

                $tasks = $taskBuilder->get()->getResultArray();
                foreach ($tasks as $task) {
                    $isDone = in_array($task['status'], ['done', 'approved']);
                    
                    $taskColor = '#39afd1'; // default info
                    if ($isDone) {
                        $taskColor = '#0acf97'; // green
                    } elseif (($task['priority'] ?? '') === 'urgent' || ($task['priority'] ?? '') === 'high') {
                        $taskColor = '#fa5c7c'; // red/high
                    } elseif (($task['priority'] ?? '') === 'medium') {
                        $taskColor = '#ffbc00'; // amber
                    }

                    $pointsStr = !empty($task['story_points']) ? " [{$task['story_points']} pts]" : '';

                    $events[] = [
                        'id'            => 'task_' . $task['id'],
                        'title'         => "✓ TASK: {$task['title']}{$pointsStr}",
                        'start'         => $task['due_date'],
                        'color'         => $taskColor,
                        'allDay'        => true,
                        'extendedProps' => [
                            'type'          => 'task',
                            'task_title'    => $task['title'],
                            'status'        => $task['status'] ?? 'todo',
                            'priority'      => $task['priority'] ?? 'medium',
                            'story_points'  => $task['story_points'] ?? null,
                            'assignee_name' => $task['assignee_name'] ?? 'Unassigned',
                            'creator_name'  => $task['creator_name'] ?? 'Team Member',
                            'due_date'      => $task['due_date'],
                            'description'   => $task['description'] ?: 'No description provided.',
                            'project_name'  => $task['project_name'] ?: 'Workspace Task',
                            'dbId'          => $task['id'],
                            'url'           => !empty($task['project_slug']) ? site_url('projects/kanban/' . $task['project_slug']) : site_url('kanban'),
                            'icon'          => 'fa-tasks'
                        ]
                    ];
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'Calendar tasks error: ' . $e->getMessage());
        }

        // 4. Project Target Completion Dates
        try {
            if ($db->tableExists('projects')) {
                $projBuilder = $db->table('projects')
                    ->where('due_date IS NOT NULL')
                    ->where('due_date >', '1970-01-01');

                if (!$isAdmin && !$isManager) {
                    $projBuilder->where('user_id', $userId);
                }

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
            }
        } catch (\Throwable $e) {
            log_message('error', 'Calendar projects error: ' . $e->getMessage());
        }

        // 5. Milestones
        try {
            if ($db->tableExists('project_milestones')) {
                $msBuilder = $db->table('project_milestones')
                    ->select('project_milestones.*, projects.name as project_name, projects.slug as project_slug, projects.color as project_color')
                    ->join('projects', 'projects.id = project_milestones.project_id', 'left')
                    ->where('project_milestones.due_date IS NOT NULL')
                    ->where('project_milestones.due_date >', '1970-01-01');

                if (!$isAdmin && !$isManager) {
                    $msBuilder->where('projects.user_id', $userId);
                }

                if (!empty($projectIdFilter)) {
                    $msBuilder->where('project_milestones.project_id', (int)$projectIdFilter);
                }

                $milestones = $msBuilder->get()->getResultArray();
                foreach ($milestones as $ms) {
                    if (($ms['status'] ?? '') !== 'completed') {
                        $events[] = [
                            'id'            => 'ms_due_' . $ms['id'],
                            'title'         => '🚩 MILESTONE: ' . $ms['name'],
                            'start'         => $ms['due_date'],
                            'color'         => '#f59e0b',
                            'allDay'        => true,
                            'extendedProps' => [
                                'type'         => 'milestone',
                                'description'  => 'Deadline for "' . $ms['name'] . '" in ' . ($ms['project_name'] ?? 'Project'),
                                'dbId'         => $ms['id'],
                                'icon'         => 'fa-flag'
                            ]
                        ];
                    }
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'Calendar milestones error: ' . $e->getMessage());
        }

        // 6. Time Logs
        try {
            if ($db->tableExists('time_logs')) {
                $timeBuilder = $db->table('time_logs')
                    ->where('start_time IS NOT NULL')
                    ->where('start_time >', '1970-01-01');

                if (!$isAdmin && !$isManager) {
                    $timeBuilder->where('user_id', $userId);
                }
                if (!empty($projectIdFilter)) {
                    $timeBuilder->where('project_id', (int)$projectIdFilter);
                }
                $logs = $timeBuilder->limit(100)->get()->getResultArray();
                foreach ($logs as $log) {
                    $events[] = [
                        'id'            => 'time_' . $log['id'],
                        'title'         => '⏱ TIME: ' . ($log['task_name'] ?? 'Logged Work'),
                        'start'         => $log['start_time'],
                        'end'           => $log['end_time'] ?? null,
                        'color'         => '#8b5cf6',
                        'extendedProps' => [
                            'type'        => 'time_log',
                            'description' => 'Time logged: ' . ($log['task_name'] ?? '') . (!empty($log['notes']) ? ' - ' . $log['notes'] : ''),
                            'dbId'        => $log['id'],
                            'icon'        => 'fa-clock'
                        ]
                    ];
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'Calendar time logs error: ' . $e->getMessage());
        }

        // 7. System & Security Events (Admin Only)
        try {
            if ($isAdmin && $db->tableExists('audit_logs')) {
                $auditLogs = $db->table('audit_logs')
                    ->where('created_at IS NOT NULL')
                    ->where('created_at >', '1970-01-01')
                    ->orderBy('created_at', 'DESC')
                    ->limit(50)
                    ->get()->getResultArray();

                foreach ($auditLogs as $al) {
                    $actionLabel = ucfirst(str_replace('_', ' ', $al['action'] ?? 'event'));
                    $events[] = [
                        'id'            => 'audit_' . $al['id'],
                        'title'         => '🔔 SYSTEM: ' . $actionLabel,
                        'start'         => $al['created_at'],
                        'color'         => '#6c757d',
                        'allDay'        => false,
                        'extendedProps' => [
                            'type'        => 'audit_log',
                            'description' => 'Action: ' . ($al['action'] ?? '') . ' on ' . ($al['target_table'] ?? '') . ' #' . ($al['target_id'] ?? ''),
                            'dbId'        => $al['id'],
                            'icon'        => 'fa-shield-alt'
                        ]
                    ];
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'Calendar audit logs error: ' . $e->getMessage());
        }

        return $this->response->setJSON($events);
    }
}
