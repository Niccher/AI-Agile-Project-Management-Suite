<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title><?= esc($portal['project_name']) ?> • Project Status Portal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Live project progress and delivery status for <?= esc($portal['project_name']) ?>" />
    <meta name="robots" content="noindex, nofollow" />

    <!-- App favicon -->
    <link rel="shortcut icon" href="<?= base_url('assets/img/app_logo.jpg') ?>">

    <!-- App css -->
    <link href="<?= base_url('assets/hyper/css/icons.min.css') ?>" rel="stylesheet" type="text/css" />
    <link href="<?= base_url('assets/hyper/css/app.min.css') ?>" rel="stylesheet" type="text/css" id="light-style" />
    <link href="<?= base_url('assets/hyper/css/app-dark.min.css') ?>" rel="stylesheet" type="text/css" id="dark-style" disabled="disabled" />

    <style>
        body {
            background-color: #f4f7fc;
            color: #313a46;
            font-family: 'Nunito', sans-serif;
        }
        .portal-header {
            background: linear-gradient(135deg, #727cf5 0%, #39afd1 100%);
            color: #ffffff;
            padding: 40px 0 60px 0;
            margin-bottom: -40px;
        }
        .portal-card {
            border: 0;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        }
        .stat-badge-box {
            border-radius: 10px;
            padding: 16px;
            text-align: center;
        }
    </style>
</head>
<body>

    <!-- Top Brand Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark py-2 px-3">
        <div class="container">
            <div class="d-flex align-items-center">
                <img src="<?= base_url('assets/img/app_logo.jpg') ?>" alt="Logo" class="rounded-circle me-2" style="width: 28px; height: 28px; object-fit: cover;">
                <span class="text-white fw-bold font-16"><?= esc($siteName) ?></span>
                <span class="badge bg-light text-dark font-11 ms-2 px-2 py-1">Client Portal</span>
            </div>
            <div class="ms-auto font-12 text-light opacity-75">
                <i class="mdi mdi-shield-check-outline text-success me-1"></i> Verified Live Status
            </div>
        </div>
    </nav>

    <!-- Header Banner -->
    <div class="portal-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <span class="badge bg-white text-primary font-12 fw-semibold px-2 py-1 mb-2">
                        <i class="mdi mdi-access-point me-1"></i> Live Project Delivery Tracker
                    </span>
                    <h1 class="text-white fw-bold mb-2 font-28"><?= esc($portal['project_name']) ?></h1>
                    <p class="text-white-50 font-14 mb-0" style="max-width: 650px;">
                        <?= esc($portal['project_description']) ?>
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="d-inline-block bg-white bg-opacity-10 rounded-3 p-3 text-center border border-white border-opacity-25">
                        <div class="text-white font-12 text-uppercase mb-1">Overall Delivery</div>
                        <div class="text-white font-24 fw-bold"><?= (int)($portal['project_progress'] ?? 0) ?>%</div>
                        <small class="text-white-50 font-10">Last updated: <?= date('M j, Y', strtotime($portal['project_updated_at'] ?? 'now')) ?></small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Container -->
    <div class="container py-4">
        
        <!-- Delivery Progress Card -->
        <div class="card portal-card mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="fw-bold font-15 mb-0 text-dark">
                        <i class="mdi mdi-chart-donut me-1 text-primary"></i> Progress & Milestone Completion
                    </h5>
                    <span class="badge bg-primary-lighten text-primary font-12">
                        <?= ucfirst(str_replace('_', ' ', $portal['project_status'] ?? 'in_progress')) ?>
                    </span>
                </div>
                <div class="progress" style="height: 12px; border-radius: 6px;">
                    <div class="progress-bar bg-primary progress-bar-striped progress-bar-animated" role="progressbar" style="width: <?= (int)($portal['project_progress'] ?? 0) ?>%;" aria-valuenow="<?= (int)($portal['project_progress'] ?? 0) ?>" aria-valuemin="0" aria-valuemax="100"></div>
                </div>

                <!-- Column Task Count Counters -->
                <div class="row g-3 mt-3">
                    <div class="col-6 col-md-3">
                        <div class="stat-badge-box bg-secondary-lighten text-secondary">
                            <div class="font-11 text-uppercase fw-bold mb-1">Backlog / To Do</div>
                            <div class="font-20 fw-bold"><?= $taskCounts['todo'] ?> <small class="font-12">tasks</small></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-badge-box bg-primary-lighten text-primary">
                            <div class="font-11 text-uppercase fw-bold mb-1">In Development</div>
                            <div class="font-20 fw-bold"><?= $taskCounts['in_progress'] ?> <small class="font-12">tasks</small></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-badge-box bg-warning-lighten text-warning">
                            <div class="font-11 text-uppercase fw-bold mb-1">QA & Review</div>
                            <div class="font-20 fw-bold"><?= $taskCounts['review'] ?> <small class="font-12">tasks</small></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-badge-box bg-success-lighten text-success">
                            <div class="font-11 text-uppercase fw-bold mb-1">Completed / Done</div>
                            <div class="font-20 fw-bold"><?= $taskCounts['done'] ?> <small class="font-12">tasks</small></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Active Sprint & Velocity -->
            <div class="col-lg-6">
                <div class="card portal-card mb-4 h-100">
                    <div class="card-header bg-transparent border-bottom py-3">
                        <h5 class="fw-bold font-15 mb-0 text-dark">
                            <i class="mdi mdi-run-fast me-1 text-primary"></i> Current Sprint Cycle
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($activeSprint): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="font-16 fw-bold text-primary mb-0"><?= esc($activeSprint['name']) ?></h5>
                                <span class="badge bg-success font-11">Active Sprint</span>
                            </div>
                            <?php if (!empty($activeSprint['goal'])): ?>
                                <p class="text-muted font-13 mb-3">
                                    <i class="mdi mdi-flag-outline me-1"></i><strong>Goal:</strong> <?= esc($activeSprint['goal']) ?>
                                </p>
                            <?php endif; ?>
                            
                            <div class="row font-12 text-muted mb-3">
                                <div class="col-6">
                                    <span><i class="mdi mdi-calendar-start me-1"></i> Start: <?= date('M j, Y', strtotime($activeSprint['start_date'])) ?></span>
                                </div>
                                <div class="col-6 text-end">
                                    <span><i class="mdi mdi-calendar-end me-1"></i> End: <?= date('M j, Y', strtotime($activeSprint['end_date'])) ?></span>
                                </div>
                            </div>

                            <div class="p-3 bg-light rounded-3 mb-2">
                                <div class="d-flex justify-content-between font-12 fw-semibold mb-1">
                                    <span>Story Points Completed</span>
                                    <span><?= (int)$activeSprint['completed_points'] ?> / <?= (int)$activeSprint['total_points'] ?> pts</span>
                                </div>
                                <?php 
                                    $sprintPct = $activeSprint['total_points'] > 0 ? round(($activeSprint['completed_points'] / $activeSprint['total_points']) * 100) : 0;
                                ?>
                                <div class="progress progress-sm">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: <?= $sprintPct ?>%;" aria-valuenow="<?= $sprintPct ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4 text-muted">
                                <i class="mdi mdi-calendar-clock font-24 d-block mb-1 opacity-50"></i>
                                <span class="font-13">No active sprint cycle at this moment.</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Milestones Roadmap -->
            <div class="col-lg-6">
                <div class="card portal-card mb-4 h-100">
                    <div class="card-header bg-transparent border-bottom py-3">
                        <h5 class="fw-bold font-15 mb-0 text-dark">
                            <i class="mdi mdi-flag-checkered me-1 text-primary"></i> Key Milestones
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <?php if (empty($milestones)): ?>
                            <div class="text-center py-4 text-muted">
                                <i class="mdi mdi-flag-outline font-24 d-block mb-1 opacity-50"></i>
                                <span class="font-13">Milestones will be published as phases are agreed.</span>
                            </div>
                        <?php else: ?>
                            <div class="timeline-alt">
                                <?php foreach ($milestones as $ms): 
                                    $isDone = ($ms['status'] ?? '') === 'completed';
                                    $isInProgress = ($ms['status'] ?? '') === 'in_progress';
                                    $badgeClass = $isDone ? 'bg-success text-white' : ($isInProgress ? 'bg-primary text-white' : 'bg-light text-muted border');
                                    $icon = $isDone ? 'mdi-check' : ($isInProgress ? 'mdi-play' : 'mdi-clock-outline');
                                ?>
                                    <div class="d-flex align-items-start mb-3">
                                        <div class="avatar-xs rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0 <?= $badgeClass ?>" style="width: 28px; height: 28px;">
                                            <i class="mdi <?= $icon ?> font-14"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="m-0 font-14 fw-bold <?= $isDone ? 'text-decoration-line-through text-muted' : 'text-dark' ?>">
                                                    <?= esc($ms['name']) ?>
                                                </h6>
                                                <?php if (!empty($ms['due_date'])): ?>
                                                    <span class="font-11 text-muted"><?= date('M j, Y', strtotime($ms['due_date'])) ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <?php if (!empty($ms['description'])): ?>
                                                <p class="font-12 text-muted mb-0"><?= esc($ms['description']) ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="mt-4 pt-3 border-top text-center text-muted font-12">
            <p class="mb-1">
                <i class="mdi mdi-shield-lock-outline me-1"></i> Secure Token Access • Shared via <strong><?= esc($portal['label']) ?></strong>
            </p>
            <p class="mb-0 opacity-75">
                <?= date('Y') ?> © <strong><?= esc($siteName) ?></strong>. All rights reserved.
            </p>
        </footer>

    </div>

</body>
</html>
