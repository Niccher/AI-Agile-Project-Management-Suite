<?php

namespace App\Controllers\Manager;

use App\Controllers\BaseController;
use App\Models\TaskModel;

class WorkApprovalController extends BaseController
{
    public function index()
    {
        $taskModel = new TaskModel();
        $currentUser = auth()->user();
        $managerId = ($currentUser && $currentUser->inGroup('admin')) ? null : auth()->id();
        $tasks = $taskModel->getPendingReviews($managerId);

        return view('manager/approvals/index', ['tasks' => $tasks]);
    }

    public function approve($id)
    {
        $taskModel = new TaskModel();
        $task = $taskModel->find($id);
        
        if (!$task) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Task not found.']);
            }
            return redirect()->back()->with('error', 'Task not found.');
        }
        
        $data = [
            'status'      => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => date('Y-m-d H:i:s')
        ];

        if ($taskModel->update($id, $data)) {
            \App\Services\AuditService::record('approve_task', 'tasks', $id, ['status' => $task['status']], ['status' => 'approved']);
            
            if (!empty($task['assigned_to'])) {
                \App\Services\NotificationService::send(
                    $task['assigned_to'], 
                    'task_approved', 
                    'Task Approved', 
                    'Your task "'.esc($task['title']).'" has been approved.'
                );
            }
            
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => 'success', 'message' => 'Task approved successfully.', 'id' => $id]);
            }
            return redirect()->back()->with('message', 'Task approved successfully.');
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to approve task.']);
        }
        return redirect()->back()->with('error', 'Failed to approve task.');
    }

    public function reject($id)
    {
        $taskModel = new TaskModel();
        $task = $taskModel->find($id);
        
        if (!$task) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Task not found.']);
            }
            return redirect()->back()->with('error', 'Task not found.');
        }
        
        $reason = $this->request->getPost('rejected_reason');
        $data = [
            'status'          => 'rejected',
            'rejected_by'     => auth()->id(),
            'rejected_reason' => $reason
        ];

        if ($taskModel->update($id, $data)) {
            \App\Services\AuditService::record('reject_task', 'tasks', $id, ['status' => $task['status']], ['status' => 'rejected', 'reason' => $reason]);
            
            if (!empty($task['assigned_to'])) {
                \App\Services\NotificationService::send(
                    $task['assigned_to'], 
                    'task_rejected', 
                    'Task Rejected', 
                    'Your task "'.esc($task['title']).'" was rejected. Reason: ' . esc($reason)
                );
            }
            
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => 'success', 'message' => 'Task rejected.', 'id' => $id]);
            }
            return redirect()->back()->with('message', 'Task rejected.');
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to reject task.']);
        }
        return redirect()->back()->with('error', 'Failed to reject task.');
    }
}
