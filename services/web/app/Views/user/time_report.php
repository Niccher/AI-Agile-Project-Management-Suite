<?= $this->extend('layouts/hyper/main') ?>

<?= $this->section('title') ?>Time Reports &amp; Billing<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="page-title">
                <i class="mdi mdi-clock-check-outline text-primary me-1"></i> Time Reports &amp; Invoicing
            </h4>
            <div class="page-title-right d-flex gap-2">
                <a href="<?= site_url('time') ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="mdi mdi-arrow-left me-1"></i> Back to Live Tracker
                </a>
                <a href="<?= site_url('time/report/csv') ?>?<?= http_build_query($filters) ?>" class="btn btn-outline-success btn-sm">
                    <i class="mdi mdi-file-delimited-outline me-1"></i> Export CSV
                </a>
                <a href="<?= site_url('time/report/pdf') ?>?<?= http_build_query($filters) ?>" target="_blank" class="btn btn-danger btn-sm">
                    <i class="mdi mdi-file-pdf-box me-1"></i> Export PDF Invoice
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Filters Card -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" action="<?= site_url('time/report') ?>" class="row g-2 align-items-end">
            <div class="col-md-2 col-sm-6">
                <label class="form-label font-12 text-muted mb-1">From Date</label>
                <input type="date" name="from_date" class="form-control form-control-sm" value="<?= esc($filters['from_date']) ?>">
            </div>
            <div class="col-md-2 col-sm-6">
                <label class="form-label font-12 text-muted mb-1">To Date</label>
                <input type="date" name="to_date" class="form-control form-control-sm" value="<?= esc($filters['to_date']) ?>">
            </div>
            <div class="col-md-3 col-sm-6">
                <label class="form-label font-12 text-muted mb-1">Project</label>
                <select name="project_id" class="form-select form-select-sm">
                    <option value="">All Projects</option>
                    <?php foreach ($projects as $proj): ?>
                        <option value="<?= $proj['id'] ?>" <?= ($filters['project_id'] == $proj['id']) ? 'selected' : '' ?>>
                            <?= esc($proj['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if ($isAdmin): ?>
            <div class="col-md-2 col-sm-6">
                <label class="form-label font-12 text-muted mb-1">Team Member</label>
                <select name="user_id" class="form-select form-select-sm">
                    <option value="">All Members</option>
                    <?php foreach ($users as $u): 
                        $uId = is_array($u) ? ($u['id'] ?? '') : ($u->id ?? '');
                        $uName = is_array($u) ? ($u['username'] ?? $u['email'] ?? ('User #' . $uId)) : ($u->username ?? $u->email ?? ('User #' . $uId));
                    ?>
                        <option value="<?= esc($uId) ?>" <?= ((string)$filters['user_id'] === (string)$uId) ? 'selected' : '' ?>>
                            <?= esc($uName) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            <div class="col-md-2 col-sm-6">
                <label class="form-label font-12 text-muted mb-1">Billing Type</label>
                <select name="is_billable" class="form-select form-select-sm">
                    <option value="all" <?= ($filters['is_billable'] === 'all') ? 'selected' : '' ?>>All Hours</option>
                    <option value="1" <?= ($filters['is_billable'] === '1') ? 'selected' : '' ?>>Billable Only</option>
                    <option value="0" <?= ($filters['is_billable'] === '0') ? 'selected' : '' ?>>Non-Billable Only</option>
                </select>
            </div>
            <div class="col-md-1 col-sm-12 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm w-100" title="Apply Filter">
                    <i class="mdi mdi-filter-variant"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Summary Metrics Cards -->
<div class="row mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 avatar-sm bg-primary-lighten rounded-circle d-flex align-items-center justify-content-center me-3">
                        <i class="mdi mdi-timer-outline font-20 text-primary"></i>
                    </div>
                    <div>
                        <h6 class="text-muted font-12 text-uppercase mb-0">Total Tracked</h6>
                        <h3 class="my-1 fw-bold text-dark"><?= $summary['total_hours'] ?> <small class="font-14 text-muted">hrs</small></h3>
                        <small class="text-muted font-11"><?= count($logs) ?> total log entries</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 avatar-sm bg-success-lighten rounded-circle d-flex align-items-center justify-content-center me-3">
                        <i class="mdi mdi-cash-multiple font-20 text-success"></i>
                    </div>
                    <div>
                        <h6 class="text-muted font-12 text-uppercase mb-0">Billable Hours</h6>
                        <h3 class="my-1 fw-bold text-success"><?= $summary['billable_hours'] ?> <small class="font-14 text-muted">hrs</small></h3>
                        <small class="text-muted font-11"><?= $summary['total_hours'] > 0 ? round(($summary['billable_hours'] / $summary['total_hours']) * 100, 1) : 0 ?>% billable ratio</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 avatar-sm bg-info-lighten rounded-circle d-flex align-items-center justify-content-center me-3">
                        <i class="mdi mdi-currency-usd font-20 text-info"></i>
                    </div>
                    <div>
                        <h6 class="text-muted font-12 text-uppercase mb-0">Est. Billable Value</h6>
                        <h3 class="my-1 fw-bold text-info">$<?= number_format($summary['total_amount'], 2) ?></h3>
                        <small class="text-muted font-11">Based on hourly rates</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 avatar-sm bg-secondary-lighten rounded-circle d-flex align-items-center justify-content-center me-3">
                        <i class="mdi mdi-coffee-outline font-20 text-secondary"></i>
                    </div>
                    <div>
                        <h6 class="text-muted font-12 text-uppercase mb-0">Non-Billable</h6>
                        <h3 class="my-1 fw-bold text-muted"><?= $summary['non_billable_hours'] ?> <small class="font-14 text-muted">hrs</small></h3>
                        <small class="text-muted font-11">Internal / administrative</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Logs Table & Project Breakdown -->
<div class="row">
    <!-- Log Entries Table -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center py-3">
                <h5 class="header-title mb-0 font-15">
                    <i class="mdi mdi-format-list-bulleted me-1 text-primary"></i> Detailed Time Entries
                </h5>
                <span class="badge bg-light text-dark font-12"><?= count($logs) ?> records</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light font-12">
                            <tr>
                                <th>Activity / Task</th>
                                <th>Project</th>
                                <?php if ($isAdmin): ?><th>Member</th><?php endif; ?>
                                <th>Date</th>
                                <th>Duration</th>
                                <th>Rate</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody class="font-13">
                            <?php if (empty($logs)): ?>
                                <tr>
                                    <td colspan="<?= $isAdmin ? 7 : 6 ?>" class="text-center py-5 text-muted">
                                        <i class="mdi mdi-timer-off-outline font-24 d-block mb-1 opacity-50"></i>
                                        No time entries match the selected filters.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($logs as $log): 
                                    $hrs = round(($log['duration'] ?? 0) / 3600, 2);
                                    $isBillable = (int)($log['is_billable'] ?? 1) === 1;
                                    $rate = (float)($log['hourly_rate'] ?? 50.00);
                                    $amt = $isBillable ? ($hrs * $rate) : 0.00;
                                ?>
                                    <tr>
                                        <td>
                                            <div class="fw-semibold text-dark"><?= esc($log['task_name']) ?></div>
                                            <?php if (!empty($log['notes'])): ?>
                                                <small class="text-muted font-11 d-block text-truncate" style="max-width: 250px;">
                                                    <?= esc($log['notes']) ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($log['project_name'])): ?>
                                                <span class="badge font-11 px-2 py-1" style="background-color: <?= $log['project_color'] ?: '#727cf5' ?>20; color: <?= $log['project_color'] ?: '#727cf5' ?>; border: 1px solid <?= $log['project_color'] ?: '#727cf5' ?>40;">
                                                    <?= esc($log['project_name']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted font-12">General</span>
                                            <?php endif; ?>
                                        </td>
                                        <?php if ($isAdmin): ?>
                                            <td>
                                                <small class="fw-medium text-dark"><?= esc($log['user_name'] ?? 'User #' . $log['user_id']) ?></small>
                                            </td>
                                        <?php endif; ?>
                                        <td>
                                            <span class="font-12 text-muted"><?= date('M j, Y', strtotime($log['start_time'])) ?></span>
                                            <small class="text-muted font-10 d-block"><?= date('g:i A', strtotime($log['start_time'])) ?></small>
                                        </td>
                                        <td>
                                            <span class="fw-bold"><?= $hrs ?>h</span>
                                            <?php if ($isBillable): ?>
                                                <span class="badge bg-success-lighten text-success font-10 ms-1">Billable</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary-lighten text-muted font-10 ms-1">Non-billable</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="font-12 text-muted">$<?= number_format($rate, 2) ?>/h</td>
                                        <td class="fw-bold <?= $isBillable ? 'text-success' : 'text-muted' ?>">
                                            $<?= number_format($amt, 2) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Project Breakdown Sidebar -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-bottom py-3">
                <h5 class="header-title mb-0 font-15">
                    <i class="mdi mdi-chart-pie me-1 text-primary"></i> Hours by Project
                </h5>
            </div>
            <div class="card-body p-3">
                <?php if (empty($summary['by_project'])): ?>
                    <p class="text-muted text-center py-4 font-13">No project data for this period.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($summary['by_project'] as $pData): 
                            $pHours = round($pData['duration'] / 3600, 2);
                            $pct = $summary['total_hours'] > 0 ? round(($pHours / $summary['total_hours']) * 100) : 0;
                        ?>
                            <div class="list-group-item px-0 py-2 border-0">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="font-13 fw-semibold text-dark text-truncate" style="max-width: 180px;">
                                        <span class="rounded-circle d-inline-block me-1" style="width: 8px; height: 8px; background-color: <?= $pData['color'] ?>;"></span>
                                        <?= esc($pData['name']) ?>
                                    </span>
                                    <span class="font-13 fw-bold text-dark"><?= $pHours ?>h <small class="text-muted font-11">($<?= number_format($pData['billable_amount'], 2) ?>)</small></span>
                                </div>
                                <div class="progress progress-sm">
                                    <div class="progress-bar" role="progressbar" style="width: <?= $pct ?>%; background-color: <?= $pData['color'] ?>;" aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($isAdmin && !empty($summary['by_user'])): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-bottom py-3">
                <h5 class="header-title mb-0 font-15">
                    <i class="mdi mdi-account-group me-1 text-primary"></i> Hours by Team Member
                </h5>
            </div>
            <div class="card-body p-3">
                <div class="list-group list-group-flush">
                    <?php foreach ($summary['by_user'] as $uData): 
                        $uHours = round($uData['duration'] / 3600, 2);
                        $uPct = $summary['total_hours'] > 0 ? round(($uHours / $summary['total_hours']) * 100) : 0;
                    ?>
                        <div class="list-group-item px-0 py-2 border-0">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="font-13 fw-semibold text-dark"><?= esc($uData['name']) ?></span>
                                <span class="font-13 fw-bold text-primary"><?= $uHours ?>h <small class="text-muted font-11">(<?= $uPct ?>%)</small></span>
                            </div>
                            <div class="progress progress-sm">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $uPct ?>%;" aria-valuenow="<?= $uPct ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
