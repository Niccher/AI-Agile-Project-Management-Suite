<?= $this->extend('layouts/hyper/main') ?>

<?= $this->section('title') ?>Kanban Board • <?= esc(setting('App.siteName')) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php 
$user = auth()->user(); 
$initials = strtoupper(substr($user->first_name ?? $user->username ?? 'U', 0, 1) . substr($user->last_name ?? '', 0, 1)); 
?>

<!-- Page Header -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <button type="button" class="btn btn-primary rounded-pill" id="newTaskBtn">
                    <i class="mdi mdi-plus-circle me-1"></i> New Task
                </button>
            </div>
            <h4 class="page-title">
                <i class="uil-columns me-2 text-primary"></i> Workspace Kanban Board
            </h4>
        </div>
    </div>
</div>

<!-- Quick Stats Overview -->
<div class="row g-3 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="card widget-flat h-100 shadow-sm border-0">
            <div class="card-body">
                <div class="float-end">
                    <div class="avatar-sm bg-primary-lighten text-primary rounded d-flex align-items-center justify-content-center">
                        <i class="mdi mdi-format-list-checks font-22"></i>
                    </div>
                </div>
                <h6 class="text-muted text-uppercase mt-0 font-12 fw-semibold">Total Tasks</h6>
                <h3 class="my-2" id="totalTasks">24</h3>
                <p class="mb-0 text-muted font-13">
                    <span class="text-primary me-1"><i class="mdi mdi-checkbox-marked-circle-outline"></i></span>
                    <span>All Active Tasks</span>
                </p>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card widget-flat h-100 shadow-sm border-0">
            <div class="card-body">
                <div class="float-end">
                    <div class="avatar-sm bg-warning-lighten text-warning rounded d-flex align-items-center justify-content-center">
                        <i class="mdi mdi-progress-clock font-22"></i>
                    </div>
                </div>
                <h6 class="text-muted text-uppercase mt-0 font-12 fw-semibold">In Progress</h6>
                <h3 class="my-2 text-warning" id="inProgress">8</h3>
                <p class="mb-0 text-muted font-13">
                    <span class="text-warning me-1"><i class="mdi mdi-run-fast"></i></span>
                    <span>Currently Active</span>
                </p>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card widget-flat h-100 shadow-sm border-0">
            <div class="card-body">
                <div class="float-end">
                    <div class="avatar-sm bg-danger-lighten text-danger rounded d-flex align-items-center justify-content-center">
                        <i class="mdi mdi-alert-circle-outline font-22"></i>
                    </div>
                </div>
                <h6 class="text-muted text-uppercase mt-0 font-12 fw-semibold">Blocked</h6>
                <h3 class="my-2 text-danger" id="blockedTasks">3</h3>
                <p class="mb-0 text-muted font-13">
                    <span class="text-danger me-1"><i class="mdi mdi-alert"></i></span>
                    <span>Requires Action</span>
                </p>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card widget-flat h-100 shadow-sm border-0">
            <div class="card-body">
                <div class="float-end">
                    <div class="avatar-sm bg-success-lighten text-success rounded d-flex align-items-center justify-content-center">
                        <i class="mdi mdi-check-all font-22"></i>
                    </div>
                </div>
                <h6 class="text-muted text-uppercase mt-0 font-12 fw-semibold">Completed</h6>
                <h3 class="my-2 text-success" id="completedTasks">13</h3>
                <p class="mb-0 text-muted font-13">
                    <span class="text-success me-1"><i class="mdi mdi-check-decagram"></i></span>
                    <span>Done & Verified</span>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Search / Filter Bar -->
<div class="row mb-3">
    <div class="col-md-4">
        <div class="input-group">
            <span class="input-group-text"><i class="mdi mdi-magnify"></i></span>
            <input type="text" class="form-control" id="taskSearch" placeholder="Search tasks on board...">
        </div>
    </div>
</div>

