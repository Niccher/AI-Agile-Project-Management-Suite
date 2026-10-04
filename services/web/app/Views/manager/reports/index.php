<?= $this->extend('layouts/hyper/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-file-invoice text-primary me-2"></i> Team Reports</h2>
    </div>

    <!-- Live Alert Area -->
    <div id="ajaxReportAlertArea">
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
    </div>

    <div class="row">
        <!-- Generate Report Form -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">Generate New Report</h5>
                </div>
                <div class="card-body">
                    <form id="reportGenerateForm" action="<?= site_url('manage/reports/generate') ?>" method="POST">
                        <?= csrf_field() ?>
                        
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold">DATE RANGE</label>
                            <div class="row g-2">
                                <div class="col">
                                    <input type="date" name="period_start" id="period_start" class="form-control form-control-sm">
                                </div>
                                <div class="col-auto d-flex align-items-center text-muted">to</div>
                                <div class="col">
                                    <input type="date" name="period_end" id="period_end" class="form-control form-control-sm">
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
                            <button type="submit" id="generateReportSubmitBtn" class="btn btn-primary">
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
                    <div id="no-reports-placeholder" class="text-center py-5 text-muted <?= !empty($reports) ? 'd-none' : '' ?>">
                        <i class="fas fa-folder-open fa-3x mb-3"></i>
                        <p>No reports generated yet.</p>
                    </div>
                    <div id="reports-table-container" class="table-responsive <?= empty($reports) ? 'd-none' : '' ?>">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Type</th>
                                    <th>Report Name</th>
                                    <th>Date Range</th>
                                    <th>Generated At</th>
                                    <th class="text-end pe-4">Action</th>
                                </tr>
                            </thead>
                            <tbody id="reports-table-body">
                                <?php if (!empty($reports)): ?>
                                    <?php foreach ($reports as $report): ?>
                                        <?php
                                            $params = !empty($report['parameters']) ? (is_string($report['parameters']) ? json_decode($report['parameters'], true) : $report['parameters']) : [];
                                            $start = !empty($params['start']) ? $params['start'] : null;
                                            $end = !empty($params['end']) ? $params['end'] : null;
                                        ?>
                                        <tr>
                                            <td class="ps-4">
                                                <?php if ($report['type'] === 'pdf'): ?>
                                                    <span class="badge badge-danger bg-opacity-10 text-danger border border-danger"><i class="fas fa-file-pdf"></i> PDF</span>
                                                <?php else: ?>
                                                    <span class="badge badge-success bg-opacity-10 text-success border border-success"><i class="fas fa-file-csv"></i> CSV</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="fw-semibold text-dark">
                                                <?= esc($report['name'] ?? 'Team Performance Report') ?>
                                            </td>
                                            <td>
                                                <small>
                                                    <?= (!empty($start) && strtotime((string)$start)) ? date('M j, Y', strtotime((string)$start)) : 'All Time' ?> - 
                                                    <?= (!empty($end) && strtotime((string)$end)) ? date('M j, Y', strtotime((string)$end)) : 'Present' ?>
                                                </small>
                                            </td>
                                            <td class="text-muted small">
                                                <?= date('M j, Y g:i A', strtotime($report['created_at'])) ?>
                                            </td>
                                            <td class="text-end pe-4">
                                                <a href="<?= site_url('manage/reports/download/' . ($report['id'] ?? $report['file_path'])) ?>" class="btn btn-sm btn-outline-primary" download>
                                                    <i class="fas fa-download"></i> Download
                                                </a>
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
</div>
<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('reportGenerateForm');
    const submitBtn = document.getElementById('generateReportSubmitBtn');
    const tableBody = document.getElementById('reports-table-body');
    const placeholder = document.getElementById('no-reports-placeholder');
    const tableContainer = document.getElementById('reports-table-container');
    const alertArea = document.getElementById('ajaxReportAlertArea');

    function showAlert(type, message) {
        if (!alertArea) return;
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                <i class="mdi mdi-${type === 'success' ? 'check-circle' : 'alert-circle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        alertArea.innerHTML = alertHtml;
    }

    function triggerSilentDownload(url, filename) {
        if (!url) return;
        const link = document.createElement('a');
        link.href = url;
        if (filename) {
            link.setAttribute('download', filename);
        }
        link.style.display = 'none';
        document.body.appendChild(link);
        link.click();
        setTimeout(() => {
            if (link.parentNode) {
                link.parentNode.removeChild(link);
            }
        }, 2000);
    }

    if (form && submitBtn) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const origHtml = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span> Generating...';

            const formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => {
                if (!res.ok) {
                    return res.json().then(errData => { throw new Error(errData.message || ('HTTP ' + res.status)); });
                }
                return res.json();
            })
            .then(data => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = origHtml;

                if (data.status === 'success') {
                    showAlert('success', 'Report generated successfully! Starting download...');

                    // Add new row to table
                    if (placeholder) placeholder.classList.add('d-none');
                    if (tableContainer) tableContainer.classList.remove('d-none');

                    const r = data.report || {};
                    const isPdf = (r.type === 'pdf');
                    const badgeHtml = isPdf 
                        ? '<span class="badge badge-danger bg-opacity-10 text-danger border border-danger"><i class="fas fa-file-pdf"></i> PDF</span>'
                        : '<span class="badge badge-success bg-opacity-10 text-success border border-success"><i class="fas fa-file-csv"></i> CSV</span>';
                    
                    const startVal = r.parameters && r.parameters.start ? r.parameters.start : 'All Time';
                    const endVal = r.parameters && r.parameters.end ? r.parameters.end : 'Present';
                    const dateRangeStr = `${startVal} - ${endVal}`;
                    const createdStr = new Date().toLocaleString();

                    const downloadUrl = data.download_url || ('<?= site_url('manage/reports/download/') ?>' + (r.id || r.filename));

                    const newRow = document.createElement('tr');
                    newRow.className = 'table-success bg-opacity-10';
                    newRow.innerHTML = `
                        <td class="ps-4">${badgeHtml}</td>
                        <td class="fw-semibold text-dark">${r.name || 'Team Performance Report'}</td>
                        <td><small>${dateRangeStr}</small></td>
                        <td class="text-muted small">${createdStr}</td>
                        <td class="text-end pe-4">
                            <a href="${downloadUrl}" class="btn btn-sm btn-outline-primary" download>
                                <i class="fas fa-download"></i> Download
                            </a>
                        </td>
                    `;
                    if (tableBody) {
                        tableBody.insertBefore(newRow, tableBody.firstChild);
                    }

                    // Silent browser download without navigating page
                    triggerSilentDownload(downloadUrl, r.filename);
                } else {
                    showAlert('danger', data.message || 'Could not generate report.');
                }
            })
            .catch(err => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = origHtml;
                showAlert('danger', err.message || 'An unexpected error occurred while generating report.');
            });
        });
    }
});
</script>
<?= $this->endSection() ?>
