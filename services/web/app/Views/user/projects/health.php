<?= $this->extend('layouts/hyper/main') ?>

<?= $this->section('title') ?>Project Health & Risk Audit<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="page-title">
                <i class="mdi mdi-heart-pulse text-danger me-1"></i> Project Health & Risk Monitor
            </h4>
            <div class="page-title-right d-flex gap-2">
                <a href="<?= site_url('projects') ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="mdi mdi-view-grid-outline me-1"></i> All Projects
                </a>
                <a href="<?= site_url('projects/create') ?>" class="btn btn-primary btn-sm">
                    <i class="mdi mdi-plus me-1"></i> Create Project
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Health Summary Metrics -->
<div class="row mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 avatar-sm bg-primary-lighten rounded-circle d-flex align-items-center justify-content-center me-3">
                        <i class="mdi mdi-speedometer font-20 text-primary"></i>
                    </div>
                    <div>
                        <h6 class="text-muted font-12 text-uppercase mb-0">Avg Workspace Health</h6>
                        <h3 class="my-1 fw-bold text-dark"><?= $avgScore ?><small class="font-14 text-muted">/100</small></h3>
                        <small class="text-muted font-11">Across <?= $totalCount ?> total projects</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <a href="<?= site_url('projects/health?filter=danger') ?>" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 <?= $currentFilter === 'danger' ? 'border border-danger' : '' ?>">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 avatar-sm bg-danger-lighten rounded-circle d-flex align-items-center justify-content-center me-3">
                            <i class="mdi mdi-alert-circle-outline font-20 text-danger"></i>
                        </div>
                        <div>
                            <h6 class="text-muted font-12 text-uppercase mb-0">At Risk</h6>
                            <h3 class="my-1 fw-bold text-danger"><?= $atRiskCount ?></h3>
                            <small class="text-danger font-11"><i class="mdi mdi-alert me-1"></i>Requires urgent attention</small>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3 col-sm-6">
        <a href="<?= site_url('projects/health?filter=warning') ?>" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 <?= $currentFilter === 'warning' ? 'border border-warning' : '' ?>">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 avatar-sm bg-warning-lighten rounded-circle d-flex align-items-center justify-content-center me-3">
                            <i class="mdi mdi-alert-outline font-20 text-warning"></i>
                        </div>
                        <div>
                            <h6 class="text-muted font-12 text-uppercase mb-0">Needs Attention</h6>
                            <h3 class="my-1 fw-bold text-warning"><?= $warningCount ?></h3>
                            <small class="text-warning font-11">Mild risk factors detected</small>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3 col-sm-6">
        <a href="<?= site_url('projects/health?filter=healthy') ?>" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 <?= $currentFilter === 'healthy' ? 'border border-success' : '' ?>">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 avatar-sm bg-success-lighten rounded-circle d-flex align-items-center justify-content-center me-3">
                            <i class="mdi mdi-check-decagram font-20 text-success"></i>
                        </div>
                        <div>
                            <h6 class="text-muted font-12 text-uppercase mb-0">Healthy</h6>
                            <h3 class="my-1 fw-bold text-success"><?= $healthyCount ?></h3>
                            <small class="text-success font-11">On track & progressing</small>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Filters Bar -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="btn-group btn-group-sm">
        <a href="<?= site_url('projects/health') ?>" class="btn <?= empty($currentFilter) ? 'btn-primary' : 'btn-outline-secondary' ?>">
            All Projects (<?= $totalCount ?>)
        </a>
        <a href="<?= site_url('projects/health?filter=danger') ?>" class="btn <?= $currentFilter === 'danger' ? 'btn-danger' : 'btn-outline-secondary' ?>">
            🔴 At Risk (<?= $atRiskCount ?>)
        </a>
        <a href="<?= site_url('projects/health?filter=warning') ?>" class="btn <?= $currentFilter === 'warning' ? 'btn-warning' : 'btn-outline-secondary' ?>">
            🟡 Needs Attention (<?= $warningCount ?>)
        </a>
        <a href="<?= site_url('projects/health?filter=healthy') ?>" class="btn <?= $currentFilter === 'healthy' ? 'btn-success' : 'btn-outline-secondary' ?>">
            🟢 Healthy (<?= $healthyCount ?>)
        </a>
    </div>
    <span class="font-12 text-muted">
        <i class="mdi mdi-information-outline me-1"></i>Sorted by risk severity (lowest score first)
    </span>
