<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\TimeLogModel;

class TimeApiController extends BaseController
{
    public function start()
    {
        $userId = auth()->id();
        $input = $this->request->getPost();
        if (empty($input)) {
            try {
                $input = $this->request->getJSON(true) ?: [];
            } catch (\Throwable $e) {
                $input = [];
            }
        }
        
        $projectId = !empty($input['project_id']) ? (int)$input['project_id'] : ($this->request->getVar('project_id') ? (int)$this->request->getVar('project_id') : null);
        $taskName = trim($input['task_name'] ?? $this->request->getVar('task_name') ?? 'Work session');
        
        if (empty($taskName)) {
            $taskName = 'Work session';
        }

        $projectModel = new \App\Models\ProjectModel();
        $projectName = 'General';
        $projectSlug = '';
        if ($projectId) {
            $proj = $projectModel->find($projectId);
            if ($proj) {
                $projectName = $proj['name'] ?? 'Project';
                $projectSlug = $proj['slug'] ?? (string)$projectId;
            }
        }

        $startTime = date('Y-m-d H:i:s');
        $timeModel = new TimeLogModel();
        $id = $timeModel->insert([
            'user_id'    => $userId,
            'project_id' => $projectId,
            'task_name'  => $taskName,
            'start_time' => $startTime,
            'notes'      => ''
        ], true);

        return $this->response->setJSON([
            'status'       => 'success',
            'success'      => true,
            'id'           => $id,
            'project_id'   => $projectId,
            'project_name' => $projectName,
            'project_slug' => $projectSlug,
            'task_name'    => $taskName,
            'start_time'   => $startTime,
            'message'      => "Timer started for \"{$taskName}\""
        ]);
    }

    public function stop($id)
    {
        $timeModel = new TimeLogModel();
        $log = $timeModel->find($id);
        
        if ($log) {
            $endTime = date('Y-m-d H:i:s');
            $startTime = $log['start_time'];
            $duration = max(1, strtotime($endTime) - strtotime($startTime));
            
            $timeModel->update($id, [
                'end_time' => $endTime,
                'duration' => $duration
            ]);
            
            $hrs = round($duration / 3600, 2);
            return $this->response->setJSON([
                'status'   => 'success',
                'success'  => true,
                'duration' => $duration,
                'duration_hours' => $hrs,
                'message'  => "Timer stopped. {$hrs} hrs recorded."
            ]);
        }
        
        return $this->response->setJSON(['status' => 'error', 'success' => false, 'message' => 'Time log not found'], 404);
    }

    public function delete($id)
    {
        $userId = auth()->id();
        $timeModel = new TimeLogModel();
        $log = $timeModel->find($id);

        if ($log) {
            // Check ownership or admin privileges
            $currentUser = auth()->user();
            $isAdminOrManager = $currentUser && ($currentUser->inGroup('admin', 'manager') || is_solo_mode());
            if ((int)$log['user_id'] !== (int)$userId && !$isAdminOrManager) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'success' => false,
                    'message' => 'Unauthorized to delete this time entry.'
                ], 403);
            }

            $timeModel->delete($id);

            return $this->response->setJSON([
                'status'  => 'success',
                'success' => true,
                'message' => 'Time entry deleted successfully.'
            ]);
        }

        return $this->response->setJSON([
            'status'  => 'error',
            'success' => false,
            'message' => 'Time entry not found.'
        ], 404);
    }
}
