<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\TaskCommentModel;
use App\Models\TaskModel;
use App\Models\UserModel;
use App\Models\ProjectModel;
use App\Services\ActivityLogger;
use App\Services\NotificationService;

class TaskCommentApiController extends BaseController
{
    public function list(int $taskId)
    {
        $commentModel = new TaskCommentModel();
        $comments = $commentModel->getCommentsForTask($taskId);

        return $this->response->setJSON([
            'status'   => 'success',
            'comments' => $comments,
        ]);
    }

    public function store(int $taskId)
    {
        $taskModel = new TaskModel();
        $task = $taskModel->find($taskId);

        if (!$task) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Task not found',
            ]);
        }

        $userId = auth()->id();
        $body = trim((string)$this->request->getPost('body'));

        if (empty($body)) {
            $json = $this->request->getJSON(true);
            $body = trim((string)($json['body'] ?? ''));
        }

        if (empty($body)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Comment cannot be empty.',
            ]);
        }

        $commentModel = new TaskCommentModel();
        $commentId = $commentModel->insert([
            'task_id' => $taskId,
            'user_id' => $userId,
            'body'    => $body,
        ]);

        $comment = $commentModel->find($commentId);
        $user = auth()->user();
        $comment['username'] = $user->username ?? 'User';
        $comment['first_name'] = $user->first_name ?? '';
        $comment['last_name'] = $user->last_name ?? '';

        // Log Activity
        ActivityLogger::log($taskId, 'commented', 'Added a comment', $userId);

        // Parse @mentions and notify users (if not solo mode)
        if (!is_solo_mode() && preg_match_all('/@([a-zA-Z0-9_\-]+)/', $body, $matches)) {
            $mentionedUsernames = array_unique($matches[1]);
            $userModel = new UserModel();
            $projectModel = new ProjectModel();
            $project = $projectModel->find($task['project_id']);
            $projectName = $project['name'] ?? 'Project';
            $projectSlug = !empty($project['slug']) ? $project['slug'] : ($project['id'] ?? '');

            foreach ($mentionedUsernames as $uname) {
                $targetUser = $userModel->where('username', $uname)->first();
                if ($targetUser && (int)$targetUser->id !== (int)$userId) {
                    NotificationService::send(
                        (int)$targetUser->id,
                        'task_mention',
                        'Mentioned in: ' . $task['title'],
                        ($user->username ?? 'Someone') . ' mentioned you in task: ' . $task['title'],
                        'projects/view/' . $projectSlug . '/kanban',
                        ['task_id' => $taskId, 'project_name' => $projectName]
                    );
                }
            }
        }

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Comment posted.',
            'comment' => $comment,
        ]);
    }

    public function delete(int $id)
    {
        $commentModel = new TaskCommentModel();
        $comment = $commentModel->find($id);

        if (!$comment) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Comment not found']);
        }

        $userId = auth()->id();
        $currentUser = auth()->user();
        $isAdmin = $currentUser && ($currentUser->inGroup('admin') || $currentUser->inGroup('manager'));

        if (!$isAdmin && (int)$comment['user_id'] !== (int)$userId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Permission denied']);
        }

        $commentModel->delete($id);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Comment deleted.',
        ]);
    }
}
