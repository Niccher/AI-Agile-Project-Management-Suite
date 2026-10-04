<?= $this->extend('layouts/hyper/main') ?>

<?= $this->section('title') ?>AI Engine Diagnostics • <?= esc(setting('App.siteName')) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid py-2">
    <!-- Page Header -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="page-title mb-0">
                        <i class="mdi mdi-brain text-purple me-2"></i> AI Engine Diagnostics & LLM Telemetry
                    </h4>
                    <p class="text-muted font-13 mb-0">Live container vitals, GGUF model memory allocations, downloads, and llama-cpp-python runtime status.</p>
                </div>
                <div>
                    <a href="<?= site_url('admin/ai/settings') ?>" class="btn btn-sm btn-outline-primary rounded-pill me-2">
                        <i class="mdi mdi-tune-vertical me-1"></i> AI Settings
                    </a>
                    <button id="refreshTelemetryBtn" class="btn btn-sm btn-primary rounded-pill shadow-sm">
                        <i class="mdi mdi-refresh me-1"></i> Refresh Metrics
                    </button>
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

    <!-- Offline Alert Banner -->
    <div id="ai-offline-banner" class="alert alert-warning border-0 shadow-sm mb-4 <?= $isOnline ? 'd-none' : '' ?>">
        <div class="d-flex align-items-center">
            <div class="avatar-sm bg-warning-lighten text-warning rounded d-flex align-items-center justify-content-center me-3">
                <i class="mdi mdi-power-plug-off font-22"></i>
            </div>
            <div>
                <h5 class="my-0 text-warning fw-bold">AI Microservice Offline / Unreachable</h5>
                <p class="mb-0 text-muted font-13">
                    Could not connect to FastAPI container at <code id="ai-banner-service-url"><?= esc(setting('Ml.serviceUrl') ?? env('ML_SERVICE_URL', 'http://ml-chege-jira:8000')) ?></code>. 
                    Error: <span id="ai-banner-error-msg"><?= esc($errorMsg ?? 'Connection refused') ?></span>. Ensure the <code>ml-chege-jira</code> container is running and verify its URL in <a href="<?= site_url('admin/ai/settings') ?>" class="fw-bold text-warning text-decoration-underline">AI Settings</a>.
                </p>
            </div>
        </div>
    </div>

    <?php 
        $container = $telemetry['container'] ?? [];
        $llmRuntime = $telemetry['llm_runtime'] ?? [];
        $db = $telemetry['database'] ?? [];
        $modelsList = $telemetry['models'] ?? $models;

        $formatMemHuman = function($mb) {
            if (!is_numeric($mb) || $mb <= 0) return '0 MB';
            if ($mb >= 1024) {
                return number_format($mb / 1024, 2) . ' GB';
            }
            return number_format($mb, 1) . ' MB';
        };
    ?>

    <!-- Hardware & Runtime Stats -->
    <div class="row g-3 mb-4">
        <!-- CPU Card -->
        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted text-uppercase font-12 fw-bold">AI Container CPU</span>
                        <div class="avatar-xs bg-info-lighten text-info rounded d-flex align-items-center justify-content-center">
                            <i class="mdi mdi-cpu-64-bit font-18"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-baseline mb-2">
                        <h3 id="stat-cpu-percent" class="my-0 me-2 <?= ($container['cpu_percent'] ?? 0) > 80 ? 'text-danger' : 'text-primary' ?>">
                            <?= $container['cpu_percent'] ?? '0' ?>%
                        </h3>
                        <span id="stat-cpu-cores" class="text-muted font-13"><?= $container['cpu_count'] ?? 1 ?> Core(s)</span>
                    </div>
                    <div class="progress mb-2" style="height: 6px;">
                        <div id="stat-cpu-bar" class="progress-bar bg-primary" role="progressbar" style="width: <?= min(100, $container['cpu_percent'] ?? 0) ?>%"></div>
                    </div>
                    <span class="text-muted font-12">Threads configured: <strong id="stat-threads"><?= $llmRuntime['n_threads'] ?? 4 ?></strong></span>
                </div>
            </div>
        </div>

        <!-- RAM Card -->
        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted text-uppercase font-12 fw-bold">RAM Consumption</span>
                        <div class="avatar-xs bg-success-lighten text-success rounded d-flex align-items-center justify-content-center">
                            <i class="mdi mdi-memory font-18"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-baseline mb-2">
                        <h3 id="stat-ram-used" class="my-0 me-2 text-success"><?= $formatMemHuman($container['ram_used_mb'] ?? 0) ?></h3>
                        <span id="stat-ram-total" class="text-muted font-13">/ <?= $formatMemHuman($container['ram_total_mb'] ?? 0) ?></span>
                    </div>
                    <div class="progress mb-2" style="height: 6px;">
                        <div id="stat-ram-bar" class="progress-bar bg-success" role="progressbar" style="width: <?= min(100, $container['ram_percent'] ?? 0) ?>%"></div>
                    </div>
                    <span class="text-muted font-12">Pressure: <strong id="stat-ram-percent"><?= $container['ram_percent'] ?? 0 ?>%</strong></span>
                </div>
            </div>
        </div>

        <!-- Compute Mode Card -->
        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted text-uppercase font-12 fw-bold">Compute Mode</span>
                        <div class="avatar-xs bg-warning-lighten text-warning rounded d-flex align-items-center justify-content-center">
                            <i class="mdi mdi-chip font-18"></i>
                        </div>
                    </div>
                    <h3 id="stat-compute-mode" class="my-0 text-warning"><?= esc($llmRuntime['compute_mode'] ?? 'CPU') ?></h3>
                    <p class="text-muted font-13 my-1">
                        GPU Layers: <strong id="stat-gpu-layers"><?= esc($llmRuntime['n_gpu_layers'] ?? 0) ?></strong>
                    </p>
                    <span id="stat-default-model-badge" class="badge bg-soft-warning text-warning">Default: <?= esc($llmRuntime['default_model'] ?? 'mistral-7b') ?></span>
                </div>
            </div>
        </div>

        <!-- Framework Card -->
        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted text-uppercase font-12 fw-bold">Engine Specs</span>
                        <div class="avatar-xs bg-purple-lighten text-purple rounded d-flex align-items-center justify-content-center">
                            <i class="mdi mdi-code-tags font-18"></i>
                        </div>
                    </div>
                    <p class="font-13 mb-1">llama.cpp: <strong id="stat-llamacpp-ver"><?= esc($llmRuntime['llama_cpp_version'] ?? 'N/A') ?></strong></p>
                    <p class="font-13 mb-1">FastAPI: <strong id="stat-fastapi-ver"><?= esc($llmRuntime['fastapi_version'] ?? 'N/A') ?></strong></p>
                    <p class="font-13 mb-0">Python: <strong id="stat-python-ver"><?= esc($container['python_version'] ?? 'N/A') ?></strong></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Models Management Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title my-0">
                            <i class="mdi mdi-database me-1 text-primary"></i> Model Downloads & Memory Cache Management
                        </h5>
                        <span class="text-muted font-12">Download open-source GGUF models directly to disk, preload into memory, or hot-reload runtime weights</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light font-12 text-uppercase text-muted">
                                <tr>
                                    <th>Model Key</th>
                                    <th>GGUF File</th>
                                    <th>Disk Status</th>
                                    <th>Size</th>
                                    <th>RAM Status</th>
                                    <th>Requests</th>
                                    <th>Avg Latency</th>
                                    <th class="text-end">Model Actions</th>
                                </tr>
                            </thead>
                            <tbody id="telemetry-models-tbody">
                                <?php if (empty($modelsList)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">
                                            No models found or backend unreachable.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($modelsList as $mKey => $m): ?>
                                        <tr id="model-row-<?= esc($mKey) ?>">
                                            <td class="fw-bold text-body">
                                                <code><?= esc($mKey) ?></code>
                                                <?php if (($llmRuntime['default_model'] ?? '') === $mKey): ?>
                                                    <span class="badge bg-info-lighten text-info ms-1">Default</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="font-13 text-muted"><?= esc($m['file'] ?? '') ?></td>
                                            <td id="telemetry-disk-status-<?= esc($mKey) ?>">
                                                <?php if (!empty($m['exists_on_disk'])): ?>
                                                    <span class="badge bg-success-lighten text-success"><i class="mdi mdi-check me-1"></i>Present on Disk</span>
                                                <?php elseif (($m['download_status'] ?? '') === 'downloading'): ?>
                                                    <span class="badge bg-warning text-dark"><i class="mdi mdi-loading mdi-spin me-1"></i>Downloading (<?= $m['download_progress_pct'] ?? 0 ?>%)</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger-lighten text-danger"><i class="mdi mdi-close me-1"></i>Not Downloaded</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="font-13"><?= esc($m['size_gb'] ?? 0) ?> GB</td>
                                            <td id="telemetry-ram-status-<?= esc($mKey) ?>">
                                                <?php if (!empty($m['loaded_in_ram'])): ?>
                                                    <span class="badge bg-success"><i class="mdi mdi-memory me-1"></i>In RAM</span>
                                                <?php else: ?>
                                                    <span class="badge bg-light text-muted">Uncached</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="font-13" id="telemetry-requests-<?= esc($mKey) ?>"><?= esc($m['total_requests'] ?? 0) ?></td>
                                            <td class="font-13" id="telemetry-latency-<?= esc($mKey) ?>">
                                                <?= !empty($m['avg_duration_ms']) ? esc($m['avg_duration_ms']) . ' ms' : '-' ?>
                                            </td>
                                            <td class="text-end" id="telemetry-actions-<?= esc($mKey) ?>">
                                                <div class="btn-group btn-group-sm">
                                                    <?php if (empty($m['exists_on_disk'])): ?>
                                                        <button type="button" class="btn btn-outline-primary" onclick="triggerTelemetryModelAction('download', '<?= esc($mKey) ?>')" <?= ($m['download_status'] ?? '') === 'downloading' ? 'disabled' : '' ?>>
                                                            <i class="mdi mdi-download me-1"></i> Download GGUF
                                                        </button>
                                                    <?php else: ?>
                                                        <?php if (!empty($m['loaded_in_ram'])): ?>
                                                            <button type="button" class="btn btn-outline-warning" onclick="triggerTelemetryModelAction('reload', '<?= esc($mKey) ?>')" title="Reload model weights in RAM">
                                                                <i class="mdi mdi-reload me-1"></i> Reload
                                                            </button>
                                                            <button type="button" class="btn btn-outline-danger ms-1" onclick="triggerTelemetryModelAction('evict', '<?= esc($mKey) ?>')" title="Evict from RAM to free memory">
                                                                <i class="mdi mdi-eject me-1"></i> Evict
                                                            </button>
                                                        <?php else: ?>
                                                            <button type="button" class="btn btn-outline-success" onclick="triggerTelemetryModelAction('preload', '<?= esc($mKey) ?>')">
                                                                <i class="mdi mdi-lightning-bolt me-1"></i> Pre-load into RAM
                                                            </button>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
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

<script>
function formatMemHuman(mb) {
    if (!mb || mb <= 0) return '0 MB';
    if (mb >= 1024) return (mb / 1024).toFixed(2) + ' GB';
    return Number(mb).toFixed(1) + ' MB';
}

function triggerTelemetryModelAction(action, modelKey) {
    let confirmTitle = 'Confirm Action';
    let confirmText = `Are you sure you want to perform "${action}" on ${modelKey}?`;
    let confirmBtn = 'Yes, Proceed';
    let confirmColor = '#3b82f6';

    if (action === 'download') {
        confirmTitle = 'Download Model?';
        confirmText = `Start background download for ${modelKey}?`;
        confirmBtn = 'Download';
    } else if (action === 'preload') {
        confirmTitle = 'Pre-load Model to RAM?';
        confirmText = `Load ${modelKey} into host RAM?`;
        confirmBtn = 'Pre-load';
        confirmColor = '#10b981';
    } else if (action === 'reload') {
        confirmTitle = 'Reload Model in RAM?';
        confirmText = `Restart and refresh memory cache for ${modelKey}?`;
        confirmBtn = 'Reload';
        confirmColor = '#f59e0b';
    } else if (action === 'evict') {
        confirmTitle = 'Evict Model from RAM?';
        confirmText = `Unload ${modelKey} to free system memory?`;
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

        Swal.fire({
            title: 'Executing...',
            text: `Applying ${action} to ${modelKey}`,
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        const formData = new FormData();
        formData.append('action', action);
        formData.append('model_key', modelKey);
        formData.append('redirect', 'telemetry');

        fetch('<?= site_url('admin/ai/cache-action') ?>', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                Swal.fire({ icon: 'success', title: 'Success', text: res.message, timer: 2000, showConfirmButton: false });
                const ramCell = document.getElementById('telemetry-ram-status-' + modelKey);
                const actionCell = document.getElementById('telemetry-actions-' + modelKey);
                if (action === 'preload' || action === 'reload') {
                    if (ramCell) ramCell.innerHTML = '<span class="badge bg-success"><i class="mdi mdi-memory me-1"></i>In RAM</span>';
                    if (actionCell) {
                        actionCell.innerHTML = `
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-warning" onclick="triggerTelemetryModelAction('reload', '${modelKey}')" title="Reload model weights in RAM">
                                    <i class="mdi mdi-reload me-1"></i> Reload
                                </button>
                                <button type="button" class="btn btn-outline-danger ms-1" onclick="triggerTelemetryModelAction('evict', '${modelKey}')" title="Evict from RAM to free memory">
                                    <i class="mdi mdi-eject me-1"></i> Evict
                                </button>
                            </div>
                        `;
                    }
                } else if (action === 'evict') {
                    if (ramCell) ramCell.innerHTML = '<span class="badge bg-light text-muted">Uncached</span>';
                    if (actionCell) {
                        actionCell.innerHTML = `
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-success" onclick="triggerTelemetryModelAction('preload', '${modelKey}')">
                                    <i class="mdi mdi-lightning-bolt me-1"></i> Pre-load into RAM
                                </button>
                            </div>
                        `;
                    }
                }
            } else {
                Swal.fire({ icon: 'error', title: 'Action Failed', text: res.message || 'Operation failed' });
            }
        })
        .catch(err => {
            Swal.fire({ icon: 'error', title: 'Error', text: err.message });
        });
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const refreshBtn = document.getElementById('refreshTelemetryBtn');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            const btn = this;
            const origHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Refreshing...';

            fetch('<?= site_url('admin/ai/telemetry') ?>', {
                headers: { 
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(r => r.json())
            .then(res => {
                btn.disabled = false;
                btn.innerHTML = origHtml;

                const offlineBanner = document.getElementById('ai-offline-banner');
                const errMsgSpan = document.getElementById('ai-banner-error-msg');

                if (res.isOnline && res.telemetry) {
                    if (offlineBanner) offlineBanner.classList.add('d-none');

                    const t = res.telemetry || {};
                    const container = t.container || {};
                    const llm = t.llm_runtime || {};

                    // Update CPU
                    const cpuPercent = container.cpu_percent || 0;
                    const cpuEl = document.getElementById('stat-cpu-percent');
                    if (cpuEl) {
                        cpuEl.textContent = cpuPercent + '%';
                        cpuEl.className = 'my-0 me-2 ' + (cpuPercent > 80 ? 'text-danger' : 'text-primary');
                    }
                    const cpuCoresEl = document.getElementById('stat-cpu-cores');
                    if (cpuCoresEl) cpuCoresEl.textContent = (container.cpu_count || 1) + ' Core(s)';
                    const cpuBar = document.getElementById('stat-cpu-bar');
                    if (cpuBar) cpuBar.style.width = Math.min(100, cpuPercent) + '%';
                    const threadsEl = document.getElementById('stat-threads');
                    if (threadsEl) threadsEl.textContent = llm.n_threads || 4;

                    // Update RAM
                    const ramUsed = container.ram_used_mb || 0;
                    const ramTotal = container.ram_total_mb || 0;
                    const ramPercent = container.ram_percent || 0;
                    const ramUsedEl = document.getElementById('stat-ram-used');
                    if (ramUsedEl) ramUsedEl.textContent = formatMemHuman(ramUsed);
                    const ramTotalEl = document.getElementById('stat-ram-total');
                    if (ramTotalEl) ramTotalEl.textContent = '/ ' + formatMemHuman(ramTotal);
                    const ramBar = document.getElementById('stat-ram-bar');
                    if (ramBar) ramBar.style.width = Math.min(100, ramPercent) + '%';
                    const ramPercentEl = document.getElementById('stat-ram-percent');
                    if (ramPercentEl) ramPercentEl.textContent = ramPercent + '%';

                    // Update Compute Mode
                    const computeModeEl = document.getElementById('stat-compute-mode');
                    if (computeModeEl) computeModeEl.textContent = llm.compute_mode || 'CPU';
                    const gpuLayersEl = document.getElementById('stat-gpu-layers');
                    if (gpuLayersEl) gpuLayersEl.textContent = llm.n_gpu_layers || 0;
                    const defaultModelBadge = document.getElementById('stat-default-model-badge');
                    if (defaultModelBadge) defaultModelBadge.textContent = 'Default: ' + (llm.default_model || 'mistral-7b');

                    // Update Engine Specs
                    const llamacppEl = document.getElementById('stat-llamacpp-ver');
                    if (llamacppEl) llamacppEl.textContent = llm.llama_cpp_version || 'N/A';
                    const fastapiEl = document.getElementById('stat-fastapi-ver');
                    if (fastapiEl) fastapiEl.textContent = llm.fastapi_version || 'N/A';
                    const pythonEl = document.getElementById('stat-python-ver');
                    if (pythonEl) pythonEl.textContent = container.python_version || 'N/A';

                    // Toast
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true
                    });
                    Toast.fire({ icon: 'success', title: 'Metrics refreshed successfully' });
                } else {
                    if (offlineBanner) offlineBanner.classList.remove('d-none');
                    if (errMsgSpan) errMsgSpan.textContent = res.errorMsg || 'Could not communicate with ML microservice.';

                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3500,
                        timerProgressBar: true
                    });
                    Toast.fire({ icon: 'warning', title: 'AI Microservice is currently offline' });
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = origHtml;
                Swal.fire({
                    icon: 'error',
                    title: 'Refresh Error',
                    text: err.message
                });
            });
        });
    }
});
</script>
<?= $this->endSection() ?>
