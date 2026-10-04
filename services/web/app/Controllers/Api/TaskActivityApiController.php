<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\ActivityLogger;

class TaskActivityApiController extends BaseController
{
    public function list(int $taskId)
    {
        $activities = ActivityLogger::getForTask($taskId);

        return $this->response->setJSON([
            'status'     => 'success',
            'activities' => $activities,
        ]);
    }
}
