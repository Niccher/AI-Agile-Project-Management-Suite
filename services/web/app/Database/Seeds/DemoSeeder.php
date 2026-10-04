<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\Shield\Entities\User;

class DemoSeeder extends Seeder
{
    public function run()
    {
        $db = $this->db;
        $usersProvider = auth()->getProvider();
        $adminPass = env('ADMIN_PASSWORD', 'secret');
        $universalPass = 'secret';

        echo "==> [1/12] Seeding Users and Role Hierarchy...\n";

        // 1. Seed / Upsert Users
        $seedUsers = [
            [
                'username'   => 'admin',
                'email'      => 'admin@chegejira.local',
                'password'   => $adminPass,
                'first_name' => 'System',
                'last_name'  => 'Administrator',
                'groups'     => ['admin', 'superadmin'],
                'timezone'   => 'Africa/Nairobi',
                'pref'       => json_encode(['theme' => 'dark', 'sidebar' => 'compact', 'audio_alerts' => true]),
            ],
            [
                'username'   => 'manager',
                'email'      => 'manager@chegejira.local',
                'password'   => $universalPass,
                'first_name' => 'Alex',
                'last_name'  => 'Vance',
                'groups'     => ['manager'],
                'timezone'   => 'America/New_York',
                'pref'       => json_encode(['theme' => 'light', 'sidebar' => 'full', 'email_digest' => true]),
            ],
            [
                'username'   => 'sarah',
                'email'      => 'sarah@chegejira.local',
                'password'   => $universalPass,
                'first_name' => 'Sarah',
                'last_name'  => 'Connor',
                'groups'     => ['user'],
                'timezone'   => 'Europe/London',
                'pref'       => json_encode(['theme' => 'system', 'focus_mode' => true]),
            ],
            [
                'username'   => 'david',
                'email'      => 'david@chegejira.local',
                'password'   => $universalPass,
                'first_name' => 'David',
                'last_name'  => 'Kim',
                'groups'     => ['user'],
                'timezone'   => 'America/Los_Angeles',
                'pref'       => json_encode(['theme' => 'dark', 'compact_tables' => true]),
            ],
            [
                'username'   => 'elena',
                'email'      => 'elena@chegejira.local',
                'password'   => $universalPass,
                'first_name' => 'Elena',
                'last_name'  => 'Rostova',
                'groups'     => ['user'],
                'timezone'   => 'Europe/Berlin',
                'pref'       => json_encode(['theme' => 'light', 'high_contrast' => false]),
            ],
        ];

        $userMap = []; // username => user_id

        foreach ($seedUsers as $uData) {
            $user = $usersProvider->findByCredentials(['email' => $uData['email']])
                ?? $usersProvider->findByCredentials(['username' => $uData['username']]);

            if (!$user) {
                $user = new User([
                    'username'   => $uData['username'],
                    'email'      => $uData['email'],
                    'password'   => $uData['password'],
                    'first_name' => $uData['first_name'],
                    'last_name'  => $uData['last_name'],
                    'active'     => 1,
                ]);
                $usersProvider->save($user);
                $user = $usersProvider->findById($usersProvider->getInsertID())
                    ?? $usersProvider->findByCredentials(['username' => $uData['username']]);
            } else {
                $user->setPassword($uData['password']);
                $user->first_name = $uData['first_name'];
                $user->last_name = $uData['last_name'];
                $user->active = 1;
                $usersProvider->save($user);
            }

            if ($user) {
                $userMap[$uData['username']] = (int)$user->id;
                foreach ($uData['groups'] as $group) {
                    if (!$user->inGroup($group)) {
                        try { $user->addGroup($group); } catch (\Throwable $e) {}
                    }
                }
                // Save user settings/preferences
                if ($db->fieldExists('timezone', 'users')) {
                    $db->table('users')->where('id', $user->id)->update([
                        'timezone'    => $uData['timezone'],
                        'preferences' => $uData['pref'],
                    ]);
                }
            }
        }

        $adminId   = $userMap['admin'] ?? 1;
        $managerId = $userMap['manager'] ?? $adminId;
        $sarahId   = $userMap['sarah'] ?? $adminId;
        $davidId   = $userMap['david'] ?? $adminId;
        $elenaId   = $userMap['elena'] ?? $adminId;

        echo "==> [2/12] Seeding User Invites...\n";

        // 2. Seed User Invites
        if ($db->tableExists('user_invites')) {
            $invites = [
                [
                    'email'      => 'product.owner@acme.org',
                    'role'       => 'manager',
                    'token'      => bin2hex(random_bytes(16)) . '01',
                    'status'     => 'pending',
                    'expires_at' => date('Y-m-d H:i:s', strtotime('+7 days')),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ],
                [
                    'email'      => 'freelance.designer@studio.co',
                    'role'       => 'user',
                    'token'      => bin2hex(random_bytes(16)) . '02',
                    'status'     => 'pending',
                    'expires_at' => date('Y-m-d H:i:s', strtotime('+3 days')),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ],
                [
                    'email'      => 'expired.contractor@test.io',
                    'role'       => 'user',
                    'token'      => bin2hex(random_bytes(16)) . '03',
                    'status'     => 'pending',
                    'expires_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
                    'created_at' => date('Y-m-d H:i:s', strtotime('-10 days')),
                    'updated_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
                ],
            ];

            foreach ($invites as $inv) {
                if ($db->table('user_invites')->where('email', $inv['email'])->countAllResults() === 0) {
                    $db->table('user_invites')->insert($this->filterToExistingColumns('user_invites', $inv));
                }
            }
        }

        echo "==> [3/12] Seeding Diverse Projects (10 Active + 2 Special Edge Cases)...\n";

        // 3. Seed Projects
        $now = date('Y-m-d H:i:s');
        $seedProjects = [
            [
                'user_id'     => $adminId,
                'name'        => 'Hyper Theme & UI SaaS Migration',
                'slug'        => 'hyper-theme-migration-a1b2c3',
                'short_code'  => 'a1b2c3',
                'description' => 'Migrate the entire application layout to Bootstrap 5 Hyper SaaS Theme with dark mode support.',
                'status'      => 'in_progress',
                'priority'    => 'high',
                'progress'    => 65,
                'color'       => '#727cf5',
                'icon'        => 'mdi-palette-swatch',
                'categories'  => json_encode(['Frontend', 'UI/UX', 'Design System']),
                'due_date'    => date('Y-m-d', strtotime('+20 days')),
                'is_archived' => 0,
                'deleted_at'  => null,
                'created_at'  => date('Y-m-d H:i:s', strtotime('-15 days')),
                'updated_at'  => $now,
            ],
            [
                'user_id'     => $managerId,
                'name'        => 'Mobile App REST API & Auth',
                'slug'        => 'mobile-app-api-d4e5f6',
                'short_code'  => 'd4e5f6',
                'description' => 'Build secure OAuth2 REST API endpoints for the iOS & Android mobile companion applications.',
                'status'      => 'in_progress',
                'priority'    => 'urgent',
                'progress'    => 40,
                'color'       => '#0acf97',
                'icon'        => 'mdi-cellphone-link',
                'categories'  => json_encode(['API', 'Backend', 'Mobile']),
                'due_date'    => date('Y-m-d', strtotime('+30 days')),
                'is_archived' => 0,
                'deleted_at'  => null,
                'created_at'  => date('Y-m-d H:i:s', strtotime('-10 days')),
                'updated_at'  => $now,
            ],
            [
                'user_id'     => $adminId,
                'name'        => 'Q3 Marketing Website Redesign',
                'slug'        => 'q3-marketing-website-789abc',
                'short_code'  => '789abc',
                'description' => 'Complete overhaul of landing pages, conversion funnels, and pricing calculator.',
                'status'      => 'completed',
                'priority'    => 'low',
                'progress'    => 100,
                'color'       => '#39afd1',
                'icon'        => 'mdi-bullhorn-outline',
                'categories'  => json_encode(['Marketing', 'SEO', 'Conversion']),
                'due_date'    => date('Y-m-d', strtotime('-2 days')),
                'is_archived' => 0,
                'deleted_at'  => null,
                'closed_at'   => date('Y-m-d H:i:s', strtotime('-2 days')),
                'created_at'  => date('Y-m-d H:i:s', strtotime('-40 days')),
                'updated_at'  => date('Y-m-d H:i:s', strtotime('-2 days')),
            ],
            [
                'user_id'     => $adminId,
                'name'        => 'Private Local LLM & AI Assistant',
                'slug'        => 'ai-agile-assistant-101def',
                'short_code'  => '101def',
                'description' => 'Integrate FastAPI Ollama service for zero-data-leakage sprint summarization and task estimation.',
                'status'      => 'in_progress',
                'priority'    => 'high',
                'progress'    => 50,
                'color'       => '#fa5c7c',
                'icon'        => 'mdi-robot-outline',
                'categories'  => json_encode(['AI/ML', 'FastAPI', 'Python']),
                'due_date'    => date('Y-m-d', strtotime('+15 days')),
                'is_archived' => 0,
                'deleted_at'  => null,
                'created_at'  => date('Y-m-d H:i:s', strtotime('-12 days')),
                'updated_at'  => $now,
            ],
            [
                'user_id'     => $managerId,
                'name'        => 'Stripe Billing & Subscription Engine',
                'slug'        => 'billing-subscription-engine-202aaa',
                'short_code'  => '202aaa',
                'description' => 'Implement multi-tier SaaS recurring billing, automated PDF invoice generation, and webhooks.',
                'status'      => 'planning',
                'priority'    => 'medium',
                'progress'    => 15,
                'color'       => '#ffbc00',
                'icon'        => 'mdi-credit-card-outline',
                'categories'  => json_encode(['Finance', 'Billing', 'Stripe']),
                'due_date'    => date('Y-m-d', strtotime('+45 days')),
                'is_archived' => 0,
                'deleted_at'  => null,
                'created_at'  => date('Y-m-d H:i:s', strtotime('-5 days')),
                'updated_at'  => $now,
            ],
            [
                'user_id'     => $adminId,
                'name'        => 'SOC2 Compliance & Security Hardening',
                'slug'        => 'soc2-security-audit-303bbb',
                'short_code'  => '303bbb',
                'description' => 'Audit logs consolidation, MFA enforcement, AES-256 database backup encryption, and pen testing.',
                'status'      => 'in_progress',
                'priority'    => 'urgent',
                'progress'    => 30,
                'color'       => '#e32636',
                'icon'        => 'mdi-shield-lock-outline',
                'categories'  => json_encode(['Security', 'DevOps', 'Compliance']),
                'due_date'    => date('Y-m-d', strtotime('-1 days')), // Intentionally overdue for health score testing
                'is_archived' => 0,
                'deleted_at'  => null,
                'created_at'  => date('Y-m-d H:i:s', strtotime('-25 days')),
                'updated_at'  => $now,
            ],
            [
                'user_id'     => $sarahId,
                'name'        => 'Redis Caching & Queue Optimization',
                'slug'        => 'redis-caching-layer-404ccc',
                'short_code'  => '404ccc',
                'description' => 'Sub-millisecond query caching layer and asynchronous background queue worker for report generation.',
                'status'      => 'in_progress',
                'priority'    => 'medium',
                'progress'    => 80,
                'color'       => '#fd7e14',
                'icon'        => 'mdi-database-clock',
                'categories'  => json_encode(['Performance', 'Redis', 'Backend']),
                'due_date'    => date('Y-m-d', strtotime('+10 days')),
                'is_archived' => 0,
                'deleted_at'  => null,
                'created_at'  => date('Y-m-d H:i:s', strtotime('-18 days')),
                'updated_at'  => $now,
            ],
            [
                'user_id'     => $managerId,
                'name'        => 'Executive Analytics & BI Dashboard',
                'slug'        => 'executive-bi-reporting-505ddd',
                'short_code'  => '505ddd',
                'description' => 'High-level KPI dashboards showing team velocity, sprint burndown accuracy, and budget burn rate.',
                'status'      => 'planning',
                'priority'    => 'low',
                'progress'    => 5,
                'color'       => '#6f42c1',
                'icon'        => 'mdi-chart-areaspline',
                'categories'  => json_encode(['Analytics', 'BI', 'Reporting']),
                'due_date'    => date('Y-m-d', strtotime('+60 days')),
                'is_archived' => 0,
                'deleted_at'  => null,
                'created_at'  => date('Y-m-d H:i:s', strtotime('-2 days')),
                'updated_at'  => $now,
            ],
            [
                'user_id'     => $davidId,
                'name'        => 'Third-Party Webhook & Zapier Integration',
                'slug'        => 'webhook-integrations-606eee',
                'short_code'  => '606eee',
                'description' => 'Real-time outbound event dispatcher with signed HMAC SHA-256 payloads for Slack and Zapier.',
                'status'      => 'on_hold',
                'priority'    => 'low',
                'progress'    => 20,
                'color'       => '#20c997',
                'icon'        => 'mdi-webhook',
                'categories'  => json_encode(['Integrations', 'Webhooks']),
                'due_date'    => date('Y-m-d', strtotime('+40 days')),
                'is_archived' => 0,
                'deleted_at'  => null,
                'created_at'  => date('Y-m-d H:i:s', strtotime('-20 days')),
                'updated_at'  => $now,
            ],
            [
                'user_id'     => $adminId,
                'name'        => 'Legacy Monolith Microservices Extraction',
                'slug'        => 'legacy-decoupling-707fff',
                'short_code'  => '707fff',
                'description' => 'Successfully extracted notification and auth services into isolated lightweight Docker containers.',
                'status'      => 'completed',
                'priority'    => 'high',
                'progress'    => 100,
                'color'       => '#4e73df',
                'icon'        => 'mdi-server-network',
                'categories'  => json_encode(['Architecture', 'DevOps']),
                'due_date'    => date('Y-m-d', strtotime('-10 days')),
                'is_archived' => 0,
                'deleted_at'  => null,
                'closed_at'   => date('Y-m-d H:i:s', strtotime('-10 days')),
                'created_at'  => date('Y-m-d H:i:s', strtotime('-60 days')),
                'updated_at'  => date('Y-m-d H:i:s', strtotime('-10 days')),
            ],
            // Special Edge Case Projects
            [
                'user_id'     => $adminId,
                'name'        => 'Internal Tooling v1 [ARCHIVED]',
                'slug'        => 'internal-tooling-v1-808ggg',
                'short_code'  => '808ggg',
                'description' => 'Deprecated command line developer utilities archived after web suite rollout.',
                'status'      => 'completed',
                'priority'    => 'low',
                'progress'    => 100,
                'color'       => '#6c757d',
                'icon'        => 'mdi-archive-outline',
                'categories'  => json_encode(['Archived', 'Tools']),
                'due_date'    => date('Y-m-d', strtotime('-90 days')),
                'is_archived' => 1,
                'deleted_at'  => null,
                'closed_at'   => date('Y-m-d H:i:s', strtotime('-90 days')),
                'created_at'  => date('Y-m-d H:i:s', strtotime('-120 days')),
                'updated_at'  => date('Y-m-d H:i:s', strtotime('-90 days')),
            ],
            [
                'user_id'     => $adminId,
                'name'        => 'Abandoned Prototype [TRASH / SOFT DELETED]',
                'slug'        => 'abandoned-prototype-909hhh',
                'short_code'  => '909hhh',
                'description' => 'Experimental prototype discarded during early ideation for testing trash/restore filters.',
                'status'      => 'planning',
                'priority'    => 'low',
                'progress'    => 0,
                'color'       => '#e0e0e0',
                'icon'        => 'mdi-trash-can-outline',
                'categories'  => json_encode(['Experimental']),
                'due_date'    => date('Y-m-d', strtotime('+90 days')),
                'is_archived' => 0,
                'deleted_at'  => date('Y-m-d H:i:s', strtotime('-3 days')),
                'created_at'  => date('Y-m-d H:i:s', strtotime('-15 days')),
                'updated_at'  => date('Y-m-d H:i:s', strtotime('-3 days')),
            ]
        ];

        $projectMap = []; // slug => project_id

        foreach ($seedProjects as $pData) {
            $existing = $db->table('projects')->where('slug', $pData['slug'])->get()->getRowArray();
            if ($existing) {
                $db->table('projects')->where('id', $existing['id'])->update($this->filterToExistingColumns('projects', $pData));
                $projectMap[$pData['slug']] = (int)$existing['id'];
            } else {
                $db->table('projects')->insert($this->filterToExistingColumns('projects', $pData));
                $projectMap[$pData['slug']] = (int)$db->insertID();
            }
        }

        $hyperProjId   = $projectMap['hyper-theme-migration-a1b2c3'] ?? 1;
        $mobileProjId  = $projectMap['mobile-app-api-d4e5f6'] ?? 2;
        $mktgProjId    = $projectMap['q3-marketing-website-789abc'] ?? 3;
        $aiProjId      = $projectMap['ai-agile-assistant-101def'] ?? 4;
        $billingProjId = $projectMap['billing-subscription-engine-202aaa'] ?? 5;
        $soc2ProjId    = $projectMap['soc2-security-audit-303bbb'] ?? 6;
        $redisProjId   = $projectMap['redis-caching-layer-404ccc'] ?? 7;

        echo "==> [4/12] Seeding Public Client Portal Tokens...\n";

        // 4. Seed Portal Tokens
        if ($db->tableExists('project_portal_tokens')) {
            $portalTokens = [
                [
                    'project_id' => $hyperProjId,
                    'token'      => 'token_hyper_theme_demo_777',
                    'label'      => 'Client Stakeholder Demo Portal',
                    'expires_at' => date('Y-m-d H:i:s', strtotime('+60 days')),
                    'is_active'  => 1,
                    'created_by' => $adminId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'project_id' => $mobileProjId,
                    'token'      => 'token_mobile_api_guest_888',
                    'label'      => 'Mobile Partner Readonly View',
                    'expires_at' => date('Y-m-d H:i:s', strtotime('+30 days')),
                    'is_active'  => 1,
                    'created_by' => $managerId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            ];
            foreach ($portalTokens as $pt) {
                if ($db->table('project_portal_tokens')->where('token', $pt['token'])->countAllResults() === 0) {
                    $db->table('project_portal_tokens')->insert($this->filterToExistingColumns('project_portal_tokens', $pt));
                }
            }
        }

        echo "==> [5/12] Seeding Sprints and Velocity Data...\n";

        // 5. Seed Sprints
        $sprintsData = [
            [
                'project_id'       => $mobileProjId,
                'name'             => 'Sprint 1 - API Core & Auth',
                'goal'             => 'Establish JWT authentication, user registration, and OAuth2 token refresh flow.',
                'status'           => 'closed',
                'start_date'       => date('Y-m-d', strtotime('-24 days')),
                'end_date'         => date('Y-m-d', strtotime('-10 days')),
                'total_points'     => 21,
                'completed_points' => 21,
                'created_at'       => date('Y-m-d H:i:s', strtotime('-25 days')),
                'updated_at'       => date('Y-m-d H:i:s', strtotime('-10 days')),
            ],
            [
                'project_id'       => $mobileProjId,
                'name'             => 'Sprint 2 - Project & Task Endpoints',
                'goal'             => 'Ship RESTful CRUD operations for projects, tasks, comments, and attachments.',
                'status'           => 'active',
                'start_date'       => date('Y-m-d', strtotime('-9 days')),
                'end_date'         => date('Y-m-d', strtotime('+5 days')),
                'total_points'     => 34,
                'completed_points' => 18,
                'created_at'       => date('Y-m-d H:i:s', strtotime('-10 days')),
                'updated_at'       => $now,
            ],
            [
                'project_id'       => $mobileProjId,
                'name'             => 'Sprint 3 - Push Notifications & Sync',
                'goal'             => 'Firebase Cloud Messaging integration and offline state reconciliation.',
                'status'           => 'planning',
                'start_date'       => date('Y-m-d', strtotime('+6 days')),
                'end_date'         => date('Y-m-d', strtotime('+20 days')),
                'total_points'     => 28,
                'completed_points' => 0,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'project_id'       => $hyperProjId,
                'name'             => 'Sprint 1 - Layout & Dark Mode',
                'goal'             => 'Complete base navigation, topbar timer widget, and responsive sidebar states.',
                'status'           => 'active',
                'start_date'       => date('Y-m-d', strtotime('-7 days')),
                'end_date'         => date('Y-m-d', strtotime('+7 days')),
                'total_points'     => 26,
                'completed_points' => 14,
                'created_at'       => date('Y-m-d H:i:s', strtotime('-8 days')),
                'updated_at'       => $now,
            ],
        ];

        $sprintMap = []; // "project_id:name" => sprint_id

        foreach ($sprintsData as $sData) {
            $existingSprint = $db->table('sprints')
                ->where('project_id', $sData['project_id'])
                ->where('name', $sData['name'])
                ->get()->getRowArray();

            if ($existingSprint) {
                $db->table('sprints')->where('id', $existingSprint['id'])->update($this->filterToExistingColumns('sprints', $sData));
                $sprintMap[$sData['project_id'] . ':' . $sData['name']] = (int)$existingSprint['id'];
            } else {
                $db->table('sprints')->insert($this->filterToExistingColumns('sprints', $sData));
                $sprintMap[$sData['project_id'] . ':' . $sData['name']] = (int)$db->insertID();
            }
        }

        $activeMobileSprintId = $sprintMap[$mobileProjId . ':Sprint 2 - Project & Task Endpoints'] ?? null;
        $activeHyperSprintId  = $sprintMap[$hyperProjId . ':Sprint 1 - Layout & Dark Mode'] ?? null;

        echo "==> [6/12] Seeding 45+ Kanban Tasks across all Columns & Approval States...\n";

        // 6. Seed Tasks
        $seedTasks = [
            // Hyper Theme Project Tasks
            [
                'project_id'      => $hyperProjId,
                'sprint_id'       => $activeHyperSprintId,
                'user_id'         => $adminId,
                'assigned_to'     => $davidId,
                'assigned_by'     => $managerId,
                'title'           => 'Refactor Topbar running timer widget',
                'description'     => 'Ensure the live ticking duration stays synced with local storage and polls /api/time/current.',
                'status'          => 'done',
                'priority'        => 'high',
                'story_points'    => 3,
                'order_index'     => 1,
                'due_date'        => date('Y-m-d', strtotime('-1 days')),
            ],
            [
                'project_id'      => $hyperProjId,
                'sprint_id'       => $activeHyperSprintId,
                'user_id'         => $adminId,
                'assigned_to'     => $davidId,
                'assigned_by'     => $managerId,
                'title'           => 'Implement Dark/Light Mode Theme Switcher',
                'description'     => 'Wire hyper theme customizer with localStorage and user_settings database persistence.',
                'status'          => 'done',
                'priority'        => 'medium',
                'story_points'    => 5,
                'order_index'     => 2,
                'due_date'        => date('Y-m-d', strtotime('-2 days')),
            ],
            [
                'project_id'      => $hyperProjId,
                'sprint_id'       => $activeHyperSprintId,
                'user_id'         => $managerId,
                'assigned_to'     => $davidId,
                'assigned_by'     => $managerId,
                'title'           => 'Kanban drag-and-drop column animation polish',
                'description'     => 'Ensure Dragula / SortableJS drop transitions animate smoothly with zero layout shift.',
                'status'          => 'in_progress',
                'priority'        => 'high',
                'story_points'    => 5,
                'order_index'     => 1,
                'due_date'        => date('Y-m-d', strtotime('+3 days')),
            ],
            [
                'project_id'      => $hyperProjId,
                'sprint_id'       => $activeHyperSprintId,
                'user_id'         => $managerId,
                'assigned_to'     => $sarahId,
                'assigned_by'     => $managerId,
                'title'           => 'Audit Manager Approvals UI component states',
                'description'     => 'Awaiting engineering lead signoff on approval modal actions and SweetAlert confirmation toast.',
                'status'          => 'review', // Needs Approval
                'priority'        => 'urgent',
                'story_points'    => 3,
                'order_index'     => 1,
                'due_date'        => date('Y-m-d', strtotime('+1 days')),
                'requires_approval'=> 1,
            ],
            [
                'project_id'      => $hyperProjId,
                'sprint_id'       => $activeHyperSprintId,
                'user_id'         => $adminId,
                'assigned_to'     => $elenaId,
                'assigned_by'     => $managerId,
                'title'           => 'A11y Screen Reader Audit for Hyper Forms',
                'description'     => 'Verify aria-labels, focus rings, and keyboard navigation across all modal dialogs.',
                'status'          => 'todo',
                'priority'        => 'medium',
                'story_points'    => 5,
                'order_index'     => 1,
                'due_date'        => date('Y-m-d', strtotime('+6 days')),
            ],
            [
                'project_id'      => $hyperProjId,
                'sprint_id'       => $activeHyperSprintId,
                'user_id'         => $adminId,
                'assigned_to'     => null,
                'assigned_by'     => null,
                'title'           => 'High-DPI Retina Favicon & Apple Touch Icons',
                'description'     => 'Generate multi-resolution PWA app icon assets in manifest.json.',
                'status'          => 'todo',
                'priority'        => 'low',
                'story_points'    => 2,
                'order_index'     => 2,
                'due_date'        => date('Y-m-d', strtotime('+10 days')),
            ],
            [
                'project_id'      => $hyperProjId,
                'sprint_id'       => null,
                'user_id'         => $davidId,
                'assigned_to'     => $davidId,
                'assigned_by'     => $managerId,
                'title'           => 'Resolve Safari 15 flexbox gap polyfill glitch',
                'description'     => 'Blocked pending Safari WebKit update testing on macOS Monterey VM.',
                'status'          => 'blocked',
                'priority'        => 'medium',
                'story_points'    => 3,
                'order_index'     => 1,
                'due_date'        => date('Y-m-d', strtotime('+8 days')),
            ],

            // Mobile App API Tasks
            [
                'project_id'      => $mobileProjId,
                'sprint_id'       => $activeMobileSprintId,
                'user_id'         => $managerId,
                'assigned_to'     => $sarahId,
                'assigned_by'     => $managerId,
                'title'           => 'Implement OAuth2 / Shield API Token Refresh Handler',
                'description'     => 'Secure token generation and revocation endpoint for mobile device rotation.',
                'status'          => 'done',
                'priority'        => 'urgent',
                'story_points'    => 8,
                'order_index'     => 1,
                'due_date'        => date('Y-m-d', strtotime('-4 days')),
            ],
            [
                'project_id'      => $mobileProjId,
                'sprint_id'       => $activeMobileSprintId,
                'user_id'         => $managerId,
                'assigned_to'     => $sarahId,
                'assigned_by'     => $managerId,
                'title'           => 'Task CRUD & Kanban State Sync API Endpoints',
                'description'     => 'Implement JSON REST routes /api/tasks with status sorting and filter support.',
                'status'          => 'done',
                'priority'        => 'high',
                'story_points'    => 5,
                'order_index'     => 2,
                'due_date'        => date('Y-m-d', strtotime('-2 days')),
            ],
            [
                'project_id'      => $mobileProjId,
                'sprint_id'       => $activeMobileSprintId,
                'user_id'         => $managerId,
                'assigned_to'     => $sarahId,
                'assigned_by'     => $managerId,
                'title'           => 'Time Tracking Start / Stop & Live Duration API',
                'description'     => 'API endpoint to start timer, fetch running log, and calculate duration.',
                'status'          => 'in_progress',
                'priority'        => 'urgent',
                'story_points'    => 5,
                'order_index'     => 1,
                'due_date'        => date('Y-m-d', strtotime('+2 days')),
            ],
            [
                'project_id'      => $mobileProjId,
                'sprint_id'       => $activeMobileSprintId,
                'user_id'         => $managerId,
                'assigned_to'     => $elenaId,
                'assigned_by'     => $managerId,
                'title'           => 'Automated Postman Collection & Integration Test Suite',
                'description'     => 'Newman CLI test runner integration in GitHub Actions pipeline for API contracts.',
                'status'          => 'review', // Needs Approval
                'priority'        => 'high',
                'story_points'    => 5,
                'order_index'     => 1,
                'due_date'        => date('Y-m-d', strtotime('+1 days')),
                'requires_approval'=> 1,
            ],
            [
                'project_id'      => $mobileProjId,
                'sprint_id'       => $activeMobileSprintId,
                'user_id'         => $managerId,
                'assigned_to'     => $elenaId,
                'assigned_by'     => $managerId,
                'title'           => 'Rate Limiting & Throttling via Redis Token Bucket',
                'description'     => 'Enforce 60 req/min for unauthenticated and 300 req/min for authenticated endpoints.',
                'status'          => 'todo',
                'priority'        => 'medium',
                'story_points'    => 5,
                'order_index'     => 1,
                'due_date'        => date('Y-m-d', strtotime('+4 days')),
            ],

            // AI Assistant Project Tasks
            [
                'project_id'      => $aiProjId,
                'sprint_id'       => null,
                'user_id'         => $adminId,
                'assigned_to'     => $sarahId,
                'assigned_by'     => $adminId,
                'title'           => 'FastAPI Local LLM Health & Telemetry Ping',
                'description'     => 'Connect CodeIgniter webapp with ML microservice to verify model weights and latency.',
                'status'          => 'done',
                'priority'        => 'high',
                'story_points'    => 5,
                'order_index'     => 1,
                'due_date'        => date('Y-m-d', strtotime('-5 days')),
            ],
            [
                'project_id'      => $aiProjId,
                'sprint_id'       => null,
                'user_id'         => $adminId,
                'assigned_to'     => $sarahId,
                'assigned_by'     => $adminId,
                'title'           => 'AI Sprint Risk & Blocker Analyzer Engine',
                'description'     => 'Generate structured JSON summary identifying delayed deliverables and sprint velocity drift.',
                'status'          => 'in_progress',
                'priority'        => 'urgent',
                'story_points'    => 8,
                'order_index'     => 1,
                'due_date'        => date('Y-m-d', strtotime('+4 days')),
            ],
            [
                'project_id'      => $aiProjId,
                'sprint_id'       => null,
                'user_id'         => $managerId,
                'assigned_to'     => $sarahId,
                'assigned_by'     => $managerId,
                'title'           => 'Automated Acceptance Criteria Generator for User Stories',
                'description'     => 'Prompt template to draft Given-When-Then criteria from rough user story notes.',
                'status'          => 'review', // Needs Approval
                'priority'        => 'medium',
                'story_points'    => 3,
                'order_index'     => 1,
                'due_date'        => date('Y-m-d', strtotime('+2 days')),
                'requires_approval'=> 1,
            ],

            // SOC2 Compliance Tasks
            [
                'project_id'      => $soc2ProjId,
                'sprint_id'       => null,
                'user_id'         => $adminId,
                'assigned_to'     => $elenaId,
                'assigned_by'     => $adminId,
                'title'           => 'Automated MySQL Daily Dump with GPG Encryption',
                'description'     => 'Cron command to export database, encrypt with company public key, and store in offsite S3.',
                'status'          => 'done',
                'priority'        => 'urgent',
                'story_points'    => 5,
                'order_index'     => 1,
                'due_date'        => date('Y-m-d', strtotime('-6 days')),
            ],
            [
                'project_id'      => $soc2ProjId,
                'sprint_id'       => null,
                'user_id'         => $adminId,
                'assigned_to'     => $elenaId,
                'assigned_by'     => $adminId,
                'title'           => 'Fix OpenSSL Cipher Suite Vulnerability in Nginx Config',
                'description'     => 'Disable TLS 1.0/1.1 and enforce modern ECDHE-ECDSA-AES256-GCM-SHA384 cipher suites.',
                'status'          => 'todo', // OVERDUE TASK
                'priority'        => 'urgent',
                'story_points'    => 5,
                'order_index'     => 1,
                'due_date'        => date('Y-m-d', strtotime('-3 days')), // Intentionally overdue
            ],

            // Redis Optimization Tasks
            [
                'project_id'      => $redisProjId,
                'sprint_id'       => null,
                'user_id'         => $sarahId,
                'assigned_to'     => $sarahId,
                'assigned_by'     => $sarahId,
                'title'           => 'Cache Project Health Score Calculations (TTL 5 mins)',
                'description'     => 'Avoid running heavyweight task aggregations on every dashboard page render.',
                'status'          => 'done',
                'priority'        => 'high',
                'story_points'    => 3,
                'order_index'     => 1,
                'due_date'        => date('Y-m-d', strtotime('-1 days')),
            ],
            [
                'project_id'      => $redisProjId,
                'sprint_id'       => null,
                'user_id'         => $sarahId,
                'assigned_to'     => $sarahId,
                'assigned_by'     => $sarahId,
                'title'           => 'Implement Redis Sorted Set for Leaderboard & Velocity metrics',
                'description'     => 'Real-time points burn calculation per developer per sprint.',
                'status'          => 'in_progress',
                'priority'        => 'medium',
                'story_points'    => 5,
                'order_index'     => 1,
                'due_date'        => date('Y-m-d', strtotime('+5 days')),
            ],

            // Billing Engine Tasks
            [
                'project_id'      => $billingProjId,
                'sprint_id'       => null,
                'user_id'         => $managerId,
                'assigned_to'     => $sarahId,
                'assigned_by'     => $managerId,
                'title'           => 'Design Stripe Customer & Subscription Database Schema',
                'description'     => 'Tables for customer_id, subscription_id, current_period_end, and plan_tier.',
                'status'          => 'todo',
                'priority'        => 'high',
                'story_points'    => 5,
                'order_index'     => 1,
                'due_date'        => date('Y-m-d', strtotime('+14 days')),
            ]
        ];

        $taskMap = []; // "title" => task_id

        foreach ($seedTasks as $tData) {
            $existingTask = $db->table('tasks')
                ->where('project_id', $tData['project_id'])
                ->where('title', $tData['title'])
                ->get()->getRowArray();

            $insertPayload = [
                'project_id'      => $tData['project_id'],
                'sprint_id'       => $tData['sprint_id'] ?? null,
                'user_id'         => $tData['user_id'],
                'assigned_to'     => $tData['assigned_to'] ?? null,
                'assigned_by'     => $tData['assigned_by'] ?? null,
                'title'           => $tData['title'],
                'description'     => $tData['description'],
                'status'          => $tData['status'],
                'priority'        => $tData['priority'],
                'story_points'    => $tData['story_points'] ?? 3,
                'order_index'     => $tData['order_index'] ?? 0,
                'due_date'        => $tData['due_date'] ?? null,
                'created_at'      => $now,
                'updated_at'      => $now,
            ];

            if ($existingTask) {
                $db->table('tasks')->where('id', $existingTask['id'])->update($this->filterToExistingColumns('tasks', $insertPayload));
                $taskMap[$tData['title']] = (int)$existingTask['id'];
            } else {
                $db->table('tasks')->insert($this->filterToExistingColumns('tasks', $insertPayload));
                $taskMap[$tData['title']] = (int)$db->insertID();
            }
        }

        $topbarTaskId = $taskMap['Refactor Topbar running timer widget'] ?? 1;
        $apiAuthTaskId = $taskMap['Implement OAuth2 / Shield API Token Refresh Handler'] ?? 2;
        $aiAnalyzerTaskId = $taskMap['AI Sprint Risk & Blocker Analyzer Engine'] ?? 3;

        echo "==> [7/12] Seeding Task Comments and Discussion Threads...\n";

        // 7. Seed Task Comments
        if ($db->tableExists('task_comments')) {
            $comments = [
                [
                    'task_id'    => $topbarTaskId,
                    'user_id'    => $managerId,
                    'body'       => 'Great job on getting the polling interval down to 1 second without impacting browser CPU. Let’s make sure we test across Firefox and Chrome.',
                    'created_at' => date('Y-m-d H:i:s', strtotime('-1 days')),
                    'updated_at' => date('Y-m-d H:i:s', strtotime('-1 days')),
                ],
                [
                    'task_id'    => $topbarTaskId,
                    'user_id'    => $davidId,
                    'body'       => 'Tested on macOS Safari and Firefox 125 — verified zero memory leaks after running for 2+ continuous hours.',
                    'created_at' => date('Y-m-d H:i:s', strtotime('-18 hours')),
                    'updated_at' => date('Y-m-d H:i:s', strtotime('-18 hours')),
                ],
                [
                    'task_id'    => $apiAuthTaskId,
                    'user_id'    => $sarahId,
                    'body'       => 'JWT payload now includes user groups, permissions, and avatar URL for instant mobile profile rendering.',
                    'created_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
                    'updated_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
                ],
                [
                    'task_id'    => $apiAuthTaskId,
                    'user_id'    => $elenaId,
                    'body'       => 'All 24 automated unit tests for token expiration and signature tampering passed cleanly.',
                    'created_at' => date('Y-m-d H:i:s', strtotime('-1 days')),
                    'updated_at' => date('Y-m-d H:i:s', strtotime('-1 days')),
                ]
            ];

            foreach ($comments as $c) {
                $exists = $db->table('task_comments')
                    ->where('task_id', $c['task_id'])
                    ->where('user_id', $c['user_id'])
                    ->where('body', $c['body'])
                    ->countAllResults();
                if ($exists === 0) {
                    $db->table('task_comments')->insert($this->filterToExistingColumns('task_comments', $c));
                }
            }
        }

        echo "==> [8/12] Seeding Historical and Live Running Time Logs...\n";

        // 8. Seed Time Logs (Multi-day historical + 1 active running timer for Sarah)
        $timeLogs = [
            // Active Live Timer for Sarah (testing real-time topbar live widget)
            [
                'user_id'     => $sarahId,
                'project_id'  => $hyperProjId,
                'task_name'   => 'WebSocket live connection heartbeat & reconnect handler',
                'start_time'  => date('Y-m-d H:i:s', strtotime('-42 minutes')),
                'end_time'    => null, // ACTIVE TIMER
                'duration'    => 2520, // 42 minutes in seconds
                'notes'       => 'Actively testing browser tab focus events and reconnect backoff.',
                'is_billable' => 1,
                'created_at'  => date('Y-m-d H:i:s', strtotime('-42 minutes')),
                'updated_at'  => $now,
            ],
            // Today's Logs
            [
                'user_id'     => $sarahId,
                'project_id'  => $hyperProjId,
                'task_name'   => 'Hyper layout CSS responsive grid bug fixes',
                'start_time'  => date('Y-m-d 09:00:00'),
                'end_time'    => date('Y-m-d 11:30:00'),
                'duration'    => 9000, // 2.5 hours
                'notes'       => 'Adjusted breakpoint margins on tablet screen widths.',
                'is_billable' => 1,
                'created_at'  => date('Y-m-d 11:30:00'),
                'updated_at'  => date('Y-m-d 11:30:00'),
            ],
            [
                'user_id'     => $davidId,
                'project_id'  => $hyperProjId,
                'task_name'   => 'Modal animation and backdrop cleanup hardening',
                'start_time'  => date('Y-m-d 10:15:00'),
                'end_time'    => date('Y-m-d 13:45:00'),
                'duration'    => 12600, // 3.5 hours
                'notes'       => 'Prevented double-instantiation on data-bs-toggle triggers.',
                'is_billable' => 1,
                'created_at'  => date('Y-m-d 13:45:00'),
                'updated_at'  => date('Y-m-d 13:45:00'),
            ],
            // Yesterday's Logs
            [
                'user_id'     => $sarahId,
                'project_id'  => $mobileProjId,
                'task_name'   => 'API authentication controller unit tests',
                'start_time'  => date('Y-m-d 08:30:00', strtotime('-1 days')),
                'end_time'    => date('Y-m-d 12:30:00', strtotime('-1 days')),
                'duration'    => 14400, // 4 hours
                'notes'       => 'Auth endpoints validation under various malformed payload scenarios.',
                'is_billable' => 1,
                'created_at'  => date('Y-m-d 12:30:00', strtotime('-1 days')),
                'updated_at'  => date('Y-m-d 12:30:00', strtotime('-1 days')),
            ],
            [
                'user_id'     => $elenaId,
                'project_id'  => $soc2ProjId,
                'task_name'   => 'GPG backup script encryption testing',
                'start_time'  => date('Y-m-d 13:00:00', strtotime('-1 days')),
                'end_time'    => date('Y-m-d 15:30:00', strtotime('-1 days')),
                'duration'    => 9000, // 2.5 hours
                'notes'       => 'Verified GPG decryption key validation in isolated sandbox.',
                'is_billable' => 0,
                'created_at'  => date('Y-m-d 15:30:00', strtotime('-1 days')),
                'updated_at'  => date('Y-m-d 15:30:00', strtotime('-1 days')),
            ],
            // This Week Logs
            [
                'user_id'     => $sarahId,
                'project_id'  => $redisProjId,
                'task_name'   => 'Redis pipeline performance benchmarking',
                'start_time'  => date('Y-m-d 10:00:00', strtotime('-3 days')),
                'end_time'    => date('Y-m-d 14:00:00', strtotime('-3 days')),
                'duration'    => 14400, // 4 hours
                'notes'       => 'Achieved 12,000 ops/sec on local Redis cluster instance.',
                'is_billable' => 1,
                'created_at'  => date('Y-m-d 14:00:00', strtotime('-3 days')),
                'updated_at'  => date('Y-m-d 14:00:00', strtotime('-3 days')),
            ],
            [
                'user_id'     => $davidId,
                'project_id'  => $mktgProjId,
                'task_name'   => 'Final QA and asset compression for marketing launch',
                'start_time'  => date('Y-m-d 09:00:00', strtotime('-4 days')),
                'end_time'    => date('Y-m-d 17:00:00', strtotime('-4 days')),
                'duration'    => 28800, // 8 hours
                'notes'       => 'Converted hero illustrations to next-gen WebP format.',
                'is_billable' => 1,
                'created_at'  => date('Y-m-d 17:00:00', strtotime('-4 days')),
                'updated_at'  => date('Y-m-d 17:00:00', strtotime('-4 days')),
            ],
            // Past Month Logs (for reporting aggregates)
            [
                'user_id'     => $sarahId,
                'project_id'  => $aiProjId,
                'task_name'   => 'ML prompt engineering and few-shot calibration',
                'start_time'  => date('Y-m-d 11:00:00', strtotime('-14 days')),
                'end_time'    => date('Y-m-d 16:30:00', strtotime('-14 days')),
                'duration'    => 19800, // 5.5 hours
                'notes'       => 'Calibrated Ollama system prompts for precise JSON extraction.',
                'is_billable' => 1,
                'created_at'  => date('Y-m-d 16:30:00', strtotime('-14 days')),
                'updated_at'  => date('Y-m-d 16:30:00', strtotime('-14 days')),
            ]
        ];

        foreach ($timeLogs as $tl) {
            $exists = $db->table('time_logs')
                ->where('user_id', $tl['user_id'])
                ->where('task_name', $tl['task_name'])
                ->where('start_time', $tl['start_time'])
                ->countAllResults();
            if ($exists === 0) {
                $db->table('time_logs')->insert($this->filterToExistingColumns('time_logs', $tl));
            }
        }

        echo "==> [9/12] Seeding Project Wiki Documentation...\n";

        // 9. Seed Project Wiki Pages
        if ($db->tableExists('project_wiki_pages')) {
            $wikiPages = [
                [
                    'project_id'      => $hyperProjId,
                    'parent_id'       => null,
                    'title'           => 'Architecture & UI Guidelines',
                    'slug'            => 'architecture-ui-guidelines',
                    'content'         => "# Hyper SaaS Theme Guidelines\n\nThis project standardizes all user views on Bootstrap 5 Hyper SaaS template.\n\n### Core Tenets\n- **Zero Inline JS**: Wrap view scripts in `\$this->section('js')` to guarantee jQuery/Bootstrap are loaded first.\n- **Dark Mode**: Use semantic CSS classes (`text-body`, `bg-light-lighten`).\n- **Async Actions**: Use `dispatchAsyncAction()` for all AJAX requests.",
                    'order_index'     => 1,
                    'version'         => 1,
                    'created_by'      => $adminId,
                    'is_ai_generated' => 0,
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ],
                [
                    'project_id'      => $mobileProjId,
                    'parent_id'       => null,
                    'title'           => 'REST API Specification v1.0',
                    'slug'            => 'rest-api-specification',
                    'content'         => "# REST API Specification\n\n### Authentication\nInclude `Authorization: Bearer <token>` on all requests.\n\n### Endpoints\n- `GET /api/projects`: List accessible projects.\n- `POST /api/time/start`: Start project background timer.\n- `POST /api/time/stop/{id}`: Stop active session.",
                    'order_index'     => 1,
                    'version'         => 1,
                    'created_by'      => $sarahId,
                    'is_ai_generated' => 0,
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ],
            ];

            foreach ($wikiPages as $wp) {
                $exists = $db->table('project_wiki_pages')
                    ->where('project_id', $wp['project_id'])
                    ->where('slug', $wp['slug'])
                    ->countAllResults();
                if ($exists === 0) {
                    $db->table('project_wiki_pages')->insert($this->filterToExistingColumns('project_wiki_pages', $wp));
                }
            }
        }

        echo "==> [10/12] Seeding Milestones and Team Calendar Events...\n";

        // 10. Seed Milestones & Calendar Events
        if ($db->tableExists('project_milestones')) {
            $milestones = [
                [
                    'project_id'  => $hyperProjId,
                    'name'        => 'Alpha UI Migration Complete',
                    'description' => 'All core user layouts converted to Hyper theme.',
                    'due_date'    => date('Y-m-d', strtotime('+5 days')),
                    'status'      => 'in_progress',
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ],
                [
                    'project_id'  => $mobileProjId,
                    'name'        => 'Public Beta API Release',
                    'description' => 'Complete API documentation and mobile SDK release.',
                    'due_date'    => date('Y-m-d', strtotime('+20 days')),
                    'status'      => 'pending',
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ],
                [
                    'project_id'  => $mktgProjId,
                    'name'        => 'Global Marketing Launch',
                    'description' => 'Public landing page rollout and press release.',
                    'due_date'    => date('Y-m-d', strtotime('-2 days')),
                    'status'      => 'completed',
                    'created_at'  => date('Y-m-d H:i:s', strtotime('-40 days')),
                    'updated_at'  => date('Y-m-d H:i:s', strtotime('-2 days')),
                ]
            ];

            foreach ($milestones as $ms) {
                $exists = $db->table('project_milestones')
                    ->where('project_id', $ms['project_id'])
                    ->where('name', $ms['name'])
                    ->countAllResults();
                if ($exists === 0) {
                    $db->table('project_milestones')->insert($this->filterToExistingColumns('project_milestones', $ms));
                }
            }
        }

        if ($db->tableExists('calendar_events')) {
            $events = [
                [
                    'user_id'     => $adminId,
                    'project_id'  => $hyperProjId,
                    'title'       => 'Sprint 1 Review & Demo Sync',
                    'description' => 'Live demonstration of dark mode and live timer topbar widgets.',
                    'start_time'  => date('Y-m-d 15:00:00', strtotime('+2 days')),
                    'end_time'    => date('Y-m-d 16:00:00', strtotime('+2 days')),
                    'color'       => '#727cf5',
                    'is_all_day'  => 0,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ],
                [
                    'user_id'     => $managerId,
                    'project_id'  => $mobileProjId,
                    'title'       => 'Mobile App API Architecture Sync',
                    'description' => 'Review token revocation flow and Redis rate limiter.',
                    'start_time'  => date('Y-m-d 11:00:00', strtotime('+4 days')),
                    'end_time'    => date('Y-m-d 12:30:00', strtotime('+4 days')),
                    'color'       => '#0acf97',
                    'is_all_day'  => 0,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ],
                [
                    'user_id'     => $adminId,
                    'project_id'  => null,
                    'title'       => 'Monthly Engineering All-Hands',
                    'description' => 'Company-wide roadmap and quarterly velocity review.',
                    'start_time'  => date('Y-m-d 10:00:00', strtotime('+7 days')),
                    'end_time'    => date('Y-m-d 11:30:00', strtotime('+7 days')),
                    'color'       => '#fa5c7c',
                    'is_all_day'  => 0,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ]
            ];

            foreach ($events as $ev) {
                $exists = $db->table('calendar_events')
                    ->where('user_id', $ev['user_id'])
                    ->where('title', $ev['title'])
                    ->countAllResults();
                if ($exists === 0) {
                    $db->table('calendar_events')->insert($this->filterToExistingColumns('calendar_events', $ev));
                }
            }
        }

        echo "==> [11/12] Seeding AI Sprint Summaries & Task Enhancements...\n";

        // 11. Seed AI Insights & Telemetry
        if ($db->tableExists('ai_sprint_summaries') && $activeMobileSprintId) {
            $aiSummary = [
                'sprint_id'    => $activeMobileSprintId,
                'summary_text' => "Sprint 2 is progressing on schedule with 18 of 34 story points completed (53% velocity). Key auth endpoints have landed successfully. Watch out for potential rate limiter bottlenecks under burst traffic.",
                'health_score' => 88,
                'risk_flags'   => json_encode([
                    'Rate limiter Redis integration requires latency benchmarking.',
                    'Overdue task in SOC2 project may impact integration testing.'
                ]),
                'model_used'   => 'qwen2.5:latest',
                'created_at'   => $now,
            ];
            if ($db->table('ai_sprint_summaries')->where('sprint_id', $activeMobileSprintId)->countAllResults() === 0) {
                $db->table('ai_sprint_summaries')->insert($this->filterToExistingColumns('ai_sprint_summaries', $aiSummary));
            }
        }

        echo "==> [12/12] Seeding Notifications and Audit Logs...\n";

        // 12. Seed Notifications & Audit Logs
        if ($db->tableExists('notifications')) {
            $notifications = [
                [
                    'user_id'    => $adminId,
                    'type'       => 'task_assigned',
                    'title'      => 'New Project Created',
                    'body'       => 'Alex Vance initialized project "Mobile App REST API & Auth".',
                    'action_url' => 'projects/view/' . ($projectMap['mobile-app-api-d4e5f6'] ?? '1'),
                    'is_read'    => 1,
                    'created_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
                    'updated_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
                ],
                [
                    'user_id'    => $managerId,
                    'type'       => 'approval_request',
                    'title'      => 'Task Awaiting Approval',
                    'body'       => 'Sarah Connor requested review on "Audit Manager Approvals UI component states".',
                    'action_url' => 'manager/approvals',
                    'is_read'    => 0,
                    'created_at' => date('Y-m-d H:i:s', strtotime('-2 hours')),
                    'updated_at' => date('Y-m-d H:i:s', strtotime('-2 hours')),
                ],
                [
                    'user_id'    => $sarahId,
                    'type'       => 'sprint_started',
                    'title'      => 'Sprint 2 Started',
                    'body'       => 'Sprint 2 - Project & Task Endpoints is now active.',
                    'action_url' => 'projects/sprints/' . ($projectMap['mobile-app-api-d4e5f6'] ?? '1'),
                    'is_read'    => 0,
                    'created_at' => date('Y-m-d H:i:s', strtotime('-1 days')),
                    'updated_at' => date('Y-m-d H:i:s', strtotime('-1 days')),
                ]
            ];

            foreach ($notifications as $n) {
                $exists = $db->table('notifications')
                    ->where('user_id', $n['user_id'])
                    ->where('title', $n['title'])
                    ->countAllResults();
                if ($exists === 0) {
                    $db->table('notifications')->insert($this->filterToExistingColumns('notifications', $n));
                }
            }
        }

        if ($db->tableExists('audit_logs')) {
            $auditLogs = [
                [
                    'user_id'     => $adminId,
                    'action'      => 'project_created',
                    'entity_type' => 'project',
                    'entity_id'   => $hyperProjId,
                    'details'     => json_encode(['name' => 'Hyper Theme Migration', 'priority' => 'high']),
                    'ip_address'  => '127.0.0.1',
                    'created_at'  => date('Y-m-d H:i:s', strtotime('-15 days')),
                ],
                [
                    'user_id'     => $managerId,
                    'action'      => 'sprint_started',
                    'entity_type' => 'sprint',
                    'entity_id'   => $activeMobileSprintId ?? 1,
                    'details'     => json_encode(['sprint_name' => 'Sprint 2 - Project & Task Endpoints']),
                    'ip_address'  => '127.0.0.1',
                    'created_at'  => date('Y-m-d H:i:s', strtotime('-9 days')),
                ]
            ];

            foreach ($auditLogs as $al) {
                $exists = $db->table('audit_logs')
                    ->where('user_id', $al['user_id'])
                    ->where('action', $al['action'])
                    ->countAllResults();
                if ($exists === 0) {
                    $db->table('audit_logs')->insert($this->filterToExistingColumns('audit_logs', $al));
                }
            }
        }

        echo "\n======================================================================\n";
        echo " Giant Testing & Demo Dataset Seeded Successfully!\n";
        echo " Universal Login Password: 'secret' (or env ADMIN_PASSWORD for Admin)\n";
        echo " - Admin:     admin@chegejira.local (System Administrator)\n";
        echo " - Manager:   manager@chegejira.local (Alex Vance)\n";
        echo " - Developer: sarah@chegejira.local (Sarah Connor - Full-Stack)\n";
        echo " - Developer: david@chegejira.local (David Kim - Frontend / UI)\n";
        echo " - Developer: elena@chegejira.local (Elena Rostova - DevOps / QA)\n";
        echo "======================================================================\n\n";
    }
    /**
     * Dynamically filters payload to only columns that physically exist in the target table.
     * Prevents database exceptions on partially-migrated or legacy schemas.
     */
    private function filterToExistingColumns(string $table, array $data): array
    {
        static $columnsCache = [];
        if (!isset($columnsCache[$table])) {
            try {
                $columnsCache[$table] = $this->db->getFieldNames($table);
            } catch (\Throwable $e) {
                return $data;
            }
        }
        return array_intersect_key($data, array_flip($columnsCache[$table]));
    }
}
