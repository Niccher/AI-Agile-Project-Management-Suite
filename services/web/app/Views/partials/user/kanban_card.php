<?php
$priority = strtolower($task['priority'] ?? 'medium');
$priorityBorder = '#727cf5'; // medium default
$priorityBadgeClass = 'bg-primary text-white';

if ($priority === 'critical') {
    $priorityBorder = '#fa5c7c';
    $priorityBadgeClass = 'bg-danger text-white';
} elseif ($priority === 'high') {
    $priorityBorder = '#ffbc00';
    $priorityBadgeClass = 'bg-warning text-dark';
} elseif ($priority === 'low') {
    $priorityBorder = '#6c757d';
    $priorityBadgeClass = 'bg-secondary text-white';
}

$isOverdue = false;
$isDueToday = false;
if (!empty($task['due_date']) && ($task['status'] ?? '') !== 'done') {
    $dueDateTimestamp = strtotime($task['due_date']);
    $todayTimestamp = strtotime('today');
    if ($dueDateTimestamp < $todayTimestamp) {
        $isOverdue = true;
    } elseif ($dueDateTimestamp === $todayTimestamp) {
        $isDueToday = true;
    }
}
$initials = !empty($task['assignee_name']) ? strtoupper(substr($task['assignee_name'], 0, 2)) : strtoupper(substr($task['title'] ?? 'TK', 0, 2));
?>

<div class="kanban-card card shadow-sm mb-2" 
     id="task-card-<?= $task['id'] ?>"
     data-task-id="<?= $task['id'] ?>" 
     data-title="<?= esc($task['title']) ?>"
     data-description="<?= esc($task['description'] ?? '') ?>" 
     data-priority="<?= esc($priority) ?>" 
     data-due-date="<?= esc($task['due_date'] ?? '') ?>" 
     data-status="<?= esc($task['status'] ?? 'todo') ?>"
     data-assigned-to="<?= esc($task['assigned_to'] ?? '') ?>"
     draggable="true"
     style="border-left: 4px solid <?= $priorityBorder ?> !important;">
    
    <div class="kanban-card-header pb-1">
        <div class="d-flex justify-content-between align-items-start gap-1">
            <span class="badge bg-light text-muted font-11 px-1 py-0 border">
                #<?= $task['id'] ?>
            </span>
            <div class="task-title font-14 fw-semibold text-body flex-grow-1 text-truncate" title="<?= esc($task['title']) ?>">
                <?= esc($task['title']) ?>
            </div>
            <div class="dropdown">
                <button class="btn btn-xs btn-link text-muted p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="mdi mdi-dots-vertical font-16"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                    <li>
                        <a class="dropdown-item small edit-task-btn" href="#" data-task-id="<?= $task['id'] ?>">
                            <i class="mdi mdi-pencil me-2 text-primary"></i>Edit Task
                        </a>
                    </li>
                    <li class="dropdown-header text-uppercase font-10 py-1">Quick Move</li>
                    <li><a class="dropdown-item small quick-move-btn" href="#" data-status="todo" data-task-id="<?= $task['id'] ?>"><i class="mdi mdi-clipboard-outline me-2 text-secondary"></i>To Do</a></li>
                    <li><a class="dropdown-item small quick-move-btn" href="#" data-status="in_progress" data-task-id="<?= $task['id'] ?>"><i class="mdi mdi-progress-clock me-2 text-info"></i>In Progress</a></li>
                    <li><a class="dropdown-item small quick-move-btn" href="#" data-status="review" data-task-id="<?= $task['id'] ?>"><i class="mdi mdi-eye-check-outline me-2 text-warning"></i>In Review</a></li>
                    <li><a class="dropdown-item small quick-move-btn" href="#" data-status="done" data-task-id="<?= $task['id'] ?>"><i class="mdi mdi-check-all me-2 text-success"></i>Done</a></li>
                    <li><hr class="dropdown-divider my-1"></li>
                    <li>
                        <a class="dropdown-item small text-danger delete-task-btn" href="#" data-task-id="<?= $task['id'] ?>">
                            <i class="mdi mdi-trash-can-outline me-2"></i>Delete
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    
    <div class="kanban-card-body py-1">
        <?php if (!empty($task['description'])): ?>
            <p class="font-12 text-muted text-truncate-2 mb-2"><?= esc($task['description']) ?></p>
        <?php endif; ?>
        
        <div class="d-flex justify-content-between align-items-center mt-2 flex-wrap gap-1">
            <div class="task-meta d-flex align-items-center gap-1">
                <span class="badge <?= $priorityBadgeClass ?> font-11 rounded-pill priority-pill">
                    <?= ucfirst($priority) ?>
                </span>
            </div>
            
            <div class="task-date font-11 <?= $isOverdue ? 'text-danger fw-bold' : ($isDueToday ? 'text-warning fw-semibold' : 'text-muted') ?>" title="Due Date">
                <i class="mdi <?= $isOverdue ? 'mdi-alert-circle-outline' : 'mdi-calendar-clock' ?> me-1"></i>
                <?= !empty($task['due_date']) ? date('M j', strtotime($task['due_date'])) : 'No due date' ?>
                <?php if ($isOverdue): ?>
                    <span class="badge bg-danger-lighten text-danger ms-1 font-10">Overdue</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="kanban-card-footer pt-2 mt-1 border-top border-light d-flex justify-content-between align-items-center">
        <div class="task-assignee d-flex align-items-center" title="Assignee: <?= esc($task['assignee_name'] ?? 'Team Member') ?>">
            <div class="task-avatar">
                <?= $initials ?>
            </div>
            <span class="font-11 text-muted ms-1 text-truncate" style="max-width: 110px;">
                <?= esc($task['assignee_name'] ?? 'Team Member') ?>
            </span>
        </div>
        <div class="task-hints font-11 text-muted" title="Right-click for quick actions">
            <i class="mdi mdi-cursor-default-click-outline opacity-50"></i>
        </div>
    </div>
</div>

<style>
    .kanban-card {
        background-color: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 8px !important;
        padding: 0.85rem !important;
        margin-bottom: 0.85rem !important;
        cursor: grab;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05) !important;
        transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
        position: relative;
    }
    .kanban-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.09) !important;
        border-color: #cbd5e1 !important;
    }
    .kanban-card:active {
        cursor: grabbing;
    }
    .dark-theme .kanban-card,
    [data-bs-theme="dark"] .kanban-card {
        background-color: #37404a !important;
        border-color: #464f5b !important;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25) !important;
    }
    .dark-theme .kanban-card:hover,
    [data-bs-theme="dark"] .kanban-card:hover {
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.45) !important;
        border-color: #55606d !important;
    }
    .text-truncate-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .task-avatar {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        font-weight: 700;
        background: #eef2f7;
        color: #495057;
    }
    .dark-theme .task-avatar,
    [data-bs-theme="dark"] .task-avatar {
        background: #464f5b;
        color: #ced4da;
    }
</style>
