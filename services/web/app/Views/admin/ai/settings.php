<?= $this->extend('layouts/hyper/main') ?>

<?= $this->section('title') ?>AI Engine Settings & Model Manager • <?= esc(setting('App.siteName')) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid py-2">
    <!-- Page Header -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="page-title mb-0">
                        <i class="mdi mdi-tune-vertical text-info me-2"></i> AI Engine Configuration & Model Manager
                    </h4>
                    <p class="text-muted font-13 mb-0">Configure microservice connection endpoints, manage on-demand GGUF downloads, and tune inference runtime parameters.</p>
                </div>
                <div>
                    <a href="<?= site_url('admin/ai/telemetry') ?>" class="btn btn-sm btn-outline-primary rounded-pill shadow-sm">
                        <i class="mdi mdi-chart-line me-1"></i> View Live Diagnostics
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php if (session()->getFlashdata('message')): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="mdi mdi-check-circle-outline me-2 font-16"></i> <?= session()->getFlashdata('message') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="mdi mdi-alert-circle-outline me-2 font-16"></i> <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Left Column: Connection & Hardware Parameters -->
        <div class="col-lg-5">
            <form id="aiSettingsForm" method="post" action="<?= site_url('admin/ai/settings/update') ?>">
                <?= csrf_field() ?>

                <!-- 1. Connection Card -->
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
                        <h5 class="card-title my-0">
                            <i class="uil-plug me-1 text-primary"></i> Microservice Connection
                        </h5>
                        <div id="connectionStatusBadge">
                            <?php if ($isOnline): ?>
                                <span class="badge bg-success-lighten text-success"><i class="mdi mdi-check-circle me-1"></i>Connected</span>
                            <?php else: ?>
                                <span class="badge bg-danger-lighten text-danger"><i class="mdi mdi-alert-circle me-1"></i>Unreachable</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold font-13">Microservice URL & Port</label>
                            <input type="text" name="service_url" id="service_url" class="form-control" value="<?= esc($serviceUrl) ?>" placeholder="e.g. http://ml-chege-jira:8000" required>
                            <div class="form-text font-12">
                                GCP VPC / Internal DNS: <code>http://ml-chege-jira:8000</code><br>
                                Local Docker Compose: <code>http://ml-chege-jira:8000</code>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold font-13">API Secret Key (X-API-Key)</label>
                            <div class="input-group input-group-merge">
                                <input type="password" id="api_key" name="api_key" class="form-control" value="<?= esc($apiKey) ?>" placeholder="Secret key matching ML container" required>
                                <div class="input-group-text" data-password="false" style="cursor: pointer;" onclick="const input = document.getElementById('api_key'); const icon = this.querySelector('i'); if(input.type === 'password'){ input.type = 'text'; icon.classList.remove('mdi-eye-outline'); icon.classList.add('mdi-eye-off-outline'); } else { input.type = 'password'; icon.classList.remove('mdi-eye-off-outline'); icon.classList.add('mdi-eye-outline'); }">
                                    <i class="mdi mdi-eye-outline"></i>
                                </div>
                            </div>
                            <div class="form-text font-12">Shared authorization token.</div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="button" id="btnTestConnection" class="btn btn-sm btn-outline-secondary">
                                <i class="mdi mdi-connection me-1"></i> Test Connection
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 2. Hardware Acceleration Card -->
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-header bg-transparent border-bottom">
                        <h5 class="card-title my-0">
                            <i class="uil-processor me-1 text-primary"></i> Hardware & Inference Tuning
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Default Model Selection -->
                        <div class="mb-3">
                            <label class="form-label fw-bold font-13">Active Default Model</label>
                            <select name="default_model" class="form-select">
                                <?php foreach ($models as $mKey => $m): ?>
                                    <option value="<?= esc($mKey) ?>" <?= ($config['default_model'] ?? '') === $mKey ? 'selected' : '' ?>>
                                        <?= esc($m['name'] ?? $mKey) ?> (<?= esc($m['params'] ?? '') ?> - <?= esc($m['quant'] ?? '') ?>)
                                        <?= !empty($m['exists_on_disk']) ? '✓ [Ready]' : '[Download Needed]' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Compute Mode -->
                        <div class="mb-3">
                            <label class="form-label fw-bold font-13">Compute Acceleration</label>
                            <div class="p-2 bg-light rounded">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="compute_mode" id="computeCpu" value="cpu" <?= ($config['n_gpu_layers'] ?? 0) == 0 ? 'checked' : '' ?>>
                                    <label class="form-check-label font-13" for="computeCpu">
                                        <strong>CPU Multi-threading (Default)</strong>
                                        <span class="d-block text-muted font-11">OpenBLAS optimized execution</span>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="compute_mode" id="computeGpu" value="gpu" <?= ($config['n_gpu_layers'] ?? 0) != 0 ? 'checked' : '' ?>>
                                    <label class="form-check-label font-13" for="computeGpu">
                                        <strong>NVIDIA GPU Offload (CUDA)</strong>
                                        <span class="d-block text-muted font-11">Full layer offloading (n_gpu_layers = -1)</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold font-13">CPU Threads</label>
                                <input type="number" name="n_threads" class="form-control" min="1" max="64" value="<?= esc($config['n_threads'] ?? 4) ?>">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold font-13">Context Window</label>
                                <input type="number" name="n_ctx" class="form-control" min="512" max="32768" step="512" value="<?= esc($config['n_ctx'] ?? 4096) ?>">
                            </div>
                        </div>

                        <div class="text-end border-top pt-2">
                            <button type="submit" class="btn btn-primary btn-sm px-3">
                                <i class="mdi mdi-content-save me-1"></i> Save & Apply Configuration
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Right Column: Model Catalog, Specs, & Live Download Manager -->
        <div class="col-lg-7">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title my-0">
                            <i class="mdi mdi-database me-1 text-primary"></i> GGUF Model Catalog & Downloader
                        </h5>
                        <span class="text-muted font-12">One-click model downloads, live progress tracking, and memory allocations</span>
                    </div>
                    <div>
                        <button type="button" class="btn btn-xs btn-outline-secondary" onclick="pollModelStatus(true);">
                            <i class="mdi mdi-refresh me-1"></i> Refresh Catalog
                        </button>
                    </div>
                </div>
                <div class="card-body p-3">
                    <div id="modelsContainer" class="d-flex flex-column gap-3">
                        <?php foreach ($models as $mKey => $m): ?>
                            <?php 
                                $isDefault = ($config['default_model'] ?? '') === $mKey;
                                $isLoaded = !empty($m['loaded_in_ram']);
                                $isDownloaded = !empty($m['exists_on_disk']);
                                $isDownloading = ($m['download_status'] ?? '') === 'downloading';
                                $badgeColor = $m['badge_color'] ?? 'primary';
                            ?>
                            <div class="border rounded p-3 bg-white shadow-none model-card" id="model-card-<?= esc($mKey) ?>">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <h5 class="my-0 fw-bold text-body"><?= esc($m['name'] ?? $mKey) ?></h5>
                                            <span class="badge bg-<?= esc($badgeColor) ?>-lighten text-<?= esc($badgeColor) ?> font-11">
                                                <?= esc($m['params'] ?? '') ?> • <?= esc($m['quant'] ?? 'Q4_K_M') ?>
                                            </span>
                                            <?php if ($isDefault): ?>
                                                <span class="badge bg-success font-11"><i class="mdi mdi-star me-1"></i>Default</span>
                                            <?php endif; ?>
                                        </div>
                                        <span class="text-muted font-11">Provider: <strong><?= esc($m['provider'] ?? 'Open Source') ?></strong> • File: <code><?= esc($m['file'] ?? '') ?></code></span>
                                    </div>

                                    <div class="text-end">
                                        <span class="d-block font-12 text-muted">Disk Size: <strong><?= esc($m['approx_size_gb'] ?? 0) ?> GB</strong></span>
                                        <span class="d-block font-11 text-muted">RAM Req: <strong>~<?= esc($m['recommended_ram_gb'] ?? 0) ?> GB</strong></span>
                                    </div>
                                </div>

                                <p class="text-secondary font-12 mb-2"><?= esc($m['best_for'] ?? '') ?></p>

                                <!-- Live Download Progress Bar (shown when downloading) -->
                                <div class="download-progress-box mb-2 <?= $isDownloading ? '' : 'd-none' ?>" id="progress-box-<?= esc($mKey) ?>">
                                    <div class="d-flex justify-content-between font-11 mb-1">
                                        <span class="text-warning fw-bold"><i class="mdi mdi-loading mdi-spin me-1"></i>Downloading GGUF weights...</span>
                                        <span class="progress-pct-text fw-bold" id="progress-text-<?= esc($mKey) ?>"><?= $m['download_progress_pct'] ?? 0 ?>%</span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-warning progress-bar-striped progress-bar-animated" id="progress-bar-<?= esc($mKey) ?>" role="progressbar" style="width: <?= $m['download_progress_pct'] ?? 0 ?>%"></div>
                                    </div>
                                </div>

                                <!-- Action Buttons & Status -->
                                <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                    <div class="status-badge-container" id="status-badge-<?= esc($mKey) ?>">
                                        <?php if ($isDownloaded): ?>
                                            <?php if ($isLoaded): ?>
                                                <span class="badge bg-success"><i class="mdi mdi-memory me-1"></i>In RAM (<?= $m['ram_mb'] ?? 0 ?> MB)</span>
                                            <?php else: ?>
                                                <span class="badge bg-success-lighten text-success"><i class="mdi mdi-check-circle-outline me-1"></i>Ready on Disk</span>
                                            <?php endif; ?>
                                        <?php elseif ($isDownloading): ?>
                                            <span class="badge bg-warning text-dark"><i class="mdi mdi-cloud-download me-1"></i>Downloading</span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-muted"><i class="mdi mdi-cloud-off-outline me-1"></i>Not on Disk</span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="action-btn-group" id="action-btn-group-<?= esc($mKey) ?>">
                                        <?php if (!$isDownloaded): ?>
                                            <button type="button" class="btn btn-xs btn-primary shadow-sm" onclick="triggerModelAction('download', '<?= esc($mKey) ?>', '<?= esc($m['approx_size_gb'] ?? '') ?> GB')" <?= $isDownloading ? 'disabled' : '' ?>>
                                                <i class="mdi mdi-download me-1"></i> Download GGUF (<?= esc($m['approx_size_gb'] ?? '') ?> GB)
                                            </button>
                                        <?php else: ?>
                                            <div class="btn-group btn-group-sm">
                                                <?php if (!$isLoaded): ?>
                                                    <button type="button" class="btn btn-xs btn-outline-success" onclick="triggerModelAction('preload', '<?= esc($mKey) ?>')">
                                                        <i class="mdi mdi-lightning-bolt me-1"></i> Pre-load to RAM
                                                    </button>
                                                <?php else: ?>
                                                    <button type="button" class="btn btn-xs btn-outline-warning" onclick="triggerModelAction('reload', '<?= esc($mKey) ?>')">
                                                        <i class="mdi mdi-reload me-1"></i> Reload
                                                    </button>
                                                    <button type="button" class="btn btn-xs btn-outline-danger ms-1" onclick="triggerModelAction('evict', '<?= esc($mKey) ?>')">
                                                        <i class="mdi mdi-eject me-1"></i> Evict
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let pollingInterval = null;

