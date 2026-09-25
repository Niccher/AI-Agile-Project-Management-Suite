<?= $this->extend('layouts/hyper/main') ?>

<?= $this->section('title') ?>Analytics • <?= esc(setting('App.siteName')) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <button class="btn btn-primary rounded-pill" id="exportAnalyticsBtn">
                    <i class="mdi mdi-download me-1"></i> Export Report
                </button>
            </div>
            <h4 class="page-title"><i class="uil-chart-line me-2 text-primary"></i> Analytics & Performance</h4>
        </div>
    </div>
</div>

<!-- Key Metrics -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card widget-flat h-100 mb-0 shadow-sm border-0">
            <div class="card-body">
                <div class="float-end">
                    <div class="avatar-sm bg-primary-lighten text-primary rounded-circle d-flex align-items-center justify-content-center">
                        <i class="uil-folder font-22"></i>
                    </div>
                </div>
                <h5 class="text-muted fw-normal mt-0" title="Total Projects">Total Projects</h5>
                <h3 class="mt-3 mb-1 fw-bold"><?= esc($totalProjects) ?></h3>
                <p class="mb-0 text-muted font-13">
                    <span class="text-primary me-1"><i class="mdi mdi-arrow-up-bold"></i> <?= esc($thisMonthStarted) ?></span>
                    <span>started this month</span>
                </p>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card widget-flat h-100 mb-0 shadow-sm border-0">
            <div class="card-body">
                <div class="float-end">
                    <div class="avatar-sm bg-success-lighten text-success rounded-circle d-flex align-items-center justify-content-center">
                        <i class="uil-check-circle font-22"></i>
                    </div>
                </div>
                <h5 class="text-muted fw-normal mt-0" title="Completion Rate">Completion Rate</h5>
                <h3 class="mt-3 mb-1 fw-bold text-success"><?= esc(round($completionRate)) ?>%</h3>
                <p class="mb-0 text-muted font-13">
                    <span class="text-success me-1"><i class="mdi mdi-trending-up"></i> Overall</span>
                    <span>delivery health</span>
                </p>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card widget-flat h-100 mb-0 shadow-sm border-0">
            <div class="card-body">
                <div class="float-end">
                    <div class="avatar-sm bg-warning-lighten text-warning rounded-circle d-flex align-items-center justify-content-center">
                        <i class="uil-clock font-22"></i>
                    </div>
                </div>
                <h5 class="text-muted fw-normal mt-0" title="Hours Logged">Hours Logged</h5>
                <h3 class="mt-3 mb-1 fw-bold text-warning"><?= esc(number_format($totalHours, 1)) ?></h3>
                <p class="mb-0 text-muted font-13">
                    <span class="text-warning me-1"><i class="mdi mdi-timer-sand"></i> Total</span>
                    <span>tracked across projects</span>
                </p>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card widget-flat h-100 mb-0 shadow-sm border-0">
            <div class="card-body">
                <div class="float-end">
                    <div class="avatar-sm bg-info-lighten text-info rounded-circle d-flex align-items-center justify-content-center">
                        <i class="uil-tachometer-fast font-22"></i>
                    </div>
                </div>
                <h5 class="text-muted fw-normal mt-0" title="Avg Daily Hours">Avg Daily Hours</h5>
                <h3 class="mt-3 mb-1 fw-bold text-info"><?= esc(number_format($avgDaily, 1)) ?></h3>
                <p class="mb-0 text-muted font-13">
                    <span class="text-info me-1"><i class="mdi mdi-speedometer"></i> Daily</span>
                    <span>average commitment</span>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row 1 -->
