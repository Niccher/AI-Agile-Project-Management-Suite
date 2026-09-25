<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;

class SearchApiController extends BaseController
{
    /**
     * Unified search endpoint for Command Palette & Quick Search
     * GET /api/search?q={term}
     */
    public function index()
    {
        $userId = auth()->id();
        if (!$userId) {
            return $this->response->setStatusCode(401)->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $query = trim($this->request->getGet('q') ?? '');
        $db = \Config\Database::connect();
        $currentUser = auth()->user();
        $isAdmin = $currentUser && $currentUser->inGroup('admin');
        $isManager = $currentUser && ($currentUser->inGroup('manager') || $isAdmin);

        $results = [];

        // 1. Action Commands (always matched if query starts with '>' or matches keywords)
        $actions = $this->getAvailableActions($isManager, $isAdmin);
        $cleanQuery = ltrim($query, '>');

        if (!empty($cleanQuery)) {
            $filteredActions = array_filter($actions, function ($action) use ($cleanQuery) {
                return stripos($action['title'], $cleanQuery) !== false 
                    || stripos($action['subtitle'], $cleanQuery) !== false
                    || stripos($action['keywords'], $cleanQuery) !== false;
            });
            foreach ($filteredActions as $act) {
                $results[] = [
                    'category' => 'Quick Actions',
                    'title'    => $act['title'],
                    'subtitle' => $act['subtitle'],
                    'icon'     => $act['icon'],
                    'badge'    => 'Action',
                    'badge_class' => 'bg-info-lighten text-info',
                    'url'      => $act['url'],
                    'action'   => $act['action'] ?? null,
                ];
            }
        }

        if (strlen($cleanQuery) >= 2) {
            // 2. Search Projects
            $projectBuilder = $db->table('projects')
                ->select('id, name, description, status, color, tech_stack, priority')
                ->where('deleted_at', null);

            $projectBuilder->groupStart()
                ->like('name', $cleanQuery)
                ->orLike('description', $cleanQuery)
                ->orLike('tech_stack', $cleanQuery)
                ->orLike('categories', $cleanQuery)
            ->groupEnd();

            $projects = $projectBuilder->limit(6)->get()->getResultArray();
            foreach ($projects as $proj) {
                $statusBadge = match($proj['status']) {
                    'completed'   => 'bg-success-lighten text-success',
                    'in_progress' => 'bg-primary-lighten text-primary',
                    'testing'     => 'bg-warning-lighten text-warning',
                    default       => 'bg-secondary-lighten text-secondary'
                };

                $projectTarget = !empty($proj['slug']) ? $proj['slug'] : $proj['id'];

                $results[] = [
                    'category' => 'Projects',
                    'title'    => $proj['name'],
                    'subtitle' => (!empty($proj['tech_stack']) ? $proj['tech_stack'] . ' • ' : '') . ucfirst(str_replace('_', ' ', $proj['status'])),
                    'icon'     => 'uil-briefcase text-primary',
                    'badge'    => ucfirst($proj['status']),
                    'badge_class' => $statusBadge,
                    'url'      => site_url('projects/view/' . $projectTarget),
                ];
            }

            // 3. Search Tasks
            $taskBuilder = $db->table('tasks')
                ->select('tasks.id, tasks.project_id, tasks.title, tasks.status, tasks.priority, projects.name as project_name, projects.slug as project_slug')
                ->join('projects', 'projects.id = tasks.project_id', 'left');

            $numericQuery = preg_replace('/[^0-9]/', '', $cleanQuery);
            $taskBuilder->groupStart()
                ->like('tasks.title', $cleanQuery)
                ->orLike('tasks.description', $cleanQuery);

            if (!empty($numericQuery)) {
                $taskBuilder->orWhere('tasks.id', (int)$numericQuery);
            }
            $taskBuilder->groupEnd();

            $tasks = $taskBuilder->limit(8)->get()->getResultArray();
            foreach ($tasks as $task) {
                $prioBadge = match($task['priority'] ?? 'medium') {
                    'critical', 'high' => 'bg-danger-lighten text-danger',
                    'medium'           => 'bg-warning-lighten text-warning',
                    default            => 'bg-info-lighten text-info'
                };

                $taskProjectTarget = !empty($task['project_slug']) ? $task['project_slug'] : $task['project_id'];

                $results[] = [
                    'category' => 'Tasks',
                    'title'    => '#' . $task['id'] . ' ' . $task['title'],
                    'subtitle' => ($task['project_name'] ? esc($task['project_name']) . ' • ' : '') . ucfirst(str_replace('_', ' ', $task['status'] ?? 'todo')),
                    'icon'     => 'uil-clipboard-alt text-info',
                    'badge'    => ucfirst($task['priority'] ?? 'medium'),
                    'badge_class' => $prioBadge,
                    'url'      => $taskProjectTarget ? site_url('projects/view/' . $taskProjectTarget) : site_url('kanban'),
                ];
            }

            // 4. Search Notes
            $notes = $db->table('notes')
                ->select('id, title, content, is_completed')
                ->where('user_id', $userId)
                ->groupStart()
                    ->like('title', $cleanQuery)
                    ->orLike('content', $cleanQuery)
                ->groupEnd()
                ->limit(4)
                ->get()->getResultArray();

            foreach ($notes as $note) {
                $results[] = [
                    'category' => 'Scratch Notes',
                    'title'    => $note['title'] ?: 'Untitled Note #' . $note['id'],
                    'subtitle' => mb_strimwidth(strip_tags($note['content']), 0, 60, '...'),
                    'icon'     => 'uil-notes text-warning',
                    'badge'    => $note['is_completed'] ? 'Completed' : 'Active',
                    'badge_class' => $note['is_completed'] ? 'bg-success-lighten text-success' : 'bg-secondary-lighten text-secondary',
                    'url'      => site_url('notes'),
                ];
            }

            // 5. Search Project Wiki / Docs
            if ($db->tableExists('project_wiki_pages')) {
                $wikiPages = $db->table('project_wiki_pages')
                    ->select('project_wiki_pages.id, project_wiki_pages.project_id, project_wiki_pages.title, project_wiki_pages.slug, project_wiki_pages.version, projects.name as project_name, projects.slug as project_slug')
                    ->join('projects', 'projects.id = project_wiki_pages.project_id', 'left')
                    ->groupStart()
                        ->like('project_wiki_pages.title', $cleanQuery)
                        ->orLike('project_wiki_pages.content', $cleanQuery)
                    ->groupEnd()
                    ->limit(4)
                    ->get()->getResultArray();

                foreach ($wikiPages as $wp) {
                    $wikiProjectTarget = !empty($wp['project_slug']) ? $wp['project_slug'] : $wp['project_id'];
                    $results[] = [
                        'category' => 'Wiki & Docs',
                        'title'    => $wp['title'],
                        'subtitle' => ($wp['project_name'] ? esc($wp['project_name']) . ' • ' : '') . 'Documentation (v' . $wp['version'] . ')',
                        'icon'     => 'uil-book-open text-success',
                        'badge'    => 'v' . $wp['version'],
                        'badge_class' => 'bg-success-lighten text-success',
                        'url'      => site_url('projects/wiki/' . $wikiProjectTarget . '/page/' . $wp['slug']),
                    ];
                }
            }

            // 5. Search Team Members (if Manager/Admin or general search)
            $users = $db->table('users')
                ->select('users.id, users.username, auth_identities.secret as email')
                ->join('auth_identities', 'auth_identities.user_id = users.id AND auth_identities.type = "email_password"', 'left')
                ->groupStart()
                    ->like('users.username', $cleanQuery)
                    ->orLike('auth_identities.secret', $cleanQuery)
                ->groupEnd()
                ->limit(4)
                ->get()->getResultArray();

            foreach ($users as $u) {
                $results[] = [
                    'category' => 'Team Members',
                    'title'    => $u['username'] ?? 'User #' . $u['id'],
                    'subtitle' => $u['email'] ?? 'Member',
                    'icon'     => 'uil-user text-success',
                    'badge'    => 'Member',
                    'badge_class' => 'bg-light text-dark',
                    'url'      => $isManager ? site_url('manager/team') : site_url('dashboard'),
                ];
            }
        }

        return $this->response->setJSON([
            'status'  => 'success',
            'query'   => $query,
            'count'   => count($results),
            'results' => $results
        ]);
    }

    /**
     * System action shortcuts
     */
    private function getAvailableActions(bool $isManager, bool $isAdmin): array
    {
        $actions = [
            [
                'title'    => 'Create New Project',
                'subtitle' => 'Launch a new agile sprint or kanban project',
                'keywords' => 'new project add create initiative',
                'icon'     => 'uil-plus-circle text-primary',
                'url'      => site_url('projects/create'),
            ],
            [
                'title'    => 'Go to Kanban Board',
                'subtitle' => 'Interactive drag-and-drop workspace',
                'keywords' => 'kanban board agile sprint cards tasks',
                'icon'     => 'uil-clipboard-alt text-info',
                'url'      => site_url('kanban'),
            ],
            [
                'title'    => 'Time Tracker & Logs',
                'subtitle' => 'Track working hours and view timesheets',
                'keywords' => 'time tracker hours timer timesheet duration',
                'icon'     => 'uil-clock text-warning',
                'url'      => site_url('time'),
            ],
            [
                'title'    => 'Calendar & Deadlines',
                'subtitle' => 'Sprint milestones and task due dates',
                'keywords' => 'calendar schedule events deadlines sprints',
                'icon'     => 'uil-calender text-danger',
                'url'      => site_url('calendar'),
            ],
            [
                'title'    => 'Velocity & Analytics',
                'subtitle' => 'Team throughput, completion rates, and stats',
                'keywords' => 'analytics metrics reports charts velocity velocity burndown',
                'icon'     => 'uil-chart-line text-purple',
                'url'      => site_url('analytics'),
            ],
            [
                'title'    => 'Scratch Notes & Memos',
                'subtitle' => 'Personal markdown scratchpad and reminders',
                'keywords' => 'notes scratchpad memo pad todo checklist',
                'icon'     => 'uil-notes text-secondary',
                'url'      => site_url('notes'),
            ],
            [
                'title'    => 'Toggle Dark / Light Theme',
                'subtitle' => 'Switch interface color scheme immediately',
                'keywords' => 'theme dark light mode toggle contrast',
                'icon'     => 'uil-moon text-warning',
                'url'      => 'javascript:void(0);',
                'action'   => 'toggleTheme',
            ],
            [
                'title'    => 'My Profile & Security',
                'subtitle' => 'Update password, username, and preferences',
                'keywords' => 'settings profile password account security user',
                'icon'     => 'uil-cog text-info',
                'url'      => site_url('settings'),
            ],
        ];

        if ($isManager) {
            $actions[] = [
                'title'    => 'Team Workload & Capacity',
                'subtitle' => 'Manager review of member allocations and leaderboards',
                'keywords' => 'manager team workload capacity developers leaderboard',
                'icon'     => 'uil-users-alt text-success',
                'url'      => site_url('manager/team'),
            ];
            $actions[] = [
                'title'    => 'Work Approvals',
                'subtitle' => 'Review and approve submitted developer deliverables',
                'keywords' => 'manager approvals review signoff tasks done',
                'icon'     => 'uil-check-circle text-info',
                'url'      => site_url('manager/approvals'),
            ];
            $actions[] = [
                'title'    => 'Sprint PDF Reports',
                'subtitle' => 'Generate executive printable sprint summaries',
                'keywords' => 'reports pdf sprint summary export print',
                'icon'     => 'uil-file-alt text-warning',
                'url'      => site_url('manager/reports'),
            ];
        }

        if ($isAdmin) {
            $actions[] = [
                'title'    => 'System Settings & SMTP',
                'subtitle' => 'Configure app branding, email server, and parameters',
                'keywords' => 'admin settings system email smtp mail backup configuration',
                'icon'     => 'uil-sliders-v-alt text-danger',
                'url'      => site_url('admin/settings'),
            ];
            $actions[] = [
                'title'    => 'Telemetry & Server Diagnostics',
                'subtitle' => 'MySQL containers, runtime metrics, and error logs',
                'keywords' => 'admin telemetry logs server diagnostics errors specs',
                'icon'     => 'uil-server text-light',
                'url'      => site_url('admin/telemetry'),
            ];
            $actions[] = [
                'title'    => 'Create Database Backup Snapshot',
                'subtitle' => 'Instant 1-click GZIP database dump',
                'keywords' => 'backup database snapshot dump sql mysql download',
                'icon'     => 'uil-database text-success',
                'url'      => site_url('admin/settings/backup/create'),
            ];
        }

        return $actions;
    }
}
