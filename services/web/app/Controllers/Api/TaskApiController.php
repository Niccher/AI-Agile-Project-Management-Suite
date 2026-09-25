<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\TaskModel;

class TaskApiController extends BaseController
{
    public function store()
    {
        $taskModel = new TaskModel();
        $userId = auth()->id();

        $data = [
            'user_id'     => $userId,
            'project_id'  => $this->request->getPost('project_id'),
            'title'       => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'status'      => $this->request->getPost('status') ?? 'todo',
            'priority'    => $this->request->getPost('priority') ?? 'medium',
            'order_index' => 0,
        ];

        $taskModel->insert($data);

        return redirect()->back()->with('success', 'Task added successfully.');
    }

    public function update($id = null)
    {
        $taskModel = new TaskModel();
        $userId = auth()->id();

        $task = $taskModel->where('user_id', $userId)->find($id);
        if (!$task) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Task not found']);
        }

        $allowedFields = ['title', 'description', 'priority', 'due_date', 'status'];
        $data = [];
        foreach ($allowedFields as $field) {
            $value = $this->request->getPost($field);
            if ($value !== null) {
                $data[$field] = $value;
            }
        }

        if (empty($data)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No data provided']);
        }

        $taskModel->update($id, $data);
        return $this->response->setJSON(['status' => 'success']);
    }

    public function move()
    {
        $taskModel = new TaskModel();
        $userId = auth()->id();

        $taskId = $this->request->getPost('task_id');
        $newStatus = $this->request->getPost('status');
        $newOrder = $this->request->getPost('order');

        $task = $taskModel->where('user_id', $userId)->find($taskId);

        if (!$task) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Task not found']);
        }

        $taskModel->update($taskId, [
            'status'      => $newStatus,
            'order_index' => $newOrder
        ]);

        return $this->response->setJSON(['status' => 'success']);
    }
}
