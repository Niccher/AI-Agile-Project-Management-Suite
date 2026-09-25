<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\NotificationModel;
use CodeIgniter\HTTP\ResponseInterface;

class NotificationApiController extends BaseController
{
    protected NotificationModel $notificationModel;
    protected int $userId = 0;

    public function __construct()
    {
        $this->notificationModel = new NotificationModel();
        if (auth()->loggedIn()) {
            $this->userId = (int) auth()->id();
        }
    }

    /**
     * Get unread notifications count and recent items.
     *
     * If a human visits this URL directly in a browser (Accept: text/html),
     * redirect them to the dashboard. This prevents users from seeing raw JSON
     * if they accidentally open a bookmarked or copy-pasted API URL.
     */
    public function unreadCount(): ResponseInterface
    {
        // Detect browser (non-AJAX) request by Accept header
        $acceptHeader = $this->request->getHeaderLine('Accept');
        $isAjax = $this->request->isAJAX()
            || str_contains($acceptHeader, 'application/json')
            || str_contains($acceptHeader, 'application/vnd');

        if (! $isAjax) {
            // Human opened this URL directly — redirect to appropriate page
            if ($this->userId === 0) {
                return redirect()->to(site_url('auth/login'));
            }
            return redirect()->to(site_url('home'));
        }

        if ($this->userId === 0) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized'])->setStatusCode(401);
        }

        $count = $this->notificationModel->getUnreadCountForUser($this->userId);
        $recent = $this->notificationModel->getRecentForUser($this->userId, 8);

        // Format timestamps nicely
        $formatted = array_map(function ($item) {
            $createdTime = strtotime($item['created_at']);
            $diff = time() - $createdTime;
            
            if ($diff < 60) {
                $timeAgo = 'Just now';
            } elseif ($diff < 3600) {
                $timeAgo = floor($diff / 60) . 'm ago';
            } elseif ($diff < 86400) {
                $timeAgo = floor($diff / 3600) . 'h ago';
            } else {
                $timeAgo = date('M j, Y', $createdTime);
            }

            // Icon by type
            $icon = 'mdi-bell-outline';
            $bgClass = 'bg-primary-lighten text-primary';
            switch ($item['type']) {
                case 'task_assigned':
                    $icon = 'mdi-clipboard-account-outline';
                    $bgClass = 'bg-info-lighten text-info';
                    break;
                case 'task_moved':
                case 'task_status':
                    $icon = 'mdi-swap-horizontal';
                    $bgClass = 'bg-warning-lighten text-warning';
                    break;
                case 'work_approved':
                    $icon = 'mdi-check-decagram-outline';
                    $bgClass = 'bg-success-lighten text-success';
                    break;
                case 'work_rejected':
                    $icon = 'mdi-alert-circle-outline';
                    $bgClass = 'bg-danger-lighten text-danger';
                    break;
                case 'sprint':
                    $icon = 'mdi-run-fast';
                    $bgClass = 'bg-primary-lighten text-primary';
                    break;
                case 'project':
                    $icon = 'mdi-folder-star-outline';
                    $bgClass = 'bg-secondary-lighten text-secondary';
                    break;
            }

            return [
                'id'         => (int) $item['id'],
                'type'       => $item['type'],
                'title'      => esc($item['title']),
                'body'       => esc($item['body']),
                'action_url' => $item['action_url'] ? base_url($item['action_url']) : null,
                'is_read'    => (int) $item['is_read'],
                'time_ago'   => $timeAgo,
                'icon'       => $icon,
                'bg_class'   => $bgClass,
            ];
        }, $recent);

        return $this->response->setJSON([
            'status'        => 'success',
            'unread_count'  => $count,
            'notifications' => $formatted,
        ]);
    }

    /**
     * Mark single notification as read
     */
    public function markRead(int $id): ResponseInterface
    {
        if ($this->userId === 0) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized'])->setStatusCode(401);
        }

        $this->notificationModel->markAsRead($id, $this->userId);
        $count = $this->notificationModel->getUnreadCountForUser($this->userId);

        return $this->response->setJSON([
            'status'       => 'success',
            'message'      => 'Marked as read',
            'unread_count' => $count,
        ]);
    }

    /**
     * Mark all notifications as read for current user
     */
    public function markAllRead(): ResponseInterface
    {
        if ($this->userId === 0) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized'])->setStatusCode(401);
        }

        $this->notificationModel->markAllAsRead($this->userId);

        return $this->response->setJSON([
            'status'       => 'success',
            'message'      => 'All marked as read',
            'unread_count' => 0,
        ]);
    }
}
