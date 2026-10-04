<?= $this->extend('layouts/hyper/main') ?>

<?= $this->section('title') ?>Agile Dashboard • <?= esc(setting('App.siteName')) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- ApexCharts Dependency -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<div class="container-fluid py-3">

    <!-- Welcome & Action Header -->
    <div class="row mb-3 align-items-center">
        <div class="col-md-7">
            <div class="d-flex align-items-center">
                <div class="avatar-md bg-primary-lighten text-primary rounded-circle d-flex align-items-center justify-content-center me-3 font-24 fw-bold shadow-sm">
                    <?= strtoupper(substr($user->username ?? 'U', 0, 1)) ?>
                </div>
                <div>
                    <h4 class="page-title mb-1 fw-bold text-dark">
                        Welcome back, <?= esc($user->username ?? 'Team Member') ?>! 👋
                    </h4>
                    <p class="text-muted font-13 mb-0 d-flex align-items-center flex-wrap gap-2">
                        <span><i class="mdi mdi-calendar-clock text-primary me-1"></i> <?= date('l, F j, Y') ?></span>
                        <span class="badge bg-primary-lighten text-primary font-11 px-2 py-1">
                            <i class="mdi mdi-shield-account-outline me-1"></i><?= $isAdminOrManager ? 'Manager / Executive View' : 'Developer Workspace' ?>
                        </span>
                        <?php if ($stats['active_sprints'] > 0): ?>
                            <span class="badge bg-success-lighten text-success font-11 px-2 py-1">
                                <i class="mdi mdi-run-fast me-1"></i><?= $stats['active_sprints'] ?> Active Sprint<?= $stats['active_sprints'] > 1 ? 's' : '' ?>
                            </span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-5 text-md-end mt-3 mt-md-0">
            <div class="d-inline-flex gap-2 flex-wrap">
                <a href="<?= site_url('projects/create') ?>" class="btn btn-primary rounded-pill shadow-sm">
                    <i class="mdi mdi-plus-circle-outline me-1"></i> New Project
                </a>
                <a href="<?= site_url('kanban') ?>" class="btn btn-outline-primary rounded-pill shadow-sm">
                    <i class="mdi mdi-view-column-outline me-1"></i> Kanban
                </a>
                <?php if ($isAdminOrManager): ?>
                    <a href="<?= site_url('manager/team') ?>" class="btn btn-outline-secondary rounded-pill shadow-sm" title="Team Leaderboard & Workload">
                        <i class="mdi mdi-account-group-outline me-1"></i> Team
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- 4 Modern Executive KPI Metric Cards -->
    <div class="row g-3 mb-4">
        <!-- 1. Active Projects -->
        <div class="col-sm-6 col-xl-3">
            <div class="card widget-flat h-100 mb-0 shadow-sm border-0 border-top border-primary border-3">
                <div class="card-body">
                    <div class="float-end">
                        <div class="avatar-sm bg-primary-lighten text-primary rounded-circle d-flex align-items-center justify-content-center">
                            <i class="mdi mdi-folder-multiple font-22"></i>
                        </div>
                    </div>
                    <h6 class="text-muted text-uppercase font-12 fw-semibold mt-0" title="Active Projects">Projects Portfolio</h6>
                    <h3 class="mt-2 mb-1 fw-bold text-dark"><?= esc($stats['active_projects'] ?? 0) ?> <span class="font-14 text-muted fw-normal">/ <?= esc($stats['total_projects'] ?? 0) ?> Total</span></h3>
                    <div class="d-flex align-items-center gap-2 mt-2 font-12">
                        <span class="badge bg-success-lighten text-success"><i class="mdi mdi-check-circle-outline me-1"></i><?= esc($stats['completed_projects'] ?? 0) ?> Done</span>
                        <span class="badge bg-warning-lighten text-warning"><i class="mdi mdi-clock-outline me-1"></i><?= esc($stats['planning_projects'] ?? 0) ?> In Queue</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Sprint & Task Telemetry -->
        <div class="col-sm-6 col-xl-3">
            <div class="card widget-flat h-100 mb-0 shadow-sm border-0 border-top border-success border-3">
                <div class="card-body">
                    <div class="float-end">
                        <div class="avatar-sm bg-success-lighten text-success rounded-circle d-flex align-items-center justify-content-center">
                            <i class="mdi mdi-checkbox-marked-circle-outline font-22"></i>
                        </div>
                    </div>
                    <h6 class="text-muted text-uppercase font-12 fw-semibold mt-0" title="Task Completion Rate">Agile Task Throughput</h6>
                    <h3 class="mt-2 mb-1 fw-bold text-dark"><?= esc($stats['completion_rate'] ?? 0) ?>%</h3>
                    <div class="progress progress-sm my-2" style="height: 6px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: <?= esc($stats['completion_rate'] ?? 0) ?>%" aria-valuenow="<?= esc($stats['completion_rate'] ?? 0) ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <p class="mb-0 text-muted font-12">
                        <span class="text-success fw-semibold me-1"><i class="mdi mdi-check"></i><?= esc($stats['completed_tasks'] ?? 0) ?></span> of <?= esc($stats['total_tasks'] ?? 0) ?> tasks closed
                    </p>
                </div>
            </div>
        </div>

        <!-- 3. In-Flight & Approvals -->
        <div class="col-sm-6 col-xl-3">
            <div class="card widget-flat h-100 mb-0 shadow-sm border-0 border-top border-warning border-3">
                <div class="card-body">
                    <div class="float-end">
                        <div class="avatar-sm bg-warning-lighten text-warning rounded-circle d-flex align-items-center justify-content-center">
                            <i class="mdi mdi-progress-clock font-22"></i>
                        </div>
                    </div>
                    <h6 class="text-muted text-uppercase font-12 fw-semibold mt-0" title="In Flight & Approvals">Work In Progress</h6>
                    <h3 class="mt-2 mb-1 fw-bold text-dark"><?= esc($stats['in_progress_tasks'] ?? 0) ?> <span class="font-14 text-muted fw-normal">In Flight</span></h3>
                    <div class="d-flex align-items-center gap-2 mt-2 font-12">
                        <?php if (($stats['review_tasks'] ?? 0) > 0): ?>
                            <span class="badge bg-info-lighten text-info"><i class="mdi mdi-eye-outline me-1"></i><?= esc($stats['review_tasks']) ?> In Review</span>
                        <?php endif; ?>
                        <?php if (($stats['blocked_tasks'] ?? 0) > 0): ?>
                            <span class="badge bg-danger-lighten text-danger"><i class="mdi mdi-alert-octagon-outline me-1"></i><?= esc($stats['blocked_tasks']) ?> Blocked</span>
                        <?php endif; ?>
                        <?php if (($stats['overdue_tasks'] ?? 0) > 0): ?>
                            <span class="badge bg-danger-lighten text-danger"><i class="mdi mdi-clock-alert-outline me-1"></i><?= esc($stats['overdue_tasks']) ?> Overdue</span>
                        <?php endif; ?>
                        <?php if (($stats['review_tasks'] ?? 0) === 0 && ($stats['blocked_tasks'] ?? 0) === 0 && ($stats['overdue_tasks'] ?? 0) === 0): ?>
                            <span class="badge bg-success-lighten text-success"><i class="mdi mdi-check-all me-1"></i>Pipeline Clean</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Logged Effort -->
        <div class="col-sm-6 col-xl-3">
            <div class="card widget-flat h-100 mb-0 shadow-sm border-0 border-top border-info border-3">
                <div class="card-body">
                    <div class="float-end">
                        <div class="avatar-sm bg-info-lighten text-info rounded-circle d-flex align-items-center justify-content-center">
                            <i class="mdi mdi-timer-outline font-22"></i>
                        </div>
                    </div>
                    <h6 class="text-muted text-uppercase font-12 fw-semibold mt-0" title="Effort Tracked">Logged Effort</h6>
                    <h3 class="mt-2 mb-1 fw-bold text-dark"><?= esc($stats['weekly_hours'] ?? 0) ?> <span class="font-14 text-muted fw-normal">hrs this week</span></h3>
                    <p class="mb-0 text-muted font-12">
                        <span class="text-info fw-semibold"><i class="mdi mdi-history me-1"></i><?= esc($stats['total_hours'] ?? 0) ?> hrs</span> all-time recorded
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Interactive Charts & Agile Telemetry Row -->
    <div class="row g-3 mb-4">
        <!-- Task Status Breakdown (Donut) -->
        <div class="col-xl-5 col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                    <h5 class="mb-0 fw-bold font-15 text-dark">
                        <i class="mdi mdi-chart-donut text-primary me-2"></i> Task Status Distribution
                    </h5>
                    <span class="badge bg-light text-dark font-11 px-2 py-1"><?= $stats['total_tasks'] ?> Total Items</span>
                </div>
                <div class="card-body d-flex flex-column justify-content-center align-items-center p-3">
                    <?php if ($stats['total_tasks'] > 0): ?>
                        <div id="task-status-donut" style="min-height: 250px; width: 100%;"></div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <div class="avatar-lg bg-light rounded-circle mx-auto d-flex align-items-center justify-content-center mb-2">
                                <i class="mdi mdi-checkbox-blank-off-outline font-24 text-muted"></i>
                            </div>
                            <h6 class="text-muted fw-normal">No tasks logged yet</h6>
                            <a href="<?= site_url('projects') ?>" class="btn btn-xs btn-primary mt-2">Create Task in Project</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- 7-Day Velocity & Workload (Area / Bar) -->
        <div class="col-xl-7 col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                    <h5 class="mb-0 fw-bold font-15 text-dark">
                        <i class="mdi mdi-chart-bell-curve-cumulative text-success me-2"></i> 7-Day Agile Velocity & Activity
                    </h5>
                    <div class="d-flex align-items-center gap-2 font-11 text-muted">
                        <span class="d-inline-flex align-items-center"><span class="badge bg-success rounded-circle me-1" style="width: 8px; height: 8px;"></span> Completed</span>
                        <span class="d-inline-flex align-items-center"><span class="badge bg-primary rounded-circle me-1" style="width: 8px; height: 8px;"></span> Created</span>
                    </div>
                </div>
                <div class="card-body p-3">
                    <div id="velocity-trend-chart" style="min-height: 250px; width: 100%;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Row -->
    <div class="row g-3">
        <!-- Left Column: Active Projects Portfolio & Action Items (8 cols) -->
        <div class="col-xl-8 col-lg-7">

            <!-- Active Projects Portfolio -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                    <h5 class="mb-0 fw-bold font-15 text-dark">
                        <i class="mdi mdi-briefcase-outline text-primary me-2"></i> Active Projects Portfolio
                    </h5>
                    <a href="<?= site_url('projects') ?>" class="btn btn-xs btn-outline-primary rounded-pill">
                        View All Projects <i class="mdi mdi-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($projects)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 font-13">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Project</th>
                                        <th>Health</th>
                                        <th>Progress</th>
                                        <th>Status</th>
                                        <th class="text-end pe-4">Quick Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($projects as $proj): ?>
                                        <?php 
                                            $pId = is_array($proj) ? ($proj['id'] ?? '') : ($proj->id ?? '');
                                            $pSlug = is_array($proj) ? ($proj['slug'] ?? $pId) : ($proj->slug ?? $pId);
                                            $pName = is_array($proj) ? ($proj['name'] ?? 'Project') : ($proj->name ?? 'Project');
                                            $pStatus = is_array($proj) ? ($proj['status'] ?? 'in_progress') : ($proj->status ?? 'in_progress');
                                            $pProgress = is_array($proj) ? ($proj['progress'] ?? 0) : ($proj->progress ?? 0);
                                            $pColor = is_array($proj) ? ($proj['color'] ?? '#727cf5') : ($proj->color ?? '#727cf5');
                                            $health = is_array($proj) ? ($proj['health'] ?? []) : [];
                                            $healthScore = $health['score'] ?? 100;
                                            $healthBadge = $health['badge_class'] ?? 'bg-success-lighten text-success';
                                            $healthLabel = $health['label'] ?? 'Healthy';
                                            $healthIcon = $health['icon'] ?? 'mdi-check-circle-outline';

                                            $statusBadge = match($pStatus) {
                                                'completed' => 'bg-success-lighten text-success',
                                                'in_progress' => 'bg-primary-lighten text-primary',
                                                'planning', 'on_hold' => 'bg-warning-lighten text-warning',
                                                default => 'bg-secondary-lighten text-secondary',
                                            };
                                        ?>
                                        <tr>
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-xs me-2 d-flex align-items-center justify-content-center rounded" style="background-color: <?= esc($pColor) ?>20; color: <?= esc($pColor) ?>; font-weight: bold; width: 32px; height: 32px;">
                                                        <i class="mdi mdi-folder font-16"></i>
                                                    </div>
                                                    <div>
                                                        <a href="<?= site_url('projects/view/' . $pSlug) ?>" class="text-dark fw-semibold d-block text-truncate" style="max-width: 220px;">
                                                            <?= esc($pName) ?>
                                                        </a>
                                                        <span class="text-muted font-11">
                                                            <i class="mdi mdi-tag-outline me-1"></i>ID: #<?= esc($pId) ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge <?= $healthBadge ?> font-11 px-2 py-1">
                                                    <i class="mdi <?= $healthIcon ?> me-1"></i><?= $healthScore ?>% <?= $healthLabel ?>
                                                </span>
                                            </td>
                                            <td style="min-width: 140px;">
                                                <div class="d-flex align-items-center">
                                                    <div class="progress progress-sm flex-grow-1 me-2" style="height: 6px;">
                                                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?= (int)$pProgress ?>%" aria-valuenow="<?= (int)$pProgress ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                    <span class="fw-semibold font-12 text-muted"><?= (int)$pProgress ?>%</span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge <?= $statusBadge ?> font-11">
                                                    <?= ucfirst(str_replace('_', ' ', $pStatus)) ?>
                                                </span>
                                            </td>
                                            <td class="text-end pe-4">
                                                <div class="btn-group btn-group-sm">
                                                    <a href="<?= site_url('projects/kanban/' . $pSlug) ?>" class="btn btn-outline-primary" title="Kanban Board">
                                                        <i class="mdi mdi-view-column me-1"></i> Board
                                                    </a>
                                                    <a href="<?= site_url('projects/sprints/' . $pSlug) ?>" class="btn btn-outline-info" title="Sprint Planner">
                                                        <i class="mdi mdi-run-fast"></i>
                                                    </a>
                                                    <a href="<?= site_url('projects/view/' . $pSlug) ?>" class="btn btn-outline-secondary" title="Project Overview">
                                                        <i class="mdi mdi-eye"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <div class="avatar-lg bg-light rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3">
                                <i class="mdi mdi-folder-plus-outline font-28 text-muted"></i>
                            </div>
                            <h5 class="fw-semibold">No active projects yet</h5>
                            <p class="text-muted font-13 mb-3">Create your first project to configure sprint backlogs, repositories, and workflows.</p>
                            <a href="<?= site_url('projects/create') ?>" class="btn btn-primary btn-sm rounded-pill">
                                <i class="mdi mdi-plus me-1"></i> Create Project
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Priority Action Items / Task Workload -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                    <h5 class="mb-0 fw-bold font-15 text-dark">
                        <i class="mdi mdi-clipboard-list-outline text-warning me-2"></i> Priority Action Items & Tasks
                    </h5>
                    <span class="badge bg-warning-lighten text-warning font-11 px-2 py-1">Top Priorities</span>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($myTasks)): ?>
                        <div class="list-group list-group-flush font-13">
                            <?php foreach ($myTasks as $task): ?>
                                <?php 
                                    $tPriority = $task['priority'] ?? 'medium';
                                    $tStatus = $task['status'] ?? 'todo';
                                    $tDue = $task['due_date'] ?? null;
                                    $pName = $task['project_name'] ?? 'General';
                                    $pSlug = $task['project_slug'] ?? '';
                                    $isOverdue = !empty($tDue) && strtotime($tDue) < time();

                                    $priorityBadge = match($tPriority) {
                                        'critical' => 'bg-danger-lighten text-danger',
                                        'high' => 'bg-warning-lighten text-warning',
                                        'medium' => 'bg-info-lighten text-info',
                                        default => 'bg-secondary-lighten text-secondary',
                                    };
                                ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center py-3 px-4">
                                    <div class="d-flex align-items-start me-3">
                                        <div class="mt-1 me-3">
                                            <i class="mdi mdi-radiobox-blank text-muted font-18"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-dark mb-1">
                                                <?= esc($task['title'] ?? 'Untitled Task') ?>
                                            </div>
                                            <div class="d-flex align-items-center flex-wrap gap-2 font-11 text-muted">
                                                <span class="badge bg-light text-dark">
                                                    <i class="mdi mdi-folder-outline me-1"></i><?= esc($pName) ?>
                                                </span>
                                                <span class="badge <?= $priorityBadge ?>">
                                                    <i class="mdi mdi-flag-variant me-1"></i><?= ucfirst($tPriority) ?>
                                                </span>
                                                <span class="badge bg-secondary-lighten text-secondary">
                                                    <?= ucfirst(str_replace('_', ' ', $tStatus)) ?>
                                                </span>
                                                <?php if (!empty($tDue)): ?>
                                                    <span class="<?= $isOverdue ? 'text-danger fw-semibold' : 'text-muted' ?>">
                                                        <i class="mdi mdi-calendar-alert me-1"></i>Due: <?= date('M d', strtotime($tDue)) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <a href="<?= site_url('projects/kanban/' . ($pSlug ?: '')) ?>" class="btn btn-xs btn-outline-primary rounded-pill">
                                            <i class="mdi mdi-arrow-right-circle me-1"></i> Open
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <div class="avatar-sm bg-success-lighten text-success rounded-circle mx-auto d-flex align-items-center justify-content-center mb-2">
                                <i class="mdi mdi-check-all font-20"></i>
                            </div>
                            <h6 class="text-muted fw-normal mb-1">All action items cleared!</h6>
                            <p class="text-muted font-12 mb-0">No urgent pending tasks assigned at this moment.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <!-- Right Column: Sprints Spotlight, AI Assistant, Activity (4 cols) -->
        <div class="col-xl-4 col-lg-5">

            <!-- Active Sprints Spotlight -->
            <?php if (!empty($activeSprints)): ?>
                <div class="card shadow-sm border-0 mb-4 border-start border-success border-3">
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold font-15 text-dark">
                            <i class="mdi mdi-run-fast text-success me-2"></i> Active Sprint Spotlight
                        </h5>
                        <span class="badge bg-success text-white font-11 px-2 py-1">In Execution</span>
                    </div>
                    <div class="card-body p-3">
                        <?php foreach ($activeSprints as $asp): ?>
                            <div class="p-3 bg-light rounded mb-3 border">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-0 fw-bold text-dark"><?= esc($asp['name']) ?></h6>
                                        <small class="text-muted font-11"><i class="mdi mdi-folder-outline me-1"></i><?= esc($asp['project_name'] ?? 'Project') ?></small>
                                    </div>
                                    <span class="badge bg-warning-lighten text-warning font-11">
                                        <i class="mdi mdi-clock-outline me-1"></i><?= $asp['days_remaining'] ?>d left
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-1 font-12">
                                    <span class="text-muted">Burndown Progress</span>
                                    <span class="fw-bold text-success"><?= $asp['progress'] ?>% (<?= $asp['done_tasks'] ?>/<?= $asp['total_tasks'] ?>)</span>
                                </div>
                                <div class="progress progress-sm" style="height: 6px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: <?= $asp['progress'] ?>%"></div>
                                </div>
                                <div class="mt-2 text-end">
                                    <a href="<?= site_url('projects/sprints/' . ($asp['project_slug'] ?? '')) ?>" class="btn btn-xs btn-outline-success">
                                        Sprint Backlog <i class="mdi mdi-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- AI Agile Insights & Recommendations -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold font-15 text-dark">
                        <i class="mdi mdi-robot-excited-outline text-primary me-2"></i> AI Agile Insights
                    </h5>
                    <span class="badge bg-primary-lighten text-primary font-11">Live Assistant</span>
                </div>
                <div class="card-body p-3">
                    <?php if (!empty($aiInsights)): ?>
                        <div class="d-flex flex-column gap-2">
                            <?php foreach ($aiInsights as $insight): ?>
                                <div class="alert alert-<?= $insight['type'] ?> border-0 mb-0 p-3 d-flex align-items-start">
                                    <i class="mdi <?= $insight['icon'] ?> font-22 me-2 mt-1"></i>
                                    <div>
                                        <h6 class="fw-bold mb-1"><?= esc($insight['title']) ?></h6>
                                        <p class="font-12 mb-0"><?= esc($insight['message']) ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted font-13 mb-0">No active risk flags detected. Workspace operating normally.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Action Shortcuts -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0 fw-bold font-15 text-dark">
                        <i class="mdi mdi-view-grid-outline text-info me-2"></i> Workspace Shortcuts
                    </h5>
                </div>
                <div class="card-body p-3">
                    <div class="row g-2 text-center">
                        <div class="col-6">
                            <a href="<?= site_url('kanban') ?>" class="p-3 border rounded d-block text-body text-decoration-none bg-light-subtle hover-shadow">
                                <i class="mdi mdi-view-column font-24 text-primary d-block mb-1"></i>
                                <span class="font-12 fw-semibold">Kanban Board</span>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="<?= site_url('time') ?>" class="p-3 border rounded d-block text-body text-decoration-none bg-light-subtle hover-shadow">
                                <i class="mdi mdi-timer-outline font-24 text-warning d-block mb-1"></i>
                                <span class="font-12 fw-semibold">Time Tracking</span>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="<?= site_url('notes') ?>" class="p-3 border rounded d-block text-body text-decoration-none bg-light-subtle hover-shadow">
                                <i class="mdi mdi-note-text-outline font-24 text-success d-block mb-1"></i>
                                <span class="font-12 fw-semibold">Notes & Wiki</span>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="<?= site_url('manager/reports') ?>" class="p-3 border rounded d-block text-body text-decoration-none bg-light-subtle hover-shadow">
                                <i class="mdi mdi-file-chart-outline font-24 text-danger d-block mb-1"></i>
                                <span class="font-12 fw-semibold">Team Reports</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Real-Time Activity Feed -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold font-15 text-dark">
                        <i class="mdi mdi-history text-secondary me-2"></i> Recent Activity Stream
                    </h5>
                </div>
                <div class="card-body p-3">
                    <?php if (!empty($recentActivity)): ?>
                        <div class="timeline-alt pb-0">
                            <?php foreach ($recentActivity as $act): ?>
                                <div class="timeline-item mb-3">
                                    <i class="mdi <?= esc($act['icon'] ?? 'mdi-circle') ?> bg-<?= esc($act['color'] ?? 'primary') ?>-lighten text-<?= esc($act['color'] ?? 'primary') ?> timeline-icon font-14"></i>
                                    <div class="timeline-item-info ms-2">
                                        <span class="text-dark fw-semibold font-12 d-block"><?= esc($act['title']) ?></span>
                                        <p class="mb-1 font-11 text-muted"><?= esc($act['description']) ?></p>
                                        <small class="text-muted font-10"><i class="mdi mdi-clock-outline me-1"></i><?= date('M d, g:i A', strtotime($act['time'])) ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="mdi mdi-history font-24 text-muted mb-2 d-block"></i>
                            <p class="text-muted font-12 mb-0">No recent workspace activity recorded.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>

