<?= $this->extend('layouts/hyper/main') ?>

<?= $this->section('title') ?>Projects • <?= esc(setting('App.siteName')) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <a href="<?= site_url('projects/health') ?>" class="btn btn-outline-danger rounded-pill me-1">
                    <i class="mdi mdi-heart-pulse me-1"></i> Health Audit
                </a>
                <a href="<?= site_url('projects/create') ?>" class="btn btn-primary rounded-pill">
                    <i class="mdi mdi-plus me-1"></i> New Project
                </a>
            </div>
            <h4 class="page-title"><i class="mdi mdi-briefcase-outline me-2 text-primary"></i> Projects Workspace</h4>
        </div>
    </div>
</div>

<!-- Stat Cards -->
<div class="row">
    <div class="col-sm-6 col-xl-3">
        <div class="card widget-flat">
            <div class="card-body">
                <div class="float-end">
                    <div class="avatar-sm bg-primary-lighten text-primary rounded-circle d-flex align-items-center justify-content-center">
                        <i class="mdi mdi-folder-outline font-22"></i>
                    </div>
                </div>
                <h5 class="text-muted fw-normal mt-0" title="Total Projects">Total Projects</h5>
                <h3 class="mt-3 mb-1 fw-bold"><?= $stats['total'] ?></h3>
                <p class="mb-0 text-muted font-13">
                    <span class="text-primary me-1"><i class="mdi mdi-view-list"></i> All tracked</span>
                </p>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card widget-flat">
            <div class="card-body">
                <div class="float-end">
                    <div class="avatar-sm bg-success-lighten text-success rounded-circle d-flex align-items-center justify-content-center">
                        <i class="mdi mdi-play-circle-outline font-22"></i>
                    </div>
                </div>
                <h5 class="text-muted fw-normal mt-0" title="Active Projects">In Progress</h5>
                <h3 class="mt-3 mb-1 fw-bold"><?= $stats['active'] ?></h3>
                <p class="mb-0 text-muted font-13">
                    <span class="text-success me-1"><i class="mdi mdi-trending-up"></i> Active execution</span>
                </p>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card widget-flat">
            <div class="card-body">
                <div class="float-end">
                    <div class="avatar-sm bg-warning-lighten text-warning rounded-circle d-flex align-items-center justify-content-center">
                        <i class="mdi mdi-clock-outline font-22"></i>
                    </div>
                </div>
                <h5 class="text-muted fw-normal mt-0" title="Pending Projects">Pending / Planning</h5>
                <h3 class="mt-3 mb-1 fw-bold"><?= $stats['pending'] ?></h3>
                <p class="mb-0 text-muted font-13">
                    <span class="text-warning me-1"><i class="mdi mdi-pause-circle-outline"></i> On hold & queue</span>
                </p>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card widget-flat">
            <div class="card-body">
                <div class="float-end">
                    <div class="avatar-sm bg-danger-lighten text-danger rounded-circle d-flex align-items-center justify-content-center">
                        <i class="mdi mdi-archive-outline font-22"></i>
                    </div>
                </div>
                <h5 class="text-muted fw-normal mt-0" title="Archived Projects">Archived</h5>
                <h3 class="mt-3 mb-1 fw-bold"><?= $stats['archived'] ?></h3>
                <p class="mb-0 text-muted font-13">
                    <span class="text-danger me-1"><i class="mdi mdi-archive-outline"></i> Inactive</span>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Main Projects Card with Tabs -->
