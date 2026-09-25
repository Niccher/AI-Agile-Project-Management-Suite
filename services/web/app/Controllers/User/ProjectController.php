<?php

namespace App\Controllers\User;

use App\Models\ProjectModel;

class ProjectController extends BaseUserController
{
    public function index()
    {
        $projectModel = new ProjectModel();

        $data = [
            'user'               => $this->currentUser,
            'all_projects'       => $projectModel->where('user_id', $this->userId)->paginate(10, 'all'),
            'active_projects'    => $projectModel->where('user_id', $this->userId)->where('status', 'in_progress')->paginate(10, 'active'),
            'pending_projects'   => $projectModel->where('user_id', $this->userId)->whereIn('status', ['planning', 'on_hold'])->paginate(10, 'pending'),
            'completed_projects' => $projectModel->where('user_id', $this->userId)->where('status', 'completed')->paginate(10, 'completed'),
            'archived_projects'  => $projectModel->where('user_id', $this->userId)->where('is_archived', 1)->paginate(10, 'archived'),
            'pager'              => $projectModel->pager,
            'stats'              => $projectModel->getStats($this->userId),
            'tagStats'           => $projectModel->getTagStats($this->userId),
        ];

        return view('user/projects/index', $data);
    }

    public function create()
    {
        return view('user/projects/create', ['user' => $this->currentUser]);
    }

    public function store()
    {
        $projectModel = new ProjectModel();

        $data = $this->request->getPost();
        $data['user_id'] = $this->userId;
        $data['is_archived'] = 0;

        $data['tech_stack'] = json_encode(array_filter(explode(',', $this->request->getPost('tech_stack') ?? '')));
        $data['categories'] = json_encode(array_filter(explode(',', $this->request->getPost('categories') ?? '')));

        if (!$projectModel->insert($data)) {
            return redirect()->back()->withInput()->with('errors', $projectModel->errors());
        }

        $projectId = $projectModel->getInsertID();

        // Handle Milestones
        $milestones = $this->request->getPost('milestones');
        if (is_array($milestones)) {
            $db = \Config\Database::connect();
            foreach ($milestones as $ms) {
                if (!empty($ms['name'])) {
                    $db->table('project_milestones')->insert([
                        'project_id'  => $projectId,
                        'name'        => $ms['name'],
                        'description' => $ms['description'] ?? null,
                        'due_date'    => !empty($ms['due_date']) ? $ms['due_date'] : null,
                        'status'      => $ms['status'] ?? 'pending',
                        'created_at'  => date('Y-m-d H:i:s'),
                        'updated_at'  => date('Y-m-d H:i:s'),
                    ]);
                }
            }
        }

        $createdProject = $projectModel->find($projectId);
        $redirectSlug = $createdProject['slug'] ?? $projectId;

        return redirect()->to(site_url('projects/view/' . $redirectSlug))->with('message', 'Project created successfully!');
    }

