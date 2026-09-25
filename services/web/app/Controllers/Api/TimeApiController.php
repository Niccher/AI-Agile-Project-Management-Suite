<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\TimeLogModel;

class TimeApiController extends BaseController
{
    public function start()
    {
        $userId = auth()->id();
        $projectId = $this->request->getPost('project_id');
        $taskName = $this->request->getPost('task_name');
        
        $timeModel = new TimeLogModel();
        $id = $timeModel->insert([
            'user_id' => $userId,
            'project_id' => $projectId ?: null,
            'task_name' => $taskName,
            'start_time' => date('Y-m-d H:i:s'),
            'notes' => ''
        ], true);

        return $this->response->setJSON(['status' => 'success', 'id' => $id]);
    }

    public function stop($id)
    {
        $timeModel = new TimeLogModel();
        $log = $timeModel->find($id);
        
        if ($log) {
            $endTime = date('Y-m-d H:i:s');
            $startTime = $log['start_time'];
            $duration = strtotime($endTime) - strtotime($startTime);
            
            $timeModel->update($id, [
                'end_time' => $endTime,
                'duration' => $duration
            ]);
            
            return $this->response->setJSON(['status' => 'success', 'duration' => $duration]);
        }
        
        return $this->response->setJSON(['status' => 'error', 'message' => 'Log not found'], 404);
    }
}
