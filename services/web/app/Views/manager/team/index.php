<?= $this->extend('layouts/hyper/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid py-3">
    <!-- Page Header -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <h4 class="page-title mb-0 fw-bold">
                        <i class="mdi mdi-account-group-outline text-primary me-2 font-22"></i> Team Velocity & Performance Hub
                    </h4>
                    <p class="text-muted font-13 mb-0">Real-time contributor leaderboard, task velocity tracking, and active project team rosters.</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="<?= site_url('manage/tasks/assign') ?>" class="btn btn-sm btn-primary rounded-pill shadow-sm">
                        <i class="mdi mdi-plus-circle me-1"></i> Assign New Task
                    </a>
                    <a href="<?= site_url('manage/approvals') ?>" class="btn btn-sm btn-outline-info rounded-pill">
                        <i class="mdi mdi-clipboard-check-outline me-1"></i> Approvals Queue
                    </a>
                    <a href="<?= site_url('manage/reports') ?>" class="btn btn-sm btn-outline-secondary rounded-pill">
                        <i class="mdi mdi-file-chart-outline me-1"></i> Team Reports
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Top 3 Metric Cards -->
    <div class="row g-3 mb-4">
        <!-- Total Tasks -->
        <div class="col-sm-6 col-lg-4">
            <div class="card widget-flat border-0 shadow-sm h-100 rounded-3">
                <div class="card-body">
                    <div class="float-end">
                        <div class="avatar-sm bg-primary-lighten text-primary rounded-circle d-flex align-items-center justify-content-center shadow-none" style="width: 44px; height: 44px;">
                            <i class="mdi mdi-format-list-checks font-22"></i>
                        </div>
                    </div>
                    <h6 class="text-muted text-uppercase fw-bold font-12 mt-0 mb-2">Total Tasks</h6>
                    <h2 class="my-2 fw-bold text-dark"><?= number_format($totalTasks) ?></h2>
                    <p class="mb-0 text-muted font-12">
                        <span class="badge bg-primary-lighten text-primary font-11 me-1"><i class="mdi mdi-layers-outline"></i> Workspaces</span>
                        <span>Across all active projects</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Approved & Completed -->
        <div class="col-sm-6 col-lg-4">
            <div class="card widget-flat border-0 shadow-sm h-100 rounded-3">
                <div class="card-body">
                    <div class="float-end">
                        <div class="avatar-sm bg-success-lighten text-success rounded-circle d-flex align-items-center justify-content-center shadow-none" style="width: 44px; height: 44px;">
                            <i class="mdi mdi-checkbox-marked-circle-outline font-22"></i>
                        </div>
                    </div>
                    <h6 class="text-muted text-uppercase fw-bold font-12 mt-0 mb-2">Approved & Completed</h6>
                    <h2 class="my-2 fw-bold text-success"><?= number_format($approvedTasks) ?></h2>
                    <p class="mb-0 text-muted font-12">
                        <span class="badge bg-success-lighten text-success font-11 me-1"><i class="mdi mdi-trending-up"></i> <?= $completionRate ?>% Success</span>
                        <span>Resolved and verified</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Pending / In Progress -->
        <div class="col-sm-6 col-lg-4">
            <div class="card widget-flat border-0 shadow-sm h-100 rounded-3">
                <div class="card-body">
                    <div class="float-end">
                        <div class="avatar-sm bg-warning-lighten text-warning rounded-circle d-flex align-items-center justify-content-center shadow-none" style="width: 44px; height: 44px;">
                            <i class="mdi mdi-progress-clock font-22"></i>
                        </div>
                    </div>
                    <h6 class="text-muted text-uppercase fw-bold font-12 mt-0 mb-2">Pending / In Progress</h6>
                    <h2 class="my-2 fw-bold text-warning"><?= number_format($pendingTasks) ?></h2>
                    <p class="mb-0 text-muted font-12">
                        <span class="badge bg-warning-lighten text-warning font-11 me-1"><i class="mdi mdi-timer-sand"></i> <?= $pendingRate ?>% In Pipeline</span>
                        <span>Active or awaiting review</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($leaderboard) && count($leaderboard) >= 3): ?>
        <!-- Top 3 Podium Highlights -->
        <div class="row g-3 mb-4">
            <!-- Rank 2: Silver -->
            <div class="col-md-4 order-2 order-md-1">
                <?php $top2 = $leaderboard[1]; ?>
                <div class="card border-0 shadow-sm rounded-3 h-100 text-center" style="background: linear-gradient(180deg, #f8f9fa 0%, #ffffff 100%);">
                    <div class="card-body py-4">
                        <div class="mb-2">
                            <span class="badge rounded-pill px-3 py-1 font-12" style="background-color: #e2e8f0; color: #475569;">
                                <i class="mdi mdi-medal font-14 me-1" style="color: #64748b;"></i> 2nd Place · Silver
                            </span>
                        </div>
                        <div class="avatar-md mx-auto my-3 bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 58px; height: 58px; font-size: 20px; border: 3px solid #cbd5e1;">
                            <?= strtoupper(substr($top2['username'] ?? 'U', 0, 2)) ?>
                        </div>
                        <h5 class="fw-bold mb-1 text-dark text-truncate"><?= esc($top2['display_name']) ?></h5>
                        <p class="text-muted font-12 mb-3">@<?= esc($top2['username']) ?></p>

                        <div class="d-flex justify-content-center gap-3 font-13 mb-3">
                            <div>
                                <span class="d-block fw-bold text-success font-16"><?= $top2['completed_tasks'] ?></span>
                                <span class="text-muted font-11">Done</span>
                            </div>
                            <div class="border-start"></div>
                            <div>
                                <span class="d-block fw-bold text-dark font-16"><?= $top2['total_tasks'] ?></span>
                                <span class="text-muted font-11">Total</span>
                            </div>
                            <div class="border-start"></div>
                            <div>
                                <span class="d-block fw-bold text-primary font-16"><?= $top2['completion_rate'] ?>%</span>
                                <span class="text-muted font-11">Rate</span>
                            </div>
                        </div>

                        <div class="progress rounded-pill" style="height: 6px;">
                            <div class="progress-bar bg-secondary rounded-pill" style="width: <?= $top2['completion_rate'] ?>%;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rank 1: Gold (Centerpiece) -->
            <div class="col-md-4 order-1 order-md-2">
                <?php $top1 = $leaderboard[0]; ?>
                <div class="card border border-warning shadow-sm rounded-3 h-100 text-center" style="background: linear-gradient(180deg, #fffdf5 0%, #ffffff 100%);">
                    <div class="card-body py-4">
                        <div class="mb-2">
                            <span class="badge bg-warning text-dark rounded-pill px-3 py-1 font-12 fw-bold shadow-sm">
                                <i class="mdi mdi-trophy font-14 me-1 text-dark"></i> 1st Place · Champion
                            </span>
                        </div>
                        <div class="avatar-lg mx-auto my-3 bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center shadow" style="width: 68px; height: 68px; font-size: 24px; border: 4px solid #fde047;">
                            <?= strtoupper(substr($top1['username'] ?? 'U', 0, 2)) ?>
                        </div>
                        <h5 class="fw-bold mb-1 text-dark text-truncate"><?= esc($top1['display_name']) ?></h5>
                        <p class="text-muted font-12 mb-3">@<?= esc($top1['username']) ?></p>

                        <div class="d-flex justify-content-center gap-3 font-13 mb-3">
                            <div>
                                <span class="d-block fw-bold text-success font-18"><?= $top1['completed_tasks'] ?></span>
                                <span class="text-muted font-11">Done</span>
                            </div>
                            <div class="border-start"></div>
                            <div>
                                <span class="d-block fw-bold text-dark font-18"><?= $top1['total_tasks'] ?></span>
                                <span class="text-muted font-11">Total</span>
                            </div>
                            <div class="border-start"></div>
                            <div>
                                <span class="d-block fw-bold text-warning font-18"><?= $top1['completion_rate'] ?>%</span>
                                <span class="text-muted font-11">Rate</span>
                            </div>
                        </div>

                        <div class="progress rounded-pill" style="height: 8px;">
                            <div class="progress-bar bg-warning rounded-pill" style="width: <?= $top1['completion_rate'] ?>%;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rank 3: Bronze -->
            <div class="col-md-4 order-3 order-md-3">
                <?php $top3 = $leaderboard[2]; ?>
                <div class="card border-0 shadow-sm rounded-3 h-100 text-center" style="background: linear-gradient(180deg, #fbf7f4 0%, #ffffff 100%);">
                    <div class="card-body py-4">
                        <div class="mb-2">
                            <span class="badge rounded-pill px-3 py-1 font-12" style="background-color: #ffedd5; color: #9a3412;">
                                <i class="mdi mdi-medal font-14 me-1" style="color: #c2410c;"></i> 3rd Place · Bronze
                            </span>
                        </div>
                        <div class="avatar-md mx-auto my-3 text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 58px; height: 58px; font-size: 20px; background-color: #c2410c; border: 3px solid #fed7aa;">
                            <?= strtoupper(substr($top3['username'] ?? 'U', 0, 2)) ?>
                        </div>
                        <h5 class="fw-bold mb-1 text-dark text-truncate"><?= esc($top3['display_name']) ?></h5>
                        <p class="text-muted font-12 mb-3">@<?= esc($top3['username']) ?></p>

                        <div class="d-flex justify-content-center gap-3 font-13 mb-3">
                            <div>
                                <span class="d-block fw-bold text-success font-16"><?= $top3['completed_tasks'] ?></span>
                                <span class="text-muted font-11">Done</span>
                            </div>
                            <div class="border-start"></div>
                            <div>
                                <span class="d-block fw-bold text-dark font-16"><?= $top3['total_tasks'] ?></span>
                                <span class="text-muted font-11">Total</span>
                            </div>
                            <div class="border-start"></div>
                            <div>
                                <span class="d-block fw-bold text-primary font-16"><?= $top3['completion_rate'] ?>%</span>
                                <span class="text-muted font-11">Rate</span>
                            </div>
                        </div>

                        <div class="progress rounded-pill" style="height: 6px;">
                            <div class="progress-bar rounded-pill" style="width: <?= $top3['completion_rate'] ?>%; background-color: #c2410c;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Performance Leaderboard Table & Insights Row -->
    <div class="row g-3 mb-4">
        <!-- Worker Performance Leaderboard Table -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 rounded-3 h-100">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="mdi mdi-trophy-variant text-warning me-2 font-18"></i> Worker Performance Leaderboard
                        </h5>
                        <small class="text-muted">Ranked by verified completed tasks and team velocity score</small>
                    </div>
                    <span class="badge bg-light text-muted border font-12 px-2 py-1">Top Contributors</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-muted font-12 text-uppercase">
                                <tr>
                                    <th class="ps-4" style="width: 70px;">Rank</th>
                                    <th>Team Member</th>
                                    <th style="min-width: 170px;">Task Progress</th>
                                    <th class="text-center" style="width: 110px;">Pipeline</th>
                                    <th class="text-end pe-4" style="width: 140px;">Completed</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($leaderboard)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i class="mdi mdi-account-search font-36 d-block mb-2 opacity-50"></i>
                                            <p class="mb-0">No worker task activity recorded yet.</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php $rank = 1; foreach($leaderboard as $user): ?>
                                        <tr>
                                            <!-- Rank Column -->
                                            <td class="ps-4">
                                                <?php if($rank == 1): ?>
                                                    <span class="badge bg-warning text-dark rounded-circle p-2 shadow-sm" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;">
                                                        <i class="mdi mdi-trophy font-14"></i>
                                                    </span>
                                                <?php elseif($rank == 2): ?>
                                                    <span class="badge rounded-circle p-2 shadow-sm text-white" style="background-color: #64748b; width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;">
                                                        <i class="mdi mdi-medal font-14"></i>
                                                    </span>
                                                <?php elseif($rank == 3): ?>
                                                    <span class="badge rounded-circle p-2 shadow-sm text-white" style="background-color: #c2410c; width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;">
                                                        <i class="mdi mdi-medal font-14"></i>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-light text-muted border font-12 rounded-circle" style="width: 30px; height: 30px; display: inline-flex; align-items: center; justify-content: center;">
                                                        <?= $rank ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>

                                            <!-- Team Member Column -->
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm bg-primary-lighten text-primary rounded-circle d-flex align-items-center justify-content-center me-3 fw-bold font-13" style="width: 38px; height: 38px;">
                                                        <?= strtoupper(substr($user['username'] ?? 'U', 0, 2)) ?>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold text-dark font-14"><?= esc($user['display_name']) ?></div>
                                                        <small class="text-muted font-12">@<?= esc($user['username']) ?></small>
                                                    </div>
                                                </div>
                                            </td>

                                            <!-- Task Progress Column -->
                                            <td>
                                                <div class="d-flex justify-content-between align-items-center font-11 mb-1">
                                                    <span class="text-muted"><?= $user['completed_tasks'] ?> of <?= $user['total_tasks'] ?> Tasks</span>
                                                    <span class="fw-semibold text-primary"><?= $user['completion_rate'] ?>%</span>
                                                </div>
                                                <div class="progress rounded-pill" style="height: 6px;">
                                                    <div class="progress-bar <?= $user['completion_rate'] >= 75 ? 'bg-success' : ($user['completion_rate'] >= 40 ? 'bg-primary' : 'bg-warning') ?> rounded-pill" style="width: <?= $user['completion_rate'] ?>%;"></div>
                                                </div>
                                            </td>

                                            <!-- In Progress / Active Column -->
                                            <td class="text-center">
                                                <span class="badge bg-warning-lighten text-warning font-12 px-2 py-1">
                                                    <i class="mdi mdi-timer-sand-empty me-1"></i> <?= $user['active_tasks'] ?> Active
                                                </span>
                                            </td>

                                            <!-- Completed Tasks Column -->
                                            <td class="text-end pe-4">
                                                <span class="badge bg-success-lighten text-success font-13 px-3 py-1 rounded-pill fw-semibold">
                                                    <i class="mdi mdi-check-circle me-1"></i> <?= $user['completed_tasks'] ?> Done
                                                </span>
                                            </td>
                                        </tr>
                                    <?php $rank++; endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Insights Card -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 rounded-3 h-100 bg-white">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0 fw-bold text-dark"><i class="mdi mdi-lightbulb-on-outline text-warning me-2"></i> Agile Insights & Velocity</h5>
                </div>
                <div class="card-body d-flex flex-column justify-content-between p-4">
                    <div>
                        <div class="text-center mb-4">
                            <div class="avatar-lg bg-primary-lighten text-primary rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3 shadow-none" style="width: 64px; height: 64px;">
                                <i class="mdi mdi-chart-timeline-variant font-28"></i>
                            </div>
                            <h5 class="fw-bold text-dark mb-1">Performance Analytics</h5>
                            <p class="text-muted font-13">Evaluate team velocity, review milestone deliveries, and track individual worker engagement.</p>
                        </div>

                        <div class="border rounded-3 p-3 bg-light mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2 font-13">
                                <span class="text-muted">Overall Team Health</span>
                                <span class="badge bg-success-lighten text-success font-12"><i class="mdi mdi-check me-1"></i> Optimal</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2 font-13">
                                <span class="text-muted">Total Active Projects</span>
                                <span class="fw-bold text-dark"><?= count($projectsWithMembers ?? []) ?></span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center font-13">
                                <span class="text-muted">Sprint Completion Ratio</span>
                                <span class="fw-bold text-primary"><?= $completionRate ?>%</span>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <a href="<?= site_url('manage/reports') ?>" class="btn btn-primary rounded-pill py-2 font-13">
                            <i class="mdi mdi-file-pdf-box me-1"></i> Generate Team Reports
                        </a>
                        <a href="<?= site_url('manage/approvals') ?>" class="btn btn-outline-secondary rounded-pill py-2 font-13">
                            <i class="mdi mdi-check-all me-1"></i> Review Pending Approvals
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Projects & Assigned Team Roster Section -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="mdi mdi-folder-multiple-outline text-primary me-2 font-18"></i> Project Teams & Assigned Members
                        </h5>
                        <small class="text-muted">Direct breakdown of team member allocations per workspace</small>
                    </div>
                    <span class="badge bg-primary-lighten text-primary font-12 px-3 py-1 rounded-pill">
                        <?= count($projectsWithMembers ?? []) ?> Active Projects
                    </span>
                </div>
                <div class="card-body p-4">
                    <?php if (empty($projectsWithMembers)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="mdi mdi-folder-open-outline font-48 d-block mb-3 opacity-50"></i>
                            <h5 class="fw-bold text-dark">No Active Projects Found</h5>
                            <p class="font-13">Create a new project workspace to assign tasks and start monitoring team velocity.</p>
                            <a href="<?= site_url('projects/create') ?>" class="btn btn-sm btn-primary rounded-pill">
                                <i class="mdi mdi-plus me-1"></i> Create Project
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="row g-4">
                            <?php foreach ($projectsWithMembers as $proj): ?>
                                <div class="col-md-6 col-lg-4">
                                    <div class="card border shadow-none rounded-3 h-100 bg-white">
                                        <div class="card-body p-3 d-flex flex-column justify-content-between">
                                            <div>
                                                <!-- Project Status & Lead Header -->
                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                    <span class="badge font-11 px-2 py-1 rounded" style="background-color: <?= esc($proj['color'] ?? '#727cf5') ?>; color: #fff;">
                                                        <?= esc(strtoupper($proj['status'] ?? 'Active')) ?>
                                                    </span>
                                                    <small class="text-muted font-12">
                                                        <i class="mdi mdi-shield-account text-primary me-1"></i> <?= esc($proj['owner_name'] ?: 'Manager') ?>
                                                    </small>
                                                </div>

                                                <!-- Project Title -->
                                                <h5 class="card-title fw-bold text-truncate mb-2 font-15">
                                                    <a href="<?= site_url('projects/view/' . ($proj['slug'] ?: $proj['id'])) ?>" class="text-dark hover-primary text-decoration-none">
                                                        <?= esc($proj['name']) ?>
                                                    </a>
                                                </h5>
                                                
                                                <!-- Progress Bar -->
                                                <div class="mb-3">
                                                    <div class="d-flex justify-content-between font-12 text-muted mb-1">
                                                        <span>Progress</span>
                                                        <span class="fw-semibold text-dark"><?= $proj['done_tasks'] ?> / <?= $proj['total_tasks'] ?> tasks (<?= $proj['progress_pct'] ?>%)</span>
                                                    </div>
                                                    <div class="progress rounded-pill" style="height: 6px;">
                                                        <div class="progress-bar rounded-pill" style="width: <?= $proj['progress_pct'] ?>%; background-color: <?= esc($proj['color'] ?? '#727cf5') ?>;"></div>
                                                    </div>
                                                </div>

                                                <!-- Assigned Users Roster -->
                                                <h6 class="font-11 text-muted text-uppercase fw-bold mb-2">Assigned Members</h6>
                                                <?php if (empty($proj['members'])): ?>
                                                    <p class="font-12 text-muted mb-0 italic">No assigned members yet.</p>
                                                <?php else: ?>
                                                    <div class="d-flex flex-column gap-2 mb-3">
                                                        <?php foreach ($proj['members'] as $member): ?>
                                                            <div class="d-flex align-items-center justify-content-between p-2 rounded-2 bg-light font-12">
                                                                <div class="d-flex align-items-center">
                                                                    <div class="avatar-xs bg-primary text-white rounded-circle me-2 d-flex align-items-center justify-content-center font-10 fw-bold" style="width: 26px; height: 26px;">
                                                                        <?= strtoupper(substr($member['username'] ?? 'U', 0, 1)) ?>
                                                                    </div>
                                                                    <div>
                                                                        <div class="fw-semibold text-dark"><?= esc($member['display_name']) ?></div>
                                                                        <small class="text-muted font-10">@<?= esc($member['username']) ?></small>
                                                                    </div>
                                                                </div>
                                                                <span class="badge bg-white text-muted border font-11">
                                                                    <?= $member['completed_task_count'] ?> / <?= $member['task_count'] ?> Tasks
                                                                </span>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>

                                            <div class="border-top pt-2 mt-2 text-end">
                                                <a href="<?= site_url('projects/view/' . ($proj['slug'] ?: $proj['id'])) ?>" class="text-primary font-12 fw-semibold text-decoration-none">
                                                    View Project Workspace <i class="mdi mdi-arrow-right"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

