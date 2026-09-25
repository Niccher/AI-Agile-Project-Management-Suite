<?php

namespace App\Models;

use CodeIgniter\Model;

class ProjectModel extends Model
{
    protected $table            = 'projects';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id', 'name', 'slug', 'short_code', 'description', 'tech_stack', 'status', 
        'priority', 'start_date', 'due_date', 'progress', 
        'repository_url', 'categories', 'icon', 'color', 
        'budget', 'is_archived', 'deleted_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Callbacks
    protected $beforeInsert = ['generateSlugHook'];
    protected $beforeUpdate = ['updateSlugHook'];

    // Validation
    protected $validationRules = [
        'name'        => 'required|min_length[3]|max_length[255]',
        'description' => 'required',
        'status'      => 'required|in_list[planning,in_progress,testing,completed,on_hold,abandoned]',
        'priority'    => 'required|in_list[low,medium,high,critical]',
    ];

    /**
     * Generate hybrid SEO slug and short hex code (e.g. mobile-app-redesign-8f9c1b)
     */
    public static function generateHybridSlug(string $name, ?string $existingCode = null): array
    {
        helper('url');
        $baseSlug = url_title(strtolower(trim($name)), '-', true);
        if (empty($baseSlug)) {
            $baseSlug = 'project';
        }

        if ($existingCode && preg_match('/^[a-f0-9]{6,16}$/i', $existingCode)) {
            $code = strtolower($existingCode);
        } else {
            try {
                $code = bin2hex(random_bytes(3)); // 6 hex characters
            } catch (\Exception $e) {
                $code = substr(md5(uniqid((string)mt_rand(), true)), 0, 6);
            }
        }

        return [
            'slug'       => $baseSlug . '-' . $code,
            'short_code' => $code,
        ];
    }

    /**
     * Hook before inserting a new project
     */
    protected function generateSlugHook(array $data)
    {
        if (!empty($data['data']['name'])) {
            if (empty($data['data']['slug']) || empty($data['data']['short_code'])) {
                $slugInfo = self::generateHybridSlug($data['data']['name']);
                $data['data']['slug']       = $slugInfo['slug'];
                $data['data']['short_code'] = $slugInfo['short_code'];
            }
        }
        return $data;
    }

    /**
     * Hook before updating an existing project (regenerate slug with same short_code if name changed)
     */
    protected function updateSlugHook(array $data)
    {
        if (!empty($data['data']['name'])) {
            // Check if existing short code is present or retrieve from DB
            $existingShortCode = $data['data']['short_code'] ?? null;
            if (!$existingShortCode && !empty($data['id'])) {
                $id = is_array($data['id']) ? reset($data['id']) : $data['id'];
                $existing = $this->asArray()->find($id);
                if ($existing) {
                    $existingShortCode = $existing['short_code'] ?? null;
                }
            }

            $slugInfo = self::generateHybridSlug($data['data']['name'], $existingShortCode);
            $data['data']['slug'] = $slugInfo['slug'];
            if (empty($data['data']['short_code'])) {
                $data['data']['short_code'] = $slugInfo['short_code'];
            }
        }
        return $data;
    }

    /**
     * Flexible project resolver supporting:
     * 1. Full hybrid slug (e.g. mobile-app-redesign-8f9c1b)
     * 2. Short code (e.g. 8f9c1b or old-title-8f9c1b)
     * 3. Legacy integer ID (e.g. 10)
     */
    public function findByIdentifier($identifier, ?int $userId = null, bool $isAdmin = false): ?array
    {
        if (empty($identifier)) {
            return null;
        }

        $query = $this;

        // 1. Try exact slug match
        $project = (clone $query)->where('slug', (string)$identifier)->first();

        // 2. Extract trailing short code or direct short code match
        if (!$project) {
            $shortCode = null;
            if (preg_match('/-([a-f0-9]{6,16})$/i', (string)$identifier, $matches)) {
                $shortCode = strtolower($matches[1]);
            } elseif (preg_match('/^[a-f0-9]{6,16}$/i', (string)$identifier)) {
                $shortCode = strtolower($identifier);
            }

            if ($shortCode) {
                $project = (clone $query)->where('short_code', $shortCode)->first();
            }
        }

        // 3. Fallback to numeric ID for legacy backward compatibility
        if (!$project && is_numeric($identifier)) {
            $project = (clone $query)->where('id', (int)$identifier)->first();
        }

        if (!$project) {
            return null;
        }

        // Authorization check if userId is provided
        if ($userId !== null && !$isAdmin) {
            if ((int)$project['user_id'] !== (int)$userId) {
                // Check if user is assigned any tasks in this project
                $db = \Config\Database::connect();
                $isAssigned = $db->table('tasks')
                    ->where('project_id', $project['id'])
                    ->where('assignee_id', $userId)
                    ->countAllResults() > 0;

                if (!$isAssigned) {
                    return null; // Not authorized
                }
            }
        }

        return $project;
    }

