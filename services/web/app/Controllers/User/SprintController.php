<?php

namespace App\Controllers\User;

use App\Models\ProjectModel;
use App\Models\SprintModel;
use App\Models\SprintSnapshotModel;
use App\Models\TaskModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class SprintController extends BaseUserController
{
    protected ProjectModel $projectModel;
    protected SprintModel $sprintModel;
    protected TaskModel $taskModel;
    protected SprintSnapshotModel $snapshotModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->projectModel = new ProjectModel();
        $this->sprintModel = new SprintModel();
        $this->taskModel = new TaskModel();
        $this->snapshotModel = new SprintSnapshotModel();
    }

    /**
     * Sprints & Backlog Planning View
     * GET /projects/sprints/(:segment)
     */
    public function index($projectIdentifier)
    {
        $isAdmin = auth()->user() && auth()->user()->inGroup('admin', 'manager');
        $project = $this->projectModel->findByIdentifier($projectIdentifier, $this->userId, $isAdmin);
        if (!$project) {
            throw PageNotFoundException::forPageNotFound('Project not found');
        }

        $projectId = (int)$project['id'];

        // Active sprint
        $activeSprint = $this->sprintModel->getActiveSprint($projectId);
        $activeTasks = $activeSprint ? $this->taskModel->getSprintTasks($activeSprint['id']) : [];

        // Planning / Upcoming sprints
        $plannedSprints = $this->sprintModel->where('project_id', $projectId)
                                            ->where('status', 'planning')
                                            ->orderBy('created_at', 'ASC')
                                            ->findAll();

        // Attach tasks to planned sprints
        foreach ($plannedSprints as &$ps) {
            $ps['tasks'] = $this->taskModel->getSprintTasks($ps['id']);
            $points = $this->sprintModel->recalculatePoints($ps['id']);
            $ps['total_points'] = $points['total_points'];
        }

        // Past completed sprints
        $completedSprints = $this->sprintModel->where('project_id', $projectId)
                                              ->where('status', 'completed')
                                              ->orderBy('end_date', 'DESC')
                                              ->limit(5)
                                              ->findAll();

        // Backlog tasks (no sprint assigned)
        $backlogTasks = $this->taskModel->getBacklogTasks($projectId);
        $backlogPoints = array_sum(array_column($backlogTasks, 'story_points'));

        // Recalculate points for active sprint
        if ($activeSprint) {
            $pts = $this->sprintModel->recalculatePoints($activeSprint['id']);
            $activeSprint['total_points'] = $pts['total_points'];
            $activeSprint['completed_points'] = $pts['completed_points'];
            $activeSprint['remaining_points'] = $pts['remaining_points'];
        }

        return view('user/projects/sprints', [
            'project'          => $project,
            'activeSprint'     => $activeSprint,
            'activeTasks'      => $activeTasks,
            'plannedSprints'   => $plannedSprints,
            'completedSprints' => $completedSprints,
            'backlogTasks'     => $backlogTasks,
            'backlogPoints'    => $backlogPoints,
        ]);
    }

    /**
     * Store new planned sprint
     * POST /projects/sprints/store/(:segment)
     */
    public function store($projectIdentifier)
    {
        $isAdmin = auth()->user() && auth()->user()->inGroup('admin', 'manager');
        $project = $this->projectModel->findByIdentifier($projectIdentifier, $this->userId, $isAdmin);
        if (!$project) {
            return redirect()->back()->with('error', 'Project not found.');
        }

        $projectId = (int)$project['id'];
        $name = trim($this->request->getPost('name') ?? '');
        $goal = trim($this->request->getPost('goal') ?? '');
        $durationWeeks = (int)($this->request->getPost('duration_weeks') ?? 2);
        
        if (empty($name)) {
            $existingCount = $this->sprintModel->where('project_id', $projectId)->countAllResults();
            $name = 'Sprint ' . ($existingCount + 1);
        }

        $startDate = date('Y-m-d H:i:s');
        $endDate = date('Y-m-d H:i:s', strtotime("+{$durationWeeks} weeks"));

        $this->sprintModel->insert([
            'project_id'       => $projectId,
            'name'             => $name,
            'goal'             => $goal,
            'status'           => 'planning',
            'start_date'       => $startDate,
            'end_date'         => $endDate,
            'total_points'     => 0,
            'completed_points' => 0,
        ]);

        return redirect()->back()->with('success', 'New Sprint created in planning mode.');
    }

    /**
     * Start an active sprint
     * POST /projects/sprints/start/(:num)
     */
    public function start(int $sprintId)
    {
        $sprint = $this->sprintModel->find($sprintId);
        if (!$sprint) {
            return redirect()->back()->with('error', 'Sprint not found.');
        }

        // Check if there is already an active sprint for this project
        $existingActive = $this->sprintModel->getActiveSprint($sprint['project_id']);
        if ($existingActive && $existingActive['id'] != $sprintId) {
            return redirect()->back()->with('error', 'There is already an active sprint (' . esc($existingActive['name']) . '). Complete it before starting a new one.');
        }

        $durationWeeks = (int)($this->request->getPost('duration_weeks') ?? 2);
        $goal = $this->request->getPost('goal') ?: $sprint['goal'];

        $startDate = date('Y-m-d H:i:s');
        $endDate = date('Y-m-d H:i:s', strtotime("+{$durationWeeks} weeks"));

        $pts = $this->sprintModel->recalculatePoints($sprintId);

        $this->sprintModel->update($sprintId, [
            'status'           => 'active',
            'goal'             => $goal,
            'start_date'       => $startDate,
            'end_date'         => $endDate,
            'total_points'     => $pts['total_points'],
            'completed_points' => $pts['completed_points'],
        ]);

        // Take Day 1 Snapshot
        $this->snapshotModel->insert([
            'sprint_id'        => $sprintId,
            'snapshot_date'    => date('Y-m-d'),
            'remaining_points' => $pts['remaining_points'],
            'completed_points' => $pts['completed_points'],
        ]);

        \App\Services\NotificationService::send(
            $this->userId,
            'sprint',
            'Sprint Started: ' . $sprint['name'],
            'Sprint "' . esc($sprint['name']) . '" is now active with ' . $pts['total_points'] . ' story points.',
            'projects/sprints/' . ($project['slug'] ?? $sprint['project_id'])
        );

        return redirect()->back()->with('success', 'Sprint "' . esc($sprint['name']) . '" has officially started!');
    }

    /**
     * Complete an active sprint
     * POST /projects/sprints/complete/(:num)
     */
    public function complete(int $sprintId)
    {
        $sprint = $this->sprintModel->find($sprintId);
        if (!$sprint) {
            return redirect()->back()->with('error', 'Sprint not found.');
        }

        $pts = $this->sprintModel->recalculatePoints($sprintId);

        // Mark sprint completed
        $this->sprintModel->update($sprintId, [
            'status'           => 'completed',
            'end_date'         => date('Y-m-d H:i:s'),
            'total_points'     => $pts['total_points'],
            'completed_points' => $pts['completed_points'],
        ]);

        \App\Services\NotificationService::send(
            $this->userId,
            'sprint',
            'Sprint Completed: ' . $sprint['name'],
            'Sprint "' . esc($sprint['name']) . '" completed (' . $pts['completed_points'] . '/' . $pts['total_points'] . ' points achieved).',
            'projects/sprints/' . $sprint['project_id']
        );

        // Move uncompleted tasks back to the backlog
        $db = \Config\Database::connect();
        $db->table('tasks')
           ->where('sprint_id', $sprintId)
           ->whereNotIn('status', ['done', 'approved'])
           ->update(['sprint_id' => null]);

        return redirect()->back()->with('success', 'Sprint "' . esc($sprint['name']) . '" closed! Completed points: ' . $pts['completed_points'] . '/' . $pts['total_points'] . '. Remaining tasks moved to Backlog.');
    }

    /**
     * Get Burndown Chart Data (JSON)
     * GET /projects/sprints/burndown/(:num)
     */
    public function burndown(int $sprintId)
    {
        $sprint = $this->sprintModel->find($sprintId);
        if (!$sprint) {
            return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => 'Sprint not found']);
        }

        $startDate = !empty($sprint['start_date']) ? strtotime($sprint['start_date']) : strtotime($sprint['created_at']);
        $endDate = !empty($sprint['end_date']) ? strtotime($sprint['end_date']) : strtotime('+2 weeks', $startDate);
        $totalPoints = (int)($sprint['total_points'] ?? 0);

        $daysCount = max(1, (int)ceil(($endDate - $startDate) / 86400));
        
        $categories = [];
        $idealData = [];
        $actualData = [];

        // Ideal line slope
        $pointDropPerDay = $daysCount > 0 ? $totalPoints / $daysCount : 0;

        // Fetch snapshots
        $snapshots = $this->snapshotModel->where('sprint_id', $sprintId)
                                         ->orderBy('snapshot_date', 'ASC')
                                         ->findAll();
        $snapshotMap = [];
        foreach ($snapshots as $snap) {
            $snapshotMap[$snap['snapshot_date']] = (int)$snap['remaining_points'];
        }

        $currentTimestamp = time();
        $currentRemaining = max(0, $totalPoints - (int)($sprint['completed_points'] ?? 0));

        for ($i = 0; $i <= $daysCount; $i++) {
            $dayTime = $startDate + ($i * 86400);
            $dateStr = date('Y-m-d', $dayTime);
            $label = date('M j', $dayTime);
            $categories[] = $label;

            // Ideal value
            $idealRemaining = max(0, round($totalPoints - ($i * $pointDropPerDay), 1));
            $idealData[] = $idealRemaining;

            // Actual value
            if ($dayTime <= $currentTimestamp + 86400) {
                if (isset($snapshotMap[$dateStr])) {
                    $actualData[] = $snapshotMap[$dateStr];
                } else if ($i == 0) {
                    $actualData[] = $totalPoints;
                } else if ($dayTime <= $currentTimestamp) {
                    $actualData[] = $currentRemaining;
                } else {
                    $actualData[] = null;
                }
            } else {
                $actualData[] = null;
            }
        }

        return $this->response->setJSON([
            'status'     => 'success',
            'sprint'     => [
                'name'             => $sprint['name'],
                'goal'             => $sprint['goal'],
                'total_points'     => $totalPoints,
                'completed_points' => (int)($sprint['completed_points'] ?? 0),
                'remaining_points' => max(0, $totalPoints - (int)($sprint['completed_points'] ?? 0)),
            ],
            'categories' => $categories,
            'ideal'      => $idealData,
            'actual'     => $actualData,
        ]);
    }
}