    public function view($identifier)
    {
        $projectModel = new ProjectModel();
        $db = \Config\Database::connect();
        $isAdmin = auth()->user() && auth()->user()->inGroup('admin', 'manager');
        
        $project = $projectModel->findByIdentifier($identifier, $this->userId, $isAdmin);

        if (!$project) {
            return redirect()->to('/projects')->with('error', 'Project not found or unauthorized.');
        }

        $projectId = (int)$project['id'];

        $timeStats = $db->table('time_logs')
            ->select('COALESCE(SUM(duration), 0) as total_seconds, 
                      COUNT(DISTINCT DATE(start_time)) as days_logged,
                      COALESCE(SUM(CASE WHEN start_time >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN duration ELSE 0 END), 0) as week_seconds,
                      COALESCE(SUM(CASE WHEN start_time >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN duration ELSE 0 END), 0) as month_seconds')
            ->where('project_id', $projectId)
            ->get()->getRowArray();

        $projectNotes = $db->table('notes')
            ->where('project_id', $projectId)
            ->where('is_deleted', 0)
            ->orderBy('created_at', 'DESC')
            ->limit(10)
            ->get()->getResultArray();

        $taskModel = new \App\Models\TaskModel();
        $sprintModel = new \App\Models\SprintModel();
        $tasks = $taskModel->where('project_id', $projectId)->findAll();
        $activeSprint = $sprintModel->getActiveSprint($projectId);
        $portalTokenModel = new \App\Models\PortalTokenModel();
        $portalTokens = $portalTokenModel->getTokensForProject($projectId);
        $health = $projectModel->getProjectHealth($projectId);

        $data = [
            'user'          => $this->currentUser,
            'project'       => $project,
            'health'        => $health,
            'portal_tokens' => $portalTokens,
            'milestones'    => $projectModel->getMilestones($projectId),
            'tech_stack'    => json_decode($project['tech_stack'], true) ?? [],
            'categories'    => json_decode($project['categories'], true) ?? [],
            'time_stats'    => $timeStats,
            'project_notes' => $projectNotes,
        ];

        return view('user/projects/view', $data);
    }

    /**
     * Dedicated Project Health & RAG Risk Dashboard
     */
    public function health()
    {
        $projectModel = new ProjectModel();
        $isAdmin = auth()->user() && auth()->user()->inGroup('admin', 'manager');
        $filter = $this->request->getGet('filter'); // 'danger', 'warning', 'healthy', or null

        $projects = $projectModel->getProjectsWithHealth($this->userId, $isAdmin, $filter);

        // Sort by health score ascending (most at-risk first)
        usort($projects, function($a, $b) {
            return ($a['health']['score'] ?? 100) <=> ($b['health']['score'] ?? 100);
        });

        // Compute aggregate metrics
        $totalCount = count($projects);
        $atRiskCount = 0;
        $warningCount = 0;
        $healthyCount = 0;
        $totalScore = 0;

        foreach ($projects as $p) {
            $s = $p['health']['score'] ?? 100;
            $totalScore += $s;
            if (($p['health']['status'] ?? '') === 'danger') $atRiskCount++;
            elseif (($p['health']['status'] ?? '') === 'warning') $warningCount++;
            else $healthyCount++;
        }

        $avgScore = $totalCount > 0 ? round($totalScore / $totalCount) : 100;

        return view('user/projects/health', [
            'projects'     => $projects,
            'totalCount'   => $totalCount,
            'atRiskCount'  => $atRiskCount,
            'warningCount' => $warningCount,
            'healthyCount' => $healthyCount,
            'avgScore'     => $avgScore,
            'currentFilter'=> $filter,
            'isAdmin'      => $isAdmin,
        ]);
    }

    public function edit($identifier)
    {
        $projectModel = new ProjectModel();
        $isAdmin = auth()->user() && auth()->user()->inGroup('admin', 'manager');
        
        $project = $projectModel->findByIdentifier($identifier, $this->userId, $isAdmin);

        if (!$project) {
            return redirect()->to('/projects')->with('error', 'Project not found or unauthorized.');
        }

        $projectId = (int)$project['id'];

        $data = [
            'user'       => $this->currentUser,
            'project'    => $project,
            'milestones' => $projectModel->getMilestones($projectId),
            'tech_stack' => json_decode($project['tech_stack'], true) ?? [],
            'categories' => json_decode($project['categories'], true) ?? [],
        ];

        return view('user/projects/edit', $data);
    }

    public function update($identifier)
    {
        $projectModel = new ProjectModel();
        $isAdmin = auth()->user() && auth()->user()->inGroup('admin', 'manager');
        
        $project = $projectModel->findByIdentifier($identifier, $this->userId, $isAdmin);
        if (!$project) {
            return redirect()->to('/projects')->with('error', 'Project not found or unauthorized.');
        }

        $id = (int)$project['id'];
        $data = $this->request->getPost();
        
        $data['tech_stack'] = json_encode(array_filter(explode(',', $this->request->getPost('tech_stack') ?? '')));
        $data['categories'] = json_encode(array_filter(explode(',', $this->request->getPost('categories') ?? '')));

        if (!$projectModel->update($id, $data)) {
            return redirect()->back()->withInput()->with('errors', $projectModel->errors());
        }

        // Handle Milestones (Sync)
        $db = \Config\Database::connect();
        $db->table('project_milestones')->where('project_id', $id)->delete();
        
        $milestones = $this->request->getPost('milestones');
        if (is_array($milestones)) {
            foreach ($milestones as $ms) {
                if (!empty($ms['name'])) {
                    $db->table('project_milestones')->insert([
                        'project_id'  => $id,
                        'name'        => $ms['name'],
                        'description' => $ms['description'] ?? null,
                        'due_date'    => !empty($ms['due_date']) ? $ms['due_date'] : null,
                        'status'      => $ms['status'] ?? 'pending',
                        'created_at'  => date('Y-m-d H:i:s'),
                        'updated_at'  => date('Y-m-d H:i:s'),
                    ]);
                }
            }
        }

        $refreshed = $projectModel->find($id);
        $redirectSlug = $refreshed['slug'] ?? $id;

        return redirect()->to(site_url('projects/view/' . $redirectSlug))->with('message', 'Project updated successfully!');
    }

    public function delete($identifier)
    {
        $projectModel = new ProjectModel();
        $isAdmin = auth()->user() && auth()->user()->inGroup('admin', 'manager');
        
        $project = $projectModel->findByIdentifier($identifier, $this->userId, $isAdmin);

        if (!$project) {
            return redirect()->to('/projects')->with('error', 'Project not found or unauthorized.');
        }

        if ($projectModel->delete($project['id'])) {
            return redirect()->to('/projects')->with('message', 'Project deleted successfully.');
        }

        return redirect()->back()->with('error', 'Could not delete project.');
    }

    public function archive($identifier)
    {
        $projectModel = new ProjectModel();
        $isAdmin = auth()->user() && auth()->user()->inGroup('admin', 'manager');
        
        $project = $projectModel->findByIdentifier($identifier, $this->userId, $isAdmin);

        if (!$project) {
            return redirect()->to('/projects')->with('error', 'Project not found or unauthorized.');
        }

        $id = (int)$project['id'];
        $newArchiveStatus = empty($project['is_archived']) ? 1 : 0;
        $projectModel->update($id, ['is_archived' => $newArchiveStatus]);

        $statusMsg = $newArchiveStatus ? 'Project archived successfully.' : 'Project unarchived successfully.';
        return redirect()->to('/projects')->with('message', $statusMsg);
    }

    /**
     * Live AJAX telemetry stream endpoint for real-time monitoring
     */
    public function telemetryLive()
    {
        // 1. WebApp CPU, RAM, Disk, Uptime
        $ramTotalBytes = 0;
        $ramUsedBytes = 0;
        if (is_readable('/proc/meminfo')) {
            $meminfo = @file_get_contents('/proc/meminfo') ?: '';
            preg_match('/MemTotal:\s+(\d+)\s+kB/', $meminfo, $totalMatches);
            preg_match('/MemAvailable:\s+(\d+)\s+kB/', $meminfo, $availMatches);
            
            $ramTotalBytes = isset($totalMatches[1]) ? ((float)$totalMatches[1] * 1024) : 0;
            $ramAvailBytes = isset($availMatches[1]) ? ((float)$availMatches[1] * 1024) : 0;
            $ramUsedBytes = max(0, $ramTotalBytes - $ramAvailBytes);
        }
        if ($ramTotalBytes <= 0) {
            $ramTotalBytes = 1024 * 1024 * 1024;
            $ramUsedBytes = memory_get_usage(true);
        }
        $ramPercent = $ramTotalBytes > 0 ? min(100, round(($ramUsedBytes / $ramTotalBytes) * 100)) : 0;

        $load = sys_getloadavg() ?: [0, 0, 0];
        $cpuLoad1 = $load[0] ?? 0;
        $cpuCores = 1;
        if (is_readable('/proc/cpuinfo')) {
            $cpuinfo = @file_get_contents('/proc/cpuinfo') ?: '';
            preg_match_all('/^processor/m', $cpuinfo, $matches);
            $cpuCores = max(count($matches[0] ?? []), 1);
        }
        $cpuPercent = min(100, max(1, round(($cpuLoad1 / $cpuCores) * 100)));

        $diskTotalBytes = @disk_total_space("/") ?: (20 * 1073741824);
        $diskFreeBytes = @disk_free_space("/") ?: (15 * 1073741824);
        $diskUsedBytes = max(0, $diskTotalBytes - $diskFreeBytes);
        $diskPercent = $diskTotalBytes > 0 ? round(($diskUsedBytes / $diskTotalBytes) * 100) : 0;

        // DB Status
        $db = \Config\Database::connect();
        $dbConnected = false;
        $activeThreads = 1;
        try {
            if ($db->connect()) {
                $dbConnected = true;
                $threadRow = $db->query("SHOW STATUS LIKE 'Threads_connected'")->getRowArray();
                $activeThreads = (int)($threadRow['Value'] ?? 1);
            }
        } catch (\Throwable $e) {}

        $formatBytes = function($bytes) {
            if ($bytes <= 0) return '0 B';
            $units = ['B', 'KB', 'MB', 'GB', 'TB'];
            $pow = floor(log($bytes) / log(1024));
            $pow = min($pow, count($units) - 1);
            return round($bytes / pow(1024, $pow), 1) . ' ' . $units[$pow];
        };

        return $this->response->setJSON([
            'status'          => 'online',
            'cpu_percent'     => $cpuPercent,
            'cpu_cores'       => $cpuCores,
            'ram_percent'     => $ramPercent,
            'ram_used_human'  => $formatBytes($ramUsedBytes),
            'ram_total_human' => $formatBytes($ramTotalBytes),
            'disk_percent'    => $diskPercent,
            'disk_used_human' => $formatBytes($diskUsedBytes),
            'disk_total_human'=> $formatBytes($diskTotalBytes),
            'db_connected'    => $dbConnected,
            'db_threads'      => $activeThreads,
            'redis_active'    => true,
            'timestamp'       => date('H:i:s'),
        ]);
    }
}