    /**
     * Get milestones for a project
     */
    public function getMilestones(int $projectId)
    {
        $db = \Config\Database::connect();
        return $db->table('project_milestones')
                  ->where('project_id', $projectId)
                  ->get()
                  ->getResultArray();
    }

    /**
     * Get statistics for projects
     */
    public function getStats(int $userId)
    {
        return [
            'total'    => $this->where('user_id', $userId)->countAllResults(),
            'active'   => $this->where('user_id', $userId)->where('status', 'in_progress')->countAllResults(),
            'pending'   => $this->where('user_id', $userId)->whereIn('status', ['planning', 'on_hold'])->countAllResults(),
            'completed' => $this->where('user_id', $userId)->where('status', 'completed')->countAllResults(),
            'archived'  => $this->where('user_id', $userId)->where('is_archived', 1)->countAllResults(),
        ];
    }

    /**
     * Get tag/category statistics
     */
    public function getTagStats(int $userId)
    {
        $projects = $this->where('user_id', $userId)->findAll();
        $tagStats = [];

        foreach ($projects as $project) {
            $categories = json_decode($project['categories'], true);
            if (is_array($categories)) {
                foreach ($categories as $cat) {
                    if (!isset($tagStats[$cat])) {
                        $tagStats[$cat] = 0;
                    }
                    $tagStats[$cat]++;
                }
            }
        }

        return $tagStats;
    }

    /**
     * Get weekly focus projects (Top 3 by priority and progress)
     */
    public function getWeeklyFocus(int $userId)
    {
        return $this->where('user_id', $userId)
                    ->where('status', 'in_progress')
                    ->orderBy('priority', 'DESC')
                    ->orderBy('progress', 'ASC')
                    ->limit(3)
                    ->findAll();
    }

