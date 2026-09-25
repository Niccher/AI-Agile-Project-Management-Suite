<?php

namespace App\Controllers\User;

use App\Models\ProjectModel;
use App\Models\TaskModel;

class KanbanController extends BaseUserController
{
    public function index($identifier = null)
    {
        $projectModel = new ProjectModel();
        $taskModel = new TaskModel();
        $isAdmin = auth()->user() && auth()->user()->inGroup('admin', 'manager');

        if ($identifier === null) {
            $latestProjectQuery = $isAdmin 
                ? $projectModel->orderBy('updated_at', 'DESC')
                : $projectModel->where('user_id', $this->userId)->orderBy('updated_at', 'DESC');
                
            $latestProject = $latestProjectQuery->first();
            if ($latestProject) {
                $target = $latestProject['slug'] ?? $latestProject['id'];
                return redirect()->to('projects/kanban/' . $target);
            } else {
                return redirect()->to('projects')->with('error', 'Please create a project first to use the Kanban board.');
            }
        }

        $project = $projectModel->findByIdentifier($identifier, $this->userId, $isAdmin);

        if (!$project) {
            return redirect()->to('projects')->with('error', 'Project not found or unauthorized.');
        }

        $projectId = (int)$project['id'];
        $cats = $projectModel->getCategoriesByProjectId($projectId);
        $tech = $projectModel->getTechStackByProjectId($projectId);

        $projectsList = $isAdmin
            ? $projectModel->orderBy('updated_at', 'DESC')->findAll()
            : $projectModel->where('user_id', $this->userId)->orderBy('updated_at', 'DESC')->findAll();

        $data = [
            'user'       => $this->currentUser,
            'project'    => $project,
            'projects'   => $projectsList,
            'categories' => $cats,
            'tech_stack' => $tech,
            'boardData'  => $taskModel->getBoardData($projectId, $this->userId)
        ];

        return view('user/projects/kanban', $data);
    }
}
