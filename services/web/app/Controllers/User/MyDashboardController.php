<?php

namespace App\Controllers\User;

use App\Models\ProjectModel;

class MyDashboardController extends BaseUserController
{
    public function index()
    {
        $projectModel = new ProjectModel();

        $data = [
            'user'           => $this->currentUser,
            'stats'          => $projectModel->getStats($this->userId),
            'weeklyFocus'    => $projectModel->getWeeklyFocus($this->userId),
            'recentActivity' => $projectModel->getRecentActivity($this->userId),
            'projects'       => $projectModel->where('user_id', $this->userId)
                                            ->where('is_archived', 0)
                                            ->orderBy('updated_at', 'DESC')
                                            ->limit(5)
                                            ->findAll(),
        ];

        return view('user/home', $data);
    }
}
