<?= $this->extend('layouts/hyper/main') ?>

<?= $this->section('title') ?>Dashboard • <?= esc(setting('App.siteName')) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <a href="<?= site_url('projects/create') ?>" class="btn btn-primary rounded-pill">
                    <i class="mdi mdi-plus me-1"></i> New Project
                </a>
            </div>
            <h4 class="page-title"><i class="uil-home-alt me-2 text-primary"></i> Dashboard</h4>
        </div>
    </div>
</div>

<!-- Stat Cards -->
<div class="row">
    <div class="col-sm-6 col-xl-3 mb-3">
        <div class="card widget-flat h-100 mb-0 shadow-sm border-0">
            <div class="card-body">
                <div class="float-end">
                    <div class="avatar-sm bg-primary-lighten text-primary rounded-circle d-flex align-items-center justify-content-center">
                        <i class="uil-folder font-22"></i>
                    </div>
                </div>
                <h5 class="text-muted fw-normal mt-0" title="Total Projects">Total Projects</h5>
                <h3 class="mt-3 mb-1 fw-bold"><?= esc($stats['total'] ?? 0) ?></h3>
                <p class="mb-0 text-muted font-13">
                    <span class="text-primary me-1"><i class="mdi mdi-view-list"></i> All tracked</span>
                </p>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3 mb-3">
        <div class="card widget-flat h-100 mb-0 shadow-sm border-0">
            <div class="card-body">
                <div class="float-end">
                    <div class="avatar-sm bg-success-lighten text-success rounded-circle d-flex align-items-center justify-content-center">
                        <i class="uil-play-circle font-22"></i>
                    </div>
                </div>
                <h5 class="text-muted fw-normal mt-0" title="Active Projects">In Progress</h5>
                <h3 class="mt-3 mb-1 fw-bold"><?= esc($stats['active'] ?? 0) ?></h3>
                <p class="mb-0 text-muted font-13">
                    <span class="text-success me-1"><i class="mdi mdi-trending-up"></i> Active execution</span>
                </p>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3 mb-3">
        <div class="card widget-flat h-100 mb-0 shadow-sm border-0">
            <div class="card-body">
                <div class="float-end">
                    <div class="avatar-sm bg-warning-lighten text-warning rounded-circle d-flex align-items-center justify-content-center">
                        <i class="uil-clock font-22"></i>
                    </div>
                </div>
                <h5 class="text-muted fw-normal mt-0" title="Pending Projects">Pending / Planning</h5>
                <h3 class="mt-3 mb-1 fw-bold"><?= esc($stats['pending'] ?? 0) ?></h3>
                <p class="mb-0 text-muted font-13">
                    <span class="text-warning me-1"><i class="mdi mdi-pause-circle-outline"></i> On hold & queue</span>
                </p>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3 mb-3">
        <div class="card widget-flat h-100 mb-0 shadow-sm border-0">
            <div class="card-body">
                <div class="float-end">
                    <div class="avatar-sm bg-danger-lighten text-danger rounded-circle d-flex align-items-center justify-content-center">
                        <i class="uil-archive font-22"></i>
                    </div>
                </div>
                <h5 class="text-muted fw-normal mt-0" title="Archived Projects">Archived</h5>
                <h3 class="mt-3 mb-1 fw-bold"><?= esc($stats['archived'] ?? 0) ?></h3>
                <p class="mb-0 text-muted font-13">
                    <span class="text-danger me-1"><i class="mdi mdi-archive-outline"></i> Inactive</span>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Row -->
