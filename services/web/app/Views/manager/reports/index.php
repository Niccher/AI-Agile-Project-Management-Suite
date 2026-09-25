<?= $this->extend('layouts/hyper/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-file-invoice text-primary me-2"></i> Team Reports</h2>
    </div>

    <?php if (session()->has('message')) : ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= session('message') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->has('error')) : ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Generate Report Form -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">Generate New Report</h5>
                </div>
                <div class="card-body">
                    <form action="<?= site_url('manage/reports/generate') ?>" method="POST">
                        <?= csrf_field() ?>
                        
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold">DATE RANGE</label>
                            <div class="row g-2">
                                <div class="col">
                                    <input type="date" name="period_start" class="form-control form-control-sm" required>
                                </div>
                                <div class="col-auto d-flex align-items-center text-muted">to</div>
                                <div class="col">
                                    <input type="date" name="period_end" class="form-control form-control-sm" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-muted small fw-bold">FORMAT</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="type" id="formatPdf" value="pdf" checked>
                                    <label class="form-check-label" for="formatPdf">
                                        <i class="fas fa-file-pdf text-danger"></i> PDF
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="type" id="formatCsv" value="csv">
                                    <label class="form-check-label" for="formatCsv">
                                        <i class="fas fa-file-csv text-success"></i> CSV
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-magic me-2"></i> Generate Report
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Recent Reports List -->
        <div class="col-md-8 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">Recent Reports</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($reports)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-folder-open fa-3x mb-3"></i>
                            <p>No reports generated yet.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Type</th>
                                        <th>Date Range</th>
                                        <th>Generated At</th>
                                        <th class="text-end pe-4">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reports as $report): ?>
                                        <tr>
                                            <td class="ps-4">
                                                <?php if ($report['type'] === 'pdf'): ?>
                                                    <span class="badge badge-danger bg-opacity-10 text-danger border border-danger"><i class="fas fa-file-pdf"></i> PDF</span>
                                                <?php else: ?>
                                                    <span class="badge badge-success bg-opacity-10 text-success border border-success"><i class="fas fa-file-csv"></i> CSV</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <small>
                                                    <?= date('M j, Y', strtotime($report['period_start'])) ?> - 
                                                    <?= date('M j, Y', strtotime($report['period_end'])) ?>
                                                </small>
                                            </td>
                                            <td class="text-muted small">
                                                <?= date('M j, Y g:i A', strtotime($report['created_at'])) ?>
                                            </td>
                                            <td class="text-end pe-4">
                                                <a href="<?= site_url('manage/reports/download/' . $report['id']) ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-download"></i> Download
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
