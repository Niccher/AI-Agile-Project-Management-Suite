<?php

namespace App\Models;

use CodeIgniter\Model;

class TaskCommentModel extends Model
{
    protected $table = 'task_comments';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $allowedFields = [
        'task_id',
        'user_id',
        'body',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getCommentsForTask(int $taskId): array
    {
        return $this->select('task_comments.*, users.username, users.first_name, users.last_name')
            ->join('users', 'users.id = task_comments.user_id', 'left')
            ->where('task_comments.task_id', $taskId)
            ->orderBy('task_comments.created_at', 'ASC')
            ->findAll();
    }
}
