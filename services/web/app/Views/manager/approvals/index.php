<?= $this->extend('layouts/hyper/main') ?>

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
            <?php if (empty($tasks)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                    <h4>Queue is Empty</h4>
                    <p class="text-muted">No tasks currently require your approval.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
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
                        <tbody>
                            <?php foreach ($tasks as $task): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?= esc($task['title']) ?></div>
                                        <div class="text-muted small text-truncate" style="max-width: 300px;">
                                            <?= esc($task['description']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-default">
                                            <?= esc($task['project_name']) ?>
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
                                        <?= date('M j, Y g:i A', strtotime($task['updated_at'] ?? $task['created_at'] ?? 'now')) ?>
                                    </td>
                                    <td class="text-end">
                                        <form action="<?= site_url('manage/approvals/'.$task['id'].'/approve') ?>" method="POST" class="d-inline">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Approve this task?');">
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
                                            <form action="<?= site_url('manage/approvals/'.$task['id'].'/reject') ?>" method="POST">
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
                                                    <button type="submit" class="btn btn-danger">Reject Task</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