</div>

<!-- ApexCharts Script Initialization -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Task Status Donut Chart
    const statusData = <?= json_encode(array_values($statusBreakdown)) ?>;
    const statusLabels = <?= json_encode(array_keys($statusBreakdown)) ?>;
    const totalTasks = <?= (int)$stats['total_tasks'] ?>;

    if (document.getElementById('task-status-donut') && totalTasks > 0) {
        const donutOptions = {
            series: statusData,
            labels: statusLabels,
            chart: {
                type: 'donut',
                height: 250,
                fontFamily: 'inherit'
            },
            colors: ['#6c757d', '#727cf5', '#39afd1', '#0acf97', '#fa5c7c'],
            legend: {
                position: 'bottom',
                fontSize: '12px',
                markers: { radius: 12 }
            },
            dataLabels: {
                enabled: false
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '70%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Total Tasks',
                                fontSize: '12px',
                                color: '#6c757d',
                                formatter: function () {
                                    return totalTasks;
                                }
                            }
                        }
                    }
                }
            },
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: { width: 200 },
                    legend: { position: 'bottom' }
                }
            }]
        };

        const donutChart = new ApexCharts(document.getElementById('task-status-donut'), donutOptions);
        donutChart.render();
    }

    // 2. 7-Day Velocity & Workload Chart
    const velocityLabels = <?= json_encode($velocityTrend['labels']) ?>;
    const velocityCompleted = <?= json_encode($velocityTrend['completed']) ?>;
    const velocityCreated = <?= json_encode($velocityTrend['created']) ?>;

    if (document.getElementById('velocity-trend-chart')) {
        const velocityOptions = {
            series: [
                {
                    name: 'Tasks Completed',
                    data: velocityCompleted
                },
                {
                    name: 'Tasks Created',
                    data: velocityCreated
                }
            ],
            chart: {
                type: 'area',
                height: 250,
                toolbar: { show: false },
                fontFamily: 'inherit'
            },
            colors: ['#0acf97', '#727cf5'],
            dataLabels: { enabled: false },
            stroke: {
                curve: 'smooth',
                width: 2
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.45,
                    opacityTo: 0.05,
                    stops: [0, 90, 100]
                }
            },
            xaxis: {
                categories: velocityLabels,
                labels: {
                    style: { fontSize: '11px', colors: '#6c757d' }
                },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                min: 0,
                forceNiceScale: true,
                labels: {
                    style: { fontSize: '11px', colors: '#6c757d' }
                }
            },
            grid: {
                borderColor: '#f1f3fa',
                strokeDashArray: 4
            },
            legend: {
                show: false
            }
        };

        const velocityChart = new ApexCharts(document.getElementById('velocity-trend-chart'), velocityOptions);
        velocityChart.render();
    }
});
</script>

<?= $this->endSection() ?>
