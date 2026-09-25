<?php

namespace App\Controllers;

use App\Models\PortalTokenModel;
use App\Models\ProjectModel;
use App\Models\SprintModel;
use App\Models\TaskModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class PortalController extends BaseController
{
    protected PortalTokenModel $portalTokenModel;
    protected ProjectModel $projectModel;

    public function __construct()
    {
        $this->portalTokenModel = new PortalTokenModel();
        $this->projectModel = new ProjectModel();
    }

    /**
     * Public Client Portal View (Token Gated)
     * GET /portal/(:segment)
     */
    public function view(string $token)
    {
        $portalData = $this->portalTokenModel->findByToken($token);

        if (!$portalData) {
            throw PageNotFoundException::forPageNotFound('Client portal link is invalid, expired, or deactivated.');
        }

        $projectId = (int)$portalData['project_id'];

        // Sanitized task counts by status (No sensitive internal descriptions/assignee names)
        $taskModel = new TaskModel();
        $tasks = $taskModel->where('project_id', $projectId)->findAll();
        
        $taskCounts = [
            'total'       => count($tasks),
            'todo'        => 0,
            'in_progress' => 0,
            'review'      => 0,
            'done'        => 0,
        ];

        foreach ($tasks as $t) {
            $st = $t['status'] ?? 'todo';
            if ($st === 'approved') $st = 'done';
            if (isset($taskCounts[$st])) {
                $taskCounts[$st]++;
            }
        }

        // Active Sprint (if any)
        $sprintModel = new SprintModel();
        $activeSprint = $sprintModel->getActiveSprint($projectId);

        // Milestones
        $db = \Config\Database::connect();
        $milestones = $db->table('project_milestones')
                         ->where('project_id', $projectId)
                         ->orderBy('due_date', 'ASC')
                         ->get()->getResultArray();

        return view('portal/view', [
            'portal'       => $portalData,
            'taskCounts'   => $taskCounts,
            'activeSprint' => $activeSprint,
            'milestones'   => $milestones,
            'techStack'    => json_decode($portalData['tech_stack'] ?? '[]', true) ?: [],
            'categories'   => json_decode($portalData['categories'] ?? '[]', true) ?: [],
            'siteName'     => setting('App.siteName') ?? 'Chege Jira',
        ]);
    }

    /**
     * Generate a new shareable client portal token
     * POST /projects/portal/generate
     */
    public function generate()
    {
        if (!auth()->loggedIn()) {
            return redirect()->to('/auth/login');
        }

        $userId = (int)auth()->id();
        $isAdmin = auth()->user() && auth()->user()->inGroup('admin', 'manager');
        $projectId = (int)$this->request->getPost('project_id');

        $project = $this->projectModel->find($projectId);
        if (!$project) {
            return redirect()->back()->with('error', 'Project not found.');
        }

        if (!$isAdmin && (int)$project['user_id'] !== $userId) {
            return redirect()->back()->with('error', 'Unauthorized to generate portal link.');
        }

        $label = $this->request->getPost('label') ?: 'Client Link (' . date('M j, Y') . ')';
        $expiresDays = (int)($this->request->getPost('expires_days') ?? 0);
        $expiresAt = null;
        if ($expiresDays > 0) {
            $expiresAt = date('Y-m-d H:i:s', strtotime("+{$expiresDays} days"));
        }

        $token = $this->portalTokenModel->generateToken($projectId, $userId, $label, $expiresAt);
        $portalUrl = site_url('portal/' . $token);

        return redirect()->back()->with('message', 'Client Portal link created: ' . $portalUrl);
    }

    /**
     * Revoke / deactivate a portal token
     * POST /projects/portal/revoke/(:num)
     */
    public function revoke(int $tokenId)
    {
        if (!auth()->loggedIn()) {
            return redirect()->to('/auth/login');
        }

        $this->portalTokenModel->revokeToken($tokenId);
        return redirect()->back()->with('message', 'Portal link revoked successfully.');
    }
}
