<?= $this->extend('layouts/hyper/main') ?>

<?= $this->section('title') ?><?= esc($project['name'] ?? 'Project') ?> Board • <?= esc(setting('App.siteName')) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
$currentUserId = (int)auth()->id();
?>

<!-- Page Header -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-primary rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#addTaskModal">
                        <i class="mdi mdi-plus-circle me-1"></i> Add Task
                    </button>
                    <a href="<?= site_url('projects/view/' . (!empty($project['slug']) ? $project['slug'] : $project['id'])) ?>" class="btn btn-outline-secondary rounded-pill">
                        <i class="mdi mdi-arrow-left me-1"></i> Back to Project
                    </a>
                </div>
            </div>
            <h4 class="page-title">
                <i class="uil-columns me-2 text-primary"></i> <?= esc($project['name'] ?? 'Project') ?> Kanban Board
            </h4>
        </div>
    </div>
</div>

<!-- Search & Agile Quick Filters Toolbar -->
<div class="row mb-3 align-items-center g-2">
    <div class="col-md-4 col-lg-3">
        <div class="input-group shadow-sm">
            <span class="input-group-text bg-white border-end-0 text-muted"><i class="mdi mdi-magnify font-16"></i></span>
            <input type="text" class="form-control border-start-0 ps-0" id="kanbanSearchInput" placeholder="Search tasks by title or details...">
        </div>
    </div>
    <div class="col-md-8 col-lg-9 d-flex flex-wrap align-items-center gap-2">
        <div class="btn-group btn-group-sm shadow-sm" role="group" id="filterPills">
            <button type="button" class="btn btn-outline-secondary active" data-filter="all">All Tasks</button>
            <button type="button" class="btn btn-outline-secondary" data-filter="my_tasks" data-user-id="<?= $currentUserId ?>">
                <i class="mdi mdi-account me-1"></i> My Tasks
            </button>
            <button type="button" class="btn btn-outline-secondary" data-filter="high_priority">
                <i class="mdi mdi-alert-circle-outline me-1 text-danger"></i> High & Critical
            </button>
            <button type="button" class="btn btn-outline-secondary" data-filter="overdue">
                <i class="mdi mdi-clock-alert-outline me-1 text-warning"></i> Overdue
            </button>
        </div>
        <span class="text-muted font-12 ms-auto d-none d-sm-inline">
            <i class="mdi mdi-mouse me-1 text-primary"></i> Tip: <strong>Right-click</strong> any card for instant stage transitions & priority changes.
        </span>
    </div>
</div>

