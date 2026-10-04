<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\TaskModel;
use App\Models\ProjectModel;
use App\Services\AuditService;
use App\Services\NotificationService;

class TaskApiController extends BaseController
{
    public function store()
    {
        $taskModel = new TaskModel();
        $userId = auth()->id();

        $assignedTo = $this->request->getPost('assigned_to') ?: null;
        $dueDate = $this->request->getPost('due_date') ?: null;

        $data = [
            'user_id'     => $userId,
            'project_id'  => $this->request->getPost('project_id'),
            'title'       => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'status'      => $this->request->getPost('status') ?? 'todo',
            'priority'    => $this->request->getPost('priority') ?? 'medium',
            'assigned_to' => $assignedTo,
            'assigned_by' => $assignedTo ? $userId : null,
            'due_date'    => $dueDate,
            'order_index' => 0,
        ];

        $insertId = $taskModel->insert($data);
        $newTask = $taskModel->find($insertId);

        if (!empty($assignedTo) && (int)$assignedTo !== (int)$userId) {
            try {
                $projectModel = new ProjectModel();
                $project = $projectModel->find($data['project_id']);
                $projectName = $project['name'] ?? 'Project';
                $projectSlug = !empty($project['slug']) ? $project['slug'] : ($project['id'] ?? '');

                NotificationService::send(
                    (int)$assignedTo,
                    'task_assigned',
                    'New Task Assigned: ' . $data['title'],
                    'You were assigned to task "' . $data['title'] . '" in ' . $projectName . '.',
                    'projects/view/' . $projectSlug . '/kanban',
                    [
                        'task_title'   => $data['title'],
                        'project_name' => $projectName,
                        'new_status'   => $data['status']
                    ]
                );
            } catch (\Throwable $e) {}
        }

        if ($this->request->isAJAX() || $this->request->header('Accept')?->getValue() === 'application/json' || str_contains($this->request->header('Content-Type')?->getValue() ?? '', 'json')) {
            return $this->response->setJSON([
                'success' => true,
                'status'  => 'success',
                'message' => 'Task added successfully.',
                'task'    => $newTask,
            ]);
        }

        return redirect()->back()->with('success', 'Task added successfully.');
    }

    public function delete($id = null)
    {
        $taskModel = new TaskModel();
        $userId = auth()->id();
        $currentUser = auth()->user();
        $isAdmin = $currentUser && ($currentUser->inGroup('admin') || $currentUser->inGroup('superadmin') || $currentUser->inGroup('manager'));

        $task = $taskModel->find($id);
        if (!$task) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Task not found']);
        }

        if (!$isAdmin && (int)$task['user_id'] !== (int)$userId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Permission denied']);
        }

