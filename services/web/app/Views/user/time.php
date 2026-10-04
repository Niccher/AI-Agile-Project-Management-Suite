<?= $this->extend('layouts/hyper/main') ?>

<?= $this->section('title') ?>Time Tracking & Agile Worklogs • <?= esc(setting('App.siteName')) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- ApexCharts Dependency -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<div class="container-fluid py-3">

    <!-- Page Header & Scope Switcher -->
    <div class="row mb-3 align-items-center">
        <div class="col-md-6">
            <h4 class="page-title mb-1 fw-bold text-dark">
                <i class="uil-stopwatch text-primary me-2"></i> Time Tracking & Worklogs
            </h4>
            <p class="text-muted font-13 mb-0 d-flex align-items-center flex-wrap gap-2">
                <span>Track live work sessions, inspect team effort allocation, and export timesheets.</span>
                <?php if ($isManager): ?>
                    <span class="badge bg-primary-lighten text-primary font-11 px-2 py-1">
                        <i class="mdi mdi-shield-account-outline me-1"></i>Manager / Admin Mode
                    </span>
                <?php endif; ?>
            </p>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <div class="d-inline-flex gap-2 flex-wrap align-items-center justify-content-md-end">
                <?php if ($isManager): ?>
                    <!-- Scope Switch Buttons -->
                    <div class="btn-group me-1">
                        <a href="<?= site_url('time?scope=team' . (!empty($selectedProjectId) ? '&project_id=' . $selectedProjectId : '')) ?>" 
                           class="btn btn-sm <?= ($scope === 'team') ? 'btn-primary' : 'btn-outline-primary' ?>">
                            <i class="mdi mdi-account-group me-1"></i> Team Overview
                        </a>
                        <a href="<?= site_url('time?scope=me' . (!empty($selectedProjectId) ? '&project_id=' . $selectedProjectId : '')) ?>" 
                           class="btn btn-sm <?= ($scope === 'me') ? 'btn-primary' : 'btn-outline-primary' ?>">
                            <i class="mdi mdi-account me-1"></i> My Worklogs
                        </a>
                    </div>
                <?php endif; ?>

                <button type="button" class="btn btn-primary rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#manualEntryModal">
                    <i class="mdi mdi-plus-circle-outline me-1"></i> Log Time
                </button>
                <div class="btn-group">
                    <a href="<?= site_url('time/report/pdf' . (!empty($selectedProjectId) ? '?project_id=' . $selectedProjectId : '')) ?>" class="btn btn-outline-secondary rounded-pill shadow-sm" title="Export PDF Timesheet" target="_blank">
                        <i class="mdi mdi-file-pdf-box text-danger me-1"></i> PDF
                    </a>
                    <a href="<?= site_url('time/report/csv' . (!empty($selectedProjectId) ? '?project_id=' . $selectedProjectId : '')) ?>" class="btn btn-outline-secondary rounded-pill shadow-sm ms-1" title="Export CSV Data">
                        <i class="mdi mdi-file-delimited text-success me-1"></i> CSV
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Timer & Live Stopwatch Hero Banner -->
    <div class="row mb-4">
        <div class="col-12">
            <!-- Active Timer Card (Shown when live session is running) -->
            <div class="card shadow border-0 bg-dark text-white overflow-hidden position-relative" id="activeTimerSection" style="display: none; background: linear-gradient(135deg, #1e2229 0%, #2a3042 100%);">
                <div class="card-body p-4 position-relative" style="z-index: 2;">
                    <div class="row align-items-center g-3">
                        <div class="col-lg-5">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-danger text-white font-12 px-2 py-1 d-inline-flex align-items-center">
                                    <span class="spinner-grow spinner-grow-sm me-1" style="width: 8px; height: 8px;"></span> LIVE TIMER
                                </span>
                                <span class="badge bg-light text-dark font-12" id="activeProjectBadge">Project</span>
                            </div>
                            <h4 class="text-white fw-bold mb-1 text-truncate" id="currentTask">Working on Task...</h4>
                            <p class="text-muted font-12 mb-0" id="sessionStartTime"><i class="mdi mdi-clock-start me-1"></i>Started at --:--</p>
                        </div>
                        <div class="col-lg-4 text-center">
                            <div class="display-4 font-monospace fw-bold text-success text-shadow" id="timerDisplay">00:00:00</div>
                        </div>
                        <div class="col-lg-3 text-lg-end">
                            <button class="btn btn-danger btn-lg rounded-pill px-4 shadow" id="stopTimerBtn">
                                <i class="mdi mdi-stop-circle me-1"></i> Stop & Record
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Start Stopwatch Card -->
            <div class="card shadow-sm border-0 border-top border-primary border-3" id="quickStartSection">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold font-15 text-dark">
                        <i class="mdi mdi-play-circle-outline text-success me-2"></i> Instant Live Stopwatch
                    </h5>
                    <div class="d-flex gap-1">
                        <button type="button" class="btn btn-xs btn-outline-secondary quick-chip-btn" data-preset="15">+15m</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary quick-chip-btn" data-preset="30">+30m</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary quick-chip-btn" data-preset="60">+1h</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary quick-chip-btn" data-preset="120">+2h</button>
                    </div>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div class="row g-3 align-items-end">
                        <div class="col-lg-4 col-md-5">
                            <label for="quickProjectSelect" class="form-label font-12 fw-semibold text-muted mb-1 text-uppercase">Project</label>
                            <select class="form-select" id="quickProjectSelect">
                                <option value="">Select Project / Workspace...</option>
                                <?php foreach ($projects as $proj): ?>
                                    <option value="<?= $proj['id'] ?>" data-color="<?= esc($proj['color'] ?? '#727cf5') ?>" <?= (!empty($selectedProjectId) && $selectedProjectId == $proj['id']) ? 'selected' : '' ?>>
                                        <?= esc($proj['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-lg-6 col-md-5">
                            <label for="quickTaskInput" class="form-label font-12 fw-semibold text-muted mb-1 text-uppercase">Task Description / Worklog</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="mdi mdi-pencil-outline text-muted"></i></span>
                                <input type="text" class="form-control border-start-0" id="quickTaskInput" placeholder="What are you working on right now? (e.g. Bug fixes, API integration, Code review)">
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-2 d-grid">
                            <button class="btn btn-success fw-bold shadow-sm" id="startTimerBtn" style="height: 38px;">
                                <i class="mdi mdi-play me-1"></i> Start Timer
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4 KPI Metric Cards -->
    <div class="row g-3 mb-4">
        <!-- 1. Today's Effort -->
        <div class="col-sm-6 col-xl-3">
            <div class="card widget-flat h-100 mb-0 shadow-sm border-0 border-top border-info border-3">
                <div class="card-body">
                    <div class="float-end">
                        <div class="avatar-sm bg-info-lighten text-info rounded-circle d-flex align-items-center justify-content-center">
                            <i class="mdi mdi-clock-check-outline font-22"></i>
                        </div>
                    </div>
                    <h6 class="text-muted text-uppercase font-12 fw-semibold mt-0" title="Today's Time">
                        <?= ($isManager && $scope === 'team') ? 'Team Today' : "Today's Worklog" ?>
                    </h6>
                    <h3 class="mt-2 mb-1 fw-bold text-dark" id="todayTime"><?= esc($todayTime ?? '0.0') ?> <span class="font-14 text-muted fw-normal">hrs</span></h3>
                    <?php 
                        $targetHours = ($isManager && $scope === 'team') ? max(8.0, count($teamMembers) * 8.0) : 8.0;
                        $todayPercent = min(100, round((($todayTime ?? 0) / $targetHours) * 100));
                    ?>
                    <div class="progress progress-sm my-2" style="height: 5px;">
                        <div class="progress-bar bg-info" role="progressbar" style="width: <?= $todayPercent ?>%"></div>
                    </div>
                    <p class="mb-0 text-muted font-11">
                        <span class="text-info fw-semibold"><?= $todayPercent ?>%</span> of target (<?= $targetHours ?>h)
                    </p>
                </div>
            </div>
        </div>

        <!-- 2. This Week -->
        <div class="col-sm-6 col-xl-3">
            <div class="card widget-flat h-100 mb-0 shadow-sm border-0 border-top border-primary border-3">
                <div class="card-body">
                    <div class="float-end">
                        <div class="avatar-sm bg-primary-lighten text-primary rounded-circle d-flex align-items-center justify-content-center">
                            <i class="mdi mdi-calendar-week font-22"></i>
                        </div>
                    </div>
                    <h6 class="text-muted text-uppercase font-12 fw-semibold mt-0" title="This Week">
                        <?= ($isManager && $scope === 'team') ? 'Team This Week' : 'This Week' ?>
                    </h6>
                    <h3 class="mt-2 mb-1 fw-bold text-primary" id="weekTime"><?= esc($weekTime ?? '0.0') ?> <span class="font-14 text-muted fw-normal">hrs</span></h3>
                    <div class="d-flex align-items-center gap-1 mt-2 font-12">
                        <span class="badge bg-primary-lighten text-primary"><i class="mdi mdi-speedometer me-1"></i><?= esc($avgDaily ?? '0.0') ?>h/day avg</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. This Month -->
        <div class="col-sm-6 col-xl-3">
            <div class="card widget-flat h-100 mb-0 shadow-sm border-0 border-top border-warning border-3">
                <div class="card-body">
                    <div class="float-end">
                        <div class="avatar-sm bg-warning-lighten text-warning rounded-circle d-flex align-items-center justify-content-center">
                            <i class="mdi mdi-calendar-month font-22"></i>
                        </div>
                    </div>
                    <h6 class="text-muted text-uppercase font-12 fw-semibold mt-0" title="This Month">
                        <?= ($isManager && $scope === 'team') ? 'Team Month' : 'This Month' ?>
                    </h6>
                    <h3 class="mt-2 mb-1 fw-bold text-warning" id="monthTime"><?= esc($monthTime ?? '0.0') ?> <span class="font-14 text-muted fw-normal">hrs</span></h3>
                    <p class="mb-0 text-muted font-11 mt-2">
                        <span class="text-dark fw-semibold"><i class="mdi mdi-counter me-1"></i><?= esc($totalLogsCount ?? 0) ?></span> total sessions logged
                    </p>
                </div>
            </div>
        </div>

        <!-- 4. Billable Efficiency -->
        <div class="col-sm-6 col-xl-3">
            <div class="card widget-flat h-100 mb-0 shadow-sm border-0 border-top border-success border-3">
                <div class="card-body">
                    <div class="float-end">
                        <div class="avatar-sm bg-success-lighten text-success rounded-circle d-flex align-items-center justify-content-center">
                            <i class="mdi mdi-currency-usd font-22"></i>
                        </div>
                    </div>
                    <h6 class="text-muted text-uppercase font-12 fw-semibold mt-0" title="Billable Ratio">Billable Ratio</h6>
                    <h3 class="mt-2 mb-1 fw-bold text-success"><?= esc($billableRate ?? 100) ?>%</h3>
                    <p class="mb-0 text-muted font-11 mt-2">
                        <span class="text-success fw-semibold"><i class="mdi mdi-check-circle me-1"></i><?= esc($billableHours ?? $totalHours) ?> hrs</span> billable effort
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Interactive Visual Analytics Row (ApexCharts) -->
    <div class="row g-3 mb-4">
        <!-- 7-Day Velocity Chart -->
        <div class="col-xl-7 col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                    <h5 class="mb-0 fw-bold font-15 text-dark">
                        <i class="mdi mdi-chart-bar text-primary me-2"></i> 7-Day Effort Velocity (Hours Tracked)
                    </h5>
                    <span class="badge bg-light text-muted font-11 px-2 py-1">Last 7 Days</span>
                </div>
                <div class="card-body p-3">
                    <div id="effort-velocity-chart" style="min-height: 250px; width: 100%;"></div>
                </div>
            </div>
        </div>

        <!-- Project Effort Allocation (Donut Chart) -->
        <div class="col-xl-5 col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                    <h5 class="mb-0 fw-bold font-15 text-dark">
                        <i class="mdi mdi-chart-donut text-success me-2"></i> Effort by Project
                    </h5>
                </div>
                <div class="card-body d-flex flex-column justify-content-center align-items-center p-3">
                    <?php if (!empty($chartProjectHours) && array_sum($chartProjectHours) > 0): ?>
                        <div id="project-effort-donut" style="min-height: 250px; width: 100%;"></div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <div class="avatar-lg bg-light rounded-circle mx-auto d-flex align-items-center justify-content-center mb-2">
                                <i class="mdi mdi-chart-pie font-24 text-muted"></i>
                            </div>
                            <h6 class="text-muted fw-normal">No project hours logged yet</h6>
                            <p class="text-muted font-12">Start the live stopwatch or log time manually to see project allocation.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Team Members Effort Leaderboard (Only for Admin/Manager in Team Scope) -->
    <?php if ($isManager && !empty($user_breakdown)): ?>
        <div class="card shadow-sm border-0 mb-4 border-start border-primary border-3">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold font-15 text-dark">
                    <i class="mdi mdi-account-group text-primary me-2"></i> Team Members Effort Contribution
                </h5>
                <span class="badge bg-primary-lighten text-primary font-11"><?= count($user_breakdown) ?> Active Contributors</span>
            </div>
            <div class="card-body p-3">
                <div class="row g-3">
                    <?php foreach ($user_breakdown as $ub): ?>
                        <?php 
                            $ubName = $ub['user_display_name'] ?? ($ub['username'] ?? 'User');
                            $ubHrs = $ub['hours'] ?? 0;
                            $ubSessions = $ub['sessions_count'] ?? 0;
                            $ubTotalDuration = array_sum(array_column($user_breakdown, 'hours')) ?: 1;
                            $ubPercent = min(100, round(($ubHrs / $ubTotalDuration) * 100));
                            $isSelectedUser = ($selectedUserId && (int)$selectedUserId === (int)$ub['user_id']);
                        ?>
                        <div class="col-md-6 col-xl-3">
                            <div class="p-3 border rounded h-100 <?= $isSelectedUser ? 'border-primary bg-primary-lighten' : 'bg-light-subtle' ?>">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-xs bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2 font-11 fw-bold" style="width: 30px; height: 30px;">
                                            <?= strtoupper(substr($ubName, 0, 1)) ?>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 font-13 fw-semibold text-dark text-truncate" style="max-width: 120px;"><?= esc($ubName) ?></h6>
                                            <small class="text-muted font-11">@<?= esc($ub['username'] ?? 'user') ?></small>
                                        </div>
                                    </div>
                                    <span class="badge bg-success-lighten text-success font-12 fw-bold"><?= $ubHrs ?>h</span>
                                </div>
                                <div class="progress progress-sm mb-1" style="height: 5px;">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $ubPercent ?>%"></div>
                                </div>
                                <div class="d-flex justify-content-between font-11 text-muted">
                                    <span><?= $ubSessions ?> session<?= $ubSessions !== 1 ? 's' : '' ?></span>
                                    <span><?= $ubPercent ?>% share</span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Worklogs Table & Filter Bar -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 border-bottom">
            <div class="row align-items-center g-2">
                <div class="col-md-4">
                    <h5 class="mb-0 fw-bold font-15 text-dark">
                        <i class="mdi mdi-format-list-bulleted text-primary me-2"></i> Recorded Worklogs & Timesheets
                    </h5>
                </div>
                <div class="col-md-8">
                    <div class="d-flex flex-wrap gap-2 justify-content-md-end align-items-center">
                        <?php if ($isManager && !empty($teamMembers)): ?>
                            <!-- Filter by User Dropdown -->
                            <select class="form-select form-select-sm w-auto" id="filterUserSelect" onchange="window.location.href=this.value;">
                                <option value="<?= site_url('time?scope=team' . (!empty($selectedProjectId) ? '&project_id=' . $selectedProjectId : '')) ?>" <?= (empty($selectedUserId) && $scope === 'team') ? 'selected' : '' ?>>
                                    👥 All Team Members
                                </option>
                                <?php foreach ($teamMembers as $tm): ?>
                                    <?php 
                                        $tmName = $tm['user_display_name'] ?? $tm['username'];
                                        $tmUrl = site_url('time?scope=team&user_id=' . $tm['id'] . (!empty($selectedProjectId) ? '&project_id=' . $selectedProjectId : ''));
                                    ?>
                                    <option value="<?= esc($tmUrl) ?>" <?= ((int)$selectedUserId === (int)$tm['id']) ? 'selected' : '' ?>>
                                        <?= esc($tmName) ?> (@<?= esc($tm['username']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>

                        <select class="form-select form-select-sm w-auto" id="filterProjectSelect">
                            <option value="">All Projects</option>
                            <?php foreach ($projects as $p): ?>
                                <option value="<?= strtolower(esc($p['name'])) ?>"><?= esc($p['name']) ?></option>
                            <?php endforeach; ?>
                        </select>

                        <div class="input-group input-group-sm" style="max-width: 220px;">
                            <span class="input-group-text bg-light border-end-0"><i class="mdi mdi-magnify text-muted"></i></span>
                            <input type="text" class="form-control border-start-0" id="searchLogsInput" placeholder="Search worklogs...">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 font-13" id="timeEntriesTable">
                    <thead class="table-light font-12 text-uppercase text-muted">
                        <tr>
                            <th class="ps-4">Date & Time Range</th>
                            <?php if ($isManager): ?>
                                <th>Team Member</th>
                            <?php endif; ?>
                            <th>Project</th>
                            <th>Task Description</th>
                            <th>Duration</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="timeLogsTableBody">
                        <?php if (empty($time_logs)): ?>
                            <tr id="emptyLogsRow">
                                <td colspan="<?= $isManager ? '7' : '6' ?>" class="text-center py-5 text-muted">
                                    <div class="avatar-lg bg-light rounded-circle mx-auto d-flex align-items-center justify-content-center mb-2">
                                        <i class="mdi mdi-timer-off-outline font-28 text-muted"></i>
                                    </div>
                                    <h6 class="fw-semibold">No time entries found</h6>
                                    <p class="text-muted font-12 mb-0">Use the stopwatch above or click "Log Time" to record a new work session.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($time_logs as $log): ?>
                                <?php 
                                    $pColor = $log['project_color'] ?? '#727cf5';
                                    $pName = $log['project_name'] ?? 'General';
                                    $pSlug = $log['project_slug'] ?? '';
                                    $durationSec = (int)($log['duration'] ?? 0);
                                    $hrs = floor($durationSec / 3600);
                                    $mins = floor(($durationSec % 3600) / 60);
                                    $durationFormatted = ($hrs > 0 ? "{$hrs}h " : "") . "{$mins}m";
                                    if ($durationSec < 60) $durationFormatted = "< 1m";
                                    $isBillable = isset($log['is_billable']) ? (int)$log['is_billable'] : 1;
                                    $logUser = $log['user_display_name'] ?? ($log['user_username'] ?? 'User');
                                ?>
                                <tr class="time-log-row" data-id="<?= $log['id'] ?>" data-project="<?= strtolower(esc($pName)) ?>" data-task="<?= strtolower(esc($log['task_name'])) ?>" data-user="<?= strtolower(esc($logUser)) ?>">
                                    <td class="ps-4 font-13">
                                        <span class="fw-semibold text-dark d-block"><?= date('M d, Y', strtotime($log['start_time'])) ?></span>
                                        <small class="text-muted font-11">
                                            <i class="mdi mdi-clock-outline me-1"></i><?= date('H:i', strtotime($log['start_time'])) ?> - <?= !empty($log['end_time']) ? date('H:i', strtotime($log['end_time'])) : 'In Progress' ?>
                                        </small>
                                    </td>
                                    <?php if ($isManager): ?>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-xs bg-primary-lighten text-primary rounded-circle d-flex align-items-center justify-content-center me-2 font-11 fw-bold" style="width: 24px; height: 24px;">
                                                    <?= strtoupper(substr($logUser, 0, 1)) ?>
                                                </div>
                                                <span class="font-12 fw-semibold text-dark"><?= esc($logUser) ?></span>
                                            </div>
                                        </td>
                                    <?php endif; ?>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-xs rounded me-2 d-flex align-items-center justify-content-center text-white font-11 fw-bold" 
                                                 style="width: 26px; height: 26px; background-color: <?= esc($pColor) ?>;">
                                                <i class="mdi mdi-folder"></i>
                                            </div>
                                            <span class="fw-semibold font-13 text-dark"><?= esc($pName) ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-dark d-block"><?= esc($log['task_name']) ?></span>
                                        <?php if (!empty($log['notes'])): ?>
                                            <small class="text-muted font-11 text-truncate d-block" style="max-width: 280px;"><?= esc($log['notes']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-success-lighten text-success font-13 px-2 py-1">
                                            <i class="mdi mdi-timer-outline me-1"></i><?= $durationFormatted ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($isBillable): ?>
                                            <span class="badge bg-primary-lighten text-primary font-11"><i class="mdi mdi-check me-1"></i>Billable</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary-lighten text-secondary font-11">Non-billable</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-4">
                                        <button type="button" class="btn btn-xs btn-outline-primary btn-retrack me-1" 
                                                data-project-id="<?= $log['project_id'] ?? '' ?>" 
                                                data-task-name="<?= esc($log['task_name']) ?>" 
                                                title="Re-track this task">
                                            <i class="mdi mdi-play"></i> Re-track
                                        </button>
                                        <button type="button" class="btn btn-xs btn-outline-danger btn-delete-log" 
                                                data-id="<?= $log['id'] ?>" 
                                                title="Delete this worklog">
                                            <i class="mdi mdi-trash-can-outline"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php if (isset($pager)): ?>
            <div class="card-footer bg-white border-top py-2 d-flex justify-content-between align-items-center">
                <small class="text-muted font-12">Showing paginated worklogs</small>
                <div><?= $pager->links('time_logs', 'bootstrap_full') ?></div>
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- Manual Entry Modal -->
<div class="modal fade" id="manualEntryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-dark">
                    <i class="mdi mdi-plus-circle text-primary me-2"></i> Log Manual Time Session
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= site_url('time/manual') ?>" method="POST" id="manualTimeForm">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <?php if ($isManager && !empty($teamMembers)): ?>
                        <div class="mb-3">
                            <label for="manual_user_id" class="form-label font-13 fw-semibold">Assign to Team Member</label>
                            <select class="form-select" id="manual_user_id" name="user_id">
                                <option value="<?= auth()->id() ?>">Myself (<?= esc(auth()->user()->username ?? 'Me') ?>)</option>
                                <?php foreach ($teamMembers as $tm): ?>
                                    <?php if ($tm['id'] != auth()->id()): ?>
                                        <option value="<?= $tm['id'] ?>">
                                            <?= esc($tm['user_display_name'] ?? $tm['username']) ?> (@<?= esc($tm['username']) ?>)
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="manual_project_id" class="form-label font-13 fw-semibold">Target Project <span class="text-danger">*</span></label>
                        <select class="form-select" id="manual_project_id" name="project_id" required>
                            <option value="">Select Project...</option>
                            <?php foreach ($projects as $proj): ?>
                                <option value="<?= $proj['id'] ?>" <?= (!empty($selectedProjectId) && $selectedProjectId == $proj['id']) ? 'selected' : '' ?>>
                                    <?= esc($proj['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="manual_task_name" class="form-label font-13 fw-semibold">Task Description <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="manual_task_name" name="task_name" placeholder="What task did you accomplish?" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="manual_date" class="form-label font-13 fw-semibold">Date</label>
                            <input type="date" class="form-control" id="manual_date" name="date" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-6">
                            <label for="manual_duration" class="form-label font-13 fw-semibold">Duration (Hours) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="manual_duration" name="duration" step="0.25" min="0.1" value="1.0" required>
                        </div>
                    </div>

                    <!-- Quick Preset Duration Pills -->
                    <div class="mb-3">
                        <label class="form-label font-12 text-muted mb-1">Quick Duration Presets:</label>
                        <div class="d-flex flex-wrap gap-1">
                            <button type="button" class="btn btn-xs btn-outline-secondary modal-chip-btn" data-hours="0.25">15m</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary modal-chip-btn" data-hours="0.5">30m</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary modal-chip-btn" data-hours="1.0">1h</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary modal-chip-btn" data-hours="2.0">2h</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary modal-chip-btn" data-hours="4.0">4h</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary modal-chip-btn" data-hours="8.0">8h</button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="manual_is_billable" name="is_billable" value="1" checked>
                            <label class="form-check-label font-13 fw-semibold" for="manual_is_billable">Billable session to client</label>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label for="manual_notes" class="form-label font-13 fw-semibold">Worklog Notes / Details (Optional)</label>
                        <textarea class="form-control" id="manual_notes" name="notes" rows="2" placeholder="Add any details, PR numbers, or context..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveManualTimeBtn">
                        <i class="mdi mdi-check me-1"></i> Save Worklog
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function getCsrfInfo() {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
                   || document.querySelector('input[name="csrf_token"]')?.value 
                   || '';
        const header = document.querySelector('meta[name="csrf-header"]')?.getAttribute('content') 
                    || 'X-CSRF-TOKEN';
        return { token, header };
    }

    // 1. ApexCharts - 7-Day Velocity
    const trendLabels = <?= json_encode($trendLabels) ?>;
    const trendHours = <?= json_encode($trendHours) ?>;

    if (document.getElementById('effort-velocity-chart')) {
        const velocityOptions = {
            series: [{
                name: 'Hours Tracked',
                data: trendHours
            }],
            chart: {
                type: 'bar',
                height: 250,
                toolbar: { show: false },
                fontFamily: 'inherit'
            },
            plotOptions: {
                bar: {
                    borderRadius: 6,
                    columnWidth: '40%',
                    distributed: true
                }
            },
            colors: ['#727cf5', '#0acf97', '#fa5c7c', '#ffbc00', '#39afd1', '#727cf5', '#0acf97'],
            dataLabels: { enabled: false },
            legend: { show: false },
            xaxis: {
                categories: trendLabels,
                labels: { style: { fontSize: '11px', colors: '#6c757d' } },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                min: 0,
                forceNiceScale: true,
                labels: {
                    formatter: function(val) { return val + 'h'; },
                    style: { fontSize: '11px', colors: '#6c757d' }
                }
            },
            grid: {
                borderColor: '#f1f3fa',
                strokeDashArray: 4
            }
        };

        const velocityChart = new ApexCharts(document.getElementById('effort-velocity-chart'), velocityOptions);
        velocityChart.render();
    }

    // 2. ApexCharts - Project Effort Donut
    const projectLabels = <?= json_encode($chartProjectLabels ?? []) ?>;
    const projectHours = <?= json_encode($chartProjectHours ?? []) ?>;
    const projectColors = <?= json_encode($chartProjectColors ?? []) ?>;

    if (document.getElementById('project-effort-donut') && projectHours.length > 0 && projectHours.some(h => h > 0)) {
        const donutOptions = {
            series: projectHours,
            labels: projectLabels,
            chart: {
                type: 'donut',
                height: 250,
                fontFamily: 'inherit'
            },
            colors: projectColors.length > 0 ? projectColors : ['#727cf5', '#0acf97', '#ffbc00', '#fa5c7c', '#39afd1'],
            legend: {
                position: 'bottom',
                fontSize: '11px',
                markers: { radius: 10 }
            },
            dataLabels: { enabled: false },
            plotOptions: {
                pie: {
                    donut: {
                        size: '70%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Total Hours',
                                fontSize: '11px',
                                color: '#6c757d',
                                formatter: function(w) {
                                    return w.globals.seriesTotals.reduce((a, b) => a + b, 0).toFixed(1) + 'h';
                                }
                            }
                        }
                    }
                }
            }
        };

        const donutChart = new ApexCharts(document.getElementById('project-effort-donut'), donutOptions);
        donutChart.render();
    }

    // 3. Live Stopwatch Logic
    let timerInterval = null;
    let startTime = null;
    let activeLogId = localStorage.getItem('active_time_log_id');
    let activeTaskName = localStorage.getItem('active_task_name');
    let activeProjectName = localStorage.getItem('active_project_name');
    let activeStartTimestamp = localStorage.getItem('active_start_time');

    const activeSection = document.getElementById('activeTimerSection');
    const quickSection = document.getElementById('quickStartSection');
    const timerDisplay = document.getElementById('timerDisplay');
    const currentTaskEl = document.getElementById('currentTask');
    const activeProjBadge = document.getElementById('activeProjectBadge');
    const sessionStartEl = document.getElementById('sessionStartTime');

    function updateTimerDisplay() {
        if (!startTime) return;
        const now = new Date().getTime();
        const diff = Math.max(0, Math.floor((now - startTime) / 1000));
        const hours = Math.floor(diff / 3600).toString().padStart(2, '0');
        const minutes = Math.floor((diff % 3600) / 60).toString().padStart(2, '0');
        const seconds = (diff % 60).toString().padStart(2, '0');
        if (timerDisplay) timerDisplay.textContent = `${hours}:${minutes}:${seconds}`;
    }

    if (activeLogId && activeStartTimestamp) {
        startTime = parseInt(activeStartTimestamp);
        if (activeSection) activeSection.style.display = 'block';
        if (quickSection) quickSection.style.display = 'none';
        if (currentTaskEl) currentTaskEl.textContent = activeTaskName || 'Active Session';
        if (activeProjBadge) activeProjBadge.textContent = activeProjectName || 'Project';
        if (sessionStartEl) sessionStartEl.innerHTML = `<i class="mdi mdi-clock-start me-1"></i>Started at ${new Date(startTime).toLocaleTimeString()}`;
        timerInterval = setInterval(updateTimerDisplay, 1000);
        updateTimerDisplay();
    }

    // Quick Start Timer Button
    const startTimerBtn = document.getElementById('startTimerBtn');
    if (startTimerBtn) {
        startTimerBtn.addEventListener('click', function() {
            const projectSelect = document.getElementById('quickProjectSelect');
            const taskInput = document.getElementById('quickTaskInput');
            const projectId = projectSelect.value;
            const taskName = taskInput.value.trim() || 'Work session';
            const selectedOpt = projectSelect.options[projectSelect.selectedIndex];
            const projectName = selectedOpt ? selectedOpt.text.trim() : 'Project';

            const { token, header } = getCsrfInfo();
            const fd = new FormData();
            if (projectId) fd.append('project_id', projectId);
            fd.append('task_name', taskName);
            if (token) fd.append('csrf_token', token);

            const headers = { 'X-Requested-With': 'XMLHttpRequest' };
            if (token && header) headers[header] = token;

            startTimerBtn.disabled = true;
            startTimerBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

            fetch('<?= site_url('time/start') ?>', {
                method: 'POST',
                body: fd,
                headers: headers
            })
            .then(r => r.json())
            .then(res => {
                startTimerBtn.disabled = false;
                startTimerBtn.innerHTML = '<i class="mdi mdi-play me-1"></i> Start Timer';

                if (res.status === 'success' || res.success) {
                    activeLogId = res.id;
                    activeTaskName = taskName;
                    activeProjectName = projectName;
                    startTime = new Date().getTime();

                    localStorage.setItem('active_time_log_id', activeLogId);
                    localStorage.setItem('active_task_name', activeTaskName);
                    localStorage.setItem('active_project_name', activeProjectName);
                    localStorage.setItem('active_start_time', startTime.toString());

                    if (activeSection) activeSection.style.display = 'block';
                    if (quickSection) quickSection.style.display = 'none';
                    if (currentTaskEl) currentTaskEl.textContent = activeTaskName;
                    if (activeProjBadge) activeProjBadge.textContent = activeProjectName;
                    if (sessionStartEl) sessionStartEl.innerHTML = `<i class="mdi mdi-clock-start me-1"></i>Started at ${new Date(startTime).toLocaleTimeString()}`;

                    clearInterval(timerInterval);
                    timerInterval = setInterval(updateTimerDisplay, 1000);
                    updateTimerDisplay();

                    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: `Timer started for "${taskName}"`, timer: 2000, showConfirmButton: false });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: res.message || 'Could not start timer.' });
                }
            })
            .catch(err => {
                startTimerBtn.disabled = false;
                startTimerBtn.innerHTML = '<i class="mdi mdi-play me-1"></i> Start Timer';
                Swal.fire({ icon: 'error', title: 'Error', text: err.message || 'Request failed.' });
            });
        });
    }

    // Stop Timer Button
    const stopTimerBtn = document.getElementById('stopTimerBtn');
    if (stopTimerBtn) {
        stopTimerBtn.addEventListener('click', function() {
            if (!activeLogId) return;

            stopTimerBtn.disabled = true;
            stopTimerBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Recording...';

            const { token, header } = getCsrfInfo();
            const fd = new FormData();
            if (token) fd.append('csrf_token', token);

            const headers = { 'X-Requested-With': 'XMLHttpRequest' };
            if (token && header) headers[header] = token;

            fetch('<?= site_url('time/stop/') ?>' + activeLogId, {
                method: 'POST',
                body: fd,
                headers: headers
            })
            .then(r => r.json())
            .then(res => {
                stopTimerBtn.disabled = false;
                stopTimerBtn.innerHTML = '<i class="mdi mdi-stop-circle me-1"></i> Stop & Record';

                if (res.status === 'success' || res.success) {
                    clearInterval(timerInterval);
                    localStorage.removeItem('active_time_log_id');
                    localStorage.removeItem('active_task_name');
                    localStorage.removeItem('active_project_name');
                    localStorage.removeItem('active_start_time');

                    Swal.fire({
                        icon: 'success',
                        title: 'Worklog Recorded!',
                        text: res.message || 'Session logged successfully.',
                        confirmButtonColor: '#727cf5'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: res.message || 'Could not stop timer.' });
                }
            })
            .catch(err => {
                stopTimerBtn.disabled = false;
                stopTimerBtn.innerHTML = '<i class="mdi mdi-stop-circle me-1"></i> Stop & Record';
                Swal.fire({ icon: 'error', title: 'Error', text: err.message || 'Request failed.' });
            });
        });
    }

    // 4. Quick Preset Chips (Stopwatch bar)
    document.querySelectorAll('.quick-chip-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const minutes = parseInt(this.getAttribute('data-preset') || '30');
            const projectSelect = document.getElementById('quickProjectSelect');
            const taskInput = document.getElementById('quickTaskInput');

            const modal = new bootstrap.Modal(document.getElementById('manualEntryModal'));
            document.getElementById('manual_duration').value = (minutes / 60).toFixed(2);
            if (projectSelect.value) {
                document.getElementById('manual_project_id').value = projectSelect.value;
            }
            if (taskInput.value) {
                document.getElementById('manual_task_name').value = taskInput.value;
            }
            modal.show();
        });
    });

    // 5. Modal Preset Chips
    document.querySelectorAll('.modal-chip-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('manual_duration').value = this.getAttribute('data-hours');
            document.querySelectorAll('.modal-chip-btn').forEach(b => b.classList.remove('btn-secondary'));
            this.classList.add('btn-secondary');
        });
    });

    // 6. Manual Log Form Submit (AJAX)
    const manualForm = document.getElementById('manualTimeForm');
    if (manualForm) {
        manualForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('saveManualTimeBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

            const fd = new FormData(manualForm);
            fetch(manualForm.action, {
                method: 'POST',
                body: fd,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(res => {
                btn.disabled = false;
                btn.innerHTML = '<i class="mdi mdi-check me-1"></i> Save Worklog';

                if (res.status === 'success' || res.success) {
                    const modalEl = document.getElementById('manualEntryModal');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();

                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: res.message || 'Worklog saved.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    setTimeout(() => window.location.reload(), 800);
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: res.message || 'Failed to save log.' });
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = '<i class="mdi mdi-check me-1"></i> Save Worklog';
                Swal.fire({ icon: 'error', title: 'Error', text: err.message || 'Request failed.' });
            });
        });
    }

    // 7. Search & Filter on Table
    const searchInput = document.getElementById('searchLogsInput');
    const filterProj = document.getElementById('filterProjectSelect');

    function filterTable() {
        const query = (searchInput ? searchInput.value : '').toLowerCase();
        const selectedProj = (filterProj ? filterProj.value : '').toLowerCase();
        const rows = document.querySelectorAll('.time-log-row');

        rows.forEach(row => {
            const task = row.getAttribute('data-task') || '';
            const proj = row.getAttribute('data-project') || '';
            const user = row.getAttribute('data-user') || '';
            const matchesQuery = task.includes(query) || proj.includes(query) || user.includes(query);
            const matchesProj = !selectedProj || proj === selectedProj;

            row.style.display = (matchesQuery && matchesProj) ? '' : 'none';
        });
    }

    if (searchInput) searchInput.addEventListener('input', filterTable);
    if (filterProj) filterProj.addEventListener('change', filterTable);

    // 8. Re-track Task Button
    document.addEventListener('click', function(e) {
        const retrackBtn = e.target.closest('.btn-retrack');
        if (!retrackBtn) return;

        const projectId = retrackBtn.getAttribute('data-project-id');
        const taskName = retrackBtn.getAttribute('data-task-name');

        if (projectId) {
            const projSelect = document.getElementById('quickProjectSelect');
            if (projSelect) projSelect.value = projectId;
        }
        if (taskName) {
            const taskInput = document.getElementById('quickTaskInput');
            if (taskInput) taskInput.value = taskName;
        }

        window.scrollTo({ top: 0, behavior: 'smooth' });
        const startBtn = document.getElementById('startTimerBtn');
        if (startBtn) startBtn.click();
    });

    // 9. AJAX Delete Time Log
    document.addEventListener('click', function(e) {
        const delBtn = e.target.closest('.btn-delete-log');
        if (!delBtn) return;

        const logId = delBtn.getAttribute('data-id');
        if (!logId) return;

        Swal.fire({
            title: 'Delete Worklog?',
            text: 'Are you sure you want to permanently delete this logged work session?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#fa5c7c',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="mdi mdi-trash-can me-1"></i> Yes, delete log'
        }).then((result) => {
            if (result.isConfirmed) {
                const tr = delBtn.closest('tr');
                const origHtml = delBtn.innerHTML;
                delBtn.disabled = true;
                delBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

                const { token, header } = getCsrfInfo();
                const fd = new FormData();
                if (token) fd.append('csrf_token', token);

                const headers = { 'X-Requested-With': 'XMLHttpRequest' };
                if (token && header) headers[header] = token;

                fetch('<?= site_url('time/delete/') ?>' + logId, {
                    method: 'POST',
                    body: fd,
                    headers: headers
                })
                .then(r => r.json())
                .then(res => {
                    if (res.status === 'success' || res.success) {
                        if (tr) {
                            tr.style.transition = 'all 0.3s ease';
                            tr.style.opacity = '0';
                            tr.style.transform = 'scale(0.95)';
                            setTimeout(() => {
                                tr.remove();
                                const rows = document.querySelectorAll('.time-log-row');
                                if (rows.length === 0) {
                                    const tbody = document.getElementById('timeLogsTableBody');
                                    if (tbody) {
                                        tbody.innerHTML = `<tr><td colspan="<?= $isManager ? '7' : '6' ?>" class="text-center py-4 text-muted">All logs deleted.</td></tr>`;
                                    }
                                }
                            }, 300);
                        }
                        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: res.message || 'Log deleted.', timer: 2000, showConfirmButton: false });
                    } else {
                        delBtn.disabled = false;
                        delBtn.innerHTML = origHtml;
                        Swal.fire({ icon: 'error', title: 'Error', text: res.message || 'Could not delete log.' });
                    }
                })
                .catch(err => {
                    delBtn.disabled = false;
                    delBtn.innerHTML = origHtml;
                    Swal.fire({ icon: 'error', title: 'Error', text: err.message || 'Request failed.' });
                });
            }
        });
    });
});
</script>

<?= $this->endSection() ?>
