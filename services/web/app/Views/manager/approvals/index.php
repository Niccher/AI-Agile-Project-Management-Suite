<?= $this->extend('layouts/hyper/main') ?>

<?= $this->section('title') ?>Work Approvals Queue • <?= esc(setting('App.siteName')) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-check-circle text-primary me-2"></i> Work Approvals Queue</h2>
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

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div id="empty-queue-state" class="text-center py-5 <?= !empty($tasks) ? 'd-none' : '' ?>">
                <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                <h4>Queue is Empty</h4>
                <p class="text-muted">No tasks currently require your approval.</p>
            </div>

            <div id="approvals-table-container" class="table-responsive <?= empty($tasks) ? 'd-none' : '' ?>">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Task</th>
                            <th>Project</th>
                            <th>Submitted By</th>
                            <th>Submitted At</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="approvals-table-body">
                        <?php if (!empty($tasks)): ?>
                            <?php foreach ($tasks as $task): ?>
                                <tr id="task-row-<?= $task['id'] ?>">
                                    <td>
                                        <div class="fw-bold"><?= esc($task['title']) ?></div>
                                        <div class="text-muted small text-truncate" style="max-width: 300px;">
                                            <?= esc($task['description']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <?= esc($task['project_name'] ?? 'Project') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm bg-primary text-white rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;">
                                                <?= strtoupper(substr($task['first_name'] ?? $task['username'] ?? 'U', 0, 1)) ?>
                                            </div>
                                            <?= esc(trim(($task['first_name'] ?? '') . ' ' . ($task['last_name'] ?? '')) ?: ($task['username'] ?? 'Team Member')) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php 
                                            $dateStr = !empty($task['updated_at']) ? $task['updated_at'] : (!empty($task['created_at']) ? $task['created_at'] : null);
                                            $ts = $dateStr ? strtotime((string)$dateStr) : false;
                                        ?><?= ($ts !== false) ? date('M j, Y g:i A', $ts) : 'N/A' ?>
                                    </td>
                                    <td class="text-end">
                                        <form action="<?= site_url('manager/approvals/'.$task['id'].'/approve') ?>" method="POST" class="d-inline form-approve-task" data-id="<?= $task['id'] ?>" data-title="<?= esc($task['title']) ?>">
                                            <?= csrf_field() ?>
                                            <button type="button" class="btn btn-sm btn-success btn-approve-action">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-outline-danger ms-1" data-bs-toggle="modal" data-bs-target="#rejectModal<?= $task['id'] ?>">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    </td>
                                </tr>

                                <!-- Reject Modal -->
                                <div class="modal fade" id="rejectModal<?= $task['id'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="<?= site_url('manager/approvals/'.$task['id'].'/reject') ?>" method="POST" class="form-reject-task" data-id="<?= $task['id'] ?>" data-modal-id="rejectModal<?= $task['id'] ?>">
                                                <?= csrf_field() ?>
                                                <div class="modal-header bg-danger text-white">
                                                    <h5 class="modal-title">Reject Task: <?= esc($task['title']) ?></h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Reason for Rejection <span class="text-danger">*</span></label>
                                                        <textarea name="rejected_reason" class="form-control" rows="3" required placeholder="Explain what needs to be fixed..."></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-danger btn-submit-reject">Reject Task</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });

    function checkEmptyQueue() {
        const tbody = document.getElementById('approvals-table-body');
        const rows = tbody ? tbody.querySelectorAll('tr') : [];
        if (!rows || rows.length === 0) {
            const tableContainer = document.getElementById('approvals-table-container');
            const emptyState = document.getElementById('empty-queue-state');
            if (tableContainer) tableContainer.classList.add('d-none');
            if (emptyState) emptyState.classList.remove('d-none');
        }
    }

    function removeRowAnimated(taskId) {
        const row = document.getElementById('task-row-' + taskId);
        if (row) {
            row.style.transition = 'all 0.35s ease';
            row.style.opacity = '0';
            row.style.transform = 'translateX(30px)';
            setTimeout(() => {
                row.remove();
                checkEmptyQueue();
            }, 350);
        }
    }

    // Approve Button Action Handler
    document.querySelectorAll('.btn-approve-action').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const form = this.closest('form');
            if (!form) return;
            const taskId = form.getAttribute('data-id');
            const taskTitle = form.getAttribute('data-title') || 'this task';

            Swal.fire({
                title: 'Approve Task?',
                text: `Approve "${taskTitle}" and notify assignee?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0acf97',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-check me-1"></i> Yes, Approve',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Approving...';

                    const formData = new FormData(form);
                    fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('HTTP ' + response.status);
                        return response.json();
                    })
                    .then(data => {
                        if (data.status === 'success') {
                            Toast.fire({
                                icon: 'success',
                                title: data.message || 'Task approved successfully!'
                            });
                            removeRowAnimated(taskId);
                        } else {
                            btn.disabled = false;
                            btn.innerHTML = '<i class="fas fa-check"></i> Approve';
                            Swal.fire({
                                icon: 'error',
                                title: 'Approval Failed',
                                text: data.message || 'Could not approve task.'
                            });
                        }
                    })
                    .catch(err => {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-check"></i> Approve';
                        Swal.fire({
                            icon: 'error',
                            title: 'Network / Server Error',
                            text: err.message
                        });
                    });
                }
            });
        });
    });

    // Reject Form Submit Handler
    document.querySelectorAll('.form-reject-task').forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const taskId = this.getAttribute('data-id');
            const modalId = this.getAttribute('data-modal-id');
            const submitBtn = this.querySelector('.btn-submit-reject');

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Rejecting...';
            }

            const formData = new FormData(this);
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) throw new Error('HTTP ' + response.status);
                return response.json();
            })
            .then(data => {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Reject Task';
                }

                if (data.status === 'success') {
                    // Hide Modal
                    if (modalId) {
                        const modalEl = document.getElementById(modalId);
                        if (modalEl) {
                            const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                            modalInstance.hide();
                        }
                    }

                    Toast.fire({
                        icon: 'warning',
                        title: data.message || 'Task rejected.'
                    });
                    removeRowAnimated(taskId);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Rejection Failed',
                        text: data.message || 'Could not reject task.'
                    });
                }
            })
            .catch(err => {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Reject Task';
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Network / Server Error',
                    text: err.message
                });
            });
        });
    });
});
</script>
<?= $this->endSection() ?>
