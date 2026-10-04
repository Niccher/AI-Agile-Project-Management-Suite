<?= $this->extend('layouts/hyper/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid py-3">
    <!-- Page Header -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <h4 class="page-title mb-0 fw-bold">
                        <i class="mdi mdi-file-chart-outline text-primary me-2 font-22"></i> Team Reports & Performance Exports
                    </h4>
                    <p class="text-muted font-13 mb-0">Generate sprint performance audits, work activity summaries, and downloadable PDF/CSV artifacts.</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="<?= site_url('manage/team') ?>" class="btn btn-sm btn-outline-primary rounded-pill">
                        <i class="mdi mdi-account-group-outline me-1"></i> Team Leaderboard
                    </a>
                    <a href="<?= site_url('manage/approvals') ?>" class="btn btn-sm btn-outline-info rounded-pill">
                        <i class="mdi mdi-clipboard-check-outline me-1"></i> Work Approvals
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Metric Cards -->
    <div class="row g-3 mb-4">
        <!-- Total Reports -->
        <div class="col-sm-6 col-lg-4">
            <div class="card widget-flat border-0 shadow-sm h-100 rounded-3">
                <div class="card-body">
                    <div class="float-end">
                        <div class="avatar-sm bg-primary-lighten text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <i class="mdi mdi-folder-multiple-outline font-22"></i>
                        </div>
                    </div>
                    <h6 class="text-muted text-uppercase fw-bold font-12 mt-0 mb-2">Total Reports Generated</h6>
                    <h2 class="my-2 fw-bold text-dark" id="stat-total-reports"><?= number_format($totalReports ?? count($reports ?? [])) ?></h2>
                    <p class="mb-0 text-muted font-12">
                        <span class="badge bg-primary-lighten text-primary font-11 me-1"><i class="mdi mdi-database-check"></i> Persistent Storage</span>
                        <span>Available in archive</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- PDF Reports -->
        <div class="col-sm-6 col-lg-4">
            <div class="card widget-flat border-0 shadow-sm h-100 rounded-3">
                <div class="card-body">
                    <div class="float-end">
                        <div class="avatar-sm bg-danger-lighten text-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <i class="mdi mdi-file-pdf-box font-22"></i>
                        </div>
                    </div>
                    <h6 class="text-muted text-uppercase fw-bold font-12 mt-0 mb-2">PDF Executive Documents</h6>
                    <h2 class="my-2 fw-bold text-danger" id="stat-pdf-reports"><?= number_format($pdfCount ?? 0) ?></h2>
                    <p class="mb-0 text-muted font-12">
                        <span class="badge bg-danger-lighten text-danger font-11 me-1"><i class="mdi mdi-file-document-outline"></i> Formatted</span>
                        <span>Printable sprint audits</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- CSV Reports -->
        <div class="col-sm-6 col-lg-4">
            <div class="card widget-flat border-0 shadow-sm h-100 rounded-3">
                <div class="card-body">
                    <div class="float-end">
                        <div class="avatar-sm bg-success-lighten text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <i class="mdi mdi-file-excel-box font-22"></i>
                        </div>
                    </div>
                    <h6 class="text-muted text-uppercase fw-bold font-12 mt-0 mb-2">CSV Data Spreadsheets</h6>
                    <h2 class="my-2 fw-bold text-success" id="stat-csv-reports"><?= number_format($csvCount ?? 0) ?></h2>
                    <p class="mb-0 text-muted font-12">
                        <span class="badge bg-success-lighten text-success font-11 me-1"><i class="mdi mdi-table-large"></i> Raw Data</span>
                        <span>Tabular metric exports</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Live Alert Area -->
    <div id="ajaxReportAlertArea">
        <?php if (session()->has('message')) : ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="mdi mdi-check-circle me-2"></i>
                <?= session('message') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if (session()->has('error')) : ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="mdi mdi-alert-circle me-2"></i>
                <?= session('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
    </div>

    <div class="row g-3">
        <!-- Generate Report Form Column -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 rounded-3 h-100 bg-white">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="mdi mdi-magic-staff text-primary me-2 font-18"></i> Custom Report Builder
                    </h5>
                    <small class="text-muted">Export team workload & logged hours</small>
                </div>
                <div class="card-body p-4">
                    <form id="reportGenerateForm" action="<?= site_url('manage/reports/generate') ?>" method="POST">
                        <?= csrf_field() ?>
                        
                        <!-- Date Range Preset Buttons -->
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold text-uppercase d-flex justify-content-between align-items-center">
                                <span>Date Scope</span>
                                <span class="text-muted font-11">Filter by created date</span>
                            </label>
                            <div class="btn-group btn-group-sm w-100 mb-2" role="group">
                                <button type="button" class="btn btn-outline-secondary date-preset-btn" data-range="this_month">This Month</button>
                                <button type="button" class="btn btn-outline-secondary date-preset-btn" data-range="last_30">Last 30 Days</button>
                                <button type="button" class="btn btn-outline-secondary date-preset-btn active" data-range="all">All Time</button>
                            </div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <label for="period_start" class="font-11 text-muted mb-1">From Date</label>
                                    <input type="date" name="period_start" id="period_start" class="form-control form-control-sm">
                                </div>
                                <div class="col-6">
                                    <label for="period_end" class="font-11 text-muted mb-1">To Date</label>
                                    <input type="date" name="period_end" id="period_end" class="form-control form-control-sm">
                                </div>
                            </div>
                        </div>

                        <!-- Format Selector Cards -->
                        <div class="mb-4">
                            <label class="form-label text-muted small fw-bold text-uppercase mb-2">Export Format</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="type" id="formatPdf" value="pdf" checked>
                                    <label class="btn btn-outline-danger w-100 p-3 text-start rounded-3 h-100 d-flex flex-column justify-content-between shadow-none" for="formatPdf">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <i class="mdi mdi-file-pdf-box font-24 text-danger"></i>
                                            <span class="badge bg-danger text-white font-10">PDF</span>
                                        </div>
                                        <div>
                                            <div class="fw-bold font-13 text-dark">Executive PDF</div>
                                            <small class="text-muted font-11">Styled printable sprint document</small>
                                        </div>
                                    </label>
                                </div>
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="type" id="formatCsv" value="csv">
                                    <label class="btn btn-outline-success w-100 p-3 text-start rounded-3 h-100 d-flex flex-column justify-content-between shadow-none" for="formatCsv">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <i class="mdi mdi-file-excel-box font-24 text-success"></i>
                                            <span class="badge bg-success text-white font-10">CSV</span>
                                        </div>
                                        <div>
                                            <div class="fw-bold font-13 text-dark">Spreadsheet CSV</div>
                                            <small class="text-muted font-11">Raw tabular dataset for Excel</small>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid">
                            <button type="submit" id="generateReportSubmitBtn" class="btn btn-primary py-2 rounded-pill font-14 fw-semibold shadow-sm">
                                <i class="mdi mdi-file-download-outline me-1"></i> Generate & Download Report
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Recent Reports Archive Table Column -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 rounded-3 h-100 bg-white">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div>
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="mdi mdi-history text-primary me-2 font-18"></i> Generated Reports Archive
                        </h5>
                        <small class="text-muted">Persistent history of all exported performance documents</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <input type="text" id="reportSearchInput" class="form-control form-control-sm rounded-pill px-3" placeholder="Filter reports..." style="max-width: 180px;">
                    </div>
                </div>
                <div class="card-body p-0">
                    <div id="no-reports-placeholder" class="text-center py-5 text-muted <?= !empty($reports) ? 'd-none' : '' ?>">
                        <div class="avatar-lg bg-light rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3 text-muted">
                            <i class="mdi mdi-folder-open-outline font-36"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-1">No Reports Generated Yet</h5>
                        <p class="font-13 text-muted">Use the Custom Report Builder on the left to export your first team performance audit.</p>
                    </div>

                    <div id="reports-table-container" class="table-responsive <?= empty($reports) ? 'd-none' : '' ?>">
                        <table class="table table-hover align-middle mb-0" id="reports-table">
                            <thead class="table-light text-muted font-12 text-uppercase">
                                <tr>
                                    <th class="ps-4" style="width: 100px;">Format</th>
                                    <th>Report Title</th>
                                    <th>Date Scope</th>
                                    <th>Generated At</th>
                                    <th class="text-end pe-4" style="width: 180px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="reports-table-body">
                                <?php if (!empty($reports)): ?>
                                    <?php foreach ($reports as $report): ?>
                                        <?php
                                            $params = !empty($report['parameters']) ? (is_string($report['parameters']) ? json_decode($report['parameters'], true) : $report['parameters']) : [];
                                            $start = !empty($params['start']) ? $params['start'] : null;
                                            $end = !empty($params['end']) ? $params['end'] : null;
                                            $isPdf = (strtolower($report['type'] ?? 'pdf') === 'pdf');
                                            $reportId = $report['id'] ?? $report['file_path'];
                                        ?>
                                        <tr class="report-row">
                                            <td class="ps-4">
                                                <?php if ($isPdf): ?>
                                                    <span class="badge bg-danger-lighten text-danger border border-danger font-11 px-2 py-1 rounded">
                                                        <i class="mdi mdi-file-pdf me-1"></i> PDF
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-success-lighten text-success border border-success font-11 px-2 py-1 rounded">
                                                        <i class="mdi mdi-file-excel me-1"></i> CSV
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-dark font-14"><?= esc($report['name'] ?? 'Team Performance Report') ?></div>
                                                <small class="text-muted font-11"><i class="mdi mdi-file-outline me-1"></i> <?= esc(basename($report['file_path'] ?? 'report.pdf')) ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border font-11">
                                                    <i class="mdi mdi-calendar-range me-1 text-muted"></i>
                                                    <?= (!empty($start) && strtotime((string)$start)) ? date('M j, Y', strtotime((string)$start)) : 'All Time' ?> - 
                                                    <?= (!empty($end) && strtotime((string)$end)) ? date('M j, Y', strtotime((string)$end)) : 'Present' ?>
                                                </span>
                                            </td>
                                            <td class="text-muted font-12">
                                                <i class="mdi mdi-clock-outline me-1"></i>
                                                <?= date('M j, Y g:i A', strtotime($report['created_at'])) ?>
                                            </td>
                                            <td class="text-end pe-4">
                                                <div class="d-flex justify-content-end gap-1">
                                                    <a href="<?= site_url('manage/reports/download/' . $reportId) ?>" class="btn btn-sm btn-primary rounded-pill px-3 shadow-none" download title="Download file">
                                                        <i class="mdi mdi-download me-1"></i> Download
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2 btn-delete-report shadow-none" data-id="<?= esc($reportId) ?>" title="Delete Report">
                                                        <i class="mdi mdi-trash-can-outline"></i>
                                                    </button>
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
    const searchInput = document.getElementById('reportSearchInput');
    const datePresets = document.querySelectorAll('.date-preset-btn');
    const statTotal = document.getElementById('stat-total-reports');
    const statPdf = document.getElementById('stat-pdf-reports');
    const statCsv = document.getElementById('stat-csv-reports');

    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function getCsrfHeader() {
        const meta = document.querySelector('meta[name="csrf-header"]');
        return meta ? meta.getAttribute('content') : 'X-CSRF-TOKEN';
    }

    function showAlert(type, message) {
        if (!alertArea) return;
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show shadow-sm" role="alert">
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

    // Date Preset Helper
    datePresets.forEach(btn => {
        btn.addEventListener('click', function() {
            datePresets.forEach(b => b.classList.remove('active', 'btn-secondary'));
            datePresets.forEach(b => b.classList.add('btn-outline-secondary'));
            this.classList.add('active', 'btn-secondary');
            this.classList.remove('btn-outline-secondary');

            const range = this.getAttribute('data-range');
            const startInput = document.getElementById('period_start');
            const endInput = document.getElementById('period_end');
            const today = new Date();

            if (range === 'this_month') {
                const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
                startInput.value = firstDay.toISOString().split('T')[0];
                endInput.value = today.toISOString().split('T')[0];
            } else if (range === 'last_30') {
                const past30 = new Date();
                past30.setDate(today.getDate() - 30);
                startInput.value = past30.toISOString().split('T')[0];
                endInput.value = today.toISOString().split('T')[0];
            } else {
                startInput.value = '';
                endInput.value = '';
            }
        });
    });

    // Real-Time Table Search Filter
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase();
            const rows = document.querySelectorAll('.report-row');
            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        });
    }

    // Handle Report Generation via AJAX
    if (form && submitBtn) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const origHtml = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span> Generating Report...';

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
                    const reportId = r.id || r.filename;
                    const isPdf = (r.type === 'pdf');
                    const badgeHtml = isPdf 
                        ? '<span class="badge bg-danger-lighten text-danger border border-danger font-11 px-2 py-1 rounded"><i class="mdi mdi-file-pdf me-1"></i> PDF</span>'
                        : '<span class="badge bg-success-lighten text-success border border-success font-11 px-2 py-1 rounded"><i class="mdi mdi-file-excel me-1"></i> CSV</span>';
                    
                    const startVal = r.parameters && r.parameters.start ? r.parameters.start : 'All Time';
                    const endVal = r.parameters && r.parameters.end ? r.parameters.end : 'Present';
                    const dateRangeStr = `${startVal} - ${endVal}`;
                    const createdStr = new Date().toLocaleString([], { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' });

                    const downloadUrl = data.download_url || ('<?= site_url('manage/reports/download/') ?>' + reportId);

                    const newRow = document.createElement('tr');
                    newRow.className = 'report-row table-success bg-opacity-10';
                    newRow.innerHTML = `
                        <td class="ps-4">${badgeHtml}</td>
                        <td>
                            <div class="fw-bold text-dark font-14">${r.name || 'Team Performance Report'}</div>
                            <small class="text-muted font-11"><i class="mdi mdi-file-outline me-1"></i> ${r.filename || 'report.pdf'}</small>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border font-11">
                                <i class="mdi mdi-calendar-range me-1 text-muted"></i> ${dateRangeStr}
                            </span>
                        </td>
                        <td class="text-muted font-12">
                            <i class="mdi mdi-clock-outline me-1"></i> ${createdStr}
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex justify-content-end gap-1">
                                <a href="${downloadUrl}" class="btn btn-sm btn-primary rounded-pill px-3 shadow-none" download title="Download file">
                                    <i class="mdi mdi-download me-1"></i> Download
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2 btn-delete-report shadow-none" data-id="${reportId}" title="Delete Report">
                                    <i class="mdi mdi-trash-can-outline"></i>
                                </button>
                            </div>
                        </td>
                    `;
                    if (tableBody) {
                        tableBody.insertBefore(newRow, tableBody.firstChild);
                    }

                    // Update stat counts
                    if (statTotal) statTotal.innerText = parseInt(statTotal.innerText || '0') + 1;
                    if (isPdf && statPdf) statPdf.innerText = parseInt(statPdf.innerText || '0') + 1;
                    if (!isPdf && statCsv) statCsv.innerText = parseInt(statCsv.innerText || '0') + 1;

                    // Silent browser download without reloading/navigating page
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

    // Handle AJAX Delete Report via Event Delegation
    document.addEventListener('click', function(e) {
        const deleteBtn = e.target.closest('.btn-delete-report');
        if (!deleteBtn) return;

        const reportId = deleteBtn.getAttribute('data-id');
        if (!reportId) return;

        if (!confirm('Are you sure you want to permanently delete this report?')) {
            return;
        }

        const tr = deleteBtn.closest('tr');
        const origContent = deleteBtn.innerHTML;
        deleteBtn.disabled = true;
        deleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';

        const deleteUrl = '<?= site_url('manage/reports/delete/') ?>' + encodeURIComponent(reportId);
        const headers = {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        };
        const csrfToken = getCsrfToken();
        const csrfHeader = getCsrfHeader();
        if (csrfToken) {
            headers[csrfHeader] = csrfToken;
        }

        fetch(deleteUrl, {
            method: 'POST',
            headers: headers
        })
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                if (tr) {
                    tr.style.transition = 'opacity 0.3s ease';
                    tr.style.opacity = '0';
                    setTimeout(() => {
                        tr.remove();
                        if (tableBody && tableBody.querySelectorAll('tr').length === 0) {
                            if (tableContainer) tableContainer.classList.add('d-none');
                            if (placeholder) placeholder.classList.remove('d-none');
                        }
                    }, 300);
                }
                showAlert('success', 'Report permanently deleted.');
                if (statTotal && parseInt(statTotal.innerText || '0') > 0) {
                    statTotal.innerText = parseInt(statTotal.innerText) - 1;
                }
            } else {
                deleteBtn.disabled = false;
                deleteBtn.innerHTML = origContent;
                showAlert('danger', res.message || 'Failed to delete report.');
            }
        })
        .catch(err => {
            deleteBtn.disabled = false;
            deleteBtn.innerHTML = origContent;
            showAlert('danger', err.message || 'An error occurred while deleting the report.');
        });
    });
});
</script>
<?= $this->endSection() ?>

