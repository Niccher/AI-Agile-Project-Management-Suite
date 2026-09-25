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
            
            return redirect()->back()->with('message', 'Task approved successfully.');
        }

        return redirect()->back()->with('error', 'Failed to approve task.');
    }

    public function reject($id)
    {
        $taskModel = new TaskModel();
        $task = $taskModel->find($id);
        
        if (!$task) {
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
            
            return redirect()->back()->with('message', 'Task rejected.');
        }

        return redirect()->back()->with('error', 'Failed to reject task.');
    }
}
