<?= $this->extend('layouts/hyper/main') ?>

<?= $this->section('title') ?>Work Approvals Queue • <?= esc(setting('App.siteName')) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="fas fa-check-double text-primary me-2"></i> Work Approvals Management</h2>
            <p class="text-muted font-13 mb-0">Review completed tasks submitted by team members, approve deliverables, or request revisions.</p>
        </div>
    </div>

    <?php if (session()->has('message')) : ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="mdi mdi-check-circle-outline me-2 font-16"></i> <?= session('message') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->has('error')) : ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="mdi mdi-alert-circle-outline me-2 font-16"></i> <?= session('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Tabs Nav -->
    <ul class="nav nav-pills bg-nav-pills nav-justified mb-3 shadow-sm rounded bg-white p-1" id="approvalTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded py-2 d-flex align-items-center justify-content-center gap-2" id="pending-tab" data-bs-toggle="tab" data-bs-target="#tab-pending" type="button" role="tab" aria-controls="tab-pending" aria-selected="true">
                <i class="fas fa-clock text-warning"></i>
                <span class="fw-bold">Pending Review</span>
                <span class="badge bg-warning text-dark rounded-pill px-2" id="badge-pending"><?= count($pendingTasks ?? []) ?></span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded py-2 d-flex align-items-center justify-content-center gap-2" id="approved-tab" data-bs-toggle="tab" data-bs-target="#tab-approved" type="button" role="tab" aria-controls="tab-approved" aria-selected="false">
                <i class="fas fa-check-circle text-success"></i>
                <span class="fw-bold">Approved Work</span>
                <span class="badge bg-success rounded-pill px-2" id="badge-approved"><?= count($approvedTasks ?? []) ?></span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded py-2 d-flex align-items-center justify-content-center gap-2" id="rejected-tab" data-bs-toggle="tab" data-bs-target="#tab-rejected" type="button" role="tab" aria-controls="tab-rejected" aria-selected="false">
                <i class="fas fa-undo-alt text-danger"></i>
                <span class="fw-bold">Rejected / Rework</span>
                <span class="badge bg-danger rounded-pill px-2" id="badge-rejected"><?= count($rejectedTasks ?? []) ?></span>
            </button>
        </li>
    </ul>

    <!-- Tabs Content -->
    <div class="tab-content" id="approvalTabsContent">
        <!-- 1. PENDING REVIEW TAB -->
        <div class="tab-pane fade show active" id="tab-pending" role="tabpanel" aria-labelledby="pending-tab">
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div id="empty-pending-state" class="text-center py-5 <?= !empty($pendingTasks) ? 'd-none' : '' ?>">
                        <i class="fas fa-clipboard-check fa-4x text-muted mb-3 opacity-50"></i>
                        <h4>No Pending Approvals</h4>
                        <p class="text-muted">All submitted tasks have been reviewed.</p>
                    </div>

                    <div id="pending-table-container" class="table-responsive <?= empty($pendingTasks) ? 'd-none' : '' ?>">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Task Details</th>
                                    <th>Project</th>
                                    <th>Assignee</th>
                                    <th>Logged Time</th>
                                    <th>Submitted At</th>
                                    <th class="text-end pe-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="pending-tbody">
                                <?php if (!empty($pendingTasks)): ?>
                                    <?php foreach ($pendingTasks as $task): ?>
                                        <tr id="task-row-<?= $task['id'] ?>">
                                            <td class="ps-3">
                                                <div class="fw-bold text-primary cursor-pointer" data-bs-toggle="modal" data-bs-target="#taskModal<?= $task['id'] ?>" style="cursor: pointer;">
                                                    <i class="fas fa-eye me-1 text-muted"></i> <?= esc($task['title']) ?>
                                                </div>
                                                <div class="text-muted small text-truncate" style="max-width: 320px;">
                                                    <?= esc($task['description'] ?: 'No description.') ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border">
                                                    <?= esc($task['project_name'] ?? 'Workspace') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm bg-primary text-white rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 30px; height: 30px; font-size: 13px;">
                                                        <?= strtoupper(substr($task['first_name'] ?? $task['username'] ?? 'U', 0, 1)) ?>
                                                    </div>
                                                    <div>
                                                        <span class="font-13 fw-semibold d-block"><?= esc(trim(($task['first_name'] ?? '') . ' ' . ($task['last_name'] ?? '')) ?: ($task['username'] ?? 'Team Member')) ?></span>
                                                        <small class="text-muted">@<?= esc($task['username'] ?? 'user') ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-success-lighten text-success font-12"><i class="fas fa-clock me-1"></i> <?= esc($task['logged_hours'] ?? '0.0') ?>h</span>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?= !empty($task['updated_at']) ? date('M j, Y g:i A', strtotime($task['updated_at'])) : (date('M j, Y g:i A', strtotime($task['created_at'] ?? 'now'))) ?>
                                                </small>
                                            </td>
                                            <td class="text-end pe-3">
                                                <button type="button" class="btn btn-sm btn-outline-info me-1" data-bs-toggle="modal" data-bs-target="#taskModal<?= $task['id'] ?>" title="View Task Details">
                                                    <i class="fas fa-eye"></i> Details
                                                </button>
                                                <form action="<?= site_url('manager/approvals/'.$task['id'].'/approve') ?>" method="POST" class="d-inline form-approve-task" data-id="<?= $task['id'] ?>" data-title="<?= esc($task['title']) ?>" data-project="<?= esc($task['project_name'] ?? 'Workspace') ?>" data-assignee="<?= esc(trim(($task['first_name'] ?? '') . ' ' . ($task['last_name'] ?? '')) ?: ($task['username'] ?? 'Team Member')) ?>" data-hours="<?= esc($task['logged_hours'] ?? '0.0') ?>">
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
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. APPROVED WORK TAB -->
        <div class="tab-pane fade" id="tab-approved" role="tabpanel" aria-labelledby="approved-tab">
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div id="empty-approved-state" class="text-center py-5 <?= !empty($approvedTasks) ? 'd-none' : '' ?>">
                        <i class="fas fa-check-circle fa-4x text-success mb-3 opacity-50"></i>
                        <h4>No Approved Tasks Yet</h4>
                        <p class="text-muted">Approved work will appear here.</p>
                    </div>

                    <div id="approved-table-container" class="table-responsive <?= empty($approvedTasks) ? 'd-none' : '' ?>">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Task Details</th>
                                    <th>Project</th>
                                    <th>Assignee</th>
                                    <th>Logged Time</th>
                                    <th>Approved By</th>
                                    <th>Approved Date</th>
                                    <th class="text-end pe-3">Action</th>
                                </tr>
                            </thead>
                            <tbody id="approved-tbody">
                                <?php if (!empty($approvedTasks)): ?>
                                    <?php foreach ($approvedTasks as $task): ?>
                                        <tr id="approved-row-<?= $task['id'] ?>">
                                            <td class="ps-3">
                                                <div class="fw-bold text-success cursor-pointer" data-bs-toggle="modal" data-bs-target="#taskModal<?= $task['id'] ?>" style="cursor: pointer;">
                                                    <i class="fas fa-check-circle me-1"></i> <?= esc($task['title']) ?>
                                                </div>
                                                <div class="text-muted small text-truncate" style="max-width: 300px;">
                                                    <?= esc($task['description'] ?: 'No description.') ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border"><?= esc($task['project_name'] ?? 'Workspace') ?></span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm bg-primary text-white rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-size: 12px;">
                                                        <?= strtoupper(substr($task['first_name'] ?? $task['username'] ?? 'U', 0, 1)) ?>
                                                    </div>
                                                    <span class="font-13"><?= esc(trim(($task['first_name'] ?? '') . ' ' . ($task['last_name'] ?? '')) ?: ($task['username'] ?? 'Team Member')) ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-success-lighten text-success font-12"><i class="fas fa-clock me-1"></i> <?= esc($task['logged_hours'] ?? '0.0') ?>h</span>
                                            </td>
                                            <td>
                                                <span class="text-muted font-13"><i class="fas fa-user-check me-1 text-success"></i> <?= esc(trim(($task['approver_first_name'] ?? '') . ' ' . ($task['approver_last_name'] ?? '')) ?: ($task['approver_username'] ?? 'Manager')) ?></span>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?= !empty($task['approved_at']) ? date('M j, Y g:i A', strtotime($task['approved_at'])) : (date('M j, Y g:i A', strtotime($task['updated_at'] ?? 'now'))) ?>
                                                </small>
                                            </td>
                                            <td class="text-end pe-3">
                                                <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#taskModal<?= $task['id'] ?>">
                                                    <i class="fas fa-eye"></i> Details
                                                </button>
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

        <!-- 3. REJECTED / REWORK TAB -->
        <div class="tab-pane fade" id="tab-rejected" role="tabpanel" aria-labelledby="rejected-tab">
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div id="empty-rejected-state" class="text-center py-5 <?= !empty($rejectedTasks) ? 'd-none' : '' ?>">
                        <i class="fas fa-thumbs-up fa-4x text-info mb-3 opacity-50"></i>
                        <h4>No Rejected Tasks</h4>
                        <p class="text-muted">Tasks returned for rework will appear here.</p>
                    </div>

                    <div id="rejected-table-container" class="table-responsive <?= empty($rejectedTasks) ? 'd-none' : '' ?>">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Task Details</th>
                                    <th>Project</th>
                                    <th>Assignee</th>
                                    <th>Rejection Reason</th>
                                    <th>Rejected Date</th>
                                    <th class="text-end pe-3">Action</th>
                                </tr>
                            </thead>
                            <tbody id="rejected-tbody">
                                <?php if (!empty($rejectedTasks)): ?>
                                    <?php foreach ($rejectedTasks as $task): ?>
                                        <tr id="rejected-row-<?= $task['id'] ?>">
                                            <td class="ps-3">
                                                <div class="fw-bold text-danger cursor-pointer" data-bs-toggle="modal" data-bs-target="#taskModal<?= $task['id'] ?>" style="cursor: pointer;">
                                                    <i class="fas fa-times-circle me-1"></i> <?= esc($task['title']) ?>
                                                </div>
                                                <div class="text-muted small text-truncate" style="max-width: 280px;">
                                                    <?= esc($task['description'] ?: 'No description.') ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border"><?= esc($task['project_name'] ?? 'Workspace') ?></span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm bg-primary text-white rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-size: 12px;">
                                                        <?= strtoupper(substr($task['first_name'] ?? $task['username'] ?? 'U', 0, 1)) ?>
                                                    </div>
                                                    <span class="font-13"><?= esc(trim(($task['first_name'] ?? '') . ' ' . ($task['last_name'] ?? '')) ?: ($task['username'] ?? 'Team Member')) ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="p-2 bg-danger-lighten rounded text-danger small font-12" style="max-width: 320px;">
                                                    <i class="fas fa-exclamation-circle me-1"></i> <?= esc($task['rejected_reason'] ?? 'Needs rework.') ?>
                                                </div>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?= !empty($task['updated_at']) ? date('M j, Y g:i A', strtotime($task['updated_at'])) : (date('M j, Y g:i A', strtotime($task['created_at'] ?? 'now'))) ?>
                                                </small>
                                            </td>
                                            <td class="text-end pe-3">
                                                <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#taskModal<?= $task['id'] ?>">
                                                    <i class="fas fa-eye"></i> Details
                                                </button>
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

<!-- Render Unified Modals for All Tasks -->
<?php 
    $allTasks = array_merge($pendingTasks ?? [], $approvedTasks ?? [], $rejectedTasks ?? []);
    $uniqueTasks = [];
    foreach ($allTasks as $t) {
        $uniqueTasks[$t['id']] = $t;
    }
?>
<?php foreach ($uniqueTasks as $task): ?>
    <!-- Task Detail Modal -->
    <div class="modal fade" id="taskModal<?= $task['id'] ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-light border-bottom">
                    <h5 class="modal-title font-16 fw-bold">
                        <i class="fas fa-tasks me-2 text-primary"></i> <?= esc($task['title']) ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <span class="text-muted font-12 text-uppercase fw-bold d-block mb-1">Project</span>
                            <span class="badge bg-primary-lighten text-primary fs-6"><?= esc($task['project_name'] ?? 'Workspace') ?></span>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted font-12 text-uppercase fw-bold d-block mb-1">Priority & Points</span>
                            <span class="badge bg-warning text-dark me-2"><?= esc(ucfirst($task['priority'] ?? 'Medium')) ?> Priority</span>
                            <span class="badge bg-secondary"><?= (int)($task['story_points'] ?? 1) ?> Story Points</span>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <span class="text-muted font-12 text-uppercase fw-bold d-block mb-1">Assignee / Submitted By</span>
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm bg-primary text-white rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; font-size: 14px;">
                                    <?= strtoupper(substr($task['first_name'] ?? $task['username'] ?? 'U', 0, 1)) ?>
                                </div>
                                <div>
                                    <h6 class="mb-0 font-14"><?= esc(trim(($task['first_name'] ?? '') . ' ' . ($task['last_name'] ?? '')) ?: ($task['username'] ?? 'Team Member')) ?></h6>
                                    <small class="text-muted">@<?= esc($task['username'] ?? 'user') ?></small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted font-12 text-uppercase fw-bold d-block mb-1">Logged Time</span>
                            <h5 class="my-0 text-success"><i class="fas fa-clock me-1"></i> <?= esc($task['logged_hours'] ?? '0.0') ?> hrs</h5>
                        </div>
                    </div>

                    <div class="mb-4">
                        <span class="text-muted font-12 text-uppercase fw-bold d-block mb-1">Task Description & Deliverables</span>
                        <div class="p-3 bg-light rounded border font-14">
                            <?= nl2br(esc($task['description'] ?: 'No description provided for this task.')) ?>
                        </div>
                    </div>

                    <?php if (!empty($task['rejected_reason'])): ?>
                        <div class="mb-3 p-3 bg-danger-lighten rounded border border-danger">
                            <span class="text-danger font-12 text-uppercase fw-bold d-block mb-1"><i class="fas fa-exclamation-triangle me-1"></i> Rejection Reason</span>
                            <div class="text-danger font-14"><?= nl2br(esc($task['rejected_reason'])) ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($task['due_date'])): ?>
                        <div class="mb-2 text-muted font-13">
                            <i class="fas fa-calendar-alt me-1 text-danger"></i> Target Due Date: <strong><?= date('F j, Y', strtotime($task['due_date'])) ?></strong>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer bg-light border-top">
                    <?php if (($task['status'] ?? 'review') === 'review'): ?>
                        <form action="<?= site_url('manager/approvals/'.$task['id'].'/approve') ?>" method="POST" class="d-inline form-approve-task" data-id="<?= $task['id'] ?>" data-title="<?= esc($task['title']) ?>" data-project="<?= esc($task['project_name'] ?? 'Workspace') ?>" data-assignee="<?= esc(trim(($task['first_name'] ?? '') . ' ' . ($task['last_name'] ?? '')) ?: ($task['username'] ?? 'Team Member')) ?>" data-hours="<?= esc($task['logged_hours'] ?? '0.0') ?>">
                            <?= csrf_field() ?>
                            <button type="button" class="btn btn-success btn-approve-action" data-bs-dismiss="modal">
                                <i class="fas fa-check me-1"></i> Approve Work
                            </button>
                        </form>
                        <button type="button" class="btn btn-outline-danger ms-2" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#rejectModal<?= $task['id'] ?>">
                            <i class="fas fa-times me-1"></i> Reject Work
                        </button>
                    <?php endif; ?>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal<?= $task['id'] ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <form action="<?= site_url('manager/approvals/'.$task['id'].'/reject') ?>" method="POST" class="form-reject-task" data-id="<?= $task['id'] ?>" data-modal-id="rejectModal<?= $task['id'] ?>" data-title="<?= esc($task['title']) ?>" data-project="<?= esc($task['project_name'] ?? 'Workspace') ?>" data-assignee="<?= esc(trim(($task['first_name'] ?? '') . ' ' . ($task['last_name'] ?? '')) ?: ($task['username'] ?? 'Team Member')) ?>" data-hours="<?= esc($task['logged_hours'] ?? '0.0') ?>">
                    <?= csrf_field() ?>
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title"><i class="fas fa-undo-alt me-2"></i> Reject Task: <?= esc($task['title']) ?></h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Reason for Rejection / Revision Details <span class="text-danger">*</span></label>
                            <textarea name="rejected_reason" class="form-control" rows="4" required placeholder="Explain why this work was rejected and what specific changes are needed..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger btn-submit-reject"><i class="fas fa-times me-1"></i> Reject Task</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>
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

    function updateBadge(id, delta) {
        const badge = document.getElementById(id);
        if (badge) {
            let val = parseInt(badge.textContent.trim()) || 0;
            val = Math.max(0, val + delta);
            badge.textContent = val;
        }
    }

    function checkEmptyStates() {
        const pendingTbody = document.getElementById('pending-tbody');
        const pendingRows = pendingTbody ? pendingTbody.querySelectorAll('tr') : [];
        const pendingContainer = document.getElementById('pending-table-container');
        const pendingEmpty = document.getElementById('empty-pending-state');
        if (pendingContainer && pendingEmpty) {
            if (pendingRows.length === 0) {
                pendingContainer.classList.add('d-none');
                pendingEmpty.classList.remove('d-none');
            } else {
                pendingContainer.classList.remove('d-none');
                pendingEmpty.classList.add('d-none');
            }
        }

        const approvedTbody = document.getElementById('approved-tbody');
        const approvedRows = approvedTbody ? approvedTbody.querySelectorAll('tr') : [];
        const approvedContainer = document.getElementById('approved-table-container');
        const approvedEmpty = document.getElementById('empty-approved-state');
        if (approvedContainer && approvedEmpty) {
            if (approvedRows.length === 0) {
                approvedContainer.classList.add('d-none');
                approvedEmpty.classList.remove('d-none');
            } else {
                approvedContainer.classList.remove('d-none');
                approvedEmpty.classList.add('d-none');
            }
        }

        const rejectedTbody = document.getElementById('rejected-tbody');
        const rejectedRows = rejectedTbody ? rejectedTbody.querySelectorAll('tr') : [];
        const rejectedContainer = document.getElementById('rejected-table-container');
        const rejectedEmpty = document.getElementById('empty-rejected-state');
        if (rejectedContainer && rejectedEmpty) {
            if (rejectedRows.length === 0) {
                rejectedContainer.classList.add('d-none');
                rejectedEmpty.classList.remove('d-none');
            } else {
                rejectedContainer.classList.remove('d-none');
                rejectedEmpty.classList.add('d-none');
            }
        }
    }

    // Approve Task Handler
    document.addEventListener('click', function(e) {
        const approveBtn = e.target.closest('.btn-approve-action');
        if (!approveBtn) return;

        e.preventDefault();
        const form = approveBtn.closest('form');
        if (!form) return;

        const taskId = form.getAttribute('data-id');
        const taskTitle = form.getAttribute('data-title') || 'this task';
        const projectName = form.getAttribute('data-project') || 'Workspace';
        const assigneeName = form.getAttribute('data-assignee') || 'Team Member';
        const loggedHours = form.getAttribute('data-hours') || '0.0';

        Swal.fire({
            title: 'Approve Task Deliverable?',
            text: `Confirm approval for "${taskTitle}"? The assignee will be notified.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0acf97',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-check me-1"></i> Yes, Approve',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                approveBtn.disabled = true;
                approveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Approving...';

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
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    return res.json();
                })
                .then(data => {
                    if (data.status === 'success') {
                        Toast.fire({
                            icon: 'success',
                            title: data.message || 'Task approved successfully!'
                        });

                        // Remove from pending
                        const pendingRow = document.getElementById('task-row-' + taskId);
                        if (pendingRow) {
                            pendingRow.remove();
                        }
                        updateBadge('badge-pending', -1);
                        updateBadge('badge-approved', 1);

                        // Prepend to approved tbody
                        const approvedTbody = document.getElementById('approved-tbody');
                        if (approvedTbody) {
                            const newRow = document.createElement('tr');
                            newRow.id = 'approved-row-' + taskId;
                            newRow.className = 'table-success bg-opacity-10';
                            newRow.innerHTML = `
                                <td class="ps-3">
                                    <div class="fw-bold text-success cursor-pointer" data-bs-toggle="modal" data-bs-target="#taskModal${taskId}" style="cursor: pointer;">
                                        <i class="fas fa-check-circle me-1"></i> ${escapeHtml(taskTitle)}
                                    </div>
                                    <div class="text-muted small text-truncate" style="max-width: 300px;">Deliverable approved</div>
                                </td>
                                <td><span class="badge bg-light text-dark border">${escapeHtml(projectName)}</span></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm bg-primary text-white rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-size: 12px;">
                                            ${escapeHtml(assigneeName.charAt(0).toUpperCase())}
                                        </div>
                                        <span class="font-13">${escapeHtml(assigneeName)}</span>
                                    </div>
                                </td>
                                <td><span class="badge bg-success-lighten text-success font-12"><i class="fas fa-clock me-1"></i> ${loggedHours}h</span></td>
                                <td><span class="text-muted font-13"><i class="fas fa-user-check me-1 text-success"></i> ${escapeHtml(data.approver_name || 'Manager')}</span></td>
                                <td><small class="text-muted">${data.approved_at || 'Just now'}</small></td>
                                <td class="text-end pe-3">
                                    <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#taskModal${taskId}">
                                        <i class="fas fa-eye"></i> Details
                                    </button>
                                </td>
                            `;
                            approvedTbody.insertBefore(newRow, approvedTbody.firstChild);
                        }

                        checkEmptyStates();
                    } else {
                        approveBtn.disabled = false;
                        approveBtn.innerHTML = '<i class="fas fa-check"></i> Approve';
                        Swal.fire({
                            icon: 'error',
                            title: 'Approval Failed',
                            text: data.message || 'Could not approve task.'
                        });
                    }
                })
                .catch(err => {
                    approveBtn.disabled = false;
                    approveBtn.innerHTML = '<i class="fas fa-check"></i> Approve';
                    Swal.fire({
                        icon: 'error',
                        title: 'Server Error',
                        text: err.message
                    });
                });
            }
        });
    });

    // Reject Task Handler
    document.addEventListener('submit', function(e) {
        const form = e.target.closest('.form-reject-task');
        if (!form) return;

        e.preventDefault();
        const taskId = form.getAttribute('data-id');
        const modalId = form.getAttribute('data-modal-id');
        const taskTitle = form.getAttribute('data-title') || 'this task';
        const projectName = form.getAttribute('data-project') || 'Workspace';
        const assigneeName = form.getAttribute('data-assignee') || 'Team Member';
        const submitBtn = form.querySelector('.btn-submit-reject');

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Rejecting...';
        }

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
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.json();
        })
        .then(data => {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-times me-1"></i> Reject Task';
            }

            if (data.status === 'success') {
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

                // Remove from pending
                const pendingRow = document.getElementById('task-row-' + taskId);
                if (pendingRow) {
                    pendingRow.remove();
                }
                updateBadge('badge-pending', -1);
                updateBadge('badge-rejected', 1);

                // Prepend to rejected tbody
                const rejectedTbody = document.getElementById('rejected-tbody');
                if (rejectedTbody) {
                    const newRow = document.createElement('tr');
                    newRow.id = 'rejected-row-' + taskId;
                    newRow.className = 'table-danger bg-opacity-10';
                    newRow.innerHTML = `
                        <td class="ps-3">
                            <div class="fw-bold text-danger cursor-pointer" data-bs-toggle="modal" data-bs-target="#taskModal${taskId}" style="cursor: pointer;">
                                <i class="fas fa-times-circle me-1"></i> ${escapeHtml(taskTitle)}
                            </div>
                            <div class="text-muted small text-truncate" style="max-width: 280px;">Returned for rework</div>
                        </td>
                        <td><span class="badge bg-light text-dark border">${escapeHtml(projectName)}</span></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm bg-primary text-white rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-size: 12px;">
                                    ${escapeHtml(assigneeName.charAt(0).toUpperCase())}
                                </div>
                                <span class="font-13">${escapeHtml(assigneeName)}</span>
                            </div>
                        </td>
                        <td>
                            <div class="p-2 bg-danger-lighten rounded text-danger small font-12" style="max-width: 320px;">
                                <i class="fas fa-exclamation-circle me-1"></i> ${escapeHtml(data.rejected_reason || 'Needs rework')}
                            </div>
                        </td>
                        <td><small class="text-muted">${data.rejected_at || 'Just now'}</small></td>
                        <td class="text-end pe-3">
                            <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#taskModal${taskId}">
                                <i class="fas fa-eye"></i> Details
                            </button>
                        </td>
                    `;
                    rejectedTbody.insertBefore(newRow, rejectedTbody.firstChild);
                }

                checkEmptyStates();
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
                submitBtn.innerHTML = '<i class="fas fa-times me-1"></i> Reject Task';
            }
            Swal.fire({
                icon: 'error',
                title: 'Server Error',
                text: err.message
            });
        });
    });

    function escapeHtml(text) {
        if (!text) return '';
        return String(text).replace(/[&<>"']/g, function (m) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[m];
        });
    }
});
</script>
<?= $this->endSection() ?>
