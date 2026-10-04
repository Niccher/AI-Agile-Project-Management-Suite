<?php

namespace App\Services;

class ActivityLogger
{
    /**
     * Records an activity for a task.
     *
     * @param int $taskId
     * @param string $action Action key (e.g. 'created', 'status_changed', 'points_changed', 'commented', 'attachment_added', 'assigned')
     * @param string|array|null $details Description or structured details
     * @param int|null $userId Optional user ID (defaults to current logged-in user)
     * @return bool
     */
    public static function log(int $taskId, string $action, $details = null, ?int $userId = null): bool
    {
        try {
            $db = \Config\Database::connect();
            if (!$db->tableExists('task_activities')) {
                return false;
            }

            if ($userId === null) {
                $userId = function_exists('auth') && auth()->id() ? auth()->id() : null;
            }

            $detailsString = is_array($details) ? json_encode($details) : (string)$details;

            $db->table('task_activities')->insert([
                'task_id'    => $taskId,
                'user_id'    => $userId,
                'action'     => $action,
                'details'    => $detailsString,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            return true;
        } catch (\Throwable $e) {
            log_message('error', 'ActivityLogger failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get recent activities for a task with user info.
     *
     * @param int $taskId
     * @param int $limit
     * @return array
     */
    public static function getForTask(int $taskId, int $limit = 50): array
    {
        try {
            $db = \Config\Database::connect();
            if (!$db->tableExists('task_activities')) {
                return [];
            }

            return $db->table('task_activities')
                ->select('task_activities.*, users.username, users.first_name, users.last_name')
                ->join('users', 'users.id = task_activities.user_id', 'left')
                ->where('task_activities.task_id', $taskId)
                ->orderBy('task_activities.created_at', 'DESC')
                ->limit($limit)
                ->get()
                ->getResultArray();
        } catch (\Throwable $e) {
            return [];
        }
    }
}