function triggerModelAction(action, modelKey, meta = '') {
    let confirmTitle = 'Confirm Action';
    let confirmText = `Are you sure you want to perform "${action}" on ${modelKey}?`;
    let confirmBtn = 'Yes, Proceed';
    let confirmColor = '#3b82f6';

    if (action === 'download') {
        confirmTitle = 'Download Model Weights?';
        confirmText = `Download ${modelKey} (${meta}) to the container storage? This will run in the background.`;
        confirmBtn = 'Download Now';
    } else if (action === 'preload') {
        confirmTitle = 'Pre-load Model to RAM?';
        confirmText = `Load ${modelKey} into memory for instant inference responses?`;
        confirmBtn = 'Pre-load';
        confirmColor = '#10b981';
    } else if (action === 'reload') {
        confirmTitle = 'Reload Model in RAM?';
        confirmText = `Restart and refresh the active memory cache for ${modelKey}?`;
        confirmBtn = 'Reload';
        confirmColor = '#f59e0b';
    } else if (action === 'evict') {
        confirmTitle = 'Evict Model from RAM?';
        confirmText = `Free system memory by unloading ${modelKey} from RAM?`;
        confirmBtn = 'Evict';
        confirmColor = '#ef4444';
    }

    Swal.fire({
        title: confirmTitle,
        text: confirmText,
        icon: action === 'evict' ? 'warning' : 'question',
        showCancelButton: true,
        confirmButtonText: confirmBtn,
        confirmButtonColor: confirmColor,
        cancelButtonText: 'Cancel'
    }).then(result => {
        if (!result.isConfirmed) return;

        if (action === 'download') {
            const progressBox = document.getElementById('progress-box-' + modelKey);
            const progressBar = document.getElementById('progress-bar-' + modelKey);
            const progressText = document.getElementById('progress-text-' + modelKey);
            const statusBadge = document.getElementById('status-badge-' + modelKey);

            if (progressBox) progressBox.classList.remove('d-none');
            if (progressBar) progressBar.style.width = '3%';
            if (progressText) progressText.innerText = 'Starting...';
            if (statusBadge) statusBadge.innerHTML = '<span class="badge bg-warning text-dark"><i class="mdi mdi-cloud-download me-1"></i>Downloading (Starting...)</span>';
        } else {
            Swal.fire({
                title: 'Executing Action...',
                text: `Applying ${action} to ${modelKey}`,
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });
        }

        const formData = new FormData();
        formData.append('action', action);
        formData.append('model_key', modelKey);
        formData.append('redirect', 'settings');
        if (window.csrfToken) formData.append(window.csrfToken, window.csrfHash);

        fetch('<?= site_url('admin/ai/cache-action') ?>', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                if (action === 'download') {
                    if (typeof notifySuccess === 'function') {
                        notifySuccess(res.message);
                    }
                    if (!pollingInterval) {
                        pollingInterval = setInterval(pollModelStatus, 2500);
                    }
                } else if (action === 'preload' || action === 'reload') {
                    Swal.fire({ icon: 'success', title: 'Loaded in RAM', text: res.message, timer: 2000, showConfirmButton: false });
                    const badge = document.getElementById('status-badge-' + modelKey);
                    if (badge) badge.innerHTML = '<span class="badge bg-success"><i class="mdi mdi-memory me-1"></i>In RAM</span>';
                    const btnGroup = document.getElementById('action-btn-group-' + modelKey);
                    if (btnGroup) {
                        btnGroup.innerHTML = `
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-xs btn-outline-warning" onclick="triggerModelAction('reload', '${modelKey}')">
                                    <i class="mdi mdi-reload me-1"></i> Reload
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-danger ms-1" onclick="triggerModelAction('evict', '${modelKey}')">
                                    <i class="mdi mdi-eject me-1"></i> Evict
                                </button>
                            </div>
                        `;
                    }
                } else if (action === 'evict') {
                    Swal.fire({ icon: 'success', title: 'Evicted from RAM', text: res.message, timer: 2000, showConfirmButton: false });
                    const badge = document.getElementById('status-badge-' + modelKey);
                    if (badge) badge.innerHTML = '<span class="badge bg-success-lighten text-success"><i class="mdi mdi-check-circle-outline me-1"></i>Ready on Disk</span>';
                    const btnGroup = document.getElementById('action-btn-group-' + modelKey);
                    if (btnGroup) {
                        btnGroup.innerHTML = `
                            <button type="button" class="btn btn-xs btn-outline-success" onclick="triggerModelAction('preload', '${modelKey}')">
                                <i class="mdi mdi-lightning-bolt me-1"></i> Pre-load to RAM
                            </button>
                        `;
                    }
                }
            } else {
                Swal.fire({ icon: 'error', title: 'Action Failed', text: res.message || 'Operation could not be completed' });
            }
        })
        .catch(err => {
            Swal.fire({ icon: 'error', title: 'Error', text: err.message });
        });
    });
}

