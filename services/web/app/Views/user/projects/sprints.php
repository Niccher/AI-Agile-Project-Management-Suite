<?= $this->extend('layouts/hyper/main') ?>

<?= $this->section('title') ?><?= esc($project['name']) ?> • Sprints & Backlog<?= $this->endSection() ?>

<?= $this->section('css') ?>
<style>
    .sprint-task-card {
        background-color: #ffffff;
        border: 1px solid rgba(152, 166, 173, 0.2);
        border-radius: 8px;
        padding: 12px 16px;
        margin-bottom: 8px;
        transition: all 0.2s ease;
        cursor: grab;
    }
    .sprint-task-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        border-color: #727cf5;
    }
    body.dark-theme .sprint-task-card,
    html.dark-theme .sprint-task-card {
        background-color: #37404a;
        border-color: rgba(255, 255, 255, 0.08);
    }
    .story-point-badge {
        background-color: rgba(114, 124, 245, 0.12);
        color: #727cf5;
        font-weight: 700;
        border-radius: 12px;
        padding: 2px 8px;
        font-size: 11px;
        cursor: pointer;
    }
    .story-point-badge:hover {
        background-color: #727cf5;
        color: #ffffff;
    }
    .sprint-container-drop {
        min-height: 80px;
        border: 2px dashed rgba(152, 166, 173, 0.25);
        border-radius: 8px;
        padding: 10px;
        transition: background-color 0.2s;
    }
    .sprint-container-drop.drag-over {
        background-color: rgba(114, 124, 245, 0.08);
        border-color: #727cf5;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Page Title & Navigation Header -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <button type="button" class="btn btn-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#createSprintModal">
                    <i class="mdi mdi-plus me-1"></i> Plan New Sprint
                </button>
            </div>
            <h4 class="page-title">
                <i class="uil-briefcase text-primary me-1"></i> <?= esc($project['name']) ?>
                <span class="text-muted font-16 ms-2">/ Sprints & Backlog</span>
            </h4>
        </div>
    </div>
</div>

<?php
$sprintProjectSlug = !empty($project['slug']) ? $project['slug'] : $project['id'];
?>
<!-- Project Sub-Nav Tabs -->
<div class="row mb-3">
    <div class="col-12">
        <ul class="nav nav-tabs nav-bordered">
            <li class="nav-item">
                <a href="<?= site_url('projects/view/' . $sprintProjectSlug) ?>" class="nav-link">
                    <i class="uil-eye me-1"></i> Overview
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= site_url('projects/kanban/' . $sprintProjectSlug) ?>" class="nav-link">
                    <i class="uil-clipboard-alt me-1"></i> Kanban Board
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= site_url('projects/sprints/' . $sprintProjectSlug) ?>" class="nav-link active">
                    <i class="uil-layer-group me-1"></i> Sprints & Backlog
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= site_url('projects/wiki/' . $sprintProjectSlug) ?>" class="nav-link">
                    <i class="uil-book-open me-1"></i> Wiki & Docs
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= site_url('projects/time/' . $sprintProjectSlug) ?>" class="nav-link">
                    <i class="uil-clock me-1"></i> Time Tracker
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= site_url('projects/analytics/' . $sprintProjectSlug) ?>" class="nav-link">
                    <i class="uil-chart-line me-1"></i> Analytics
                </a>
            </li>
        </ul>
    </div>
</div>

<!-- Alerts -->
<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="mdi mdi-check-all me-1"></i> <?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="mdi mdi-block-helper me-1"></i> <?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- ================= ACTIVE SPRINT SECTION ================= -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0 mb-0">
            <?php if ($activeSprint): ?>
                <?php 
                    $percent = ($activeSprint['total_points'] > 0) 
                        ? round(($activeSprint['completed_points'] / $activeSprint['total_points']) * 100) 
                        : 0;
                    $daysLeft = !empty($activeSprint['end_date']) 
                        ? max(0, (int)ceil((strtotime($activeSprint['end_date']) - time()) / 86400))
                        : 0;
                ?>
                <div class="card-header bg-primary-lighten py-3 d-flex flex-wrap align-items-center justify-content-between">
                    <div>
                        <div class="d-flex align-items-center mb-1">
                            <span class="badge bg-success font-12 px-2 py-1 me-2"><i class="mdi mdi-play-circle me-1"></i> ACTIVE SPRINT</span>
                            <h4 class="m-0 fw-bold text-dark"><?= esc($activeSprint['name']) ?></h4>
                        </div>
                        <p class="text-muted font-13 mb-0">
                            <?php if ($activeSprint['goal']): ?>
                                <strong>Goal:</strong> <?= esc($activeSprint['goal']) ?> • 
                            <?php endif; ?>
                            <span><i class="mdi mdi-calendar-range me-1"></i> <?= date('M j', strtotime($activeSprint['start_date'])) ?> – <?= date('M j, Y', strtotime($activeSprint['end_date'])) ?></span>
                            <span class="badge bg-info-lighten text-info ms-2"><?= $daysLeft ?> days remaining</span>
                        </p>
                    </div>
                    <div class="mt-2 mt-md-0 d-flex gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill" onclick="openBurndownModal(<?= $activeSprint['id'] ?>)">
                            <i class="mdi mdi-chart-bell-curve-cumulative me-1"></i> Burndown Chart
                        </button>
                        <button type="button" class="btn btn-success btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#completeSprintModal">
                            <i class="mdi mdi-check-circle-outline me-1"></i> Complete Sprint
                        </button>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Story Points Progress Bar -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="font-13 text-muted">Sprint Scope & Velocity</span>
                            <span class="font-13 fw-bold">
                                <?= $activeSprint['completed_points'] ?> / <?= $activeSprint['total_points'] ?> Story Points (<?= $percent ?>%)
                            </span>
                        </div>
                        <div class="progress progress-sm">
                            <div class="progress-bar bg-success" role="progressbar" style="width: <?= $percent ?>%;" aria-valuenow="<?= $percent ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>

                    <!-- Active Sprint Task List Container -->
                    <div class="sprint-container-drop" id="sprint-container-<?= $activeSprint['id'] ?>" data-sprint-id="<?= $activeSprint['id'] ?>">
                        <?php if (empty($activeTasks)): ?>
                            <div class="text-center py-4 text-muted">
                                <i class="uil-layer-group font-24 d-block mb-1"></i>
                                <span class="font-14">No tasks in active sprint. Drag tasks from the Backlog below or click "Add to Sprint".</span>
                            </div>
                        <?php else: ?>
                            <?php foreach ($activeTasks as $task): ?>
                                <div class="sprint-task-card d-flex flex-wrap align-items-center justify-content-between" id="task-card-<?= $task['id'] ?>" draggable="true" data-task-id="<?= $task['id'] ?>">
                                    <div class="d-flex align-items-center">
                                        <i class="mdi mdi-drag-vertical text-muted font-18 me-2 cursor-grab"></i>
                                        <span class="badge bg-secondary-lighten text-secondary font-12 me-2">#<?= $task['id'] ?></span>
                                        <div>
                                            <a href="<?= site_url('projects/view/' . $sprintProjectSlug) ?>" class="fw-bold text-dark text-decoration-none font-14"><?= esc($task['title']) ?></a>
                                            <div class="font-12 text-muted">
                                                <span class="badge <?= $task['status'] === 'done' ? 'bg-success-lighten text-success' : 'bg-primary-lighten text-primary' ?> me-1">
                                                    <?= ucfirst(str_replace('_', ' ', $task['status'])) ?>
                                                </span>
                                                <?php if (!empty($task['assignee_name'])): ?>
                                                    <span><i class="uil-user me-1"></i><?= esc($task['assignee_name']) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center mt-2 mt-md-0 gap-2">
                                        <div class="dropdown">
                                            <span class="story-point-badge dropdown-toggle" data-bs-toggle="dropdown" title="Change Story Points">
                                                <?= (int)$task['story_points'] ?> pts <i class="mdi mdi-chevron-down font-10"></i>
                                            </span>
                                            <div class="dropdown-menu dropdown-menu-end p-2 text-center" style="min-width: 140px;">
                                                <div class="font-11 text-muted mb-1 fw-bold">Story Points:</div>
                                                <div class="d-flex flex-wrap gap-1 justify-content-center">
                                                    <?php foreach ([1, 2, 3, 5, 8, 13, 21] as $pt): ?>
                                                        <button type="button" class="btn btn-xs btn-outline-primary <?= (int)$task['story_points'] === $pt ? 'active' : '' ?>" onclick="updateTaskPoints(<?= $task['id'] ?>, <?= $pt ?>)">
                                                            <?= $pt ?>
                                                        </button>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-light font-12" onclick="moveTaskToSprint(<?= $task['id'] ?>, null)" title="Send back to Backlog">
                                            <i class="mdi mdi-arrow-down me-1"></i> Backlog
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

            <?php else: ?>
                <!-- No Active Sprint State -->
                <div class="card-body text-center py-5">
                    <div class="avatar-lg bg-primary-lighten text-primary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center font-28">
                        <i class="uil-play"></i>
                    </div>
                    <h4 class="fw-bold text-dark">No Active Sprint Running</h4>
                    <p class="text-muted font-14 mx-auto" style="max-width: 500px;">
                        Plan a sprint from your Backlog, estimate story points, and launch it to track team velocity and generate real-time burndown charts.
                    </p>
                    <button type="button" class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#createSprintModal">
                        <i class="mdi mdi-plus me-1"></i> Create & Plan Sprint
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ================= PLANNED SPRINTS SECTION ================= -->
<?php if (!empty($plannedSprints)): ?>
    <div class="row mb-4">
        <div class="col-12">
            <h5 class="fw-bold mb-3"><i class="uil-calendar-alt text-warning me-1"></i> Planned Sprints</h5>
            <?php foreach ($plannedSprints as $ps): ?>
                <div class="card shadow-sm border mb-3">
                    <div class="card-header bg-light py-2 d-flex flex-wrap align-items-center justify-content-between">
                        <div>
                            <span class="badge bg-warning-lighten text-warning font-11 me-2">PLANNING</span>
                            <strong class="text-dark font-15"><?= esc($ps['name']) ?></strong>
                            <span class="text-muted font-13 ms-2">
                                (<?= count($ps['tasks']) ?> tasks • <?= $ps['total_points'] ?> story points)
                            </span>
                        </div>
                        <div class="mt-1 mt-md-0">
                            <form action="<?= site_url('projects/sprints/start/' . $ps['id']) ?>" method="POST" class="d-inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-primary btn-sm rounded-pill">
                                    <i class="mdi mdi-play me-1"></i> Start Sprint
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="card-body p-2">
                        <div class="sprint-container-drop" id="sprint-container-<?= $ps['id'] ?>" data-sprint-id="<?= $ps['id'] ?>">
                            <?php if (empty($ps['tasks'])): ?>
                                <div class="text-center py-3 text-muted font-13">
                                    Drag tasks here to include in this sprint plan.
                                </div>
                            <?php else: ?>
                                <?php foreach ($ps['tasks'] as $task): ?>
                                    <div class="sprint-task-card d-flex flex-wrap align-items-center justify-content-between" id="task-card-<?= $task['id'] ?>" draggable="true" data-task-id="<?= $task['id'] ?>">
                                        <div class="d-flex align-items-center">
                                            <i class="mdi mdi-drag-vertical text-muted font-18 me-2 cursor-grab"></i>
                                            <span class="badge bg-secondary-lighten text-secondary font-12 me-2">#<?= $task['id'] ?></span>
                                            <span class="fw-bold text-dark font-14"><?= esc($task['title']) ?></span>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="story-point-badge"><?= (int)$task['story_points'] ?> pts</span>
                                            <button type="button" class="btn btn-sm btn-light font-12" onclick="moveTaskToSprint(<?= $task['id'] ?>, null)">
                                                <i class="mdi mdi-arrow-down me-1"></i> Backlog
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<!-- ================= BACKLOG SECTION ================= -->
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-light py-3 d-flex flex-wrap align-items-center justify-content-between">
                <div>
                    <h4 class="card-title font-16 mb-0">
                        <i class="uil-inbox me-1 text-secondary"></i> Product Backlog
                    </h4>
                    <span class="text-muted font-13">
                        <?= count($backlogTasks) ?> unassigned tasks • <?= $backlogPoints ?> Story Points
                    </span>
                </div>
                <div>
                    <a href="<?= site_url('projects/view/' . $sprintProjectSlug) ?>" class="btn btn-sm btn-outline-secondary rounded-pill">
                        <i class="mdi mdi-plus me-1"></i> Add Task to Backlog
                    </a>
                </div>
            </div>
            
            <div class="card-body">
                <div class="sprint-container-drop" id="sprint-container-backlog" data-sprint-id="backlog">
                    <?php if (empty($backlogTasks)): ?>
                        <div class="text-center py-4 text-muted">
                            <i class="uil-check-circle font-28 text-success d-block mb-1"></i>
                            <div class="font-14 fw-bold">Backlog is completely clear!</div>
                            <div class="font-12 mt-1">All tasks are currently assigned to active or planned sprints.</div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($backlogTasks as $task): ?>
                            <div class="sprint-task-card d-flex flex-wrap align-items-center justify-content-between" id="task-card-<?= $task['id'] ?>" draggable="true" data-task-id="<?= $task['id'] ?>">
                                <div class="d-flex align-items-center">
                                    <i class="mdi mdi-drag-vertical text-muted font-18 me-2 cursor-grab"></i>
                                    <span class="badge bg-secondary-lighten text-secondary font-12 me-2">#<?= $task['id'] ?></span>
                                    <div>
                                        <div class="fw-bold text-dark font-14"><?= esc($task['title']) ?></div>
                                        <div class="font-12 text-muted">
                                            <span class="badge <?= $task['priority'] === 'critical' ? 'bg-danger-lighten text-danger' : 'bg-info-lighten text-info' ?> me-1">
                                                <?= ucfirst($task['priority']) ?>
                                            </span>
                                            <?php if (!empty($task['assignee_name'])): ?>
                                                <span><i class="uil-user me-1"></i><?= esc($task['assignee_name']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center mt-2 mt-md-0 gap-2">
                                    <div class="dropdown">
                                        <span class="story-point-badge dropdown-toggle" data-bs-toggle="dropdown" title="Change Story Points">
                                            <?= (int)$task['story_points'] ?> pts <i class="mdi mdi-chevron-down font-10"></i>
                                        </span>
                                        <div class="dropdown-menu dropdown-menu-end p-2 text-center" style="min-width: 140px;">
                                            <div class="font-11 text-muted mb-1 fw-bold">Story Points:</div>
                                            <div class="d-flex flex-wrap gap-1 justify-content-center">
                                                <?php foreach ([1, 2, 3, 5, 8, 13, 21] as $pt): ?>
                                                    <button type="button" class="btn btn-xs btn-outline-primary <?= (int)$task['story_points'] === $pt ? 'active' : '' ?>" onclick="updateTaskPoints(<?= $task['id'] ?>, <?= $pt ?>)">
                                                        <?= $pt ?>
                                                    </button>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if ($activeSprint): ?>
                                        <button type="button" class="btn btn-sm btn-primary font-12" onclick="moveTaskToSprint(<?= $task['id'] ?>, <?= $activeSprint['id'] ?>)">
                                            <i class="mdi mdi-arrow-up me-1"></i> Add to Sprint
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ================= MODALS ================= -->

<!-- Create Sprint Modal -->
<div class="modal fade" id="createSprintModal" tabindex="-1" aria-labelledby="createSprintModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="<?= site_url('projects/sprints/store/' . $sprintProjectSlug) ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white" id="createSprintModalLabel"><i class="mdi mdi-plus-circle me-1"></i> Plan New Sprint</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Sprint Name</label>
                        <input type="text" class="form-control" name="name" placeholder="e.g. Sprint 14 - API & Authentication" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Sprint Goal</label>
                        <textarea class="form-control" name="goal" rows="3" placeholder="What does the team aim to achieve in this sprint?"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Duration</label>
                        <select class="form-select" name="duration_weeks">
                            <option value="1">1 Week</option>
                            <option value="2" selected>2 Weeks (Standard)</option>
                            <option value="3">3 Weeks</option>
                            <option value="4">4 Weeks</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Create Sprint</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Complete Sprint Modal -->
<?php if ($activeSprint): ?>
<div class="modal fade" id="completeSprintModal" tabindex="-1" aria-labelledby="completeSprintModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="<?= site_url('projects/sprints/complete/' . $activeSprint['id']) ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title text-white" id="completeSprintModalLabel"><i class="mdi mdi-check-circle-outline me-1"></i> Complete Sprint: <?= esc($activeSprint['name']) ?></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info border-0 font-13 mb-3">
                        <i class="mdi mdi-information-outline me-1"></i> Completing this sprint will archive its metrics into velocity analytics.
                    </div>
                    <div class="d-flex justify-content-between p-3 bg-light rounded-3 mb-3 text-center">
                        <div>
                            <div class="font-20 fw-bold text-success"><?= $activeSprint['completed_points'] ?></div>
                            <div class="font-12 text-muted">Completed Points</div>
                        </div>
                        <div>
                            <div class="font-20 fw-bold text-danger"><?= $activeSprint['remaining_points'] ?></div>
                            <div class="font-12 text-muted">Remaining Points</div>
                        </div>
                    </div>
                    <p class="font-13 text-muted mb-0">
                        Any unfinished tasks will automatically be moved back to the <strong>Product Backlog</strong> for future sprint planning.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4">Confirm & Complete Sprint</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Burndown Chart Modal -->
<div class="modal fade" id="burndownModal" tabindex="-1" aria-labelledby="burndownModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white" id="burndownModalLabel"><i class="mdi mdi-chart-bell-curve-cumulative me-1"></i> Sprint Burndown Chart</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div id="burndown-loading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="text-muted mt-2 font-13">Calculating burndown velocity...</div>
                </div>
                <div id="burndown-chart-wrapper" style="display: none;">
                    <div class="row mb-3 text-center">
                        <div class="col-4">
                            <div class="p-2 border rounded">
                                <div class="font-18 fw-bold text-primary" id="bd-total-pts">0</div>
                                <div class="font-12 text-muted">Total Scope</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 border rounded">
                                <div class="font-18 fw-bold text-success" id="bd-completed-pts">0</div>
                                <div class="font-12 text-muted">Completed</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 border rounded">
                                <div class="font-18 fw-bold text-danger" id="bd-remaining-pts">0</div>
                                <div class="font-12 text-muted">Remaining</div>
                            </div>
                        </div>
                    </div>
                    <div id="burndown-apex-chart" style="height: 350px;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('js') ?>
<!-- ApexCharts -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
    // Drag and Drop & AJAX Task Move
    let burndownChart = null;

    function moveTaskToSprint(taskId, sprintId) {
        const formData = new FormData();
        formData.append('task_id', taskId);
        formData.append('sprint_id', sprintId || '');

        fetch('<?= site_url("api/sprints/assign-task") ?>', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                window.location.reload();
            } else {
                alert(data.message || 'Failed to move task.');
            }
        })
        .catch(err => console.error(err));
    }

    function updateTaskPoints(taskId, points) {
        const formData = new FormData();
        formData.append('task_id', taskId);
        formData.append('story_points', points);

        fetch('<?= site_url("api/sprints/task-points") ?>', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                window.location.reload();
            }
        })
        .catch(err => console.error(err));
    }

    // Interactive Drag & Drop Handlers
    document.querySelectorAll('.sprint-task-card').forEach(card => {
        card.addEventListener('dragstart', (e) => {
            e.dataTransfer.setData('text/plain', card.dataset.taskId);
            card.classList.add('opacity-50');
        });
        card.addEventListener('dragend', () => {
            card.classList.remove('opacity-50');
        });
    });

    document.querySelectorAll('.sprint-container-drop').forEach(dropZone => {
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('drag-over');
        });
        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('drag-over');
        });
        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('drag-over');
            const taskId = e.dataTransfer.getData('text/plain');
            const sprintId = dropZone.dataset.sprintId;
            if (taskId) {
                moveTaskToSprint(taskId, sprintId === 'backlog' ? null : sprintId);
            }
        });
    });

    // Burndown Chart Loader
    function openBurndownModal(sprintId) {
        const modal = new bootstrap.Modal(document.getElementById('burndownModal'));
        modal.show();

        document.getElementById('burndown-loading').style.display = 'block';
        document.getElementById('burndown-chart-wrapper').style.display = 'none';

        fetch('<?= site_url("projects/sprints/burndown") ?>/' + sprintId)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('bd-total-pts').textContent = data.sprint.total_points + ' pts';
                    document.getElementById('bd-completed-pts').textContent = data.sprint.completed_points + ' pts';
                    document.getElementById('bd-remaining-pts').textContent = data.sprint.remaining_points + ' pts';

                    document.getElementById('burndown-loading').style.display = 'none';
                    document.getElementById('burndown-chart-wrapper').style.display = 'block';

                    renderBurndownApex(data.categories, data.ideal, data.actual);
                }
            })
            .catch(err => {
                console.error(err);
                document.getElementById('burndown-loading').innerHTML = '<div class="text-danger font-13">Failed to load burndown data.</div>';
            });
    }

    function renderBurndownApex(categories, idealData, actualData) {
        if (burndownChart) {
            burndownChart.destroy();
        }

        const options = {
            chart: {
                type: 'line',
                height: 340,
                toolbar: { show: false }
            },
            stroke: {
                curve: 'straight',
                width: [2, 3],
                dashArray: [5, 0]
            },
            colors: ['#8391a2', '#727cf5'],
            series: [
                { name: 'Ideal Guideline (Remaining)', data: idealData },
                { name: 'Actual (Remaining)', data: actualData }
            ],
            xaxis: {
                categories: categories,
                title: { text: 'Sprint Timeline' }
            },
            yaxis: {
                title: { text: 'Story Points' },
                min: 0
            },
            tooltip: {
                y: { formatter: (val) => val !== null ? val + ' pts' : 'N/A' }
            }
        };

        burndownChart = new ApexCharts(document.getElementById('burndown-apex-chart'), options);
        burndownChart.render();
    }
</script>
<?= $this->endSection() ?>
