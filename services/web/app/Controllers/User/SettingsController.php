<?php

namespace App\Controllers\User;

use App\Models\UserModel;

class SettingsController extends BaseUserController
{
    public function index()
    {
        $this->currentUser->preferences = $this->currentUser->preferences ?? [];
        return view('user/settings', ['user' => $this->currentUser]);
    }

    public function update()
    {
        $model = new UserModel();
        $data = [];

        if ($this->request->getPost('first_name') !== null) {
            $data['first_name'] = $this->request->getPost('first_name');
        }
        if ($this->request->getPost('last_name') !== null) {
            $data['last_name'] = $this->request->getPost('last_name');
        }
        if ($this->request->getPost('bio') !== null) {
            $data['bio'] = $this->request->getPost('bio');
        }
        if ($this->request->getPost('timezone') !== null) {
            $data['timezone'] = $this->request->getPost('timezone');
        }
        if ($this->request->getPost('date_format') !== null) {
            $data['date_format'] = $this->request->getPost('date_format');
        }

        // Handle avatar upload securely with strict MIME, magic byte, and size enforcement
        $avatar = $this->request->getFile('avatar');
        if ($avatar && $avatar->isValid() && !$avatar->hasMoved()) {
            $maxSizeBytes = 2 * 1024 * 1024; // 2MB max
            if ($avatar->getSize() > $maxSizeBytes) {
                return redirect()->back()->withInput()->with('error', 'Avatar file exceeds the maximum allowed size of 2MB.');
            }

            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
            $extension = strtolower((string)$avatar->getExtension());
            if (!in_array($extension, $allowedExtensions, true)) {
                return redirect()->back()->withInput()->with('error', 'Only JPG, PNG, and WebP avatar images are allowed.');
            }

            // Inspect magic bytes directly from temporary upload file
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $detectedMime = $finfo->file($avatar->getTempName());
            $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];

            if (!in_array($detectedMime, $allowedMimes, true)) {
                return redirect()->back()->withInput()->with('error', 'Invalid avatar image content. SVGs, scripts, and non-image files are prohibited.');
            }

            $newName = $avatar->getRandomName();
            $uploadDir = WRITEPATH . 'uploads/avatars';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $avatar->move($uploadDir, $newName);
            $data['avatar'] = $newName; // Just store randomized filename

            // Mirror to MinIO S3 Object Storage with verified MIME
            $storage = new \App\Services\StorageService();
            if ($storage->isEnabled()) {
                $bucket = (string) env('S3_BUCKET_AVATARS', 'agile-avatars');
                $storage->ensureBucket($bucket);
                $storage->uploadFile($bucket, "avatars/{$newName}", $uploadDir . '/' . $newName, $detectedMime);
            }
        }

        // Gather preferences
        $preferences = is_array($this->currentUser->preferences) ? $this->currentUser->preferences : [];
        $prefFields = ['theme', 'accent_color', 'density', 'animations', 'sidebar_collapsed',
                       'notifications_email_project_updates', 'notifications_email_weekly_reports',
                       'notifications_email_stalled', 'notifications_inapp_due_dates',
                       'notifications_inapp_achievements', 'notifications_inapp_tips',
                       'notification_digest', 'reminder_time',
                       'default_priority', 'default_status', 'auto_archive_completed',
                       'show_stalled_alerts', 'kanban_default_columns', 'kanban_card_density',
                       'idle_timeout', 'rounding_interval', 'auto_break', 'timer_sound',
                       'weekly_goal_hours', 'daily_goal_hours', 'auto_weekly_report', 'show_billable'];

        foreach ($prefFields as $field) {
            $val = $this->request->getPost($field);
            if ($val !== null) {
                $preferences[$field] = $val;
            }
        }
        $data['preferences'] = json_encode($preferences);

        if ($model->update($this->userId, $data)) {
            return redirect()->to('/settings')->with('message', 'Settings saved successfully!');
        }

        return redirect()->back()->withInput()->with('errors', $model->errors());
    }

    public function changePassword()
    {
        $current = $this->request->getPost('current_password');
        $newPass = $this->request->getPost('new_password');
        $confirm = $this->request->getPost('confirm_password');

        if (empty($current) || empty($newPass) || empty($confirm)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'All password fields are required.'])->setStatusCode(400);
        }

        if (strlen($newPass) < 8) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Password must be at least 8 characters long.'])->setStatusCode(400);
        }

        if ($newPass !== $confirm) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'New passwords do not match.'])->setStatusCode(400);
        }

        $users = auth()->getProvider();
        $user = $users->findById($this->userId);

        if (!$user) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'User not found.'])->setStatusCode(404);
        }

        $credentials = [
            'email'    => $user->email,
            'password' => $current,
        ];

        $valid = auth('session')->check($credentials);
        if (!$valid->isOK()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Current password is incorrect.'])->setStatusCode(400);
        }

        $user->fill(['password' => $newPass]);
        $users->save($user);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Password changed successfully!']);
    }
}
