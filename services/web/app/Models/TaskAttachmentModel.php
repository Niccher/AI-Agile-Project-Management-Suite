<?php

namespace App\Models;

use CodeIgniter\Model;

class TaskAttachmentModel extends Model
{
    protected $table = 'task_attachments';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $allowedFields = [
        'task_id',
        'user_id',
        'file_name',
        'original_name',
        'mime_type',
        'file_size',
        'file_path',
        'created_at',
    ];

    protected $useTimestamps = false;

    public function getAttachmentsForTask(int $taskId): array
    {
        return $this->select('task_attachments.*, users.username, users.first_name, users.last_name')
            ->join('users', 'users.id = task_attachments.user_id', 'left')
            ->where('task_attachments.task_id', $taskId)
            ->orderBy('task_attachments.created_at', 'DESC')
            ->findAll();
    }
}