<div class="row mb-4">
    <!-- Project Completion Chart -->
    <div class="col-xl-8 col-lg-7 mb-3 mb-xl-0">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header d-flex justify-content-between align-items-center bg-transparent border-bottom py-3">
                <h5 class="header-title mb-0">
                    <i class="uil-chart-line me-1 text-primary"></i> Project Completion Trends
                </h5>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-secondary active">Monthly</button>
                    <button class="btn btn-outline-secondary">Quarterly</button>
                    <button class="btn btn-outline-secondary">Yearly</button>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <div class="chart-header mb-3">
                        <div class="chart-legend d-flex gap-3">
                            <span class="legend-item d-flex align-items-center gap-1 font-13">
                                <span class="legend-color rounded-circle d-inline-block" style="width: 10px; height: 10px; background-color: #727cf5;"></span> Started
                            </span>
                            <span class="legend-item d-flex align-items-center gap-1 font-13">
                                <span class="legend-color rounded-circle d-inline-block" style="width: 10px; height: 10px; background-color: #0acf97;"></span> Completed
                            </span>
                        </div>
                    </div>

                    <div class="bar-chart">
                        <?php
                        $maxVal = 1;
                        foreach ($monthlyTrends as $row) {
                            $maxVal = max($maxVal, $row['started'], $row['completed']);
                        }
                        ?>
                        <?php foreach ($monthlyTrends as $row): ?>
                        <div class="chart-row d-flex align-items-center mb-2">
                            <div class="chart-label font-13 text-muted" style="width: 70px;"><?= esc($row['month']) ?></div>
                            <div class="chart-bars flex-grow-1 mx-2" style="height: 20px; background-color: rgba(0,0,0,0.03); border-radius: 4px; overflow: hidden; position: relative;">
                                <div class="bar started position-absolute top-0 bottom-0" style="width: <?= ($row['started'] / $maxVal) * 100 ?>%; background-color: #727cf5; opacity: 0.8; height: 100%;"></div>
                                <div class="bar completed position-absolute top-0 bottom-0" style="width: <?= ($row['completed'] / $maxVal) * 100 ?>%; background-color: #0acf97; height: 100%;"></div>
                            </div>
                            <div class="chart-value font-12 text-muted fw-bold text-end" style="width: 60px;"><?= esc($row['completed']) ?> / <?= esc($row['started']) ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Project Health Distribution -->
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-transparent border-bottom py-3">
                <h5 class="header-title mb-0">
                    <i class="uil-chart-pie me-1 text-primary"></i> Project Health Distribution
                </h5>
            </div>
            <div class="card-body">
                <div class="pie-chart-container text-center py-2">
                    <?php
                    $segments = [
                        ['pct' => $goodPct, 'color' => '#0acf97'],
                        ['pct' => $warningPct, 'color' => '#ffbc00'],
                        ['pct' => $dangerPct, 'color' => '#fa5c7c'],
                        ['pct' => $archivedPct, 'color' => '#98a6ad'],
                    ];
                    $start = 0;
                    $parts = [];
                    foreach ($segments as $seg) {
                        if ($seg['pct'] > 0) {
                            $end = $start + $seg['pct'];
                            $parts[] = $seg['color'] . ' ' . $start . '% ' . $end . '%';
                            $start = $end;
                        }
                    }
                    $pieGradient = !empty($parts) ? implode(', ', $parts) : '#727cf5 0% 100%';
                    $totalHealthCount = $good + $warningCount + $dangerCount + $archivedCount;
                    ?>
                    <div class="pie-chart mx-auto position-relative rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 140px; height: 140px; background: conic-gradient(<?= $pieGradient ?>);">
                        <div class="pie-center bg-card rounded-circle d-flex flex-column align-items-center justify-content-center" style="width: 90px; height: 90px; background: var(--bs-card-bg, #fff);">
                            <div class="pie-value fw-bold font-20 text-body"><?= esc($totalHealthCount) ?></div>
                            <div class="pie-label text-muted font-11">Projects</div>
                        </div>
                    </div>

                    <div class="pie-legend row g-2 text-start font-13 mt-2">
                        <div class="col-6">
                            <span class="d-inline-block rounded-circle me-1" style="width: 10px; height: 10px; background-color: #0acf97;"></span>
                            <span class="text-body">Good (<?= esc(round($goodPct)) ?>%)</span>
                        </div>
                        <div class="col-6">
                            <span class="d-inline-block rounded-circle me-1" style="width: 10px; height: 10px; background-color: #ffbc00;"></span>
                            <span class="text-body">Warning (<?= esc(round($warningPct)) ?>%)</span>
                        </div>
                        <div class="col-6">
                            <span class="d-inline-block rounded-circle me-1" style="width: 10px; height: 10px; background-color: #fa5c7c;"></span>
                            <span class="text-body">Danger (<?= esc(round($dangerPct)) ?>%)</span>
                        </div>
                        <div class="col-6">
                            <span class="d-inline-block rounded-circle me-1" style="width: 10px; height: 10px; background-color: #98a6ad;"></span>
                            <span class="text-body">Archived (<?= esc(round($archivedPct)) ?>%)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row 2 -->