<!-- Kanban Board Container -->
<div class="kanban-board mb-4" id="kanbanBoard">
    <div class="row g-3">
        <!-- Planning Column -->
        <div class="col-lg-3">
            <div class="card shadow-sm border-0 h-100 kanban-column">
                <div class="card-header bg-primary text-white py-2 px-3 d-flex justify-content-between align-items-center rounded-top">
                    <h6 class="mb-0 text-white font-14"><i class="mdi mdi-clipboard-text-outline me-1"></i> Planning</h6>
                    <span class="badge bg-white text-primary font-12 rounded-pill">5</span>
                </div>
                <div class="card-body p-2 kanban-column-body d-flex flex-column gap-2" style="min-height: 450px; background-color: var(--bs-tertiary-bg, rgba(0,0,0,0.02));">
                    <div class="card mb-0 shadow-none border kanban-card" data-task-id="1" draggable="true" style="cursor: grab;">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <span class="task-title font-14 fw-semibold text-body">Design Dashboard Layout</span>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-link text-muted p-0" data-bs-toggle="dropdown">
                                        <i class="mdi mdi-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item small" href="#"><i class="mdi mdi-pencil me-1"></i> Edit</a></li>
                                        <li><a class="dropdown-item small text-danger" href="#"><i class="mdi mdi-trash-can me-1"></i> Delete</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="task-project font-12 text-muted mb-2">
                                <i class="mdi mdi-folder-outline me-1"></i> <?= esc(setting('App.siteName')) ?> Dashboard
                            </div>
                            <p class="font-12 text-muted mb-2">Create wireframes and design system for dashboard</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="task-meta d-flex gap-1">
                                    <span class="badge bg-warning-lighten text-warning font-11">Medium</span>
                                    <span class="badge bg-info-lighten text-info font-11">Design</span>
                                </div>
                                <div class="font-12 text-muted"><i class="mdi mdi-calendar me-1"></i>Mar 20</div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-0 shadow-none border kanban-card" data-task-id="2" draggable="true" style="cursor: grab;">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <span class="task-title font-14 fw-semibold text-body">Setup Database Schema</span>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-link text-muted p-0" data-bs-toggle="dropdown">
                                        <i class="mdi mdi-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item small" href="#"><i class="mdi mdi-pencil me-1"></i> Edit</a></li>
                                        <li><a class="dropdown-item small text-danger" href="#"><i class="mdi mdi-trash-can me-1"></i> Delete</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="task-project font-12 text-muted mb-2">
                                <i class="mdi mdi-folder-outline me-1"></i> E-commerce Backend
                            </div>
                            <p class="font-12 text-muted mb-2">Design and implement database tables & migrations</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="task-meta d-flex gap-1">
                                    <span class="badge bg-danger-lighten text-danger font-11">High</span>
                                    <span class="badge bg-dark text-white font-11">Backend</span>
                                </div>
                                <div class="font-12 text-muted"><i class="mdi mdi-calendar me-1"></i>Mar 22</div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-auto pt-2">
                        <button class="btn btn-sm btn-outline-secondary w-100" onclick="$('#newTaskModal').modal('show')">
                            <i class="mdi mdi-plus me-1"></i> Add Task
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- In Progress Column -->
        <div class="col-lg-3">
            <div class="card shadow-sm border-0 h-100 kanban-column">
                <div class="card-header bg-warning text-dark py-2 px-3 d-flex justify-content-between align-items-center rounded-top">
                    <h6 class="mb-0 text-dark font-14"><i class="mdi mdi-progress-wrench me-1"></i> In Progress</h6>
                    <span class="badge bg-dark text-white font-12 rounded-pill">8</span>
                </div>
                <div class="card-body p-2 kanban-column-body d-flex flex-column gap-2" style="min-height: 450px; background-color: var(--bs-tertiary-bg, rgba(0,0,0,0.02));">
                    <div class="card mb-0 shadow-none border kanban-card" data-task-id="4" draggable="true" style="cursor: grab;">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <span class="task-title font-14 fw-semibold text-body">Implement Authentication</span>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-link text-muted p-0" data-bs-toggle="dropdown">
                                        <i class="mdi mdi-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item small" href="#"><i class="mdi mdi-pencil me-1"></i> Edit</a></li>
                                        <li><a class="dropdown-item small text-danger" href="#"><i class="mdi mdi-trash-can me-1"></i> Delete</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="task-project font-12 text-muted mb-2">
                                <i class="mdi mdi-folder-outline me-1"></i> E-commerce Backend
                            </div>
                            <p class="font-12 text-muted mb-2">JWT auth tokens & secure session handling</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="task-meta d-flex gap-1">
                                    <span class="badge bg-danger-lighten text-danger font-11">High</span>
                                    <span class="badge bg-primary-lighten text-primary font-11">Security</span>
                                </div>
                                <div class="font-12 text-muted"><i class="mdi mdi-calendar me-1"></i>Today</div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-0 shadow-none border kanban-card border-start border-danger border-2" data-task-id="6" draggable="true" style="cursor: grab;">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <span class="task-title font-14 fw-semibold text-danger">API Rate Limiting</span>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-link text-muted p-0" data-bs-toggle="dropdown">
                                        <i class="mdi mdi-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item small" href="#"><i class="mdi mdi-pencil me-1"></i> Edit</a></li>
                                        <li><a class="dropdown-item small text-danger" href="#"><i class="mdi mdi-trash-can me-1"></i> Delete</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="task-project font-12 text-muted mb-2">
                                <i class="mdi mdi-folder-outline me-1"></i> API Integration
                            </div>
                            <p class="font-12 text-muted mb-2">Waiting for provider API keys documentation</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="task-meta d-flex gap-1">
                                    <span class="badge bg-danger text-white font-11">Blocked</span>
                                </div>
                                <div class="font-12 text-muted"><i class="mdi mdi-calendar me-1"></i>Mar 18</div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-auto pt-2">
                        <button class="btn btn-sm btn-outline-secondary w-100" onclick="$('#newTaskModal').modal('show')">
                            <i class="mdi mdi-plus me-1"></i> Add Task
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Testing Column -->
        <div class="col-lg-3">
            <div class="card shadow-sm border-0 h-100 kanban-column">
                <div class="card-header bg-info text-white py-2 px-3 d-flex justify-content-between align-items-center rounded-top">
                    <h6 class="mb-0 text-white font-14"><i class="mdi mdi-test-tube me-1"></i> Testing</h6>
                    <span class="badge bg-white text-info font-12 rounded-pill">3</span>
                </div>
                <div class="card-body p-2 kanban-column-body d-flex flex-column gap-2" style="min-height: 450px; background-color: var(--bs-tertiary-bg, rgba(0,0,0,0.02));">
                    <div class="card mb-0 shadow-none border kanban-card" data-task-id="7" draggable="true" style="cursor: grab;">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <span class="task-title font-14 fw-semibold text-body">Unit Tests for Billing</span>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-link text-muted p-0" data-bs-toggle="dropdown">
                                        <i class="mdi mdi-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item small" href="#"><i class="mdi mdi-pencil me-1"></i> Edit</a></li>
                                        <li><a class="dropdown-item small text-danger" href="#"><i class="mdi mdi-trash-can me-1"></i> Delete</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="task-project font-12 text-muted mb-2">
                                <i class="mdi mdi-folder-outline me-1"></i> Finance Core
                            </div>
                            <p class="font-12 text-muted mb-2">Write PHPUnit test suite for invoicing calculation</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="task-meta d-flex gap-1">
                                    <span class="badge bg-info-lighten text-info font-11">Testing</span>
                                </div>
                                <div class="font-12 text-muted"><i class="mdi mdi-calendar me-1"></i>Mar 21</div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-auto pt-2">
                        <button class="btn btn-sm btn-outline-secondary w-100" onclick="$('#newTaskModal').modal('show')">
                            <i class="mdi mdi-plus me-1"></i> Add Task
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Finished Column -->
        <div class="col-lg-3">
            <div class="card shadow-sm border-0 h-100 kanban-column">
                <div class="card-header bg-success text-white py-2 px-3 d-flex justify-content-between align-items-center rounded-top">
                    <h6 class="mb-0 text-white font-14"><i class="mdi mdi-check-all me-1"></i> Finished</h6>
                    <span class="badge bg-white text-success font-12 rounded-pill">13</span>
                </div>
                <div class="card-body p-2 kanban-column-body d-flex flex-column gap-2" style="min-height: 450px; background-color: var(--bs-tertiary-bg, rgba(0,0,0,0.02));">
                    <div class="card mb-0 shadow-none border kanban-card bg-light-subtle" data-task-id="8" draggable="true" style="cursor: grab;">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <span class="task-title font-14 fw-semibold text-decoration-line-through text-muted">CI/CD Pipeline Setup</span>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-link text-muted p-0" data-bs-toggle="dropdown">
                                        <i class="mdi mdi-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item small" href="#"><i class="mdi mdi-pencil me-1"></i> Edit</a></li>
                                        <li><a class="dropdown-item small text-danger" href="#"><i class="mdi mdi-trash-can me-1"></i> Delete</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="task-project font-12 text-muted mb-2">
                                <i class="mdi mdi-folder-outline me-1"></i> DevOps
                            </div>
                            <p class="font-12 text-muted mb-2">Automated deployment via GitHub Actions & Docker</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="task-meta d-flex gap-1">
                                    <span class="badge bg-success-lighten text-success font-11">Complete</span>
                                </div>
                                <div class="font-12 text-muted"><i class="mdi mdi-calendar me-1"></i>Mar 14</div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-auto pt-2">
                        <button class="btn btn-sm btn-outline-secondary w-100" onclick="$('#newTaskModal').modal('show')">
                            <i class="mdi mdi-plus me-1"></i> Add Task
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- New Task Modal -->
<div class="modal fade" id="newTaskModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="mdi mdi-plus-circle me-1"></i> New Kanban Task</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="newTaskForm">
                    <div class="mb-3">
                        <label for="taskTitle" class="form-label fw-semibold">Task Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="taskTitle" placeholder="Enter task title" required>
                    </div>
                    <div class="mb-3">
                        <label for="taskDescription" class="form-label fw-semibold">Description</label>
                        <textarea class="form-control" id="taskDescription" rows="3" placeholder="Describe the task..."></textarea>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="taskProject" class="form-label fw-semibold">Project</label>
                            <select class="form-select" id="taskProject">
                                <option value="chegeos"><?= esc(setting('App.siteName')) ?> Dashboard</option>
                                <option value="api">API Integration</option>
                                <option value="mobile">Mobile App</option>
                                <option value="portfolio">Portfolio Website</option>
                                <option value="ecommerce">E-commerce Backend</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="taskColumn" class="form-label fw-semibold">Column</label>
                            <select class="form-select" id="taskColumn">
                                <option value="planning">Planning</option>
                                <option value="progress">In Progress</option>
                                <option value="testing">Testing</option>
                                <option value="finished">Finished</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="taskPriority" class="form-label fw-semibold">Priority</label>
                            <select class="form-select" id="taskPriority">
                                <option value="high">High</option>
                                <option value="medium" selected>Medium</option>
                                <option value="low">Low</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="taskDueDate" class="form-label fw-semibold">Due Date</label>
                            <input type="date" class="form-control" id="taskDueDate">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="createTaskBtn">Create Task</button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999;"></div>