    /**
     * Get recent activity (projects, milestones, and notes)
     */
    public function getRecentActivity(int $userId)
    {
        $db = \Config\Database::connect();
        $activity = [];

        // 1. Project Activities (Created, Completed, Archived)
        $projects = $this->where('user_id', $userId)
                         ->orderBy('updated_at', 'DESC')
                         ->limit(10)
                         ->findAll();
        
        foreach ($projects as $p) {
            // Project Created
            $activity[] = [
                'type' => 'project_created',
                'title' => 'Project Created',
                'description' => 'Started new project: ' . $p['name'],
                'time' => $p['created_at'],
                'icon' => 'fa-plus-circle',
                'bg' => 'rgba(16, 185, 129, 0.2)',
                'color' => '#10b981'
            ];

            // Project Completed
            if ($p['status'] === 'completed') {
                $activity[] = [
                    'type' => 'project_completed',
                    'title' => 'Project Completed',
                    'description' => 'Finished work on ' . $p['name'],
                    'time' => $p['updated_at'],
                    'icon' => 'fa-check-double',
                    'bg' => 'rgba(99, 102, 241, 0.2)',
                    'color' => '#6366f1'
                ];
            }

            // Project Archived
            if ($p['is_archived'] == 1) {
                $activity[] = [
                    'type' => 'project_archived',
                    'title' => 'Project Archived',
                    'description' => esc($p['name']) . ' was moved to archives',
                    'time' => $p['updated_at'],
                    'icon' => 'fa-archive',
                    'bg' => 'rgba(148, 163, 184, 0.2)',
                    'color' => '#94a3b8'
                ];
            }
        }

        // 2. Milestone Activities (Started, Completed)
        $milestones = $db->table('project_milestones')
                         ->select('project_milestones.*, projects.name as project_name')
                         ->join('projects', 'projects.id = project_milestones.project_id')
                         ->where('projects.user_id', $userId)
                         ->orderBy('project_milestones.updated_at', 'DESC')
                         ->limit(10)
                         ->get()
                         ->getResultArray();

        foreach ($milestones as $m) {
            if ($m['status'] === 'completed') {
                $activity[] = [
                    'type' => 'milestone_completed',
                    'title' => 'Milestone Achieved',
                    'description' => 'Completed "' . $m['name'] . '" for ' . $m['project_name'],
                    'time' => $m['updated_at'],
                    'icon' => 'fa-flag-checkered',
                    'bg' => 'rgba(16, 185, 129, 0.2)',
                    'color' => '#10b981'
                ];
            } elseif ($m['status'] === 'in_progress') {
                $activity[] = [
                    'type' => 'milestone_started',
                    'title' => 'Milestone Started',
                    'description' => 'Working on "' . $m['name'] . '" in ' . $m['project_name'],
                    'time' => $m['updated_at'],
                    'icon' => 'fa-play-circle',
                    'bg' => 'rgba(59, 130, 246, 0.2)',
                    'color' => '#3b82f6'
                ];
            }
        }

        // 3. Note Activities (Created, Completed)
        $notes = $db->table('notes')
                    ->where('user_id', $userId)
                    ->orderBy('updated_at', 'DESC')
                    ->limit(10)
                    ->get()
                    ->getResultArray();

        foreach ($notes as $n) {
            // New Note
            $activity[] = [
                'type' => 'new_note',
                'title' => 'Idea Captured',
                'description' => 'Added new note: ' . $n['title'],
                'time' => $n['created_at'],
                'icon' => 'fa-sticky-note',
                'bg' => 'rgba(245, 158, 11, 0.2)',
                'color' => '#f59e0b'
            ];

            // Note Completed
            if ($n['is_completed'] == 1) {
                $activity[] = [
                    'type' => 'note_completed',
                    'title' => 'Note Completed',
                    'description' => 'Marked "' . $n['title'] . '" as done',
                    'time' => $n['updated_at'],
                    'icon' => 'fa-check-circle',
                    'bg' => 'rgba(16, 185, 129, 0.2)',
                    'color' => '#10b981'
                ];
            }
        }

        // Sort by time DESC
        usort($activity, function($a, $b) {
            return strtotime($b['time']) - strtotime($a['time']);
        });

        // Unique filter (to avoid showing "Project Created" and "Project Updated" for the same thing at the same time if they are same)
        // Actually, we'll just limit it.
        return array_slice($activity, 0, 10);
    }

    /**
     * Get categories for a specific project
     */
    public function getCategoriesByProjectId(int $projectId)
    {
        $project = $this->find($projectId);
        return $project ? json_decode($project['categories'], true) : [];
    }

    /**
     * Get tech stack for a specific project
     */
    public function getTechStackByProjectId(int $projectId)
    {
        $project = $this->find($projectId);
        return $project ? json_decode($project['tech_stack'], true) : [];
    }

