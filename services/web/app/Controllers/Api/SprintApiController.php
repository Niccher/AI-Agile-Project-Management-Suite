<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\SprintModel;
use App\Models\TaskModel;

class SprintApiController extends BaseController
{
    /**
     * Assign or move task between Backlog and Sprint
     * POST /api/sprints/assign-task
     * Body: { task_id: 12, sprint_id: 5 } (sprint_id null or 0 means backlog)
     */
    public function assignTask()
    {
        $userId = auth()->id();
        if (!$userId) {
            return $this->response->setStatusCode(401)->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $taskId = (int)$this->request->getPost('task_id');
        $sprintId = $this->request->getPost('sprint_id');
        $sprintId = (!empty($sprintId) && $sprintId !== 'null' && $sprintId !== 'backlog') ? (int)$sprintId : null;

        $taskModel = new TaskModel();
        $task = $taskModel->find($taskId);

        if (!$task) {
            return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => 'Task not found']);
        }

        $oldSprintId = $task['sprint_id'];
        $taskModel->update($taskId, ['sprint_id' => $sprintId]);

        $sprintModel = new SprintModel();
        if ($oldSprintId) {
            $sprintModel->recalculatePoints($oldSprintId);
        }
        if ($sprintId) {
            $sprintModel->recalculatePoints($sprintId);
        }

        return $this->response->setJSON([
            'status'     => 'success',
            'message'    => $sprintId ? 'Task moved to Sprint.' : 'Task moved to Backlog.',
            'task_id'    => $taskId,
            'sprint_id'  => $sprintId,
        ]);
    }

    /**
     * Update task story points
     * POST /api/sprints/task-points
     * Body: { task_id: 12, story_points: 5 }
     */
    public function updatePoints()
    {
        $userId = auth()->id();
        if (!$userId) {
            return $this->response->setStatusCode(401)->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $taskId = (int)$this->request->getPost('task_id');
        $points = max(0, (int)$this->request->getPost('story_points'));

        $taskModel = new TaskModel();
        $task = $taskModel->find($taskId);

        if (!$task) {
            return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => 'Task not found']);
        }

        $taskModel->update($taskId, ['story_points' => $points]);

        if (!empty($task['sprint_id'])) {
            $sprintModel = new SprintModel();
            $sprintModel->recalculatePoints((int)$task['sprint_id']);
        }

        return $this->response->setJSON([
            'status'       => 'success',
            'task_id'      => $taskId,
            'story_points' => $points,
        ]);
    }
}