function pollModelStatus(manual = false) {
    fetch('<?= site_url('admin/ai/models-json') ?>', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(res => res.json())
        .then(data => {
            if (!data || !data.models) return;
            let hasActiveDownloads = false;

            Object.keys(data.models).forEach(key => {
                const model = data.models[key];
                const progressBox = document.getElementById('progress-box-' + key);
                const progressBar = document.getElementById('progress-bar-' + key);
                const progressText = document.getElementById('progress-text-' + key);
                const statusBadge = document.getElementById('status-badge-' + key);
                const btnGroup = document.getElementById('action-btn-group-' + key);

                if (model.download_status === 'downloading') {
                    hasActiveDownloads = true;
                    if (progressBox) progressBox.classList.remove('d-none');
                    if (progressBar) progressBar.style.width = (model.download_progress_pct || 0) + '%';
                    if (progressText) progressText.innerText = (model.download_progress_pct || 0) + '%';
                    if (statusBadge) statusBadge.innerHTML = '<span class="badge bg-warning text-dark"><i class="mdi mdi-cloud-download me-1"></i>Downloading (' + (model.download_progress_pct || 0) + '%)</span>';
                } else if (model.download_status === 'completed' || model.exists_on_disk) {
                    if (progressBox) progressBox.classList.add('d-none');
                    if (statusBadge) {
                        if (model.loaded_in_ram) {
                            statusBadge.innerHTML = '<span class="badge bg-success"><i class="mdi mdi-memory me-1"></i>In RAM (' + (model.ram_mb || 0) + ' MB)</span>';
                        } else {
                            statusBadge.innerHTML = '<span class="badge bg-success-lighten text-success"><i class="mdi mdi-check-circle-outline me-1"></i>Ready on Disk</span>';
                        }
                    }
                    if (btnGroup && btnGroup.querySelector('.btn-primary')) {
                        // Switch from download button to preload button dynamically
                        btnGroup.innerHTML = `
                            <button type="button" class="btn btn-xs btn-outline-success" onclick="triggerModelAction('preload', '${key}')">
                                <i class="mdi mdi-lightning-bolt me-1"></i> Pre-load to RAM
                            </button>
                        `;
                    }
                }
            });

            if (hasActiveDownloads && !pollingInterval) {
                pollingInterval = setInterval(pollModelStatus, 2500);
            } else if (!hasActiveDownloads && pollingInterval && !manual) {
                clearInterval(pollingInterval);
                pollingInterval = null;
                if (typeof notifySuccess === 'function') {
                    notifySuccess('Model download completed!');
                }
            }
        })
        .catch(err => console.error('Model poll error:', err));
}

// Intercept Test Connection & Settings Form on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    // 1. Test Connection Interception
    const btnTest = document.getElementById('btnTestConnection');
    if (btnTest) {
        btnTest.addEventListener('click', function() {
            btnTest.disabled = true;
            const serviceUrl = document.getElementById('service_url').value;
            const apiKey = document.getElementById('api_key').value;

            Swal.fire({
                title: 'Testing Connection...',
                text: `Checking reachability of ML microservice at ${serviceUrl || 'configured URL'}`,
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            const formData = new FormData();
            formData.append('service_url', serviceUrl);
            formData.append('api_key', apiKey);
            if (window.csrfToken) formData.append(window.csrfToken, window.csrfHash);

            fetch('<?= site_url('admin/ai/test-connection') ?>', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(res => {
                btnTest.disabled = false;
                const badge = document.getElementById('connectionStatusBadge');
                if (res.status === 'success' || res.isOnline) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Connected!',
                        text: res.message,
                        confirmButtonText: 'Great'
                    });
                    if (badge) badge.innerHTML = '<span class="badge bg-success-lighten text-success"><i class="mdi mdi-check-circle me-1"></i>Connected</span>';
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Connection Failed',
                        text: res.message,
                        confirmButtonText: 'Understood'
                    });
                    if (badge) badge.innerHTML = '<span class="badge bg-danger-lighten text-danger"><i class="mdi mdi-alert-circle me-1"></i>Unreachable</span>';
                }
            })
            .catch(err => {
                btnTest.disabled = false;
                Swal.fire({ icon: 'error', title: 'Network Request Failed', text: err.message });
            });
        });
    }

    // 2. AI Settings Form Interception
    const formSettings = document.getElementById('aiSettingsForm');
    if (formSettings) {
        formSettings.addEventListener('submit', function(e) {
            e.preventDefault();
            const btnSubmit = formSettings.querySelector('button[type="submit"]');
            if (btnSubmit) btnSubmit.disabled = true;

            const fd = new FormData(formSettings);
            fetch(formSettings.action, {
                method: 'POST',
                body: fd,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(res => {
                if (btnSubmit) btnSubmit.disabled = false;
                if (res.status === 'success') {
                    if (typeof notifySuccess === 'function') {
                        notifySuccess(res.message);
                    } else {
                        Swal.fire({ icon: 'success', title: 'Saved!', text: res.message, timer: 2500, showConfirmButton: false });
                    }
                } else {
                    if (typeof notifyWarning === 'function') {
                        notifyWarning(res.message);
                    } else {
                        Swal.fire({ icon: 'warning', title: 'Notice', text: res.message });
                    }
                }
            })
            .catch(err => {
                if (btnSubmit) btnSubmit.disabled = false;
                Swal.fire({ icon: 'error', title: 'Submission Error', text: err.message });
            });
        });
    }

    // 3. Poll on page load if any model download is in progress
    <?php 
        $hasDownloading = false;
        foreach ($models as $m) {
            if (($m['download_status'] ?? '') === 'downloading') {
                $hasDownloading = true;
                break;
            }
        }
    ?>
    <?php if ($hasDownloading): ?>
        pollingInterval = setInterval(pollModelStatus, 2500);
    <?php endif; ?>
});
</script>
<?= $this->endSection() ?>
