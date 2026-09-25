<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table            = 'notifications';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id', 'type', 'title', 'body', 'action_url', 'is_read', 'created_at', 'updated_at'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    
    public function getUnreadForUser(int $userId)
    {
        return $this->where('user_id', $userId)
                    ->where('is_read', 0)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    public function getUnreadCountForUser(int $userId): int
    {
        return $this->where('user_id', $userId)
                    ->where('is_read', 0)
                    ->countAllResults();
    }

    public function getRecentForUser(int $userId, int $limit = 8): array
    {
        return $this->where('user_id', $userId)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    public function markAsRead(int $notificationId, int $userId): bool
    {
        return (bool) $this->where('id', $notificationId)
                           ->where('user_id', $userId)
                           ->set(['is_read' => 1])
                           ->update();
    }

    public function markAllAsRead(int $userId): bool
    {
        return (bool) $this->where('user_id', $userId)
                           ->where('is_read', 0)
                           ->set(['is_read' => 1])
                           ->update();
    }

    public static function createNotification(int $userId, string $type, string $title, string $body, ?string $actionUrl = null): int|false
    {
        $model = new self();
        return $model->insert([
            'user_id'    => $userId,
            'type'       => $type,
            'title'      => $title,
            'body'       => $body,
            'action_url' => $actionUrl,
            'is_read'    => 0,
        ]);
    }
}
