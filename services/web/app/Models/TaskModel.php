<?php

namespace App\Models;

use CodeIgniter\Model;

class TaskModel extends Model
{
    protected $table            = 'tasks';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'project_id',
        'sprint_id',
        'title',
        'description',
        'status',
        'priority',
        'story_points',
        'order_index',
        'due_date',
        'assigned_to',
        'assigned_by',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_reason'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get tasks grouped by status for a specific project
     */
    public function getBoardData(int $projectId, int $userId)
    {
        $tasks = $this->where('project_id', $projectId)
                      ->where('user_id', $userId)
                      ->orderBy('order_index', 'ASC')
                      ->findAll();

        $board = [
            'todo'        => [],
            'in_progress' => [],
            'review'      => [],
            'done'        => []
        ];

        foreach ($tasks as $task) {
            $status = $task['status'] ?? 'todo';
            if (isset($board[$status])) {
                $board[$status][] = $task;
            } else {
                $board['todo'][] = $task;
            }
        }

        return $board;
    }

    /**
     * Get tasks assigned to a specific user
     */
    public function getAssignedToUser(int $userId)
    {
        return $this->select('tasks.*, projects.name as project_name, projects.color as project_color')
                    ->join('projects', 'projects.id = tasks.project_id', 'left')
                    ->where('assigned_to', $userId)
                    ->orderBy('tasks.priority', 'DESC')
                    ->orderBy('tasks.created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Get tasks pending review (status = 'review')
     */
    public function getPendingReviews(?int $managerId = null)
    {
        $query = $this->select('tasks.*, projects.name as project_name, users.first_name, users.last_name, users.username')
                      ->join('projects', 'projects.id = tasks.project_id', 'left')
                      ->join('users', 'users.id = tasks.assigned_to', 'left')
                      ->where('tasks.status', 'review');
                      
        if ($managerId !== null) {
            $query->groupStart()
                  ->where('tasks.assigned_by', $managerId)
                  ->orWhere('tasks.assigned_by IS NULL')
                  ->groupEnd();
        }

        return $query->orderBy('tasks.updated_at', 'ASC')->findAll();
    }

    /**
     * Get unassigned backlog tasks for a project
     */
    public function getBacklogTasks(int $projectId)
    {
        return $this->select('tasks.*, users.username as assignee_name')
                    ->join('users', 'users.id = tasks.assigned_to', 'left')
                    ->where('tasks.project_id', $projectId)
                    ->where('tasks.sprint_id', null)
                    ->orderBy('tasks.order_index', 'ASC')
                    ->orderBy('tasks.created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Get tasks assigned to a specific sprint
     */
    public function getSprintTasks(int $sprintId)
    {
        return $this->select('tasks.*, users.username as assignee_name')
                    ->join('users', 'users.id = tasks.assigned_to', 'left')
                    ->where('tasks.sprint_id', $sprintId)
                    ->orderBy('tasks.order_index', 'ASC')
                    ->orderBy('tasks.created_at', 'DESC')
                    ->findAll();
    }
}