<!-- Kanban Board Container -->
<div class="kanban-container pb-4">
    <div class="row kanban-row g-3 flex-nowrap overflow-auto py-2">
        
        <!-- To Do Column -->
        <div class="col-12 col-md-6 col-xl-3" style="min-width: 280px;">
            <div class="card shadow-sm border-0 h-100 kanban-column" data-status="todo">
                <div class="card-header bg-light text-dark py-2 px-3 d-flex justify-content-between align-items-center rounded-top border-bottom">
                    <h6 class="mb-0 text-dark font-14 fw-bold">
                        <i class="mdi mdi-clipboard-outline text-secondary me-1"></i> TO DO
                    </h6>
                    <span class="badge bg-secondary text-white font-12 rounded-pill column-badge" id="count-todo">
                        <?= count($boardData['todo'] ?? []) ?>
                    </span>
                </div>
                <div class="card-body p-2 d-flex flex-column" style="min-height: 520px; background-color: var(--bs-tertiary-bg, rgba(0,0,0,0.02));">
                    <div class="kanban-cards sortable-list d-flex flex-column flex-grow-1" id="todo-list">
                        <?php if (!empty($boardData['todo'])): ?>
                            <?php foreach ($boardData['todo'] as $task): ?>
                                <?= view('partials/user/kanban_card', ['task' => $task]) ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <!-- Quick Inline Add Task -->
                    <div class="mt-2 pt-2 border-top border-light">
                        <button type="button" class="btn btn-xs btn-light text-muted w-100 py-1 quick-add-trigger" data-status="todo">
                            <i class="mdi mdi-plus me-1"></i> Add a task...
                        </button>
                        <div class="quick-add-form d-none mt-1">
                            <input type="text" class="form-control form-control-sm quick-add-input mb-1" placeholder="Task title..." maxlength="255">
                            <div class="d-flex justify-content-end gap-1">
                                <button type="button" class="btn btn-xs btn-light quick-add-cancel">Cancel</button>
                                <button type="button" class="btn btn-xs btn-primary quick-add-save" data-status="todo">Add</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- In Progress Column -->
        <div class="col-12 col-md-6 col-xl-3" style="min-width: 280px;">
            <div class="card shadow-sm border-0 h-100 kanban-column" data-status="in_progress">
                <div class="card-header bg-info-lighten text-info py-2 px-3 d-flex justify-content-between align-items-center rounded-top border-bottom">
                    <div class="d-flex align-items-center">
                        <h6 class="mb-0 text-info font-14 fw-bold">
                            <i class="mdi mdi-progress-clock me-1"></i> IN PROGRESS
                        </h6>
                        <span class="badge bg-danger ms-2 font-10 d-none wip-warning" id="wip-warn-in_progress" title="WIP Limit (5) Exceeded!">
                            WIP &gt; 5
                        </span>
                    </div>
                    <span class="badge bg-info text-white font-12 rounded-pill column-badge" id="count-in_progress" data-wip-limit="5">
                        <?= count($boardData['in_progress'] ?? []) ?>
                    </span>
                </div>
                <div class="card-body p-2 d-flex flex-column" style="min-height: 520px; background-color: var(--bs-tertiary-bg, rgba(0,0,0,0.02));">
                    <div class="kanban-cards sortable-list d-flex flex-column flex-grow-1" id="in_progress-list">
                        <?php if (!empty($boardData['in_progress'])): ?>
                            <?php foreach ($boardData['in_progress'] as $task): ?>
                                <?= view('partials/user/kanban_card', ['task' => $task]) ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <!-- Quick Inline Add Task -->
                    <div class="mt-2 pt-2 border-top border-light">
                        <button type="button" class="btn btn-xs btn-light text-muted w-100 py-1 quick-add-trigger" data-status="in_progress">
                            <i class="mdi mdi-plus me-1"></i> Add a task...
                        </button>
                        <div class="quick-add-form d-none mt-1">
                            <input type="text" class="form-control form-control-sm quick-add-input mb-1" placeholder="Task title..." maxlength="255">
                            <div class="d-flex justify-content-end gap-1">
                                <button type="button" class="btn btn-xs btn-light quick-add-cancel">Cancel</button>
                                <button type="button" class="btn btn-xs btn-primary quick-add-save" data-status="in_progress">Add</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Review Column -->
        <div class="col-12 col-md-6 col-xl-3" style="min-width: 280px;">
            <div class="card shadow-sm border-0 h-100 kanban-column" data-status="review">
                <div class="card-header bg-warning-lighten text-warning py-2 px-3 d-flex justify-content-between align-items-center rounded-top border-bottom">
                    <h6 class="mb-0 text-warning font-14 fw-bold">
                        <i class="mdi mdi-eye-check-outline me-1"></i> IN REVIEW
                    </h6>
                    <span class="badge bg-warning text-dark font-12 rounded-pill column-badge" id="count-review">
                        <?= count($boardData['review'] ?? []) ?>
                    </span>
                </div>
                <div class="card-body p-2 d-flex flex-column" style="min-height: 520px; background-color: var(--bs-tertiary-bg, rgba(0,0,0,0.02));">
                    <div class="kanban-cards sortable-list d-flex flex-column flex-grow-1" id="review-list">
                        <?php if (!empty($boardData['review'])): ?>
                            <?php foreach ($boardData['review'] as $task): ?>
                                <?= view('partials/user/kanban_card', ['task' => $task]) ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <!-- Quick Inline Add Task -->
                    <div class="mt-2 pt-2 border-top border-light">
                        <button type="button" class="btn btn-xs btn-light text-muted w-100 py-1 quick-add-trigger" data-status="review">
                            <i class="mdi mdi-plus me-1"></i> Add a task...
                        </button>
                        <div class="quick-add-form d-none mt-1">
                            <input type="text" class="form-control form-control-sm quick-add-input mb-1" placeholder="Task title..." maxlength="255">
                            <div class="d-flex justify-content-end gap-1">
                                <button type="button" class="btn btn-xs btn-light quick-add-cancel">Cancel</button>
                                <button type="button" class="btn btn-xs btn-primary quick-add-save" data-status="review">Add</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Done Column -->
        <div class="col-12 col-md-6 col-xl-3" style="min-width: 280px;">
            <div class="card shadow-sm border-0 h-100 kanban-column" data-status="done">
                <div class="card-header bg-success-lighten text-success py-2 px-3 d-flex justify-content-between align-items-center rounded-top border-bottom">
                    <h6 class="mb-0 text-success font-14 fw-bold">
                        <i class="mdi mdi-check-all me-1"></i> DONE
                    </h6>
                    <span class="badge bg-success text-white font-12 rounded-pill column-badge" id="count-done">
                        <?= count($boardData['done'] ?? []) ?>
                    </span>
                </div>
                <div class="card-body p-2 d-flex flex-column" style="min-height: 520px; background-color: var(--bs-tertiary-bg, rgba(0,0,0,0.02));">
                    <div class="kanban-cards sortable-list d-flex flex-column flex-grow-1" id="done-list">
                        <?php if (!empty($boardData['done'])): ?>
                            <?php foreach ($boardData['done'] as $task): ?>
                                <?= view('partials/user/kanban_card', ['task' => $task]) ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <!-- Quick Inline Add Task -->
                    <div class="mt-2 pt-2 border-top border-light">
                        <button type="button" class="btn btn-xs btn-light text-muted w-100 py-1 quick-add-trigger" data-status="done">
                            <i class="mdi mdi-plus me-1"></i> Add a task...
                        </button>
                        <div class="quick-add-form d-none mt-1">
                            <input type="text" class="form-control form-control-sm quick-add-input mb-1" placeholder="Task title..." maxlength="255">
                            <div class="d-flex justify-content-end gap-1">
                                <button type="button" class="btn btn-xs btn-light quick-add-cancel">Cancel</button>
                                <button type="button" class="btn btn-xs btn-primary quick-add-save" data-status="done">Add</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Floating Desktop Right-Click Context Menu -->
