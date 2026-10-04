<?php

namespace App\Services;

class VelocityService
{
    /**
     * Calculates rolling team velocity for a project based on the last N completed sprints.
     *
     * @param int $projectId
     * @param int $sprintCount
     * @return float
     */
    public static function calculateTeamVelocity(int $projectId, int $sprintCount = 3): float
    {
        try {
            $db = \Config\Database::connect();
            if (!$db->tableExists('sprints')) {
                return 0.0;
            }

            $completedSprints = $db->table('sprints')
                ->where('project_id', $projectId)
                ->where('status', 'completed')
                ->orderBy('end_date', 'DESC')
                ->limit($sprintCount)
                ->get()
                ->getResultArray();

            if (empty($completedSprints)) {
                return 0.0;
            }

            $totalPoints = 0;
            foreach ($completedSprints as $sprint) {
                // If sprint completed_points was saved directly, use it, else calculate from done tasks
                if (!empty($sprint['completed_points']) && (int)$sprint['completed_points'] > 0) {
                    $totalPoints += (int)$sprint['completed_points'];
                } else {
                    $donePoints = $db->table('tasks')
                        ->selectSum('story_points')
                        ->where('sprint_id', $sprint['id'])
                        ->where('status', 'done')
                        ->get()
                        ->getRow()
                        ->story_points ?? 0;
                    $totalPoints += (int)$donePoints;
                }
            }

            return round($totalPoints / count($completedSprints), 1);
        } catch (\Throwable $e) {
            log_message('error', 'VelocityService team calculation error: ' . $e->getMessage());
            return 0.0;
        }
    }

    /**
     * Calculates personal velocity for a specific user across the last N completed sprints.
     *
     * @param int $userId
     * @param int|null $projectId
     * @param int $sprintCount
     * @return float
     */
    public static function calculatePersonalVelocity(int $userId, ?int $projectId = null, int $sprintCount = 3): float
    {
        try {
            $db = \Config\Database::connect();
            if (!$db->tableExists('sprints') || !$db->tableExists('tasks')) {
                return 0.0;
            }

            $builder = $db->table('sprints')
                ->where('status', 'completed');
            
            if ($projectId !== null) {
                $builder->where('project_id', $projectId);
            }

            $completedSprints = $builder->orderBy('end_date', 'DESC')
                ->limit($sprintCount)
                ->get()
                ->getResultArray();

            if (empty($completedSprints)) {
                // If no completed sprints, count all completed tasks by this user for the project
                $taskBuilder = $db->table('tasks')
                    ->selectSum('story_points')
                    ->where('assigned_to', $userId)
                    ->where('status', 'done');
                if ($projectId !== null) {
                    $taskBuilder->where('project_id', $projectId);
                }
                $points = $taskBuilder->get()->getRow()->story_points ?? 0;
                return (float)$points;
            }

            $sprintIds = array_column($completedSprints, 'id');
            $userDonePoints = $db->table('tasks')
                ->selectSum('story_points')
                ->whereIn('sprint_id', $sprintIds)
                ->where('assigned_to', $userId)
                ->where('status', 'done')
                ->get()
                ->getRow()
                ->story_points ?? 0;

            return round((float)$userDonePoints / count($completedSprints), 1);
        } catch (\Throwable $e) {
            log_message('error', 'VelocityService personal calculation error: ' . $e->getMessage());
            return 0.0;
        }
    }

    /**
     * Computes the current sprint breakdown metrics.
     *
     * @param int $sprintId
     * @return array
     */
    public static function getSprintMetrics(int $sprintId): array
    {
        try {
            $db = \Config\Database::connect();
            $tasks = $db->table('tasks')
                ->where('sprint_id', $sprintId)
                ->get()
                ->getResultArray();

            $totalPoints = 0;
            $completedPoints = 0;
            $totalTasks = count($tasks);
            $completedTasks = 0;

            foreach ($tasks as $task) {
                $pts = (int)($task['story_points'] ?? 0);
                $totalPoints += $pts;
                if (($task['status'] ?? '') === 'done') {
                    $completedPoints += $pts;
                    $completedTasks++;
                }
            }

            $remainingPoints = max(0, $totalPoints - $completedPoints);
            $completionRate = $totalPoints > 0 ? round(($completedPoints / $totalPoints) * 100, 1) : 0;

            return [
                'total_points'      => $totalPoints,
                'completed_points'  => $completedPoints,
                'remaining_points'  => $remainingPoints,
                'total_tasks'       => $totalTasks,
                'completed_tasks'   => $completedTasks,
                'completion_rate'   => $completionRate,
            ];
        } catch (\Throwable $e) {
            return [
                'total_points'      => 0,
                'completed_points'  => 0,
                'remaining_points'  => 0,
                'total_tasks'       => 0,
                'completed_tasks'   => 0,
                'completion_rate'   => 0,
            ];
        }
    }
}