<script>
$(document).ready(function() {
    $('#newTaskBtn').on('click', function() {
        const modal = new bootstrap.Modal(document.getElementById('newTaskModal'));
        modal.show();
    });

    $('#createTaskBtn').on('click', function() {
        const taskTitle = $('#taskTitle').val().trim();
        if (!taskTitle) {
            showToast('Please enter a task title', 'danger');
            return;
        }

        showToast(`Task "${taskTitle}" created successfully!`, 'success');
        $('#newTaskForm')[0].reset();
        bootstrap.Modal.getInstance(document.getElementById('newTaskModal')).hide();
    });

    $('#taskSearch').on('keyup', function() {
        const searchTerm = $(this).val().toLowerCase();
        $('.kanban-card').each(function() {
            const cardText = $(this).text().toLowerCase();
            if (cardText.includes(searchTerm) || searchTerm === '') {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // Drag and drop
    let draggedTask = null;

    $('.kanban-card').on('dragstart', function(e) {
        draggedTask = $(this);
        setTimeout(() => $(this).addClass('opacity-50'), 0);
    });

    $('.kanban-card').on('dragend', function() {
        $(this).removeClass('opacity-50');
        draggedTask = null;
    });

    $('.kanban-column-body').on('dragover', function(e) {
        e.preventDefault();
        $(this).addClass('bg-light');
    });

    $('.kanban-column-body').on('dragleave', function() {
        $(this).removeClass('bg-light');
    });

    $('.kanban-column-body').on('drop', function(e) {
        e.preventDefault();
        $(this).removeClass('bg-light');

        if (draggedTask) {
            $(this).find('.mt-auto').before(draggedTask);
            const columnName = $(this).closest('.kanban-column').find('.card-header h6').text().trim();
            showToast(`Task moved to ${columnName}`, 'info');
            updateColumnCounts();
        }
    });

    function updateColumnCounts() {
        $('.kanban-column').each(function() {
            const count = $(this).find('.kanban-card:visible').length;
            $(this).find('.card-header .badge').text(count);
        });
    }

    $(document).on('click', '.kanban-card .dropdown-item', function(e) {
        e.stopPropagation();
        const action = $(this).text().trim();
        const taskTitle = $(this).closest('.kanban-card').find('.task-title').text();

        if (action.includes('Edit')) {
            showToast(`Editing task: ${taskTitle}`, 'warning');
        } else if (action.includes('Delete')) {
            if (confirm(`Delete task "${taskTitle}"?`)) {
                $(this).closest('.kanban-card').fadeOut(300, function() {
                    $(this).remove();
                    updateColumnCounts();
                });
                showToast(`Task "${taskTitle}" deleted`, 'danger');
            }
        }
    });

    function showToast(message, type = 'info') {
        const toastId = 'toast-' + Date.now();
        const toastHtml = `
            <div id="${toastId}" class="toast align-items-center text-bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
        $('.toast-container').append(toastHtml);
        const toastEl = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastEl);
        toast.show();
        $(toastEl).on('hidden.bs.toast', function() {
            $(this).remove();
        });
    }

    updateColumnCounts();
});
</script>

<?= $this->endSection() ?>