<div id="kanbanContextMenu" class="dropdown-menu shadow-lg py-1 border-0 rounded-3" style="display: none; position: absolute; z-index: 1060; min-width: 220px;">
    <div class="dropdown-header text-uppercase font-10 py-1 text-muted d-flex justify-content-between align-items-center">
        <span id="cmTaskLabel" class="text-truncate" style="max-width: 150px;">Task</span>
        <span class="badge bg-light text-dark font-10 border" id="cmTaskId">#</span>
    </div>
    
    <a class="dropdown-item py-1 font-13" href="#" id="cmEditBtn">
        <i class="mdi mdi-pencil-outline me-2 text-primary font-16"></i> Edit Details
    </a>
    
    <div class="dropdown-divider my-1"></div>
    <div class="dropdown-header text-uppercase font-10 py-1 text-muted">Move to Stage</div>
    <a class="dropdown-item py-1 font-13 cm-move-option" href="#" data-status="todo" id="cmMoveTodo">
        <i class="mdi mdi-clipboard-outline me-2 text-secondary font-16"></i> To Do
    </a>
    <a class="dropdown-item py-1 font-13 cm-move-option" href="#" data-status="in_progress" id="cmMoveInProgress">
        <i class="mdi mdi-progress-clock me-2 text-info font-16"></i> In Progress
    </a>
    <a class="dropdown-item py-1 font-13 cm-move-option" href="#" data-status="review" id="cmMoveReview">
        <i class="mdi mdi-eye-check-outline me-2 text-warning font-16"></i> In Review
    </a>
    <a class="dropdown-item py-1 font-13 cm-move-option" href="#" data-status="done" id="cmMoveDone">
        <i class="mdi mdi-check-all me-2 text-success font-16"></i> Done
    </a>
    
    <div class="dropdown-divider my-1"></div>
    <div class="dropdown-header text-uppercase font-10 py-1 text-muted">Change Priority</div>
    <div class="d-flex px-3 py-1 gap-1 justify-content-between">
        <button type="button" class="btn btn-xs btn-outline-danger cm-priority-btn py-0 px-2 font-11" data-priority="critical" title="Critical">Crit</button>
        <button type="button" class="btn btn-xs btn-outline-warning cm-priority-btn py-0 px-2 font-11" data-priority="high" title="High">High</button>
        <button type="button" class="btn btn-xs btn-outline-primary cm-priority-btn py-0 px-2 font-11" data-priority="medium" title="Medium">Med</button>
        <button type="button" class="btn btn-xs btn-outline-secondary cm-priority-btn py-0 px-2 font-11" data-priority="low" title="Low">Low</button>
    </div>
    
    <div class="dropdown-divider my-1"></div>
    <a class="dropdown-item py-1 font-13" href="#" id="cmCopyLinkBtn">
        <i class="mdi mdi-link-variant me-2 text-muted font-16"></i> Copy Task ID &amp; Title
    </a>
    <a class="dropdown-item py-1 font-13 text-danger" href="#" id="cmDeleteBtn">
        <i class="mdi mdi-trash-can-outline me-2 text-danger font-16"></i> Delete Task
    </a>
</div>

<!-- Add Task Modal -->
<div class="modal fade" id="addTaskModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <form id="createTaskForm" action="<?= site_url('projects/task/store') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white"><i class="mdi mdi-plus-circle me-1"></i> Create New Task</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Task Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="What needs to be done?" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Additional details or acceptance criteria..."></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Priority</label>
                            <select name="priority" class="form-select">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Initial Status</label>
                            <select name="status" class="form-select">
                                <option value="todo">To Do</option>
                                <option value="in_progress">In Progress</option>
                                <option value="review">In Review</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="createTaskBtn">Create Task</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Task Modal -->