</div>

<!-- Projects Health Cards / Table -->
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light font-12">
                            <tr>
                                <th style="width: 25%;">Project</th>
                                <th style="width: 15%;">Health Score</th>
                                <th style="width: 12%;">Status / Progress</th>
                                <th style="width: 33%;">Risk & Staleness Factors</th>
                                <th style="width: 15%; text-align: right;">Quick Actions</th>
                            </tr>
                        </thead>
                        <tbody class="font-13">
                            <?php if (empty($projects)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="mdi mdi-check-circle font-24 d-block mb-1 text-success opacity-75"></i>
                                        No projects match the selected health filter.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($projects as $p): 
                                    $h = $p['health'];
                                    $pSlug = $p['slug'] ?? $p['id'];
                                    $progressBarClass = $h['score'] >= 80 ? 'bg-success' : ($h['score'] >= 50 ? 'bg-warning' : 'bg-danger');
                                ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-xs me-2 flex-shrink-0">
                                                    <span class="avatar-title rounded font-14" style="background-color: <?= $p['color'] ?: '#727cf5' ?>20; color: <?= $p['color'] ?: '#727cf5' ?>;">
                                                        <i class="mdi <?= $p['icon'] ?: 'mdi-folder' ?>"></i>
                                                    </span>
                                                </div>
                                                <div>
                                                    <a href="<?= site_url('projects/view/' . $pSlug) ?>" class="text-dark fw-bold text-decoration-none font-14">
                                                        <?= esc($p['name']) ?>
                                                    </a>
                                                    <?php if (!empty($p['due_date'])): ?>
                                                        <small class="text-muted font-11 d-block">
                                                            <i class="mdi mdi-calendar-clock me-1"></i>Target: <?= date('M j, Y', strtotime($p['due_date'])) ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center mb-1">
                                                <span class="badge <?= $h['badge_class'] ?> font-12 fw-bold px-2 py-1 me-2">
                                                    <i class="mdi <?= $h['icon'] ?> me-1"></i> <?= $h['score'] ?>/100
                                                </span>
                                                <span class="font-12 fw-semibold text-muted"><?= $h['label'] ?></span>
                                            </div>
                                            <div class="progress progress-sm" style="height: 6px; width: 140px;">
                                                <div class="progress-bar <?= $progressBarClass ?>" role="progressbar" style="width: <?= $h['score'] ?>%;" aria-valuenow="<?= $h['score'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark font-11 border mb-1">
                                                <?= ucfirst(str_replace('_', ' ', $p['status'] ?? 'planning')) ?>
                                            </span>
                                            <div class="font-11 text-muted">
                                                Progress: <strong><?= $p['progress'] ?? 0 ?>%</strong> (<?= $h['done_tasks'] ?>/<?= $h['total_tasks'] ?> tasks)
                                            </div>
                                        </td>
                                        <td>
                                            <?php if (empty($h['issues'])): ?>
                                                <span class="text-success font-12">
                                                    <i class="mdi mdi-check-circle-outline me-1"></i> No risk factors detected. Project is healthy.
                                                </span>
                                            <?php else: ?>
                                                <ul class="list-unstyled mb-0 font-12">
                                                    <?php foreach ($h['issues'] as $issue): ?>
                                                        <li class="text-danger mb-1">
                                                            <i class="mdi mdi-close-circle-outline me-1"></i> <?= esc($issue) ?>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php endif; ?>
                                        </td>
                                        <td style="text-align: right;">
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?= site_url('projects/kanban/' . $pSlug) ?>" class="btn btn-outline-primary" title="Open Kanban Board">
                                                    <i class="mdi mdi-view-column-outline"></i> Kanban
                                                </a>
                                                <a href="<?= site_url('projects/sprints/' . $pSlug) ?>" class="btn btn-outline-info" title="Open Sprints">
                                                    <i class="mdi mdi-run-fast"></i> Sprints
                                                </a>
                                                <a href="<?= site_url('projects/view/' . $pSlug) ?>" class="btn btn-outline-secondary" title="View Project Details">
                                                    <i class="mdi mdi-eye-outline"></i>
                                                </a>
                                            </div>
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
</div>
<?= $this->endSection() ?>