    /**
     * Compute Project Health Score (0-100) and identify risk factors
     */
    public function calculateHealthScore(array $project, array $tasks = [], ?array $activeSprint = null): array
    {
        if (($project['status'] ?? '') === 'completed') {
            return [
                'score'       => 100,
                'status'      => 'healthy',
                'label'       => 'Completed',
                'badge_class' => 'bg-success-lighten text-success',
                'icon'        => 'mdi-check-all',
                'issues'      => [],
                'overdue_cnt' => 0,
            ];
        }

        $score = 100;
        $issues = [];
        $overdueCount = 0;
        $now = time();

        // 1. Check project overall due date
        if (!empty($project['due_date'])) {
            $dueTimestamp = strtotime($project['due_date']);
            if ($dueTimestamp < $now) {
                $daysOver = ceil(($now - $dueTimestamp) / 86400);
                $score -= 25;
                $issues[] = "Project target completion date was {$daysOver}d ago";
            }
        }

        // 2. Check overdue tasks
        $totalTasks = count($tasks);
        $completedTasks = 0;
        foreach ($tasks as $t) {
            if (($t['status'] ?? '') === 'done' || ($t['status'] ?? '') === 'approved') {
                $completedTasks++;
            } else {
                if (!empty($t['due_date']) && strtotime($t['due_date']) < $now) {
                    $overdueCount++;
                }
            }
        }

        if ($overdueCount > 0) {
            $deduction = min(30, $overdueCount * 6);
            $score -= $deduction;
            $issues[] = "{$overdueCount} task(s) past due date (-{$deduction} pts)";
        }

        // 3. Inactivity penalty
        if (!empty($project['updated_at'])) {
            $updatedTimestamp = strtotime($project['updated_at']);
            $daysInactive = floor(($now - $updatedTimestamp) / 86400);
            if ($daysInactive > 14) {
                $score -= 20;
                $issues[] = "No recorded project activity for {$daysInactive} days (-20 pts)";
            } elseif ($daysInactive > 7) {
                $score -= 10;
                $issues[] = "No updates in {$daysInactive} days (-10 pts)";
            }
        }

        // 4. Stalled progress check
        $progress = (int)($project['progress'] ?? 0);
        $status = $project['status'] ?? 'planning';
        if ($status === 'in_progress' && $progress < 15 && $totalTasks > 0) {
            $score -= 15;
            $issues[] = "Project active but progress is under 15% (-15 pts)";
        }

        // 5. Active Sprint health
        if ($activeSprint) {
            if (!empty($activeSprint['end_date']) && strtotime($activeSprint['end_date']) < $now) {
                $score -= 20;
                $issues[] = "Active sprint '{$activeSprint['name']}' has exceeded its end date (-20 pts)";
            }
        }

        // Clamp score between 10 and 100
        $score = max(10, min(100, $score));

        // Derive status and badge
        if ($score >= 80) {
            $healthStatus = 'healthy';
            $label = 'Healthy';
            $badgeClass = 'bg-success-lighten text-success';
            $icon = 'mdi-check-circle-outline';
        } elseif ($score >= 50) {
            $healthStatus = 'warning';
            $label = 'Needs Attention';
            $badgeClass = 'bg-warning-lighten text-warning';
            $icon = 'mdi-alert-outline';
        } else {
            $healthStatus = 'danger';
            $label = 'At Risk';
            $badgeClass = 'bg-danger-lighten text-danger';
            $icon = 'mdi-alert-circle-outline';
        }

        return [
            'score'       => $score,
            'status'      => $healthStatus,
            'label'       => $label,
            'badge_class' => $badgeClass,
            'icon'        => $icon,
            'issues'      => $issues,
            'overdue_cnt' => $overdueCount,
            'total_tasks' => $totalTasks,
            'done_tasks'  => $completedTasks,
        ];
    }

    /**
     * Get all accessible projects with computed health score
     */
    public function getProjectsWithHealth(int $userId, bool $isAdmin, ?string $healthFilter = null): array
    {
        $db = \Config\Database::connect();
        
        $builder = $this->select('projects.*');
        if (!$isAdmin) {
            $builder->where('projects.user_id', $userId);
        }
        $projects = $builder->orderBy('projects.updated_at', 'DESC')->findAll();

        if (empty($projects)) {
            return [];
        }

        $taskModel = new TaskModel();
        $sprintModel = new SprintModel();

        $results = [];
        foreach ($projects as $project) {
            $tasks = $taskModel->where('project_id', $project['id'])->findAll();
            $activeSprint = $sprintModel->getActiveSprint((int)$project['id']);
            $health = $this->calculateHealthScore($project, $tasks, $activeSprint);

            $project['health'] = $health;

            if ($healthFilter && $health['status'] !== $healthFilter) {
                continue;
            }

            $results[] = $project;
        }

        return $results;
    }

    /**
     * Get computed health metrics for a single project by ID
     */
    public function getProjectHealth(int $projectId): array
    {
        $project = $this->find($projectId);
        if (!$project) {
            return [
                'score'       => 100,
                'status'      => 'healthy',
                'label'       => 'Healthy',
                'badge_class' => 'bg-success-lighten text-success',
                'icon'        => 'mdi-check-circle',
                'risks'       => [],
            ];
        }

        $taskModel = new TaskModel();
        $sprintModel = new SprintModel();
        $tasks = $taskModel->where('project_id', $projectId)->findAll();
        $activeSprint = $sprintModel->getActiveSprint($projectId);

        return $this->calculateHealthScore($project, $tasks, $activeSprint);
    }
}
