<?= $this->extend('layouts/hyper/main') ?>

<?= $this->section('title') ?>AI Report Generator • <?= esc(setting('App.siteName')) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid py-2">
    <div class="row mb-3">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="page-title mb-0">
                        <i class="mdi mdi-text-box-search-outline text-primary me-2"></i> AI Report Generator
                    </h4>
                    <p class="text-muted font-13 mb-0">Generate comprehensive sprint summaries and system analyses asynchronously.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body">
                    <h5 class="card-title mb-3">Report Parameters</h5>
                    <form id="reportForm">
                        <div class="mb-3">
                            <label class="form-label font-13 fw-bold">Report Type / Prompt</label>
                            <textarea id="prompt" class="form-control" rows="4" required placeholder="e.g. Write a sprint retrospective for Sprint 5 highlighting accomplishments and blockers..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label font-13 fw-bold">AI Model</label>
                            <select id="model" class="form-select">
                                <option value="mistral-7b">Mistral 7B Instruct</option>
                                <option value="llama3-8b">Llama 3 8B Instruct</option>
                                <option value="phi3-mini">Phi-3 Mini 4K</option>
                                <option value="deepseek-7b">DeepSeek Coder 7B</option>
                            </select>
                        </div>
                        <div class="d-grid">
                            <button type="submit" id="generateBtn" class="btn btn-primary">
                                <i class="mdi mdi-creation me-1"></i> Generate Report
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title mb-3">Generated Output</h5>
                    
                    <div id="statusContainer" class="d-none alert alert-info align-items-center">
                        <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                        <span id="statusText">Generating report in the background... this may take a few minutes.</span>
                    </div>

                    <div id="errorContainer" class="d-none alert alert-danger"></div>

                    <div id="resultContainer" class="flex-grow-1 p-3 bg-light rounded border overflow-auto" style="min-height: 400px; display: none;">
                        <pre id="resultMarkdown" class="mb-0 text-wrap font-monospace font-13"></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('reportForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const prompt = document.getElementById('prompt').value;
    const model = document.getElementById('model').value;
    const btn = document.getElementById('generateBtn');
    
    btn.disabled = true;
    document.getElementById('statusContainer').classList.remove('d-none');
    document.getElementById('statusContainer').classList.add('d-flex');
    document.getElementById('errorContainer').classList.add('d-none');
    document.getElementById('resultContainer').style.display = 'none';

    fetch('<?= site_url('admin/ai/reports/generate') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({
            'prompt': prompt,
            'model': model,
            'csrf_test_name': '<?= csrf_hash() ?>' // adjust CSRF field if necessary
        })
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            pollTask(data.task_id);
        } else {
            showError(data.error || 'Failed to start task.');
        }
    })
    .catch(err => showError(err.message));
});

function pollTask(taskId) {
    const pollInterval = setInterval(() => {
        fetch('<?= site_url('admin/ai/reports/status/') ?>' + taskId)
            .then(res => res.json())
            .then(data => {
                if(!data.success) {
                    clearInterval(pollInterval);
                    showError(data.error || 'Failed to check status');
                    return;
                }
                
                const task = data.data;
                if(task.status === 'completed') {
                    clearInterval(pollInterval);
                    showResult(task.result);
                } else if(task.status === 'failed') {
                    clearInterval(pollInterval);
                    showError(task.error || 'LLM generation failed.');
                }
                // else pending/running, continue polling
            })
            .catch(err => {
                clearInterval(pollInterval);
                showError(err.message);
            });
    }, 3000); // poll every 3 seconds
}

function showResult(resultData) {
    document.getElementById('statusContainer').classList.remove('d-flex');
    document.getElementById('statusContainer').classList.add('d-none');
    document.getElementById('generateBtn').disabled = false;
    
    document.getElementById('resultContainer').style.display = 'block';
    
    let text = typeof resultData === 'object' ? resultData.raw_text : resultData;
    if(typeof resultData === 'object' && !resultData.raw_text) {
        text = JSON.stringify(resultData, null, 2);
    }
    document.getElementById('resultMarkdown').textContent = text;
}

function showError(msg) {
    document.getElementById('statusContainer').classList.remove('d-flex');
    document.getElementById('statusContainer').classList.add('d-none');
    document.getElementById('generateBtn').disabled = false;
    
    document.getElementById('errorContainer').classList.remove('d-none');
    document.getElementById('errorContainer').textContent = msg;
}
</script>
<?= $this->endSection() ?>
