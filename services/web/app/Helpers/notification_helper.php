<?php

use App\Services\NotificationService;

if (! function_exists('notify_user')) {
    /**
     * Helper to dispatch a notification and optional email to a user
     */
    function notify_user(int $userId, string $type, string $title, string $body, ?string $actionUrl = null, array $extra = [])
    {
        return NotificationService::send($userId, $type, $title, $body, $actionUrl, $extra);
    }
}
