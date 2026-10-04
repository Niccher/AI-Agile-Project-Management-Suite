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
    public function getBoardData(int $projectId, ?int $userId = null)
    {
        $db = $this->db;
        $userFields = $db->tableExists('users') ? $db->getFieldNames('users') : [];

        $selects = ['tasks.*'];
        if (in_array('username', $userFields, true)) {
            $selects[] = 'uc.username as creator_username';
            $selects[] = 'ua.username as assignee_username';
        }
        if (in_array('first_name', $userFields, true)) {
            $selects[] = 'uc.first_name as creator_first_name';
            $selects[] = 'ua.first_name as assignee_first_name';
        }
        if (in_array('last_name', $userFields, true)) {
            $selects[] = 'uc.last_name as creator_last_name';
            $selects[] = 'ua.last_name as assignee_last_name';
        }

        $query = (new static())->select(implode(', ', $selects))
                      ->join('users uc', 'uc.id = tasks.user_id', 'left')
                      ->join('users ua', 'ua.id = tasks.assigned_to', 'left')
                      ->where('tasks.project_id', $projectId);

        $taskFields = $db->getFieldNames('tasks') ?? [];
        if (in_array('order_index', $taskFields, true)) {
            $query->orderBy('tasks.order_index', 'ASC');
        }

        $tasks = $query->findAll();

        $board = [
            'todo'        => [],
            'in_progress' => [],
            'review'      => [],
            'done'        => []
        ];

        foreach ($tasks as $task) {
            $assigneeName = trim(($task['assignee_first_name'] ?? '') . ' ' . ($task['assignee_last_name'] ?? ''));
            $task['assignee_name'] = $assigneeName ?: ($task['assignee_username'] ?? 'Unassigned');

            $creatorName = trim(($task['creator_first_name'] ?? '') . ' ' . ($task['creator_last_name'] ?? ''));
            $task['creator_name'] = $creatorName ?: ($task['creator_username'] ?? 'Task Owner');

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
        $db = $this->db;
        $projectFields = $db->tableExists('projects') ? ($db->getFieldNames('projects') ?? []) : [];
        $taskFields = $db->getFieldNames('tasks') ?? [];

        $selects = ['tasks.*'];
        if (in_array('name', $projectFields, true)) {
            $selects[] = 'projects.name as project_name';
        }
        if (in_array('color', $projectFields, true)) {
            $selects[] = 'projects.color as project_color';
        }

        $query = (new static())->select(implode(', ', $selects));
        if ($db->tableExists('projects')) {
            $query->join('projects', 'projects.id = tasks.project_id', 'left');
        }

        if (in_array('assigned_to', $taskFields, true)) {
            $query->where('tasks.assigned_to', $userId);
        } else {
            $query->where('tasks.user_id', $userId);
        }

        if (in_array('priority', $taskFields, true)) {
            $query->orderBy('tasks.priority', 'DESC');
        }
        if (in_array('created_at', $taskFields, true)) {
            $query->orderBy('tasks.created_at', 'DESC');
        }

        return $query->findAll();
    }

    /**
     * Get tasks pending review (status = 'review')
     */
    public function getPendingReviews(?int $managerId = null)
    {
        $db = $this->db;
        $taskFields = $db->getFieldNames('tasks') ?? [];
        $userFields = $db->getFieldNames('users') ?? [];
        $projectFields = $db->tableExists('projects') ? ($db->getFieldNames('projects') ?? []) : [];

        $selects = ['tasks.*'];
        if (in_array('name', $projectFields, true)) {
            $selects[] = 'projects.name as project_name';
        }
        if (in_array('username', $userFields, true)) {
            $selects[] = 'users.username';
        }
        if (in_array('first_name', $userFields, true)) {
            $selects[] = 'users.first_name';
        }
        if (in_array('last_name', $userFields, true)) {
            $selects[] = 'users.last_name';
        }

        $query = (new static())->select(implode(', ', $selects));

        if ($db->tableExists('projects')) {
            $query->join('projects', 'projects.id = tasks.project_id', 'left');
        }

        if (in_array('assigned_to', $taskFields, true)) {
            $query->join('users', 'users.id = tasks.assigned_to', 'left');
        } elseif ($db->tableExists('users')) {
            $query->join('users', 'users.id = tasks.user_id', 'left');
        }

        $query->where('tasks.status', 'review');

        if (in_array('updated_at', $taskFields, true)) {
            $query->orderBy('tasks.updated_at', 'ASC');
        } elseif (in_array('created_at', $taskFields, true)) {
            $query->orderBy('tasks.created_at', 'ASC');
        }

        return $query->findAll();
    }

    /**
     * Get unassigned backlog tasks for a project
     */
    public function getBacklogTasks(int $projectId)
    {
        $db = $this->db;
        $taskFields = $db->getFieldNames('tasks') ?? [];
        $userFields = $db->getFieldNames('users') ?? [];

        $selects = ['tasks.*'];
        if (in_array('username', $userFields, true)) {
            $selects[] = 'users.username as assignee_name';
        }

        $query = (new static())->select(implode(', ', $selects));
        if (in_array('assigned_to', $taskFields, true)) {
            $query->join('users', 'users.id = tasks.assigned_to', 'left');
        } elseif ($db->tableExists('users')) {
            $query->join('users', 'users.id = tasks.user_id', 'left');
        }

        $query->where('tasks.project_id', $projectId);
        if (in_array('sprint_id', $taskFields, true)) {
            $query->where('tasks.sprint_id', null);
        }
        if (in_array('order_index', $taskFields, true)) {
            $query->orderBy('tasks.order_index', 'ASC');
        }
        if (in_array('created_at', $taskFields, true)) {
            $query->orderBy('tasks.created_at', 'DESC');
        }

        return $query->findAll();
    }

    /**
     * Get tasks assigned to a specific sprint
     */
    public function getSprintTasks(int $sprintId)
    {
        $db = $this->db;
        $taskFields = $db->getFieldNames('tasks') ?? [];
        $userFields = $db->getFieldNames('users') ?? [];

        $selects = ['tasks.*'];
        if (in_array('username', $userFields, true)) {
            $selects[] = 'users.username as assignee_name';
        }

        $query = (new static())->select(implode(', ', $selects));
        if (in_array('assigned_to', $taskFields, true)) {
            $query->join('users', 'users.id = tasks.assigned_to', 'left');
        } elseif ($db->tableExists('users')) {
            $query->join('users', 'users.id = tasks.user_id', 'left');
        }

        if (in_array('sprint_id', $taskFields, true)) {
            $query->where('tasks.sprint_id', $sprintId);
        }
        if (in_array('order_index', $taskFields, true)) {
            $query->orderBy('tasks.order_index', 'ASC');
        }
        if (in_array('created_at', $taskFields, true)) {
            $query->orderBy('tasks.created_at', 'DESC');
        }

        return $query->findAll();
    }
}
