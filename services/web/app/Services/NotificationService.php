<?php

namespace App\Services;

use App\Models\NotificationModel;
use App\Models\UserModel;

class NotificationService
{
    public static function send(int $userId, string $type, string $title, string $body, ?string $actionUrl = null, array $extra = [])
    {
        $notificationModel = new NotificationModel();
        
        $data = [
            'user_id'    => $userId,
            'type'       => $type,
            'title'      => $title,
            'body'       => $body,
            'action_url' => $actionUrl,
            'is_read'    => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $inserted = $notificationModel->insert($data);

        // Attempt outbound email notification if user email is present
        try {
            $userModel = new UserModel();
            $user = $userModel->find($userId);
            if ($user && !empty($user->email)) {
                self::dispatchEmailNotification($user, $type, $title, $body, $actionUrl, $extra);
            }
        } catch (\Throwable $e) {
            log_message('notice', 'Email dispatch notice: ' . $e->getMessage());
        }

        return $inserted;
    }

    protected static function dispatchEmailNotification($user, string $type, string $title, string $body, ?string $actionUrl, array $extra = [])
    {
        $email = \Config\Services::email();
        $email->initialize([
            'protocol'   => setting('Email.protocol') ?? 'smtp',
            'SMTPHost'   => setting('Email.SMTPHost') ?? 'localhost',
            'SMTPPort'   => (int)(setting('Email.SMTPPort') ?? 587),
            'SMTPUser'   => setting('Email.SMTPUser') ?? '',
            'SMTPPass'   => setting('Email.SMTPPass') ?? '',
            'SMTPCrypto' => setting('Email.SMTPCrypto') ?? 'tls',
            'mailType'   => 'html',
            'charset'    => 'utf-8',
            'newline'    => "\r\n",
            'CRLF'       => "\r\n",
        ]);

        $email->setFrom(setting('Email.fromEmail') ?? 'notifications@chege.local', setting('Email.fromName') ?? setting('App.siteName'));
        $email->setTo($user->email);
        $email->setSubject($title . ' • ' . setting('App.siteName'));

        $template = 'emails/welcome';
        $viewData = array_merge([
            'user'        => $user,
            'taskTitle'   => $extra['task_title'] ?? $title,
            'projectName' => $extra['project_name'] ?? 'Workspace',
            'actionUrl'   => $actionUrl,
            'reason'      => $extra['reason'] ?? $body,
            'newStatus'   => $extra['new_status'] ?? 'In Progress',
            'approvedBy'  => $extra['approved_by'] ?? 'Manager',
            'movedBy'     => $extra['moved_by'] ?? 'Team Member',
        ], $extra);

        if ($type === 'task_approved') {
            $template = 'emails/work_approved';
        } elseif ($type === 'task_rejected') {
            $template = 'emails/work_rejected';
        } elseif ($type === 'task_moved' || $type === 'task_assigned') {
            $template = 'emails/task_moved';
        } elseif ($type === 'project_created') {
            $template = 'emails/project_created';
        }

        $email->setMessage(view($template, $viewData));
        $email->send(false);
    }

    public static function markAsRead(int $notificationId, int $userId)
    {
        $notificationModel = new NotificationModel();
        
        $notification = $notificationModel->where('id', $notificationId)
                                          ->where('user_id', $userId)
                                          ->first();
                                          
        if ($notification) {
            return $notificationModel->update($notificationId, ['is_read' => 1]);
        }
        
        return false;
    }
}
