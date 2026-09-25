<?= $this->extend('layouts/hyper/main') ?>

<?= $this->section('title') ?><?= esc($project['name'] ?? 'Project') ?> Board • <?= esc(setting('App.siteName')) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#addTaskModal">
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
                    <span class="badge bg-secondary-lighten text-secondary font-12 rounded-pill" id="count-todo">
                        <?= count($boardData['todo'] ?? []) ?>
                    </span>
                </div>
                <div class="card-body p-2 kanban-cards sortable-list d-flex flex-column gap-2" id="todo-list" style="min-height: 500px; background-color: var(--bs-tertiary-bg, rgba(0,0,0,0.02));">
                    <?php if (!empty($boardData['todo'])): ?>
                        <?php foreach ($boardData['todo'] as $task): ?>
                            <?= view('partials/user/kanban_card', ['task' => $task]) ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- In Progress Column -->
        <div class="col-12 col-md-6 col-xl-3" style="min-width: 280px;">
            <div class="card shadow-sm border-0 h-100 kanban-column" data-status="in_progress">
                <div class="card-header bg-info-lighten text-info py-2 px-3 d-flex justify-content-between align-items-center rounded-top border-bottom">
                    <h6 class="mb-0 text-info font-14 fw-bold">
                        <i class="mdi mdi-progress-clock me-1"></i> IN PROGRESS
                    </h6>
                    <span class="badge bg-info text-white font-12 rounded-pill" id="count-in_progress">
                        <?= count($boardData['in_progress'] ?? []) ?>
                    </span>
                </div>
                <div class="card-body p-2 kanban-cards sortable-list d-flex flex-column gap-2" id="in_progress-list" style="min-height: 500px; background-color: var(--bs-tertiary-bg, rgba(0,0,0,0.02));">
                    <?php if (!empty($boardData['in_progress'])): ?>
                        <?php foreach ($boardData['in_progress'] as $task): ?>
                            <?= view('partials/user/kanban_card', ['task' => $task]) ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
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
                    <span class="badge bg-warning text-dark font-12 rounded-pill" id="count-review">
                        <?= count($boardData['review'] ?? []) ?>
                    </span>
                </div>
                <div class="card-body p-2 kanban-cards sortable-list d-flex flex-column gap-2" id="review-list" style="min-height: 500px; background-color: var(--bs-tertiary-bg, rgba(0,0,0,0.02));">
                    <?php if (!empty($boardData['review'])): ?>
                        <?php foreach ($boardData['review'] as $task): ?>
                            <?= view('partials/user/kanban_card', ['task' => $task]) ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
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
                    <span class="badge bg-success text-white font-12 rounded-pill" id="count-done">
                        <?= count($boardData['done'] ?? []) ?>
                    </span>
                </div>
                <div class="card-body p-2 kanban-cards sortable-list d-flex flex-column gap-2" id="done-list" style="min-height: 500px; background-color: var(--bs-tertiary-bg, rgba(0,0,0,0.02));">
                    <?php if (!empty($boardData['done'])): ?>
                        <?php foreach ($boardData['done'] as $task): ?>
                            <?= view('partials/user/kanban_card', ['task' => $task]) ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Add Task Modal -->
<div class="modal fade" id="addTaskModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="<?= site_url('projects/task/store') ?>" method="POST">
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
                                <option value="review">Review</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Task</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Task Modal -->
<div class="modal fade" id="editTaskModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
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

                        $.post('<?= site_url('projects/task/move') ?>', {
                            <?= csrf_token() ?>: '<?= csrf_hash() ?>',
                            task_id: taskId,
                            status: newStatus,
                            order: order
                        }, function(res) {
                            if (res && res.status === 'success') {
                                updateColumnCounts();
                            }
                        }).fail(function(xhr) {
                            console.error('Task move failed:', xhr);
                        });
                    }
                });
            }
        });

        function updateColumnCounts() {
            columns.forEach(status => {
                const count = $(`#${status}-list .kanban-card`).length;
                $(`#count-${status}`).text(count);
            });
        }

        // Edit task modal trigger
        $(document).on('click', '.edit-task-btn', function(e) {
            e.preventDefault();
            const card = $(this).closest('.kanban-card');
            $('#editTaskId').val(card.data('task-id'));
            $('#editTaskTitle').val(card.find('.task-title').text().trim());
            $('#editTaskDescription').val(card.data('description') || '');
            $('#editTaskPriority').val(card.data('priority') || 'medium');
            $('#editTaskDueDate').val(card.data('due-date') || '');
            const modalEl = document.getElementById('editTaskModal');
            if (modalEl) {
                const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                modal.show();
            }
        });

        $('#saveTaskEditBtn').on('click', function() {
            const id = $('#editTaskId').val();
            const btn = $(this);
            btn.prop('disabled', true).html('<i class="mdi mdi-spin mdi-loading me-1"></i> Saving...');

            $.post('<?= site_url('projects/task/update/') ?>' + id, {
                <?= csrf_token() ?>: '<?= csrf_hash() ?>',
                title: $('#editTaskTitle').val(),
                description: $('#editTaskDescription').val(),
                priority: $('#editTaskPriority').val(),
                due_date: $('#editTaskDueDate').val()
            }, function(res) {
                if (res && res.status === 'success') {
                    location.reload();
                } else {
                    alert(res.message || 'Error updating task');
                    btn.prop('disabled', false).text('Save Changes');
                }
            }).fail(function() {
                alert('Server communication error');
                btn.prop('disabled', false).text('Save Changes');
            });
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