        $taskModel->delete($id);
        return $this->response->setJSON(['status' => 'success', 'message' => 'Task deleted successfully.']);
    }

    public function update($id = null)
    {
        $taskModel = new TaskModel();
        $userId = auth()->id();
        $currentUser = auth()->user();
        $isAdmin = $currentUser && ($currentUser->inGroup('admin') || $currentUser->inGroup('superadmin') || $currentUser->inGroup('manager'));

        $task = $taskModel->find($id);
        if (!$task) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Task not found']);
        }

        if (!$isAdmin && (int)$task['user_id'] !== (int)$userId && (int)($task['assigned_to'] ?? 0) !== (int)$userId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Permission denied']);
        }

        $json = $this->request->getJSON(true);
        $allowedFields = ['title', 'description', 'priority', 'due_date', 'status', 'assigned_to'];
        $data = [];
        foreach ($allowedFields as $field) {
            $value = $this->request->getPost($field) ?? ($json[$field] ?? null);
            if ($value !== null) {
                $data[$field] = $value;
            }
        }

        if (empty($data)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No data provided']);
        }

        $oldAssignedTo = $task['assigned_to'] ?? null;
        $newAssignedTo = $data['assigned_to'] ?? null;

        $taskModel->update($id, $data);
        $updatedTask = $taskModel->find($id);

        if (!empty($newAssignedTo) && (int)$newAssignedTo !== (int)$oldAssignedTo && (int)$newAssignedTo !== (int)$userId) {
            try {
                $projectModel = new ProjectModel();
                $project = $projectModel->find($task['project_id']);
                $projectName = $project['name'] ?? 'Project';
                $projectSlug = !empty($project['slug']) ? $project['slug'] : ($project['id'] ?? '');

                NotificationService::send(
                    (int)$newAssignedTo,
                    'task_assigned',
                    'Task Assigned to You: ' . ($updatedTask['title'] ?? 'Task'),
                    'You have been assigned to task "' . ($updatedTask['title'] ?? 'Task') . '" in ' . $projectName . '.',
                    'projects/view/' . $projectSlug . '/kanban',
                    [
                        'task_title'   => $updatedTask['title'] ?? 'Task',
                        'project_name' => $projectName,
                        'new_status'   => $updatedTask['status'] ?? 'todo'
                    ]
                );
            } catch (\Throwable $e) {}
        }

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Task updated successfully.',
            'task'    => $updatedTask
        ]);
    }

    public function move()
    {
        $taskModel = new TaskModel();
        $userId = auth()->id();
        $currentUser = auth()->user();
        $isAdmin = $currentUser && ($currentUser->inGroup('admin') || $currentUser->inGroup('superadmin') || $currentUser->inGroup('manager'));

        $taskId = $this->request->getPost('task_id');
        $newStatus = $this->request->getPost('status');
        $newOrder = (int)($this->request->getPost('order') ?? 0);

        $task = $taskModel->find($taskId);

        if (!$task) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Task not found']);
        }

        if (!$isAdmin && (int)$task['user_id'] !== (int)$userId && (int)($task['assigned_to'] ?? 0) !== (int)$userId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Permission denied']);
        }

        $oldStatus = $task['status'] ?? 'todo';

        $updateData = [
            'status'      => $newStatus,
            'order_index' => $newOrder
        ];

        if ($newStatus === 'done' && $oldStatus !== 'done') {
            $updateData['completed_at'] = date('Y-m-d H:i:s');
        } elseif ($newStatus !== 'done' && $oldStatus === 'done') {
            $updateData['completed_at'] = null;
        }

        $taskModel->update($taskId, $updateData);

        // Audit Trail
        if (class_exists(AuditService::class)) {
            AuditService::record(
                'move_task',
                'tasks',
                $taskId,
                ['status' => $oldStatus],
                ['status' => $newStatus, 'order_index' => $newOrder]
            );
        }

        // Automated Email & Notification Dispatch on Stage Change
        if ($oldStatus !== $newStatus) {
            try {
                $projectModel = new ProjectModel();
                $project = !empty($task['project_id']) ? $projectModel->find($task['project_id']) : null;
                $projectName = $project['name'] ?? 'Project Workspace';
                $projectSlug = !empty($project['slug']) ? $project['slug'] : ($project['id'] ?? '');

                $moverName = $currentUser 
                    ? (trim(($currentUser->first_name ?? '') . ' ' . ($currentUser->last_name ?? '')) ?: ($currentUser->username ?? 'Team Member'))
                    : 'Team Member';

                $stageLabels = [
                    'todo'        => 'To Do',
                    'in_progress' => 'In Progress',
                    'review'      => 'In Review',
                    'done'        => 'Done'
                ];
                $readableNewStage = $stageLabels[$newStatus] ?? ucfirst(str_replace('_', ' ', $newStatus));

                $targetRecipients = array_filter(array_unique([
                    (int)($task['assigned_to'] ?? 0),
                    (int)($task['user_id'] ?? 0)
                ]));

                foreach ($targetRecipients as $recipientId) {
                    if ($recipientId > 0 && $recipientId !== (int)$userId) {
                        NotificationService::send(
                            $recipientId,
                            'task_moved',
                            'Task Moved: ' . ($task['title'] ?? 'Task'),
                            'Task "' . ($task['title'] ?? 'Task') . '" was moved to ' . $readableNewStage . ' by ' . $moverName . '.',
                            'projects/view/' . $projectSlug . '/kanban',
                            [
                                'task_title'   => $task['title'] ?? 'Task',
                                'project_name' => $projectName,
                                'new_status'   => $newStatus,
                                'moved_by'     => $moverName,
                                'action_url'   => 'projects/view/' . $projectSlug . '/kanban'
                            ]
                        );
                    }
                }
            } catch (\Throwable $e) {
                log_message('warning', 'Task move notification dispatch error: ' . $e->getMessage());
            }
        }

        return $this->response->setJSON([
            'status'     => 'success',
            'task_id'    => $taskId,
            'old_status' => $oldStatus,
            'new_status' => $newStatus
        ]);
    }
}
