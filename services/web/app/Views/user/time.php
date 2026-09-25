<?= $this->extend('layouts/hyper/main') ?>

<?= $this->section('title') ?>Time Tracking • <?= esc(setting('App.siteName')) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <button type="button" class="btn btn-outline-primary rounded-pill" id="manualEntryBtn">
                    <i class="mdi mdi-plus-circle-outline me-1"></i> Log Time Manually
                </button>
            </div>
            <h4 class="page-title">
                <i class="uil-stopwatch me-2 text-primary"></i> Time Tracking & Worklogs
            </h4>
        </div>
    </div>
</div>

<!-- Flash Alerts -->
<?php if (session()->getFlashdata('success') || session()->getFlashdata('message')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="mdi mdi-check-all me-1"></i> <?= session()->getFlashdata('success') ?: session()->getFlashdata('message') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="mdi mdi-block-helper me-1"></i> <?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- Active Timer & Quick Start Section -->
<div class="row mb-4">
    <div class="col-12">
        <!-- Active Timer Card (Shown when running) -->
        <div class="card shadow-sm border-0 bg-primary-lighten text-primary" id="activeTimerSection" style="display: none;">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="spinner-grow spinner-grow-sm text-danger" role="status"></span>
                            <h5 class="mb-0 text-primary fw-bold">Live Tracking Active</h5>
                        </div>
                        <p class="mb-0 text-muted font-14">Task: <strong class="text-body" id="currentTask">Working on Task...</strong></p>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <div class="display-5 font-monospace fw-bold text-body" id="timerDisplay">00:00:00</div>
                        <button class="btn btn-danger btn-lg rounded-pill px-4" id="stopTimerBtn">
                            <i class="mdi mdi-stop-circle me-1"></i> Stop Timer
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Start Timer Card -->
        <div class="card shadow-sm border-0" id="quickStartSection">
            <div class="card-header bg-transparent border-bottom py-3">
                <h5 class="header-title mb-0">
                    <i class="uil-play-circle me-1 text-success"></i> Start Live Timer
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-3 align-items-center">
                    <div class="col-lg-4 col-md-5">
                        <label for="quickProjectSelect" class="form-label font-12 fw-semibold text-muted mb-1">SELECT PROJECT</label>
                        <select class="form-select" id="quickProjectSelect">
                            <option value="">Select Project...</option>
                            <?php foreach ($projects as $proj): ?>
                                <option value="<?= $proj['id'] ?>" <?= (!empty($selectedProjectId) && $selectedProjectId == $proj['id']) ? 'selected' : '' ?>>
                                    <?= esc($proj['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-6 col-md-5">
                        <label for="quickTaskInput" class="form-label font-12 fw-semibold text-muted mb-1">TASK DESCRIPTION</label>
                        <input type="text" class="form-control" id="quickTaskInput" placeholder="What are you working on right now?">
                    </div>
                    <div class="col-lg-2 col-md-2 d-grid align-self-end">
                        <button class="btn btn-success" id="startTimerBtn" style="height: 38px;">
                            <i class="mdi mdi-play me-1"></i> Start
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Time Tracking Metrics -->
<div class="row g-3 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="card widget-flat h-100 shadow-sm border-0">
            <div class="card-body">
                <div class="float-end">
                    <div class="avatar-sm bg-info-lighten text-info rounded d-flex align-items-center justify-content-center">
                        <i class="mdi mdi-clock-check-outline font-22"></i>
                    </div>
                </div>
                <h6 class="text-muted text-uppercase mt-0 font-12 fw-semibold">Today</h6>
                <h3 class="my-2" id="todayTime"><?= esc($todayTime ?? '0.0') ?> <span class="font-14 text-muted fw-normal">hrs</span></h3>
                <p class="mb-0 text-muted font-13">
                    <span class="text-info me-1"><i class="mdi mdi-calendar-today"></i></span>
                    <span>Logged today</span>
                </p>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card widget-flat h-100 shadow-sm border-0">
            <div class="card-body">
                <div class="float-end">
                    <div class="avatar-sm bg-primary-lighten text-primary rounded d-flex align-items-center justify-content-center">
                        <i class="mdi mdi-calendar-week font-22"></i>
                    </div>
                </div>
                <h6 class="text-muted text-uppercase mt-0 font-12 fw-semibold">This Week</h6>
                <h3 class="my-2 text-primary" id="weekTime"><?= esc($weekTime ?? '0.0') ?> <span class="font-14 text-muted fw-normal">hrs</span></h3>
                <p class="mb-0 text-muted font-13">
                    <span class="text-primary me-1"><i class="mdi mdi-timeline-clock"></i></span>
                    <span>Current week</span>
                </p>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card widget-flat h-100 shadow-sm border-0">
            <div class="card-body">
                <div class="float-end">
                    <div class="avatar-sm bg-warning-lighten text-warning rounded d-flex align-items-center justify-content-center">
                        <i class="mdi mdi-calendar-month font-22"></i>
                    </div>
                </div>
                <h6 class="text-muted text-uppercase mt-0 font-12 fw-semibold">This Month</h6>
                <h3 class="my-2 text-warning" id="monthTime"><?= esc($monthTime ?? '0.0') ?> <span class="font-14 text-muted fw-normal">hrs</span></h3>
                <p class="mb-0 text-muted font-13">
                    <span class="text-warning me-1"><i class="mdi mdi-calendar-range"></i></span>
                    <span>Monthly total</span>
                </p>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card widget-flat h-100 shadow-sm border-0">
            <div class="card-body">
                <div class="float-end">
                    <div class="avatar-sm bg-success-lighten text-success rounded d-flex align-items-center justify-content-center">
                        <i class="mdi mdi-speedometer font-22"></i>
                    </div>
                </div>
                <h6 class="text-muted text-uppercase mt-0 font-12 fw-semibold">Avg Daily</h6>
                <h3 class="my-2 text-success" id="avgDaily"><?= esc($avgDaily ?? '0.0') ?> <span class="font-14 text-muted fw-normal">hrs</span></h3>
                <p class="mb-0 text-muted font-13">
                    <span class="text-success me-1"><i class="mdi mdi-trending-up"></i></span>
                    <span>Daily velocity</span>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Time Logs & Reports -->
<div class="row g-4 mb-4">
    <!-- Time Entries Table -->
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="header-title mb-0">
                    <i class="uil-history me-1 text-primary"></i> Recent Time Entries
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-centered mb-0" id="timeEntriesTable">
                        <thead class="table-light font-12 text-uppercase">
                            <tr>
                                <th>Date / Time</th>
                                <th>Project</th>
                                <th>Task Description</th>
                                <th>Duration</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($time_logs)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">
                                    <i class="mdi mdi-timer-off-outline font-24 d-block mb-1"></i>
                                    No time entries recorded yet.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($time_logs as $log): ?>
                            <tr>
                                <td class="font-13">
                                    <span class="fw-semibold text-body"><?= date('M d, Y', strtotime($log['start_time'])) ?></span><br>
                                    <span class="text-muted font-12">
                                        <?= date('H:i', strtotime($log['start_time'])) ?> - <?= !empty($log['end_time']) ? date('H:i', strtotime($log['end_time'])) : 'In Progress' ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-xs rounded-circle me-2 d-flex align-items-center justify-content-center text-white font-10" 
                                             style="width: 24px; height: 24px; background-color: <?= esc($log['project_color'] ?? '#3e60d5') ?>;">
                                            <i class="fas fa-folder"></i>
                                        </div>
                                        <span class="fw-semibold font-13 text-body"><?= esc($log['project_name'] ?? 'General') ?></span>
                                    </div>
                                </td>
                                <td class="font-13 text-body"><?= esc($log['task_name']) ?></td>
                                <td>
                                    <span class="badge bg-success-lighten text-success font-13">
                                        <?= round(($log['duration'] ?? 0) / 3600, 2) ?> hrs
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php if (isset($pager)): ?>
            <div class="card-footer bg-transparent border-top py-2">
                <?= $pager->links('time_logs', 'bootstrap_full') ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Project Breakdown & Reports -->
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 h-100" id="projectBreakdownCard">
            <div class="card-header bg-transparent border-bottom py-3">
                <h5 class="header-title mb-0">
                    <i class="uil-chart-pie me-1 text-primary"></i> Project Time Breakdown
                </h5>
            </div>
            <div class="card-body p-3">
                <div class="time-breakdown d-flex flex-column gap-3">
                    <?php if (empty($project_breakdown)): ?>
                        <p class="text-center py-4 text-muted">No time logged for projects yet.</p>
                    <?php else: ?>
                        <?php 
                        $totalDuration = array_sum(array_column($project_breakdown, 'total_duration'));
                        foreach ($project_breakdown as $item): 
                            $percent = ($totalDuration > 0) ? round(($item['total_duration'] / $totalDuration) * 100) : 0;
                        ?>
                        <div class="breakdown-item">
                            <div class="d-flex justify-content-between align-items-center mb-1 font-13">
                                <span class="fw-semibold text-body"><?= esc($item['name'] ?? 'General') ?></span>
                                <span class="text-muted"><?= round(($item['total_duration'] ?? 0) / 3600, 1) ?> hrs (<?= $percent ?>%)</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar rounded" style="width: <?= $percent ?>%; background-color: <?= esc($item['color'] ?? '#3e60d5') ?>;"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php if (($breakdown_total_pages ?? 1) > 1): ?>
            <div class="card-footer bg-transparent border-top py-2 d-flex justify-content-between align-items-center">
                <button class="btn btn-sm btn-outline-secondary <?= $breakdown_current_page <= 1 ? 'disabled' : '' ?>" 
                        onclick="window.location.search = '?page_breakdown=<?= $breakdown_current_page - 1 ?>'">
                    <i class="mdi mdi-chevron-left me-1"></i> Prev
                </button>
                <span class="font-12 text-muted">Page <?= $breakdown_current_page ?> of <?= $breakdown_total_pages ?></span>
                <button class="btn btn-sm btn-outline-secondary <?= $breakdown_current_page >= $breakdown_total_pages ? 'disabled' : '' ?>"
                        onclick="window.location.search = '?page_breakdown=<?= $breakdown_current_page + 1 ?>'">
                    Next <i class="mdi mdi-chevron-right ms-1"></i>
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Manual Entry Modal -->
<div class="modal fade" id="manualEntryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="mdi mdi-clock-outline me-1"></i> Manual Time Entry</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="manualEntryForm" action="<?= site_url('time/manual') ?>" method="POST">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label for="entryProject" class="form-label fw-semibold">Project <span class="text-danger">*</span></label>
                        <select class="form-select" id="entryProject" name="project_id" required>
                            <option value="">Select Project</option>
                            <?php foreach ($projects as $project): ?>
                                <option value="<?= $project['id'] ?>" <?= (!empty($selectedProjectId) && $selectedProjectId == $project['id']) ? 'selected' : '' ?>>
                                    <?= esc($project['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="entryTask" class="form-label fw-semibold">Task Description <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="entryTask" name="task_name" placeholder="What did you work on?" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="entryDate" class="form-label fw-semibold">Date</label>
                            <input type="date" class="form-control" id="entryDate" name="date" value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="entryDuration" class="form-label fw-semibold">Duration (Hours)</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="entryDuration" name="duration" value="1.0" step="0.25" min="0.25">
                                <span class="input-group-text">hrs</span>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="entryNotes" class="form-label fw-semibold">Notes (Optional)</label>
                        <textarea class="form-control" id="entryNotes" name="notes" rows="2" placeholder="Additional context about this session..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveEntryBtn">Save Log</button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999;"></div>

<script>
$(document).ready(function() {
    let timerInterval = null;
    let currentLogId = localStorage.getItem('active_timer_log_id');
    let startTimestamp = localStorage.getItem('active_timer_start');

    if (currentLogId && startTimestamp) {
        resumeActiveTimer();
    }

    // Start timer
    $('#startTimerBtn').on('click', function() {
        const project = $('#quickProjectSelect').val();
        const task = $('#quickTaskInput').val().trim();

        if (!project || !task) {
            showToast('Please select a project and enter a task description', 'warning');
            return;
        }

        $.post('<?= site_url('time/start') ?>', {
            <?= csrf_token() ?>: '<?= csrf_hash() ?>',
            project_id: project,
            task_name: task
        }, function(response) {
            if (response.status === 'success') {
                currentLogId = response.id;
                startTimestamp = Date.now();
                
                localStorage.setItem('active_timer_log_id', currentLogId);
                localStorage.setItem('active_timer_start', startTimestamp);
                localStorage.setItem('active_timer_task', task);

                showActiveTimerUI(task, 0);
                startTimerInterval(0);
                showToast(`Started tracking time for: ${task}`, 'success');
            }
        });
    });

    // Stop timer
    $('#stopTimerBtn').on('click', function() {
        if (!currentLogId) return;

        $.post('<?= site_url('time/stop') ?>/' + currentLogId, {
            <?= csrf_token() ?>: '<?= csrf_hash() ?>'
        }, function(response) {
            if (response.status === 'success') {
                clearInterval(timerInterval);
                localStorage.removeItem('active_timer_log_id');
                localStorage.removeItem('active_timer_start');
                localStorage.removeItem('active_timer_task');
                
                showToast(`Time logged successfully!`, 'success');
                setTimeout(() => window.location.reload(), 1200);
            }
        });
    });

    // Manual entry modal
    $('#manualEntryBtn').on('click', function() {
        const modal = new bootstrap.Modal(document.getElementById('manualEntryModal'));
        modal.show();
    });

    $('#saveEntryBtn').on('click', function() {
        $('#manualEntryForm').submit();
    });

    function resumeActiveTimer() {
        const task = localStorage.getItem('active_timer_task');
        const elapsedSeconds = Math.floor((Date.now() - parseInt(startTimestamp)) / 1000);
        showActiveTimerUI(task, elapsedSeconds);
        startTimerInterval(elapsedSeconds);
    }

    function showActiveTimerUI(task, initialSeconds) {
        $('#quickStartSection').hide();
        $('#activeTimerSection').show();
        $('#currentTask').text(task);
        updateTimerDisplay(initialSeconds);
    }

    function startTimerInterval(initialSeconds) {
        let seconds = initialSeconds;
        clearInterval(timerInterval);
        timerInterval = setInterval(function() {
            seconds++;
            updateTimerDisplay(seconds);
        }, 1000);
    }

    function updateTimerDisplay(totalSeconds) {
        const hours = Math.floor(totalSeconds / 3600);
        const minutes = Math.floor((totalSeconds % 3600) / 60);
        const seconds = totalSeconds % 60;

        $('#timerDisplay').text(
            `${hours.toString().padStart(2, '0')}:` +
            `${minutes.toString().padStart(2, '0')}:` +
            `${seconds.toString().padStart(2, '0')}`
        );
    }

    function showToast(message, type = 'info') {
        const toastId = 'toast-' + Date.now();
        const toastHtml = `
            <div id="${toastId}" class="toast align-items-center text-bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
        $('.toast-container').append(toastHtml);
        const toastEl = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastEl);
        toast.show();
        $(toastEl).on('hidden.bs.toast', function() {
            $(this).remove();
        });
    }
});
</script>

<?= $this->endSection() ?>