<div class="row">
    <!-- Left Column: Recent Projects & Weekly Focus -->
    <div class="col-xl-8 col-lg-7">
        
        <!-- Recent Projects Card -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header d-flex justify-content-between align-items-center bg-transparent border-bottom py-3">
                <h5 class="header-title mb-0">
                    <i class="uil-briefcase me-1 text-primary"></i> Recent Projects
                </h5>
                <a href="<?= site_url('projects') ?>" class="btn btn-sm btn-outline-primary rounded-pill">
                    View All <i class="mdi mdi-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($projects)): ?>
                <div class="table-responsive">
                    <table class="table table-hover table-centered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Project</th>
                                <th>Status</th>
                                <th>Progress</th>
                                <th>Updated</th>
                                <th class="text-end pe-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($projects as $project): ?>
                            <?php 
                                $pId = is_array($project) ? ($project['id'] ?? '') : ($project->id ?? '');
                                $pSlug = is_array($project) ? ($project['slug'] ?? $pId) : ($project->slug ?? $pId);
                                $pName = is_array($project) ? ($project['name'] ?? '') : ($project->name ?? '');
                                $pDesc = is_array($project) ? ($project['description'] ?? '') : ($project->description ?? '');
                                $pStatus = is_array($project) ? ($project['status'] ?? 'in_progress') : ($project->status ?? 'in_progress');
                                $pProgress = is_array($project) ? ($project['progress'] ?? 0) : ($project->progress ?? 0);
                                $pUpdated = is_array($project) ? ($project['updated_at'] ?? '') : ($project->updated_at ?? '');
                                $pColor = is_array($project) ? ($project['color'] ?? '#727cf5') : ($project->color ?? '#727cf5');
                                $pIcon = is_array($project) ? ($project['icon'] ?? 'uil-folder') : ($project->icon ?? 'uil-folder');
                                
                                $statusBadge = match($pStatus) {
                                    'completed' => 'bg-success-lighten text-success',
                                    'in_progress' => 'bg-primary-lighten text-primary',
                                    'planning', 'on_hold' => 'bg-warning-lighten text-warning',
                                    'abandoned' => 'bg-danger-lighten text-danger',
                                    default => 'bg-secondary-lighten text-secondary',
                                };
                            ?>
                            <tr>
                                <td class="ps-3">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-xs me-2 d-flex align-items-center justify-content-center rounded" style="background-color: <?= esc($pColor) ?>20; color: <?= esc($pColor) ?>;">
                                            <i class="fas <?= esc($pIcon) ?> font-14"></i>
                                        </div>
                                        <div>
                                            <a href="<?= site_url('projects/view/' . $pSlug) ?>" class="text-body fw-bold d-block">
                                                <?= esc($pName) ?>
                                            </a>
                                            <?php if (!empty($pDesc)): ?>
                                                <small class="text-muted d-block text-truncate" style="max-width: 250px;">
                                                    <?= esc($pDesc) ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge <?= $statusBadge ?> font-12">
                                        <?= ucfirst(str_replace('_', ' ', $pStatus)) ?>
                                    </span>
                                </td>
                                <td style="width: 140px;">
                                    <div class="d-flex align-items-center">
                                        <div class="progress progress-sm flex-grow-1 me-2" style="height: 6px;">
                                            <div class="progress-bar bg-primary" role="progressbar" style="width: <?= (int)$pProgress ?>%" aria-valuenow="<?= (int)$pProgress ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <small class="fw-semibold text-muted"><?= (int)$pProgress ?>%</small>
                                    </div>
                                </td>
                                <td>
                                    <small class="text-muted"><?= !empty($pUpdated) ? date('M d, Y', strtotime($pUpdated)) : 'N/A' ?></small>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= site_url('projects/view/' . $pSlug) ?>" class="btn btn-outline-secondary" title="View Details">
                                            <i class="mdi mdi-eye"></i>
                                        </a>
                                        <a href="<?= site_url('projects/kanban/' . $pSlug) ?>" class="btn btn-outline-info" title="Kanban Board">
                                            <i class="mdi mdi-view-column"></i>
                                        </a>
                                        <a href="<?= site_url('projects/edit/' . $pSlug) ?>" class="btn btn-outline-warning" title="Edit Project">
                                            <i class="mdi mdi-pencil"></i>
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
                        <i class="uil-folder-plus font-28 text-muted"></i>
                    </div>
                    <h5>No projects yet</h5>
                    <p class="text-muted font-14 mb-3">Start managing your tasks and repositories by creating your first project.</p>
                    <a href="<?= site_url('projects/create') ?>" class="btn btn-primary btn-sm rounded-pill">
                        <i class="mdi mdi-plus me-1"></i> Create Project
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Weekly Focus Section (if available) -->
        <?php if (!empty($weeklyFocus)): ?>
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-transparent border-bottom py-3">
                <h5 class="header-title mb-0">
                    <i class="uil-bullseye me-1 text-danger"></i> Weekly Focus Projects
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <?php foreach ($weeklyFocus as $focus): ?>
                    <?php 
                        $fId = is_array($focus) ? ($focus['id'] ?? '') : ($focus->id ?? '');
                        $fSlug = is_array($focus) ? ($focus['slug'] ?? $fId) : ($focus->slug ?? $fId);
                        $fName = is_array($focus) ? ($focus['name'] ?? '') : ($focus->name ?? '');
                        $fPriority = is_array($focus) ? ($focus['priority'] ?? 'medium') : ($focus->priority ?? 'medium');
                        $fProgress = is_array($focus) ? ($focus['progress'] ?? 0) : ($focus->progress ?? 0);
                        $fColor = is_array($focus) ? ($focus['color'] ?? '#727cf5') : ($focus->color ?? '#727cf5');
                        
                        $priBadge = match($fPriority) {
                            'critical' => 'bg-danger-lighten text-danger',
                            'high' => 'bg-warning-lighten text-warning',
                            'medium' => 'bg-info-lighten text-info',
                            default => 'bg-secondary-lighten text-secondary',
                        };
                    ?>
                    <div class="col-md-4">
                        <div class="p-3 border rounded h-100 bg-light-subtle">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="mb-0 text-truncate" style="max-width: 70%;">
                                    <a href="<?= site_url('projects/view/' . $fSlug) ?>" class="text-body fw-bold">
                                        <?= esc($fName) ?>
                                    </a>
                                </h6>
                                <span class="badge <?= $priBadge ?> font-11"><?= ucfirst($fPriority) ?></span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <small class="text-muted">Progress</small>
                                <small class="fw-bold text-primary"><?= (int)$fProgress ?>%</small>
                            </div>
                            <div class="progress progress-sm" style="height: 5px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: <?= (int)$fProgress ?>%"></div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <!-- Right Column: Recent Activity & Quick Shortcuts -->
    <div class="col-xl-4 col-lg-5">
        
        <!-- Quick Action Shortcuts -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-transparent border-bottom py-3">
                <h5 class="header-title mb-0">
                    <i class="uil-apps me-1 text-primary"></i> Quick Access
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-2 text-center">
                    <div class="col-6">
                        <a href="<?= site_url('projects/create') ?>" class="p-3 border rounded d-block text-body text-decoration-none hover-shadow bg-light-subtle">
                            <i class="uil-plus-circle font-24 text-primary d-block mb-1"></i>
                            <span class="font-13 fw-semibold">New Project</span>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="<?= site_url('kanban') ?>" class="p-3 border rounded d-block text-body text-decoration-none hover-shadow bg-light-subtle">
                            <i class="uil-columns font-24 text-info d-block mb-1"></i>
                            <span class="font-13 fw-semibold">Kanban Board</span>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="<?= site_url('time') ?>" class="p-3 border rounded d-block text-body text-decoration-none hover-shadow bg-light-subtle">
                            <i class="uil-stopwatch font-24 text-warning d-block mb-1"></i>
                            <span class="font-13 fw-semibold">Time Tracking</span>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="<?= site_url('notes') ?>" class="p-3 border rounded d-block text-body text-decoration-none hover-shadow bg-light-subtle">
                            <i class="uil-notes font-24 text-success d-block mb-1"></i>
                            <span class="font-13 fw-semibold">Scratch Notes</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity Stream -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-transparent border-bottom py-3">
                <h5 class="header-title mb-0">
                    <i class="uil-history me-1 text-primary"></i> Recent Activity
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($recentActivity)): ?>
                <div class="timeline-alt pb-0">
                    <?php foreach ($recentActivity as $act): ?>
                    <?php
                        $actIcon = $act['icon'] ?? 'fa-info-circle';
                        $actTime = $act['time'] ?? 'now';
                        $actTitle = $act['title'] ?? 'Activity';
                        $actDesc = $act['description'] ?? '';
                    ?>
                    <div class="timeline-item">
                        <i class="mdi mdi-circle bg-primary-lighten text-primary timeline-icon"></i>
                        <div class="timeline-item-info">
                            <span class="text-primary fw-bold font-13 d-block"><?= esc($actTitle) ?></span>
                            <p class="mb-0 font-13 text-muted"><?= esc($actDesc) ?></p>
                            <small class="text-muted font-11"><?= date('M d, Y h:i A', strtotime($actTime)) ?></small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="text-center py-4">
                    <i class="uil-history-alt font-24 text-muted mb-2 d-block"></i>
                    <p class="text-muted font-13 mb-0">No recent activity logged yet.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<?= $this->endSection() ?>
