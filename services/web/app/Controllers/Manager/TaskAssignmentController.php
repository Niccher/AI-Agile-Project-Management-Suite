<?php

namespace App\Controllers\Manager;

use App\Controllers\BaseController;
use App\Models\TaskModel;
use App\Models\UserModel;

class TaskAssignmentController extends BaseController
{
    public function index()
    {
        $userModel = new UserModel();
        // Load only users with 'user' role
        $users = $userModel->findAll(); // Would ideally filter by role here

        return view('manager/tasks/assign', [
            'users' => $users
        ]);
    }

    public function assign()
    {
        $taskModel = new TaskModel();
        
        $data = [
            'project_id'  => $this->request->getPost('project_id'),
            'title'       => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'status'      => 'todo',
            'priority'    => $this->request->getPost('priority'),
            'due_date'    => $this->request->getPost('due_date'),
            'assigned_to' => $this->request->getPost('assigned_to'),
            'assigned_by' => auth()->id(),
        ];

        if ($taskModel->insert($data)) {
            $taskId = $taskModel->getInsertID();
            
            \App\Services\AuditService::record('assign_task', 'tasks', $taskId, null, $data);
            
            if (!empty($data['assigned_to'])) {
                \App\Services\NotificationService::send(
                    $data['assigned_to'], 
                    'task_assigned', 
                    'New Task Assigned', 
                    'You have been assigned a new task: "'.esc($data['title']).'".'
                );
            }
            
            return redirect()->to('/manage/tasks/assign')->with('message', 'Task assigned successfully.');
        }

        return redirect()->back()->withInput()->with('errors', $taskModel->errors());
    }
}
