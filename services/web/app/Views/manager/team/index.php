<?= $this->extend('layouts/hyper/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-users text-primary me-2"></i> Team Dashboard & Leaderboard</h2>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 border-start border-primary border-4 h-100">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase mb-1">Total Tasks</h6>
                    <h3 class="mb-0 fw-bold"><?= $totalTasks ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 border-start border-success border-4 h-100">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase mb-1">Approved & Completed</h6>
                    <h3 class="mb-0 fw-bold text-success"><?= $approvedTasks ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 border-start border-warning border-4 h-100">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase mb-1">Pending / In Progress</h6>
                    <h3 class="mb-0 fw-bold text-warning"><?= $pendingTasks ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Projects & Team Roster Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-folder-open text-primary me-2"></i> Project Teams & Assigned Members</h5>
                    <span class="badge bg-primary-lighten text-primary font-12"><?= count($projectsWithMembers ?? []) ?> Active Projects</span>
                </div>
                <div class="card-body">
                    <?php if (empty($projectsWithMembers)): ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-project-diagram fa-3x mb-3"></i>
                            <p>No active project team rosters found.</p>
                        </div>
                    <?php else: ?>
                        <div class="row g-4">
                            <?php foreach ($projectsWithMembers as $proj): ?>
                                <div class="col-md-6 col-lg-4">
                                    <div class="card border h-100 shadow-none">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <span class="badge" style="background-color: <?= esc($proj['color'] ?? '#727cf5') ?>; color: #fff;">
                                                    <?= esc(strtoupper($proj['status'] ?? 'Active')) ?>
                                                </span>
                                                <small class="text-muted"><i class="fas fa-user-shield me-1"></i> Lead: <?= esc($proj['owner_name'] ?: 'Manager') ?></small>
                                            </div>
                                            <h5 class="card-title fw-bold text-truncate mb-2">
                                                <a href="<?= site_url('projects/view/' . ($proj['slug'] ?: $proj['id'])) ?>" class="text-dark">
                                                    <?= esc($proj['name']) ?>
                                                </a>
                                            </h5>
                                            
                                            <!-- Progress Bar -->
                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between font-12 text-muted mb-1">
                                                    <span>Completion</span>
                                                    <span><?= $proj['done_tasks'] ?> / <?= $proj['total_tasks'] ?> tasks (<?= $proj['progress_pct'] ?>%)</span>
                                                </div>
                                                <div class="progress" style="height: 6px;">
                                                    <div class="progress-bar rounded" style="width: <?= $proj['progress_pct'] ?>%; background-color: <?= esc($proj['color'] ?? '#727cf5') ?>;"></div>
                                                </div>
                                            </div>

                                            <!-- Assigned Users Roster -->
                                            <h6 class="font-12 text-muted text-uppercase fw-bold mb-2">Assigned Team Members</h6>
                                            <?php if (empty($proj['members'])): ?>
                                                <p class="font-12 text-muted mb-0">No assigned members yet.</p>
                                            <?php else: ?>
                                                <div class="d-flex flex-column gap-2">
                                                    <?php foreach ($proj['members'] as $member): ?>
                                                        <div class="d-flex align-items-center justify-content-between p-2 rounded bg-light">
                                                            <div class="d-flex align-items-center">
                                                                <div class="avatar-xs bg-primary text-white rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-size: 11px;">
                                                                    <?= strtoupper(substr($member['username'] ?? 'U', 0, 1)) ?>
                                                                </div>
                                                                <div>
                                                                    <div class="font-13 fw-semibold text-body"><?= esc($member['display_name']) ?></div>
                                                                    <small class="text-muted font-11">@<?= esc($member['username']) ?></small>
                                                                </div>
                                                            </div>
                                                            <span class="badge bg-secondary-lighten text-secondary font-11">
                                                                <?= $member['completed_task_count'] ?> / <?= $member['task_count'] ?> Tasks
                                                            </span>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
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

    <!-- Performance Leaderboard -->
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-trophy text-warning me-2"></i> Worker Performance Leaderboard</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4" style="width: 80px;">Rank</th>
                                    <th>Team Member</th>
                                    <th class="text-end pe-4">Completed Tasks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($leaderboard)): ?>
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-muted">No task data available yet.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php $rank = 1; foreach($leaderboard as $user): ?>
                                        <tr>
                                            <td class="ps-4 fw-bold text-muted">
                                                <?php if($rank == 1): ?>
                                                    <i class="fas fa-medal text-warning fa-lg"></i> 1
                                                <?php elseif($rank == 2): ?>
                                                    <i class="fas fa-medal text-secondary fa-lg"></i> 2
                                                <?php elseif($rank == 3): ?>
                                                    <i class="fas fa-medal" style="color: #cd7f32;"></i> 3
                                                <?php else: ?>
                                                    <?= $rank ?>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                                        <?= strtoupper(substr($user['username'] ?? 'U', 0, 1)) ?>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0"><?= esc($user['display_name'] ?? $user['username'] ?? 'Team Member') ?></h6>
                                                        <small class="text-muted">@<?= esc($user['username']) ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-end pe-4">
                                                <span class="badge badge-success rounded-pill px-3 py-2 fs-6">
                                                    <?= $user['completed_tasks'] ?>
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
        
        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-light">
                <div class="card-body text-center py-5">
                    <i class="fas fa-chart-pie fa-4x text-muted mb-3 opacity-50"></i>
                    <h5 class="fw-bold">Need deeper insights?</h5>
                    <p class="text-muted small mb-4">Generate a full CSV or PDF performance report to see logged hours and specific task details.</p>
                    <a href="<?= site_url('manage/reports') ?>" class="btn btn-primary px-4">
                        <i class="fas fa-file-invoice me-2"></i> Go to Reports
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