<div class="row mb-4">
    <!-- Time Distribution -->
    <div class="col-xl-6 mb-3 mb-xl-0">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header d-flex justify-content-between align-items-center bg-transparent border-bottom py-3">
                <h5 class="header-title mb-0">
                    <i class="uil-clock me-1 text-primary"></i> Time Distribution by Project
                </h5>
                <a href="<?= site_url('time') ?>" class="btn btn-sm btn-outline-secondary">
                    Details
                </a>
            </div>
            <div class="card-body">
                <div class="time-distribution">
                    <?php if (!empty($timeDistribution)): ?>
                        <?php foreach ($timeDistribution as $item): ?>
                        <?php $pct = $allTimeTotal > 0 ? round(($item['total_duration'] / $allTimeTotal) * 100) : 0; ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1 font-13">
                                <span class="fw-semibold text-body">
                                    <span class="d-inline-block rounded-circle me-1" style="width: 10px; height: 10px; background-color: <?= esc($item['color']) ?>;"></span>
                                    <?= esc($item['name']) ?>
                                </span>
                                <span class="text-muted"><?= esc(number_format($item['total_duration'], 1)) ?> hrs (<?= $pct ?>%)</span>
                            </div>
                            <div class="progress progress-sm" style="height: 6px;">
                                <div class="progress-bar" style="width: <?= $pct ?>%; background-color: <?= esc($item['color']) ?>;"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted text-center py-4">No time logs recorded yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Productivity Heatmap -->
    <div class="col-xl-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-transparent border-bottom py-3">
                <h5 class="header-title mb-0">
                    <i class="uil-calendar-alt me-1 text-primary"></i> Monthly Activity Heatmap
                </h5>
            </div>
            <div class="card-body">
                <div class="heatmap-container">
                    <div class="d-flex justify-content-between align-items-center mb-2 font-13 text-muted">
                        <span>Last 30 Days</span>
                        <span>Activity density</span>
                    </div>

                    <div class="d-flex flex-wrap gap-1 mb-3">
                        <?php foreach ($heatmapData as $cell): ?>
                        <?php
                        $colors = ['#eef2f7', '#727cf533', '#727cf566', '#727cf599', '#727cf5'];
                        $color = $colors[$cell['count']] ?? '#727cf5';
                        ?>
                        <div class="heatmap-square rounded" style="width: 18px; height: 18px; background-color: <?= $color ?>;"
                             title="<?= esc($cell['date']) ?>: <?= esc($cell['count']) ?> activities"></div>
                        <?php endforeach; ?>
                    </div>

                    <div class="d-flex justify-content-between align-items-center font-12 text-muted">
                        <span>Less</span>
                        <div class="d-flex gap-1 align-items-center">
                            <span class="rounded" style="width: 12px; height: 12px; background-color: #eef2f7;"></span>
                            <span class="rounded" style="width: 12px; height: 12px; background-color: #727cf533;"></span>
                            <span class="rounded" style="width: 12px; height: 12px; background-color: #727cf566;"></span>
                            <span class="rounded" style="width: 12px; height: 12px; background-color: #727cf599;"></span>
                            <span class="rounded" style="width: 12px; height: 12px; background-color: #727cf5;"></span>
                        </div>
                        <span>More</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Insights & Recommendations -->
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header d-flex justify-content-between align-items-center bg-transparent border-bottom py-3">
                <h5 class="header-title mb-0">
                    <i class="uil-lightbulb-alt me-1 text-warning"></i> Insights & Recommendations
                </h5>
                <button class="btn btn-sm btn-outline-secondary" onclick="window.location.reload();">
                    <i class="mdi mdi-refresh me-1"></i> Refresh
                </button>
            </div>
            <div class="card-body">
                <?php if (!empty($insights)): ?>
                <div class="row g-3 mb-4">
                    <?php foreach ($insights as $insight): ?>
                    <div class="col-md-4">
                        <div class="p-3 rounded border border-start border-4 border-<?= esc($insight['color']) ?> bg-light-subtle h-100">
                            <div class="d-flex align-items-center mb-2">
                                <i class="<?= esc($insight['icon']) ?> text-<?= esc($insight['color']) ?> font-18 me-2"></i>
                                <h6 class="mb-0 text-body fw-bold"><?= esc($insight['title']) ?></h6>
                            </div>
                            <p class="small text-muted mb-0"><?= esc($insight['message']) ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 rounded border bg-light-subtle h-100">
                            <h6 class="text-body fw-bold border-bottom pb-2 mb-3">
                                <i class="mdi mdi-check-circle-outline text-success me-1"></i> Completed This Month
                            </h6>
                            <ul class="small text-muted list-unstyled mb-0 font-13">
                                <?php if (!empty($completedThisMonth)): ?>
                                <?php foreach ($completedThisMonth as $item): ?>
                                <li class="mb-2 d-flex align-items-center">
                                    <i class="mdi mdi-check-bold text-success me-2"></i>
                                    <span class="text-body"><?= esc($item) ?></span>
                                </li>
                                <?php endforeach; ?>
                                <?php else: ?>
                                <li class="text-muted">No projects completed this month.</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="p-3 rounded border bg-light-subtle h-100">
                            <h6 class="text-body fw-bold border-bottom pb-2 mb-3">
                                <i class="mdi mdi-alert-circle-outline text-warning me-1"></i> Need Attention
                            </h6>
                            <ul class="small text-muted list-unstyled mb-3 font-13">
                                <?php if (!empty($stalledTasks)): ?>
                                <?php foreach ($stalledTasks as $item): ?>
                                <li class="mb-2 d-flex align-items-center">
                                    <i class="mdi mdi-circle text-danger me-2" style="font-size: 8px;"></i>
                                    <span class="text-body"><?= esc($item) ?></span>
                                </li>
                                <?php endforeach; ?>
                                <?php else: ?>
                                <li class="text-muted">No stalled projects.</li>
                                <?php endif; ?>
                            </ul>
                            
                            <?php if (!empty($recentDone)): ?>
                            <h6 class="text-body fw-bold border-bottom pb-2 mb-3 mt-3">
                                <i class="mdi mdi-history text-info me-1"></i> Recently Completed
                            </h6>
                            <ul class="small text-muted list-unstyled mb-0 font-13">
                                <?php foreach ($recentDone as $item): ?>
                                <li class="mb-2 d-flex align-items-center">
                                    <i class="mdi mdi-check-circle text-info me-2"></i>
                                    <span class="text-body"><?= esc($item) ?></span>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3"></div>

<!-- Analytics JavaScript -->
<script>
    $(document).ready(function() {
        // Initialize tooltips
        $('[data-bs-toggle="tooltip"]').tooltip();

        // Export analytics
        $('#exportAnalyticsBtn').click(function() {
            showToast('Exporting analytics data...', 'info');
            setTimeout(() => {
                showToast('Analytics data exported successfully!', 'success');
            }, 1500);
        });

        // Chart time period buttons
        $('.btn-group .btn').click(function() {
            $(this).parent().find('.btn').removeClass('active');
            $(this).addClass('active');

            const period = $(this).text();
            showToast(`Showing analytics for: ${period}`, 'info');
        });

        // Toast notification function
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
            const toastElement = document.getElementById(toastId);
            const toast = new bootstrap.Toast(toastElement);
            toast.show();

            $(toastElement).on('hidden.bs.toast', function() {
                $(this).remove();
            });
        }
    });
</script>

<?= $this->endSection() ?>