<div class="modal fade" id="editTaskModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="mdi mdi-pencil me-1"></i> Edit Task</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="editTaskId">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Task Title <span class="text-danger">*</span></label>
                    <input type="text" id="editTaskTitle" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea id="editTaskDescription" class="form-control" rows="3"></textarea>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Priority</label>
                        <select id="editTaskPriority" class="form-select">
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="critical">Critical</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Due Date</label>
                        <input type="date" id="editTaskDueDate" class="form-control">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveTaskEditBtn">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('js') ?>
<!-- SortableJS CDN -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
(function() {
    function initKanban() {
        if (typeof $ === 'undefined' || typeof Sortable === 'undefined') {
            setTimeout(initKanban, 50);
            return;
        }

        const columns = ['todo', 'in_progress', 'review', 'done'];
        const $contextMenu = $('#kanbanContextMenu');
        let $activeCard = null;

        // 1. Initialize SortableJS Drag & Drop
        columns.forEach(status => {
            const el = document.getElementById(status + '-list');
            if (el) {
                new Sortable(el, {
                    group: 'kanban',
                    animation: 200,
                    ghostClass: 'bg-light-subtle',
                    chosenClass: 'shadow-lg',
                    dragClass: 'opacity-75',
                    handle: '.kanban-card',
                    onEnd: function(evt) {
                        const taskId = evt.item.dataset.taskId;
                        const colEl = evt.to.closest('.kanban-column');
                        if (!colEl) return;
                        const newStatus = colEl.dataset.status;
                        const order = Array.from(evt.to.children).indexOf(evt.item);

                        // Update card data attribute
                        evt.item.dataset.status = newStatus;

                        $.post('<?= site_url('projects/task/move') ?>', {
                            <?= csrf_token() ?>: '<?= csrf_hash() ?>',
                            task_id: taskId,
                            status: newStatus,
                            order: order
                        }, function(res) {
                            if (res && res.status === 'success') {
                                updateColumnCounts();
                                if (typeof Toast !== 'undefined') {
                                    Toast.fire({
                                        icon: 'success',
                                        title: 'Task moved to ' + newStatus.replace('_', ' ')
                                    });
                                }
                            }
                        }).fail(function(xhr) {
                            console.error('Task move failed:', xhr);
                        });
                    }
                });
            }
        });

        // 2. Column Counts & WIP Limit Alerts
        function updateColumnCounts() {
            columns.forEach(status => {
                const count = $(`#${status}-list .kanban-card`).length;
                const $badge = $(`#count-${status}`);
                $badge.text(count);

                // WIP Limit Check
                const limit = parseInt($badge.data('wip-limit') || 0, 10);
                const $wipWarn = $(`#wip-warn-${status}`);
                if (limit > 0 && count > limit) {
                    $badge.removeClass('bg-info').addClass('bg-danger');
                    $wipWarn.removeClass('d-none');
                } else if (limit > 0) {
                    $badge.removeClass('bg-danger').addClass('bg-info');
                    $wipWarn.addClass('d-none');
                }
            });
            applyFilters();
        }
        updateColumnCounts();

        // 3. Right-Click Context Menu
        $(document).on('contextmenu', '.kanban-card', function(e) {
            e.preventDefault();
            $activeCard = $(this);
            const taskId = $activeCard.data('task-id');
            const taskTitle = $activeCard.data('title') || $activeCard.find('.task-title').text().trim();
            const currentStatus = $activeCard.data('status') || $activeCard.closest('.kanban-column').data('status');
            const currentPriority = $activeCard.data('priority') || 'medium';

            // Populate Context Menu Headers
            $('#cmTaskId').text('#' + taskId);
            $('#cmTaskLabel').text(taskTitle).attr('title', taskTitle);

            // Configure Move options (highlight / hide current stage)
            $('.cm-move-option').each(function() {
                const optStatus = $(this).data('status');
                if (optStatus === currentStatus) {
                    $(this).addClass('active fw-bold text-primary');
                } else {
                    $(this).removeClass('active fw-bold text-primary');
                }
            });

            // Configure Priority buttons
            $('.cm-priority-btn').each(function() {
                const btnPriority = $(this).data('priority');
                if (btnPriority === currentPriority) {
                    $(this).addClass('active');
                } else {
                    $(this).removeClass('active');
                }
            });

            // Position Menu with boundary detection
            let posX = e.pageX;
            let posY = e.pageY;
            const menuWidth = 220;
            const menuHeight = 280;

            if (posX + menuWidth > $(window).width()) {
                posX = $(window).width() - menuWidth - 15;
            }
            if (posY + menuHeight > $(document).height()) {
                posY = posY - menuHeight;
            }

            $contextMenu.css({
                top: posY + 'px',
                left: posX + 'px',
                display: 'block'
            });
        });

        // Hide Context Menu on outside click or Esc
        function hideContextMenu() {
            $contextMenu.hide();
        }
        $(document).on('click', function(e) {
            if (!$(e.target).closest('#kanbanContextMenu').length) {
                hideContextMenu();
            }
        });
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape') hideContextMenu();
        });
        $(window).on('scroll', hideContextMenu);

        // Context Menu Action: Move to Stage
        $('.cm-move-option').on('click', function(e) {
            e.preventDefault();
            hideContextMenu();
            if (!$activeCard) return;

            const newStatus = $(this).data('status');
            const currentStatus = $activeCard.data('status') || $activeCard.closest('.kanban-column').data('status');
            if (newStatus === currentStatus) return;

            const taskId = $activeCard.data('task-id');
            const $targetCol = $(`#${newStatus}-list`);

            // Animate movement into target column
            $activeCard.fadeOut(150, function() {
                $targetCol.prepend($activeCard);
                $activeCard.data('status', newStatus);
                $activeCard.attr('data-status', newStatus);
                $activeCard.fadeIn(200);

                $.post('<?= site_url('projects/task/move') ?>', {
                    <?= csrf_token() ?>: '<?= csrf_hash() ?>',
                    task_id: taskId,
                    status: newStatus,
                    order: 0
                }, function(res) {
                    if (res && res.status === 'success') {
                        updateColumnCounts();
                        if (typeof Toast !== 'undefined') {
                            Toast.fire({
                                icon: 'success',
                                title: 'Moved to ' + newStatus.replace('_', ' ')
                            });
                        }
                    }
                });
            });
        });

        // Context Menu Action: Change Priority
        $('.cm-priority-btn').on('click', async function(e) {
            e.preventDefault();
            hideContextMenu();
            if (!$activeCard) return;

            const newPriority = $(this).data('priority');
            const taskId = $activeCard.data('task-id');

            const res = await dispatchAsyncAction('<?= site_url('projects/task/update/') ?>' + taskId, {
                priority: newPriority
            });

            if (res && (res.success || res.status === 'success')) {
                $activeCard.data('priority', newPriority);
                $activeCard.attr('data-priority', newPriority);

                // Update Left Border
                const priorityBorders = {
                    critical: '#fa5c7c',
                    high: '#ffbc00',
                    medium: '#727cf5',
                    low: '#6c757d'
                };
                $activeCard.css('border-left', '4px solid ' + (priorityBorders[newPriority] || '#727cf5') + ' !important');

                // Update Badge
                const badgeClasses = {
                    critical: 'bg-danger text-white',
                    high: 'bg-warning text-dark',
                    medium: 'bg-primary text-white',
                    low: 'bg-secondary text-white'
                };
                $activeCard.find('.priority-pill')
                    .removeClass('bg-danger bg-warning bg-primary bg-secondary text-white text-dark')
                    .addClass(badgeClasses[newPriority] || 'bg-primary text-white')
                    .text(newPriority.charAt(0).toUpperCase() + newPriority.slice(1));

                if (typeof Toast !== 'undefined') {
                    Toast.fire({
                        icon: 'info',
                        title: 'Priority set to ' + newPriority.toUpperCase()
                    });
                }
            }
        });

        // Context Menu Action: Edit
        $('#cmEditBtn').on('click', function(e) {
            e.preventDefault();
            hideContextMenu();
            if (!$activeCard) return;
            openEditModal($activeCard);
        });

        // Context Menu Action: Copy Link / ID
        $('#cmCopyLinkBtn').on('click', function(e) {
            e.preventDefault();
            hideContextMenu();
            if (!$activeCard) return;
            const taskId = $activeCard.data('task-id');
            const taskTitle = $activeCard.data('title') || $activeCard.find('.task-title').text().trim();
            const textToCopy = `#${taskId}: ${taskTitle} (${window.location.href})`;

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(textToCopy);
                if (typeof Toast !== 'undefined') {
                    Toast.fire({ icon: 'success', title: 'Task details copied to clipboard!' });
                }
            }
        });

        // Context Menu Action: Delete
        $('#cmDeleteBtn').on('click', function(e) {
            e.preventDefault();
            hideContextMenu();
            if (!$activeCard) return;
            triggerDeleteTask($activeCard);
        });

        // Quick Move via 3-dots Dropdown in card
        $(document).on('click', '.quick-move-btn', function(e) {
            e.preventDefault();
            const card = $(this).closest('.kanban-card');
            const newStatus = $(this).data('status');
            const taskId = $(this).data('task-id');
            const $targetCol = $(`#${newStatus}-list`);

            card.fadeOut(150, function() {
                $targetCol.prepend(card);
                card.data('status', newStatus);
                card.attr('data-status', newStatus);
                card.fadeIn(200);

                $.post('<?= site_url('projects/task/move') ?>', {
                    <?= csrf_token() ?>: '<?= csrf_hash() ?>',
                    task_id: taskId,
                    status: newStatus,
                    order: 0
                }, function(res) {
                    if (res && res.status === 'success') {
                        updateColumnCounts();
                        if (typeof Toast !== 'undefined') {
                            Toast.fire({ icon: 'success', title: 'Moved to ' + newStatus.replace('_', ' ') });
                        }
                    }
                });
            });
        });

        // 4. Quick Inline Add Task
        $(document).on('click', '.quick-add-trigger', function() {
            $(this).addClass('d-none');
            const $form = $(this).siblings('.quick-add-form');
            $form.removeClass('d-none');
            $form.find('.quick-add-input').focus();
        });

        $(document).on('click', '.quick-add-cancel', function() {
            const $form = $(this).closest('.quick-add-form');
            $form.addClass('d-none');
            $form.siblings('.quick-add-trigger').removeClass('d-none');
            $form.find('.quick-add-input').val('');
        });

        $(document).on('click', '.quick-add-save', async function() {
            const $btn = $(this);
            const status = $btn.data('status');
            const $form = $btn.closest('.quick-add-form');
            const $input = $form.find('.quick-add-input');
            const title = $input.val().trim();

            if (!title) {
                $input.focus();
                return;
            }

            $btn.prop('disabled', true);
            const res = await dispatchAsyncAction('<?= site_url('projects/task/store') ?>', {
                project_id: '<?= $project['id'] ?>',
                title: title,
                status: status,
                priority: 'medium',
                description: ''
            });
            $btn.prop('disabled', false);

            if (res && (res.success || res.status === 'success')) {
                const taskId = (res.task && res.task.id) ? res.task.id : Date.now();
                const newCardHtml = createCardHtml(taskId, title, '', 'medium', status, '', '<?= auth()->user()->username ?? 'You' ?>');
                $(`#${status}-list`).prepend(newCardHtml);
                $input.val('');
                $form.addClass('d-none');
                $form.siblings('.quick-add-trigger').removeClass('d-none');
                updateColumnCounts();

                if (typeof Toast !== 'undefined') {
                    Toast.fire({ icon: 'success', title: 'Task created!' });
                }
            }
        });

        $(document).on('keydown', '.quick-add-input', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                $(this).closest('.quick-add-form').find('.quick-add-save').click();
            } else if (e.key === 'Escape') {
                $(this).closest('.quick-add-form').find('.quick-add-cancel').click();
            }
        });

        // 5. Create Task Modal Form Intercept
        $('#createTaskForm').on('submit', async function(e) {
            e.preventDefault();
            const form = this;
            const submitBtn = $(form).find('button[type="submit"], #createTaskBtn');
            submitBtn.prop('disabled', true);

            const formData = new FormData(form);
            const status = formData.get('status') || 'todo';
            const title = formData.get('title') || '';
            const priority = formData.get('priority') || 'medium';
            const description = formData.get('description') || '';

            const res = await dispatchAsyncAction(form.action, formData);
            submitBtn.prop('disabled', false);

            if (res && (res.success || res.status === 'success')) {
                const modalEl = document.getElementById('addTaskModal');
                if (modalEl) {
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                }
                form.reset();

                const taskId = (res.task && res.task.id) ? res.task.id : Date.now();
                const newCardHtml = createCardHtml(taskId, title, description, priority, status, '', '<?= auth()->user()->username ?? 'You' ?>');
                $(`#${status}-list`).prepend(newCardHtml);
                updateColumnCounts();

                if (typeof Toast !== 'undefined') {
                    Toast.fire({ icon: 'success', title: 'Task created successfully!' });
                }
            }
        });

        function createCardHtml(id, title, desc, priority, status, dueDate, assigneeName) {
            const p = (priority || 'medium').toLowerCase();
            const priorityBorders = { critical: '#fa5c7c', high: '#ffbc00', medium: '#727cf5', low: '#6c757d' };
            const badgeClasses = { critical: 'bg-danger text-white', high: 'bg-warning text-dark', medium: 'bg-primary text-white', low: 'bg-secondary text-white' };
            const initials = assigneeName ? assigneeName.substring(0, 2).toUpperCase() : 'ME';

            return `
            <div class="kanban-card card shadow-sm mb-2" id="task-card-${id}"
                 data-task-id="${id}" data-title="${$('<div>').text(title).html()}"
                 data-description="${$('<div>').text(desc).html()}" data-priority="${p}"
                 data-due-date="${dueDate}" data-status="${status}" data-assigned-to="<?= $currentUserId ?>"
                 draggable="true" style="border-left: 4px solid ${priorityBorders[p] || '#727cf5'} !important;">
                <div class="kanban-card-header pb-1">
                    <div class="d-flex justify-content-between align-items-start gap-1">
                        <span class="badge bg-light text-muted font-11 px-1 py-0 border">#${id}</span>
                        <div class="task-title font-14 fw-semibold text-body flex-grow-1 text-truncate" title="${$('<div>').text(title).html()}">
                            ${$('<div>').text(title).html()}
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-xs btn-link text-muted p-0" type="button" data-bs-toggle="dropdown">
                                <i class="mdi mdi-dots-vertical font-16"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                <li><a class="dropdown-item small edit-task-btn" href="#" data-task-id="${id}"><i class="mdi mdi-pencil me-2 text-primary"></i>Edit Task</a></li>
                                <li class="dropdown-header text-uppercase font-10 py-1">Quick Move</li>
                                <li><a class="dropdown-item small quick-move-btn" href="#" data-status="todo" data-task-id="${id}"><i class="mdi mdi-clipboard-outline me-2 text-secondary"></i>To Do</a></li>
                                <li><a class="dropdown-item small quick-move-btn" href="#" data-status="in_progress" data-task-id="${id}"><i class="mdi mdi-progress-clock me-2 text-info"></i>In Progress</a></li>
                                <li><a class="dropdown-item small quick-move-btn" href="#" data-status="review" data-task-id="${id}"><i class="mdi mdi-eye-check-outline me-2 text-warning"></i>In Review</a></li>
                                <li><a class="dropdown-item small quick-move-btn" href="#" data-status="done" data-task-id="${id}"><i class="mdi mdi-check-all me-2 text-success"></i>Done</a></li>
                                <li><hr class="dropdown-divider my-1"></li>
                                <li><a class="dropdown-item small text-danger delete-task-btn" href="#" data-task-id="${id}"><i class="mdi mdi-trash-can-outline me-2"></i>Delete</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="kanban-card-body py-1">
                    ${desc ? `<p class="font-12 text-muted text-truncate-2 mb-2">${$('<div>').text(desc).html()}</p>` : ''}
                    <div class="d-flex justify-content-between align-items-center mt-2 flex-wrap gap-1">
                        <div class="task-meta d-flex align-items-center gap-1">
                            <span class="badge ${badgeClasses[p] || 'bg-primary text-white'} font-11 rounded-pill priority-pill">${p.charAt(0).toUpperCase() + p.slice(1)}</span>
                        </div>
                        <div class="task-date font-11 text-muted">
                            <i class="mdi mdi-calendar-clock me-1"></i> Today
                        </div>
                    </div>
                </div>
                <div class="kanban-card-footer pt-2 mt-1 border-top border-light d-flex justify-content-between align-items-center">
                    <div class="task-assignee d-flex align-items-center">
                        <div class="task-avatar">${initials}</div>
                        <span class="font-11 text-muted ms-1 text-truncate" style="max-width: 110px;">${assigneeName || 'You'}</span>
                    </div>
                    <div class="task-hints font-11 text-muted" title="Right-click for quick actions">
                        <i class="mdi mdi-cursor-default-click-outline opacity-50"></i>
                    </div>
                </div>
            </div>`;
        }

        // 6. Edit Task Modal Handling
        function openEditModal(card) {
            $('#editTaskId').val(card.data('task-id'));
            $('#editTaskTitle').val(card.data('title') || card.find('.task-title').text().trim());
            $('#editTaskDescription').val(card.data('description') || '');
            $('#editTaskPriority').val(card.data('priority') || 'medium');
            $('#editTaskDueDate').val(card.data('due-date') || '');
            const modalEl = document.getElementById('editTaskModal');
            if (modalEl) {
                const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                modal.show();
            }
        }

        $(document).on('click', '.edit-task-btn', function(e) {
            e.preventDefault();
            const card = $(this).closest('.kanban-card');
            openEditModal(card);
        });

        $('#saveTaskEditBtn').on('click', async function() {
            const id = $('#editTaskId').val();
            const btn = $(this);
            btn.prop('disabled', true).html('<i class="mdi mdi-spin mdi-loading me-1"></i> Saving...');

            const title = $('#editTaskTitle').val().trim();
            const description = $('#editTaskDescription').val().trim();
            const priority = $('#editTaskPriority').val();
            const dueDate = $('#editTaskDueDate').val();

            const res = await dispatchAsyncAction('<?= site_url('projects/task/update/') ?>' + id, {
                title: title,
                description: description,
                priority: priority,
                due_date: dueDate
            });

            btn.prop('disabled', false).text('Save Changes');

            if (res && (res.success || res.status === 'success')) {
                const card = $(`.kanban-card[data-task-id="${id}"]`);
                if (card.length) {
                    card.find('.task-title').text(title).attr('title', title);
                    let $descP = card.find('.kanban-card-body p');
                    if (description) {
                        if ($descP.length) {
                            $descP.text(description);
                        } else {
                            card.find('.kanban-card-body').prepend(`<p class="font-12 text-muted text-truncate-2 mb-2">${$('<div>').text(description).html()}</p>`);
                        }
                    } else {
                        $descP.remove();
                    }

                    card.data('title', title);
                    card.data('description', description);
                    card.data('priority', priority);
                    card.data('due-date', dueDate);

                    // Update Left Border & Badge
                    const priorityBorders = { critical: '#fa5c7c', high: '#ffbc00', medium: '#727cf5', low: '#6c757d' };
                    const badgeClasses = { critical: 'bg-danger text-white', high: 'bg-warning text-dark', medium: 'bg-primary text-white', low: 'bg-secondary text-white' };
                    card.css('border-left', '4px solid ' + (priorityBorders[priority] || '#727cf5') + ' !important');
                    card.find('.priority-pill')
                        .removeClass('bg-danger bg-warning bg-primary bg-secondary text-white text-dark')
                        .addClass(badgeClasses[priority] || 'bg-primary text-white')
                        .text(priority.charAt(0).toUpperCase() + priority.slice(1));
                }

                const modalEl = document.getElementById('editTaskModal');
                if (modalEl) {
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                }

                if (typeof Toast !== 'undefined') {
                    Toast.fire({ icon: 'success', title: 'Task updated successfully!' });
                }
            }
        });

        // 7. Delete Task Handling
        function triggerDeleteTask(card) {
            const taskId = card.data('task-id');
            const taskTitle = card.data('title') || card.find('.task-title').text().trim();

            confirmAction({
                title: 'Delete Task?',
                text: `Are you sure you want to permanently delete "${taskTitle}"?`,
                confirmButtonText: 'Yes, Delete',
                confirmButtonColor: '#fa5c7c',
                onConfirm: async () => {
                    const res = await dispatchAsyncAction('<?= site_url('projects/task/delete/') ?>' + taskId);
                    if (res && (res.success || res.status === 'success')) {
                        card.fadeOut(250, function() {
                            $(this).remove();
                            updateColumnCounts();
                        });
                        return true;
                    }
                    return false;
                }
            });
        }

        $(document).on('click', '.delete-task-btn', function(e) {
            e.preventDefault();
            const card = $(this).closest('.kanban-card');
            triggerDeleteTask(card);
        });

        // 8. Live Search & Filter Bar Logic
        let activeFilter = 'all';

        $('#filterPills button').on('click', function() {
            $('#filterPills button').removeClass('active');
            $(this).addClass('active');
            activeFilter = $(this).data('filter');
            applyFilters();
        });

        $('#kanbanSearchInput').on('input', function() {
            applyFilters();
        });

        function applyFilters() {
            const query = $('#kanbanSearchInput').val().toLowerCase().trim();
            const userId = '<?= $currentUserId ?>';

            $('.kanban-card').each(function() {
                const card = $(this);
                const title = (card.data('title') || card.find('.task-title').text() || '').toLowerCase();
                const desc = (card.data('description') || '').toLowerCase();
                const priority = (card.data('priority') || '').toLowerCase();
                const assignedTo = String(card.data('assigned-to') || '');
                const isOverdue = card.find('.text-danger').length > 0;

                // Match Search Query
                const matchesSearch = !query || title.includes(query) || desc.includes(query);

                // Match Filter Pill
                let matchesFilter = true;
                if (activeFilter === 'my_tasks') {
                    matchesFilter = (assignedTo === userId);
                } else if (activeFilter === 'high_priority') {
                    matchesFilter = (priority === 'high' || priority === 'critical');
                } else if (activeFilter === 'overdue') {
                    matchesFilter = isOverdue;
                }

                if (matchesSearch && matchesFilter) {
                    card.show();
                } else {
                    card.hide();
                }
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initKanban);
    } else {
        initKanban();
    }
})();
</script>
<?= $this->endSection() ?>
