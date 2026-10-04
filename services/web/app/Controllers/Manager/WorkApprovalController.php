<?php

namespace App\Controllers\Manager;

use App\Controllers\BaseController;
use App\Models\TaskModel;

class WorkApprovalController extends BaseController
{
    public function index()
    {
        $taskModel = new TaskModel();
        $tasks = $taskModel->getPendingReviews();

        $db = \Config\Database::connect();
        foreach ($tasks as &$task) {
            $timeLog = $db->table('time_logs')
                ->select('COALESCE(SUM(duration), 0) as total_duration')
                ->where('project_id', $task['project_id'])
                ->where('task_name', $task['title'])
                ->get()->getRowArray();
            
            $sec = (int)($timeLog['total_duration'] ?? 0);
            $task['logged_hours'] = number_format($sec / 3600, 1);
        }
        unset($task);

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
        
        $db = \Config\Database::connect();
        $taskFields = $db->getFieldNames('tasks') ?? [];
        $data = [
            'status'      => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => date('Y-m-d H:i:s')
        ];
        $data = array_intersect_key($data, array_flip($taskFields));

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
        $db = \Config\Database::connect();
        $taskFields = $db->getFieldNames('tasks') ?? [];
        $data = [
            'status'          => 'rejected',
            'rejected_by'     => auth()->id(),
            'rejected_reason' => $reason
        ];
        $data = array_intersect_key($data, array_flip($taskFields));

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
