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
            <i class="mdi mdi-mouse me-1 text-primary"></i> <strong>Right-click</strong> any card for quick stage moves, priority, or re-assignment.
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
<div id="kanbanContextMenu" class="dropdown-menu shadow-lg py-1 border-0 rounded-3" style="display: none; position: absolute; z-index: 1060; min-width: 230px;">
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
    <div class="dropdown-header text-uppercase font-10 py-1 text-muted">Assign To</div>
    <div class="px-2 py-1">
        <select class="form-select form-select-sm font-12" id="cmAssignSelect">
            <option value="">Unassigned</option>
            <?php if (!empty($users)): ?>
                <?php foreach ($users as $u): ?>
                    <option value="<?= $u->id ?>">
                        <?= esc(trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? '')) ?: $u->username) ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
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
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Priority</label>
                            <select name="priority" class="form-select">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Story Points</label>
                            <select name="story_points" class="form-select">
                                <option value="">None</option>
                                <option value="1">1 pt</option>
                                <option value="2">2 pts</option>
                                <option value="3">3 pts</option>
                                <option value="5">5 pts</option>
                                <option value="8">8 pts</option>
                                <option value="13">13 pts</option>
                                <option value="21">21 pts</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Initial Status</label>
                            <select name="status" class="form-select">
                                <option value="todo">To Do</option>
                                <option value="in_progress">In Progress</option>
                                <option value="review">In Review</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Assign To</label>
                            <select name="assigned_to" class="form-select" id="createTaskAssignedTo">
                                <option value="">Unassigned</option>
                                <?php if (!empty($users)): ?>
                                    <?php foreach ($users as $u): ?>
                                        <option value="<?= $u->id ?>" <?= $u->id == auth()->id() ? 'selected' : '' ?>>
                                            <?= esc(trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? '')) ?: $u->username) ?> (<?= esc($u->username) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Due Date</label>
                            <input type="date" name="due_date" class="form-control">
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

<!-- Task Details & Edit Modal (Tabs: Details, Comments, Attachments, Activity) -->
<div class="modal fade" id="editTaskModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-primary text-white py-3">
                <div class="d-flex align-items-center">
                    <span class="badge bg-white text-primary me-2 font-13" id="taskDetailBadge">#Task</span>
                    <h5 class="modal-title text-white mb-0" id="taskDetailTitleHeader">Task Details</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <!-- Nav Tabs -->
                <ul class="nav nav-tabs nav-bordered px-3 pt-2 bg-light border-bottom" id="taskDetailTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active py-2" id="tab-details-btn" data-bs-toggle="tab" data-bs-target="#tab-details" type="button" role="tab">
                            <i class="mdi mdi-text-box-outline me-1"></i> Details
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link py-2" id="tab-comments-btn" data-bs-toggle="tab" data-bs-target="#tab-comments" type="button" role="tab">
                            <i class="mdi mdi-comment-text-multiple-outline me-1"></i> Comments <span class="badge bg-secondary rounded-pill ms-1" id="commentsTabCount">0</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link py-2" id="tab-attachments-btn" data-bs-toggle="tab" data-bs-target="#tab-attachments" type="button" role="tab">
                            <i class="mdi mdi-paperclip me-1"></i> Attachments <span class="badge bg-secondary rounded-pill ms-1" id="attachmentsTabCount">0</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link py-2" id="tab-activity-btn" data-bs-toggle="tab" data-bs-target="#tab-activity" type="button" role="tab">
                            <i class="mdi mdi-history me-1"></i> Activity History
                        </button>
                    </li>
                </ul>

                <!-- Tab Content Panes -->
                <div class="tab-content p-4" id="taskDetailTabContent">
                    <!-- Tab 1: Details -->
                    <div class="tab-pane fade show active" id="tab-details" role="tabpanel">
                        <input type="hidden" id="editTaskId">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Task Title <span class="text-danger">*</span></label>
                            <input type="text" id="editTaskTitle" class="form-control font-15 fw-bold" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea id="editTaskDescription" class="form-control" rows="3" placeholder="Add detailed notes or requirements..."></textarea>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Priority</label>
                                <select id="editTaskPriority" class="form-select">
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="critical">Critical</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Story Points</label>
                                <select id="editTaskStoryPoints" class="form-select">
                                    <option value="">None</option>
                                    <option value="1">1 pt</option>
                                    <option value="2">2 pts</option>
                                    <option value="3">3 pts</option>
                                    <option value="5">5 pts</option>
                                    <option value="8">8 pts</option>
                                    <option value="13">13 pts</option>
                                    <option value="21">21 pts</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Due Date</label>
                                <input type="date" id="editTaskDueDate" class="form-control">
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Assign To</label>
                                <select id="editTaskAssignedTo" class="form-select">
                                    <option value="">Unassigned</option>
                                    <?php if (!empty($users)): ?>
                                        <?php foreach ($users as $u): ?>
                                            <option value="<?= $u->id ?>">
                                                <?= esc(trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? '')) ?: $u->username) ?> (<?= esc($u->username) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 2: Comments -->
                    <div class="tab-pane fade" id="tab-comments" role="tabpanel">
                        <div id="commentsList" class="mb-4" style="max-height: 320px; overflow-y: auto;">
                            <!-- Dynamically loaded comments -->
                        </div>
                        <div class="border-top pt-3">
                            <div class="mb-2">
                                <label class="form-label fw-semibold font-13"><i class="mdi mdi-comment-plus-outline me-1"></i> Add Comment (Markdown & @mentions supported)</label>
                                <textarea id="newCommentBody" class="form-control" rows="2" placeholder="Write a comment or note... Use @username to notify teammates"></textarea>
                            </div>
                            <div class="text-end">
                                <button type="button" class="btn btn-sm btn-primary" id="postCommentBtn">
                                    <i class="mdi mdi-send me-1"></i> Post Comment
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 3: Attachments -->
                    <div class="tab-pane fade" id="tab-attachments" role="tabpanel">
                        <div class="mb-3 p-3 bg-light rounded border border-dashed text-center">
                            <i class="mdi mdi-cloud-upload font-28 text-primary d-block mb-1"></i>
                            <div class="fw-semibold font-14">Upload Task Attachments</div>
                            <div class="text-muted font-12 mb-2">Allowed: Images (JPEG, PNG, GIF, WEBP, SVG) & PDFs • Max 20MB</div>
                            <input type="file" id="taskFileInput" class="form-control form-control-sm mx-auto" style="max-width: 320px;" accept="image/*,application/pdf">
                            <button type="button" class="btn btn-sm btn-primary mt-2" id="uploadFileBtn">
                                <i class="mdi mdi-upload me-1"></i> Upload File
                            </button>
                        </div>
                        <div id="attachmentsList" class="mt-3">
                            <!-- Dynamically loaded attachments -->
                        </div>
                    </div>

                    <!-- Tab 4: Activity History -->
                    <div class="tab-pane fade" id="tab-activity" role="tabpanel">
                        <div id="activityTimeline" style="max-height: 360px; overflow-y: auto;">
                            <!-- Dynamically loaded activities -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
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
        let activeFilter = 'all';

        // Filter and Search Engine Defined at Top Scope
        function applyFilters() {
            const query = ($('#kanbanSearchInput').val() || '').toLowerCase().trim();
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

        // 1. Column Counts & WIP Limit Alerts
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

        // 2. Initialize SortableJS Drag & Drop
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

        updateColumnCounts();

        // 3. Right-Click Context Menu Engine
        $(document).on('contextmenu', '.kanban-card', function(e) {
            e.preventDefault();
            $activeCard = $(this);
            const taskId = $activeCard.data('task-id');
            const taskTitle = $activeCard.data('title') || $activeCard.find('.task-title').text().trim();
            const currentStatus = $activeCard.data('status') || $activeCard.closest('.kanban-column').data('status');
            const currentPriority = $activeCard.data('priority') || 'medium';
            const currentAssignedTo = $activeCard.data('assigned-to') || '';

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

            // Configure Assignee select
            $('#cmAssignSelect').val(currentAssignedTo);

            // Position Menu with window boundary detection
            let posX = e.pageX;
            let posY = e.pageY;
            const menuWidth = 230;
            const menuHeight = 350;

            if (posX + menuWidth > $(window).width()) {
                posX = $(window).width() - menuWidth - 15;
            }
            if (posY + menuHeight > $(document).height()) {
                posY = Math.max(10, posY - menuHeight);
            }

            $contextMenu.css({
                top: posY + 'px',
                left: posX + 'px',
                display: 'block'
            });
        });

        // Hide Context Menu
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

                const priorityBorders = { critical: '#fa5c7c', high: '#ffbc00', medium: '#727cf5', low: '#6c757d' };
                const badgeClasses = { critical: 'bg-danger text-white', high: 'bg-warning text-dark', medium: 'bg-primary text-white', low: 'bg-secondary text-white' };

                $activeCard.css('border-left', '4px solid ' + (priorityBorders[newPriority] || '#727cf5') + ' !important');
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

        // Context Menu Action: Quick Reassign
        $('#cmAssignSelect').on('change', async function() {
            if (!$activeCard) return;
            const newAssigneeId = $(this).val();
            const newAssigneeName = $(this).find('option:selected').text().trim();
            const taskId = $activeCard.data('task-id');
            hideContextMenu();

            const res = await dispatchAsyncAction('<?= site_url('projects/task/update/') ?>' + taskId, {
                assigned_to: newAssigneeId
            });

            if (res && (res.success || res.status === 'success')) {
                $activeCard.data('assigned-to', newAssigneeId);
                $activeCard.attr('data-assigned-to', newAssigneeId);

                const initials = newAssigneeId ? newAssigneeName.substring(0, 2).toUpperCase() : 'UN';
                $activeCard.find('.task-avatar').text(initials);
                $activeCard.find('.task-assignee span').text(newAssigneeId ? newAssigneeName : 'Unassigned');

                if (typeof Toast !== 'undefined') {
                    Toast.fire({
                        icon: 'success',
                        title: 'Assigned to ' + (newAssigneeId ? newAssigneeName : 'Unassigned')
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
                const newCardHtml = createCardHtml(taskId, title, '', 'medium', status, '', '<?= auth()->user()->username ?? 'You' ?>', '<?= $currentUserId ?>');
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
            const assignedTo = formData.get('assigned_to') || '';
            const dueDate = formData.get('due_date') || '';
            const assigneeName = $('#createTaskAssignedTo option:selected').text().trim();

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
                const newCardHtml = createCardHtml(taskId, title, description, priority, status, dueDate, assignedTo ? assigneeName : 'Unassigned', assignedTo);
                $(`#${status}-list`).prepend(newCardHtml);
                updateColumnCounts();

                if (typeof Toast !== 'undefined') {
                    Toast.fire({ icon: 'success', title: 'Task created successfully!' });
                }
            }
        });

        function createCardHtml(id, title, desc, priority, status, dueDate, assigneeName, assignedTo) {
            const p = (priority || 'medium').toLowerCase();
            const priorityBorders = { critical: '#fa5c7c', high: '#ffbc00', medium: '#727cf5', low: '#6c757d' };
            const badgeClasses = { critical: 'bg-danger text-white', high: 'bg-warning text-dark', medium: 'bg-primary text-white', low: 'bg-secondary text-white' };
            const initials = assigneeName && assigneeName !== 'Unassigned' ? assigneeName.substring(0, 2).toUpperCase() : 'UN';

            return `
            <div class="kanban-card card shadow-sm mb-2" id="task-card-${id}"
                 data-task-id="${id}" data-title="${$('<div>').text(title).html()}"
                 data-description="${$('<div>').text(desc).html()}" data-priority="${p}"
                 data-due-date="${dueDate || ''}" data-status="${status}" data-assigned-to="${assignedTo || ''}"
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
                            <i class="mdi mdi-calendar-clock me-1"></i> ${dueDate || 'Today'}
                        </div>
                    </div>
                </div>
                <div class="kanban-card-footer pt-2 mt-1 border-top border-light d-flex justify-content-between align-items-center">
                    <div class="task-assignee d-flex align-items-center" title="Assignee: ${assigneeName || 'Unassigned'}">
                        <div class="task-avatar">${initials}</div>
                        <span class="font-11 text-muted ms-1 text-truncate" style="max-width: 110px;">${assigneeName || 'Unassigned'}</span>
                    </div>
                    <div class="task-hints font-11 text-muted" title="Right-click for quick actions">
                        <i class="mdi mdi-cursor-default-click-outline opacity-50"></i>
                    </div>
                </div>
            </div>`;
        }

        // 6. Edit & Detail Modal Handling (Comments, Attachments, Activity, Story Points)
        function openEditModal(card) {
            const taskId = card.data('task-id');
            const title = card.data('title') || card.find('.task-title').text().trim();
            const desc = card.data('description') || '';
            const priority = card.data('priority') || 'medium';
            const storyPoints = card.data('story-points') || '';
            const dueDate = card.data('due-date') || '';
            const assignedTo = card.data('assigned-to') || '';

            $('#editTaskId').val(taskId);
            $('#taskDetailBadge').text('#' + taskId);
            $('#taskDetailTitleHeader').text(title);
            $('#editTaskTitle').val(title);
            $('#editTaskDescription').val(desc);
            $('#editTaskPriority').val(priority);
            $('#editTaskStoryPoints').val(storyPoints);
            $('#editTaskDueDate').val(dueDate);
            $('#editTaskAssignedTo').val(assignedTo);

            // Reset to Details tab
            const detailsTab = document.getElementById('tab-details-btn');
            if (detailsTab) {
                const tab = new bootstrap.Tab(detailsTab);
                tab.show();
            }

            // Load sub-resources
            loadComments(taskId);
            loadAttachments(taskId);
            loadActivities(taskId);

            const modalEl = document.getElementById('editTaskModal');
            if (modalEl) {
                const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                modal.show();
            }
        }

        async function loadComments(taskId) {
            $('#commentsList').html('<div class="text-center text-muted py-3"><span class="spinner-border spinner-border-sm me-1"></span> Loading comments...</div>');
            try {
                const res = await $.get('<?= site_url('api/tasks/') ?>' + taskId + '/comments');
                if (res && res.status === 'success') {
                    const comments = res.comments || [];
                    $('#commentsTabCount').text(comments.length);
                    if (!comments.length) {
                        $('#commentsList').html('<div class="text-muted text-center py-3 font-13"><i class="mdi mdi-comment-outline font-20 d-block mb-1"></i> No comments yet. Start the conversation!</div>');
                        return;
                    }
                    let html = '';
                    comments.forEach(c => {
                        const author = c.first_name ? `${c.first_name} ${c.last_name || ''}` : (c.username || 'User');
                        const initials = (author || 'U').substring(0, 2).toUpperCase();
                        const isOwner = (String(c.user_id) === '<?= $currentUserId ?>');
                        html += `
                        <div class="d-flex mb-3 p-2 rounded bg-light border-light" id="comment-item-${c.id}">
                            <div class="task-avatar me-2 mt-1">${initials}</div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-semibold font-13">${$('<div>').text(author).html()}</span>
                                    <small class="text-muted font-11">${c.created_at || ''}</small>
                                </div>
                                <div class="font-13 text-body mt-1">${$('<div>').text(c.body).html()}</div>
                            </div>
                            ${isOwner ? `<button type="button" class="btn btn-xs btn-link text-danger ms-2 delete-comment-btn" data-id="${c.id}"><i class="mdi mdi-trash-can-outline"></i></button>` : ''}
                        </div>`;
                    });
                    $('#commentsList').html(html);
                }
            } catch (e) {
                $('#commentsList').html('<div class="text-danger small py-2">Failed to load comments.</div>');
            }
        }

        async function loadAttachments(taskId) {
            $('#attachmentsList').html('<div class="text-center text-muted py-2"><span class="spinner-border spinner-border-sm me-1"></span> Loading attachments...</div>');
            try {
                const res = await $.get('<?= site_url('api/tasks/') ?>' + taskId + '/attachments');
                if (res && res.status === 'success') {
                    const attachments = res.attachments || [];
                    $('#attachmentsTabCount').text(attachments.length);
                    if (!attachments.length) {
                        $('#attachmentsList').html('<div class="text-muted text-center py-2 font-13">No files attached to this task.</div>');
                        return;
                    }
                    let html = '<div class="row g-2">';
                    attachments.forEach(a => {
                        const isImg = a.mime_type && a.mime_type.startsWith('image/');
                        const downloadUrl = '<?= site_url('api/attachments/') ?>' + a.id + '/download';
                        const isOwner = (String(a.user_id) === '<?= $currentUserId ?>');
                        const sizeKb = Math.round((a.file_size || 0) / 1024);
                        html += `
                        <div class="col-md-6" id="attachment-item-${a.id}">
                            <div class="card border shadow-none mb-0 p-2 d-flex flex-row align-items-center justify-content-between">
                                <div class="d-flex align-items-center text-truncate me-2">
                                    <i class="mdi ${isImg ? 'mdi-file-image text-primary' : 'mdi-file-pdf-box text-danger'} font-24 me-2"></i>
                                    <div class="text-truncate">
                                        <a href="${downloadUrl}" target="_blank" class="fw-semibold font-12 text-truncate d-block" title="${$('<div>').text(a.original_name).html()}">${$('<div>').text(a.original_name).html()}</a>
                                        <span class="text-muted font-11">${sizeKb} KB</span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-1">
                                    <a href="${downloadUrl}" target="_blank" class="btn btn-xs btn-outline-primary" download><i class="mdi mdi-download"></i></a>
                                    ${isOwner ? `<button type="button" class="btn btn-xs btn-outline-danger delete-attachment-btn" data-id="${a.id}"><i class="mdi mdi-trash-can-outline"></i></button>` : ''}
                                </div>
                            </div>
                        </div>`;
                    });
                    html += '</div>';
                    $('#attachmentsList').html(html);
                }
            } catch (e) {
                $('#attachmentsList').html('<div class="text-danger small py-2">Failed to load attachments.</div>');
            }
        }

        async function loadActivities(taskId) {
            $('#activityTimeline').html('<div class="text-center text-muted py-3"><span class="spinner-border spinner-border-sm me-1"></span> Loading activity trail...</div>');
            try {
                const res = await $.get('<?= site_url('api/tasks/') ?>' + taskId + '/activities');
                if (res && res.status === 'success') {
                    const activities = res.activities || [];
                    if (!activities.length) {
                        $('#activityTimeline').html('<div class="text-muted text-center py-3 font-13">No recorded activity history for this task.</div>');
                        return;
                    }
                    let html = '<ul class="list-unstyled mb-0">';
                    activities.forEach(act => {
                        const author = act.first_name ? `${act.first_name} ${act.last_name || ''}` : (act.username || 'System');
                        html += `
                        <li class="d-flex align-items-start mb-3 border-bottom pb-2">
                            <i class="mdi mdi-history text-primary font-18 me-2 mt-1"></i>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-semibold font-13">${$('<div>').text(author).html()}</span>
                                    <small class="text-muted font-11">${act.created_at || ''}</small>
                                </div>
                                <div class="font-12 text-muted mt-1">${$('<div>').text(act.details || act.action).html()}</div>
                            </div>
                        </li>`;
                    });
                    html += '</ul>';
                    $('#activityTimeline').html(html);
                }
            } catch (e) {
                $('#activityTimeline').html('<div class="text-danger small py-2">Failed to load activity history.</div>');
            }
        }

        // Post Comment
        $('#postCommentBtn').on('click', async function() {
            const taskId = $('#editTaskId').val();
            const body = $('#newCommentBody').val().trim();
            if (!body) return;

            const btn = $(this);
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Posting...');

            try {
                const res = await $.post('<?= site_url('api/tasks/') ?>' + taskId + '/comments', {
                    <?= csrf_token() ?>: '<?= csrf_hash() ?>',
                    body: body
                });
                btn.prop('disabled', false).html('<i class="mdi mdi-send me-1"></i> Post Comment');
                if (res && res.status === 'success') {
                    $('#newCommentBody').val('');
                    loadComments(taskId);
                    loadActivities(taskId);
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: res.message || 'Failed to post comment.' });
                }
            } catch (e) {
                btn.prop('disabled', false).html('<i class="mdi mdi-send me-1"></i> Post Comment');
                Swal.fire({ icon: 'error', title: 'Error', text: 'Network request error.' });
            }
        });

        // Delete Comment
        $(document).on('click', '.delete-comment-btn', async function() {
            const id = $(this).data('id');
            const taskId = $('#editTaskId').val();
            if (!confirm('Delete this comment?')) return;

            try {
                const res = await $.post('<?= site_url('api/comments/') ?>' + id + '/delete', {
                    <?= csrf_token() ?>: '<?= csrf_hash() ?>'
                });
                if (res && res.status === 'success') {
                    $(`#comment-item-${id}`).fadeOut(200, function() { $(this).remove(); });
                    loadActivities(taskId);
                }
            } catch (e) {}
        });

        // Upload Attachment (20MB Limit + Client Image/PDF check)
        $('#uploadFileBtn').on('click', async function() {
            const taskId = $('#editTaskId').val();
            const fileInput = document.getElementById('taskFileInput');
            if (!fileInput || !fileInput.files.length) {
                Swal.fire({ icon: 'warning', title: 'Notice', text: 'Please select a file to upload.' });
                return;
            }

            const file = fileInput.files[0];
            const maxBytes = 20 * 1024 * 1024; // 20MB
            if (file.size > maxBytes) {
                Swal.fire({ icon: 'error', title: 'File Too Large', text: 'File exceeds 20MB maximum size limit.' });
                return;
            }

            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml', 'application/pdf'];
            if (!allowedTypes.includes(file.type) && !file.name.match(/\.(jpe?g|png|gif|webp|svg|pdf)$/i)) {
                Swal.fire({ icon: 'error', title: 'Invalid Format', text: 'Only Images (JPEG, PNG, GIF, WEBP, SVG) and PDF files are allowed.' });
                return;
            }

            const btn = $(this);
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Uploading...');

            const fd = new FormData();
            fd.append('file', file);
            fd.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

            try {
                const res = await fetch('<?= site_url('api/tasks/') ?>' + taskId + '/attachments', {
                    method: 'POST',
                    body: fd,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                }).then(r => r.json());

                btn.prop('disabled', false).html('<i class="mdi mdi-upload me-1"></i> Upload File');
                if (res && res.status === 'success') {
                    fileInput.value = '';
                    loadAttachments(taskId);
                    loadActivities(taskId);
                    Swal.fire({ icon: 'success', title: 'Uploaded!', text: res.message, timer: 1500, showConfirmButton: false });
                } else {
                    Swal.fire({ icon: 'error', title: 'Upload Failed', text: res.message || 'Failed to upload attachment.' });
                }
            } catch (e) {
                btn.prop('disabled', false).html('<i class="mdi mdi-upload me-1"></i> Upload File');
                Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to upload file.' });
            }
        });

        // Delete Attachment
        $(document).on('click', '.delete-attachment-btn', async function() {
            const id = $(this).data('id');
            const taskId = $('#editTaskId').val();
            if (!confirm('Remove this attachment?')) return;

            try {
                const res = await $.post('<?= site_url('api/attachments/') ?>' + id + '/delete', {
                    <?= csrf_token() ?>: '<?= csrf_hash() ?>'
                });
                if (res && res.status === 'success') {
                    $(`#attachment-item-${id}`).fadeOut(200, function() { $(this).remove(); });
                    loadActivities(taskId);
                }
            } catch (e) {}
        });

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
            const storyPoints = $('#editTaskStoryPoints').val();
            const dueDate = $('#editTaskDueDate').val();
            const assignedTo = $('#editTaskAssignedTo').val();
            const assigneeName = $('#editTaskAssignedTo option:selected').text().trim();

            const res = await dispatchAsyncAction('<?= site_url('projects/task/update/') ?>' + id, {
                title: title,
                description: description,
                priority: priority,
                story_points: storyPoints,
                due_date: dueDate,
                assigned_to: assignedTo
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
                    card.data('story-points', storyPoints);
                    card.data('due-date', dueDate);
                    card.data('assigned-to', assignedTo);
                    card.attr('data-assigned-to', assignedTo);

                    // Update Left Border & Priority Badge
                    const priorityBorders = { critical: '#fa5c7c', high: '#ffbc00', medium: '#727cf5', low: '#6c757d' };
                    const badgeClasses = { critical: 'bg-danger text-white', high: 'bg-warning text-dark', medium: 'bg-primary text-white', low: 'bg-secondary text-white' };
                    card.css('border-left', '4px solid ' + (priorityBorders[priority] || '#727cf5') + ' !important');
                    card.find('.priority-pill')
                        .removeClass('bg-danger bg-warning bg-primary bg-secondary text-white text-dark')
                        .addClass(badgeClasses[priority] || 'bg-primary text-white')
                        .text(priority.charAt(0).toUpperCase() + priority.slice(1));

                    // Update Story Points badge in card
                    let $ptsBadge = card.find('.task-meta .badge.bg-info-lighten');
                    if (storyPoints && parseInt(storyPoints, 10) > 0) {
                        if ($ptsBadge.length) {
                            $ptsBadge.html(`<i class="mdi mdi-numeric-${storyPoints}-circle-outline me-1"></i>${storyPoints} pts`);
                        } else {
                            card.find('.task-meta').append(`<span class="badge bg-info-lighten text-info font-11 rounded-pill" title="${storyPoints} Story Points"><i class="mdi mdi-numeric-${storyPoints}-circle-outline me-1"></i>${storyPoints} pts</span>`);
                        }
                    } else if ($ptsBadge.length) {
                        $ptsBadge.remove();
                    }

                    // Update Assignee Avatar & Text
                    const initials = assignedTo ? assigneeName.substring(0, 2).toUpperCase() : 'UN';
                    card.find('.task-avatar').text(initials);
                    card.find('.task-assignee span').text(assignedTo ? assigneeName : 'Unassigned');
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

        // 8. Filter Pills Click Listeners
        $('#filterPills button').on('click', function() {
            $('#filterPills button').removeClass('active');
            $(this).addClass('active');
            activeFilter = $(this).data('filter');
            applyFilters();
        });

        $('#kanbanSearchInput').on('input', function() {
            applyFilters();
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initKanban);
    } else {
        initKanban();
    }
})();
</script>
<?= $this->endSection() ?>
