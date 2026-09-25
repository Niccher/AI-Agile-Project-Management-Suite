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

        // Handle avatar upload securely
        $avatar = $this->request->getFile('avatar');
        if ($avatar && $avatar->isValid() && !$avatar->hasMoved()) {
            $newName = $avatar->getRandomName();
            $avatar->move(WRITEPATH . 'uploads/avatars', $newName);
            $data['avatar'] = $newName; // Just store filename, controller handles path
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