<div class="card shadow-sm border-0">
    <div class="card-body">
        
        <!-- Tab Navigation -->
        <ul class="nav nav-tabs nav-bordered mb-3" role="tablist">
            <li class="nav-item">
                <a href="#all" data-bs-toggle="tab" aria-expanded="true" class="nav-link active">
                    <i class="mdi mdi-format-list-bulleted me-1"></i> All Projects 
                    <span class="badge bg-primary-lighten text-primary ms-1"><?= $stats['total'] ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#active" data-bs-toggle="tab" aria-expanded="false" class="nav-link">
                    <i class="mdi mdi-progress-clock me-1"></i> Active 
                    <span class="badge bg-success-lighten text-success ms-1"><?= $stats['active'] ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#pending" data-bs-toggle="tab" aria-expanded="false" class="nav-link">
                    <i class="mdi mdi-timer-sand me-1"></i> Pending 
                    <span class="badge bg-warning-lighten text-warning ms-1"><?= $stats['pending'] ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#completed" data-bs-toggle="tab" aria-expanded="false" class="nav-link">
                    <i class="mdi mdi-check-circle-outline me-1"></i> Completed 
                    <span class="badge bg-info-lighten text-info ms-1"><?= $stats['completed'] ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#archived" data-bs-toggle="tab" aria-expanded="false" class="nav-link">
                    <i class="mdi mdi-archive-outline me-1"></i> Archived 
                    <span class="badge bg-secondary-lighten text-secondary ms-1"><?= $stats['archived'] ?></span>
                </a>
            </li>
        </ul>

        <?php
        $priority_classes = [
            'low'      => 'bg-secondary',
            'medium'   => 'bg-info',
            'high'     => 'bg-warning',
            'critical' => 'bg-danger'
        ];
        $status_classes = [
            'planning'    => 'bg-info',
            'in_progress' => 'bg-success',
            'testing'     => 'bg-purple',
            'completed'   => 'bg-primary',
            'on_hold'     => 'bg-warning',
            'abandoned'   => 'bg-danger'
        ];

        if (!function_exists('projectInitialsAvatarBadge')) {
            function projectInitialsAvatarBadge(array $project, string $size = 'avatar-sm'): string {
                $name = trim($project['name'] ?? 'Project');
                $clean = preg_replace('/[^a-zA-Z0-9\s]/', '', $name);
                $words = preg_split('/\s+/', trim($clean));
                if (!empty($words[0]) && !empty($words[1])) {
                    $initials = strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
                } else {
                    $initials = strtoupper(substr($clean, 0, 2) ?: 'PJ');
                }
                
                $palette = ['#727cf5', '#0acf97', '#fa5c7c', '#ffbc00', '#39afd1', '#6b5eae', '#e83e8c', '#fd7e14', '#20c997', '#02a8b5'];
                $hashIdx = abs(crc32($name)) % count($palette);
                $color = !empty($project['color']) ? $project['color'] : $palette[$hashIdx];

                $fontSize = ($size === 'avatar-xs') ? '10px' : '13px';
                
                return '<div class="' . $size . ' me-2 flex-shrink-0 d-inline-block align-middle">
                            <span class="avatar-title rounded-circle shadow-sm" style="background-color: ' . esc($color) . '; color: #fff; font-weight: 700; font-size: ' . $fontSize . ';">
                                ' . esc($initials) . '
                            </span>
                        </div>';
            }
        }

        if (!function_exists('renderUnifiedProjectsTable')) {
            function renderUnifiedProjectsTable(array $projects, string $emptyMessage, array $priority_classes, array $status_classes, string $pagerKey, $pager): string {
                ob_start();
                ?>
                <div class="table-responsive">
                    <table class="table table-hover table-centered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Project Name</th>
                                <th>Status</th>
                                <th style="width: 20%;">Progress</th>
                                <th>Priority</th>
                                <th>Due Date</th>
                                <th class="text-end pe-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($projects)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="mdi mdi-folder-open-outline font-28 d-block mb-2"></i>
                                        <h5><?= esc($emptyMessage) ?></h5>
                                        <p class="font-14 mb-3">Organize tasks and sprints with a new project workspace.</p>
                                        <a href="<?= site_url('projects/create') ?>" class="btn btn-primary btn-sm rounded-pill px-3">
                                            <i class="mdi mdi-plus me-1"></i> Create Project
                                        </a>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($projects as $project): ?>
                                    <?php $pTarget = !empty($project['slug']) ? $project['slug'] : $project['id']; ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?= projectInitialsAvatarBadge($project, 'avatar-sm') ?>
                                                <div>
                                                    <h5 class="m-0 font-14">
                                                        <a href="<?= site_url('projects/view/' . $pTarget) ?>" class="text-body fw-bold text-decoration-none">
                                                            <?= esc($project['name']) ?>
                                                        </a>
                                                    </h5>
                                                    <span class="text-muted font-12 text-truncate d-inline-block" style="max-width: 300px;">
                                                        <?= esc($project['description'] ?? 'No description provided.') ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge <?= $status_classes[$project['status']] ?? 'bg-secondary' ?>">
                                                <?= ucfirst(str_replace('_', ' ', $project['status'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                                    <div class="progress-bar <?= $project['progress'] >= 100 ? 'bg-success' : 'bg-primary' ?>" style="width: <?= (int)$project['progress'] ?>%"></div>
                                                </div>
                                                <span class="font-12 fw-bold"><?= (int)$project['progress'] ?>%</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge <?= $priority_classes[$project['priority']] ?? 'bg-secondary' ?>">
                                                <?= ucfirst($project['priority']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php 
                                            $hasDue = !empty($project['due_date']) && $project['due_date'] !== '0000-00-00' && $project['due_date'] !== '0000-00-00 00:00:00' && strtotime($project['due_date']) > 0;
                                            ?>
                                            <?php if ($hasDue): ?>
                                                <?php 
                                                $dueTimestamp = strtotime($project['due_date']);
                                                $isOverdue = $dueTimestamp < time() && !in_array($project['status'], ['completed', 'abandoned']);
                                                ?>
                                                <span class="font-13 fw-semibold <?= $isOverdue ? 'text-danger' : 'text-muted' ?>">
                                                    <i class="mdi <?= $isOverdue ? 'mdi-alert-circle-outline' : 'mdi-calendar-clock' ?> me-1"></i>
                                                    <?= date('d M Y', $dueTimestamp) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="font-12 text-muted fst-italic">No Due Date</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end pe-3">
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?= site_url('projects/view/' . $pTarget) ?>" class="btn btn-outline-primary" title="View Project">
                                                    <i class="mdi mdi-eye"></i>
                                                </a>
                                                <a href="<?= site_url('projects/kanban/' . $pTarget) ?>" class="btn btn-outline-info" title="Kanban Board">
                                                    <i class="mdi mdi-view-column"></i>
                                                </a>
                                                <a href="<?= site_url('projects/edit/' . $pTarget) ?>" class="btn btn-outline-warning" title="Edit">
                                                    <i class="mdi mdi-square-edit-outline"></i>
                                                </a>
                                                <?php if (!empty($project['is_archived'])): ?>
                                                    <a href="<?= site_url('projects/archive/' . $pTarget) ?>" class="btn btn-outline-success" title="Restore Project" onclick="return confirm('Restore this archived project?');">
                                                        <i class="mdi mdi-restore"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <a href="<?= site_url('projects/archive/' . $pTarget) ?>" class="btn btn-outline-danger" title="Toggle Archive" onclick="return confirm('Change archive status for this project?');">
                                                        <i class="mdi mdi-archive-outline"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if (!empty($pager)): ?>
                    <div class="mt-3">
                        <?= $pager->links($pagerKey, 'bootstrap_full') ?>
                    </div>
                <?php endif; ?>
                <?php
                return ob_get_clean();
            }
        }
        ?>

        <div class="tab-content">
            <!-- ALL PROJECTS TAB -->
            <div class="tab-pane show active" id="all">
                <?= renderUnifiedProjectsTable($all_projects, 'No projects found', $priority_classes, $status_classes, 'all', $pager) ?>
            </div>

            <!-- ACTIVE PROJECTS TAB -->
            <div class="tab-pane" id="active">
                <?= renderUnifiedProjectsTable($active_projects, 'No active projects currently in progress.', $priority_classes, $status_classes, 'active', $pager) ?>
            </div>

            <!-- PENDING PROJECTS TAB -->
            <div class="tab-pane" id="pending">
                <?= renderUnifiedProjectsTable($pending_projects, 'No pending or planning projects.', $priority_classes, $status_classes, 'pending', $pager) ?>
            </div>

            <!-- COMPLETED PROJECTS TAB -->
            <div class="tab-pane" id="completed">
                <?= renderUnifiedProjectsTable($completed_projects, 'No completed projects yet.', $priority_classes, $status_classes, 'completed', $pager) ?>
            </div>

            <!-- ARCHIVED PROJECTS TAB -->
            <div class="tab-pane" id="archived">
                <?= renderUnifiedProjectsTable($archived_projects, 'No archived projects found.', $priority_classes, $status_classes, 'archived', $pager) ?>
            </div>

        </div> <!-- end tab-content -->
    </div> <!-- end card-body -->
</div> <!-- end card -->

<!-- Project Categories & Tags Card -->
<div class="card shadow-sm border-0 mt-4">
    <div class="card-header bg-white py-3 border-bottom">
        <h5 class="mb-0 fw-bold"><i class="mdi mdi-tag-multiple-outline text-primary me-2"></i> Project Categories & Tags</h5>
    </div>
    <div class="card-body">
        <?php if (empty($tagStats)): ?>
            <span class="text-muted font-14">No categories configured yet. Add categories when creating or editing projects.</span>
        <?php else: ?>
            <div class="d-flex flex-wrap gap-2">
                <?php 
                $tag_colors = [
                    'web_app'   => 'bg-primary',
                    'api'       => 'bg-success',
                    'mobile'    => 'bg-info',
                    'learning'  => 'bg-warning text-dark',
                    'portfolio' => 'bg-purple',
                    'freelance' => 'bg-danger'
                ];
                foreach ($tagStats as $tag => $count): 
                    $color = $tag_colors[$tag] ?? 'bg-secondary';
                    $label = str_replace('_', ' ', ucfirst($tag));
                ?>
                    <span class="badge <?= $color ?> px-3 py-2 font-13">
                        <?= esc($label) ?> <span class="badge bg-light text-dark ms-1"><?= $count ?></span>
                    </span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>
