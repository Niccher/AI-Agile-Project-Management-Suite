<?php

namespace App\Controllers\User;

use App\Models\TaskModel;

class MyTasksController extends BaseUserController
{
    public function index()
    {
        $taskModel = new TaskModel();
        // For now, this is just a placeholder until we run the migrations.
        // It will fetch tasks assigned to this user.
        // $tasks = $taskModel->where('assigned_to', $this->userId)->findAll();

        $data = [
            'user' => $this->currentUser,
            'tasks' => [] // Will populate after DB updates
        ];

        return view('user/my_tasks/index', $data);
    }
}
