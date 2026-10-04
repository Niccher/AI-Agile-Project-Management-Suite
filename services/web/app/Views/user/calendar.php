<?= $this->extend('layouts/hyper/main') ?>

<?= $this->section('title') ?>Calendar • <?= esc(setting('App.siteName')) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php $initials = strtoupper(substr($user->first_name ?? $user->username ?? 'U', 0, 1) . substr($user->last_name ?? '', 0, 1)); ?>

<!-- Page Header -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <button type="button" class="btn btn-primary rounded-pill" id="addEventBtn">
                    <i class="mdi mdi-plus-circle me-1"></i> Add Event
                </button>
            </div>
            <h4 class="page-title">
                <i class="uil-calender me-2 text-primary"></i> Calendar & Milestones
            </h4>
        </div>
    </div>
</div>

<!-- Calendar Stats Overview -->
<div class="row g-3 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="card widget-flat h-100 shadow-sm border-0">
            <div class="card-body">
                <div class="float-end">
                    <div class="avatar-sm bg-primary-lighten text-primary rounded d-flex align-items-center justify-content-center">
                        <i class="mdi mdi-calendar-check font-22"></i>
                    </div>
                </div>
                <h6 class="text-muted text-uppercase mt-0 font-12 fw-semibold">This Month</h6>
                <h3 class="my-2" id="totalEvents"><?= (int)$total_events ?></h3>
                <p class="mb-0 text-muted font-13">
                    <span class="text-primary me-1"><i class="mdi mdi-calendar-month"></i></span>
                    <span>Scheduled Events</span>
                </p>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card widget-flat h-100 shadow-sm border-0">
            <div class="card-body">
                <div class="float-end">
                    <div class="avatar-sm bg-success-lighten text-success rounded d-flex align-items-center justify-content-center">
                        <i class="mdi mdi-check-decagram font-22"></i>
                    </div>
                </div>
                <h6 class="text-muted text-uppercase mt-0 font-12 fw-semibold">Completed</h6>
                <h3 class="my-2 text-success" id="completedEvents"><?= (int)$completed_count ?></h3>
                <p class="mb-0 text-muted font-13">
                    <span class="text-success me-1"><i class="mdi mdi-check-circle"></i></span>
                    <span>Goals Done</span>
                </p>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card widget-flat h-100 shadow-sm border-0">
            <div class="card-body">
                <div class="float-end">
                    <div class="avatar-sm bg-warning-lighten text-warning rounded d-flex align-items-center justify-content-center">
                        <i class="mdi mdi-clock-outline font-22"></i>
                    </div>
                </div>
                <h6 class="text-muted text-uppercase mt-0 font-12 fw-semibold">Pending</h6>
                <h3 class="my-2 text-warning" id="pendingEvents"><?= (int)$pending_count ?></h3>
                <p class="mb-0 text-muted font-13">
                    <span class="text-warning me-1"><i class="mdi mdi-progress-clock"></i></span>
                    <span>In Progress</span>
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
                <h6 class="text-muted text-uppercase mt-0 font-12 fw-semibold">Overdue</h6>
                <h3 class="my-2 text-danger" id="overdueEvents"><?= (int)$overdue_count ?></h3>
                <p class="mb-0 text-muted font-13">
                    <span class="text-danger me-1"><i class="mdi mdi-alert"></i></span>
                    <span>Needs Attention</span>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Calendar Main Card -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-transparent border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="d-flex align-items-center gap-2">
            <h5 class="header-title mb-0">
                <i class="uil-calender me-1 text-primary"></i> <span id="calendarTitle">Calendar</span>
            </h5>
            <select id="projectCalendarFilter" class="form-select form-select-sm ms-2" style="width: 180px;">
                <option value="">All Projects</option>
                <?php if (!empty($projects)): ?>
                    <?php foreach ($projects as $up): ?>
                        <option value="<?= $up['id'] ?>"><?= esc($up['name']) ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
        <!-- Activity Legend -->
        <div class="calendar-legend d-none d-lg-flex flex-wrap gap-3 font-12 text-muted">
            <div class="d-flex align-items-center"><i class="mdi mdi-circle font-10 me-1" style="color: #727cf5;"></i>Sprint</div>
            <div class="d-flex align-items-center"><i class="mdi mdi-circle font-10 text-info me-1"></i>Task</div>
            <div class="d-flex align-items-center"><i class="mdi mdi-circle font-10 me-1" style="color: #6366f1;"></i>Project Due</div>
            <div class="d-flex align-items-center"><i class="mdi mdi-circle font-10 text-warning me-1"></i>Milestone</div>
            <div class="d-flex align-items-center"><i class="mdi mdi-circle font-10 text-purple me-1" style="color: #8b5cf6;"></i>Time</div>
            <div class="d-flex align-items-center"><i class="mdi mdi-circle font-10 text-success me-1"></i>Done</div>
        </div>
    </div>
    <div class="card-body p-3">
        <div id="calendar"></div>
    </div>
</div>

<!-- Add Event Modal -->
<div class="modal fade" id="addEventModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="mdi mdi-calendar-plus me-1"></i> Add Event</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="eventForm" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" id="eventId" name="id">
                    <div class="mb-3">
                        <label for="eventTitle" class="form-label fw-semibold">Event Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="eventTitle" name="title" placeholder="Enter event title" required>
                    </div>
                    <div class="mb-3">
                        <label for="eventDescription" class="form-label fw-semibold">Description</label>
                        <textarea class="form-control" id="eventDescription" name="description" rows="2" placeholder="Describe the event..."></textarea>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="eventProject" class="form-label fw-semibold">Project</label>
                            <select class="form-select" id="eventProject" name="project_id">
                                <option value="">General / No Project</option>
                                <?php if (!empty($projects)): ?>
                                    <?php foreach ($projects as $proj): ?>
                                    <option value="<?= $proj['id'] ?>" data-color="<?= esc($proj['color'] ?? '#3e60d5') ?>"><?= esc($proj['name']) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="eventDate" class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="eventDate" name="start_date" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="eventStartTime" class="form-label fw-semibold">Start Time</label>
                            <input type="time" class="form-control" id="eventStartTime" name="start_time" value="09:00">
                        </div>
                        <div class="col-md-6">
                            <label for="eventEndTime" class="form-label fw-semibold">End Time</label>
                            <input type="time" class="form-control" id="eventEndTime" name="end_time" value="10:00">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="eventStatus" class="form-label fw-semibold">Status</label>
                        <select class="form-select" id="eventStatus" name="status">
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveEventBtn">Save Event</button>
            </div>
        </div>
    </div>
</div>

<!-- Event & Task Details Modal -->
<div class="modal fade" id="eventDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header py-3 border-bottom" id="detailsModalHeader">
                <div class="d-flex align-items-center gap-2">
                    <span id="detailsTypeBadge" class="badge bg-primary">EVENT</span>
                    <h5 class="modal-title fw-bold mb-0 text-truncate" id="detailsTitle" style="max-width: 340px;">Item Details</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Metadata Badges -->
                <div class="d-flex flex-wrap gap-2 mb-3" id="detailsBadgesRow">
                    <span id="detailsProject" class="badge bg-light text-dark border"><i class="fas fa-folder me-1 text-primary"></i> <span id="detailsProjectText">Project</span></span>
                    <span id="detailsStatus" class="badge bg-info-lighten text-info">Status</span>
                    <span id="detailsPriority" class="badge bg-warning-lighten text-warning">Priority</span>
                    <span id="detailsPoints" class="badge bg-purple-lighten text-purple" style="display: none;">3 pts</span>
                </div>

                <!-- Description -->
                <div class="mb-3">
                    <label class="font-12 text-uppercase text-muted fw-bold mb-1">Description</label>
                    <div id="detailsDesc" class="p-3 bg-light rounded text-body font-13" style="max-height: 180px; overflow-y: auto; white-space: pre-line;">
                        No description provided.
                    </div>
                </div>

                <!-- Additional Info Grid -->
                <div class="row g-2 font-12 text-muted border-top pt-3">
                    <div class="col-6">
                        <i class="mdi mdi-calendar-clock me-1 text-primary"></i> <strong>Date:</strong> <span id="detailsTime"></span>
                    </div>
                    <div class="col-6 text-end" id="detailsAssigneeContainer">
                        <i class="mdi mdi-account-circle me-1 text-primary"></i> <strong>Assignee:</strong> <span id="detailsAssignee">Unassigned</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <a href="javascript:void(0);" class="btn btn-primary btn-sm rounded-pill" id="openWorkspaceBtn" style="display: none;">
                    <i class="mdi mdi-open-in-new me-1"></i> Open in Workspace
                </a>
                <button type="button" class="btn btn-outline-danger btn-sm rounded-pill" id="deleteEventBtn">Delete</button>
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill" id="editEventBtn">Edit</button>
                <button type="button" class="btn btn-light btn-sm rounded-pill" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999;"></div>

<style>
.fc {
    font-family: inherit;
}
.fc .fc-toolbar-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #313a46;
}
.fc .fc-button-primary {
    background-color: #727cf5;
    border-color: #727cf5;
    border-radius: 0.25rem;
    font-size: 0.82rem;
    font-weight: 500;
    text-transform: capitalize;
    padding: 0.375rem 0.75rem;
    box-shadow: none;
}
.fc .fc-button-primary:hover, .fc .fc-button-primary:focus {
    background-color: #5b65dc;
    border-color: #5b65dc;
}
.fc .fc-button-primary:not(:disabled).fc-button-active, .fc .fc-button-primary:not(:disabled):active {
    background-color: #4a54c6;
    border-color: #4a54c6;
}
.fc .fc-daygrid-day.fc-day-today {
    background-color: rgba(114, 124, 245, 0.08) !important;
}
.fc-theme-bootstrap5 a {
    color: #313a46;
    text-decoration: none;
}
.fc-event {
    border-radius: 4px !important;
    border: none !important;
    padding: 2px 4px !important;
    font-size: 0.8rem !important;
    font-weight: 500 !important;
    box-shadow: 0 1px 2px rgba(0,0,0,0.08);
    transition: transform 0.15s ease, box-shadow 0.15s ease;
    cursor: pointer;
}
.fc-event:hover {
    transform: translateY(-1px);
    box-shadow: 0 3px 6px rgba(0,0,0,0.15);
}
</style>

<!-- FullCalendar v6 CDN -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>

<script>
$(document).ready(function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
        },
        themeSystem: 'bootstrap5',
        events: '<?= site_url('calendar/events') ?>',
        editable: false,
        selectable: true,
        height: 700,
        
        datesSet: function(dateInfo) {
            $('#calendarTitle').text(dateInfo.view.title);
        },

        eventContent: function(arg) {
            let icon = arg.event.extendedProps.icon || 'fa-circle';
            let arrayOfDomNodes = [
                $('<div>', { class: 'fc-event-main-inner d-flex align-items-center gap-1 p-1' })
                    .append($('<i>', { class: 'fas ' + icon + ' me-1', style: 'font-size: 0.75rem;' }))
                    .append($('<span>', { class: 'fc-event-title text-truncate font-12' }).text(arg.event.title))[0]
            ];
            return { domNodes: arrayOfDomNodes };
        },
        
        select: function(info) {
            $('#eventForm')[0].reset();
            $('#eventId').val('');
            $('#eventDate').val(info.startStr.split('T')[0]);
            $('#eventForm').attr('action', '<?= site_url('calendar/event/store') ?>');
            $('#addEventModal .modal-title').html('<i class="mdi mdi-calendar-plus me-1"></i> Add Event');
            const modal = new bootstrap.Modal(document.getElementById('addEventModal'));
            modal.show();
        },

        eventClick: function(info) {
            const props = info.event.extendedProps;
            
            // Set Workspace Link
            if (props.url) {
                $('#openWorkspaceBtn').attr('href', props.url).show();
            } else {
                $('#openWorkspaceBtn').hide();
            }

            // Set Title & Description
            $('#detailsTitle').text(props.task_title || info.event.title);
            $('#detailsDesc').text(props.description || 'No details provided.');
            $('#detailsTime').text(info.event.start ? info.event.start.toLocaleDateString(undefined, { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' }) : 'N/A');

            // Set Project
            if (props.project_name) {
                $('#detailsProjectText').text(props.project_name);
                $('#detailsProject').show();
            } else {
                $('#detailsProject').hide();
            }

            // Set Type Badge
            const typeNames = {
                'task': 'TASK',
                'sprint': 'SPRINT',
                'project': 'PROJECT DUE',
                'milestone': 'MILESTONE',
                'manual': 'EVENT'
            };
            const typeColors = {
                'task': 'bg-info',
                'sprint': 'bg-primary',
                'project': 'bg-dark',
                'milestone': 'bg-warning text-dark',
                'manual': 'bg-secondary'
            };
            const eventType = props.type || 'manual';
            $('#detailsTypeBadge').text(typeNames[eventType] || 'EVENT')
                .attr('class', 'badge ' + (typeColors[eventType] || 'bg-primary'));

            // Set Status Badge
            if (props.status) {
                const statusLabels = {
                    'todo': 'To Do',
                    'in_progress': 'In Progress',
                    'review': 'Under Review',
                    'done': 'Done',
                    'approved': 'Approved',
                    'rejected': 'Rejected',
                    'blocked': 'Blocked'
                };
                const statusBadges = {
                    'todo': 'bg-secondary-lighten text-secondary',
                    'in_progress': 'bg-primary-lighten text-primary',
                    'review': 'bg-warning-lighten text-warning',
                    'done': 'bg-success-lighten text-success',
                    'approved': 'bg-success text-white',
                    'rejected': 'bg-danger-lighten text-danger',
                    'blocked': 'bg-danger text-white'
                };
                $('#detailsStatus').text(statusLabels[props.status] || props.status.toUpperCase())
                    .attr('class', 'badge ' + (statusBadges[props.status] || 'bg-info-lighten text-info'))
                    .show();
            } else {
                $('#detailsStatus').hide();
            }

            // Set Priority Badge
            if (props.priority) {
                const prioBadges = {
                    'urgent': 'bg-danger text-white',
                    'high': 'bg-danger-lighten text-danger',
                    'medium': 'bg-warning-lighten text-warning',
                    'low': 'bg-secondary-lighten text-secondary'
                };
                $('#detailsPriority').text(props.priority.toUpperCase() + ' PRIORITY')
                    .attr('class', 'badge ' + (prioBadges[props.priority] || 'bg-warning-lighten text-warning'))
                    .show();
            } else {
                $('#detailsPriority').hide();
            }

            // Set Story Points
            if (props.story_points) {
                $('#detailsPoints').text(props.story_points + ' pts').show();
            } else {
                $('#detailsPoints').hide();
            }

            // Set Assignee & Creator
            if (props.assignee_name) {
                $('#detailsAssignee').text(props.assignee_name);
                $('#detailsAssigneeContainer').show();
            } else {
                $('#detailsAssigneeContainer').hide();
            }

            // Manual Event edit/delete buttons
            if (eventType === 'manual') {
                $('#editEventBtn').show().off('click').on('click', function() {
                    bootstrap.Modal.getInstance(document.getElementById('eventDetailsModal')).hide();
                    $('#eventId').val(props.dbId);
                    $('#eventTitle').val(info.event.title);
                    $('#eventDescription').val(props.description);
                    $('#eventDate').val(info.event.startStr.split('T')[0]);
                    $('#eventForm').attr('action', '<?= site_url('calendar/event/update/') ?>' + props.dbId);
                    $('#addEventModal .modal-title').html('<i class="mdi mdi-pencil me-1"></i> Edit Event');
                    new bootstrap.Modal(document.getElementById('addEventModal')).show();
                });

                $('#deleteEventBtn').show().off('click').on('click', function() {
                    if (confirm('Delete this event?')) {
                        const form = $('<form>', {
                            'method': 'POST',
                            'action': '<?= site_url('calendar/event/delete/') ?>' + props.dbId
                        }).append($('<input>', {
                            'type': 'hidden',
                            'name': '<?= csrf_token() ?>',
                            'value': '<?= csrf_hash() ?>'
                        }));
                        $('body').append(form);
                        form.submit();
                    }
                });
            } else {
                $('#editEventBtn').hide();
                $('#deleteEventBtn').hide();
            }
            
            const modal = new bootstrap.Modal(document.getElementById('eventDetailsModal'));
            modal.show();
        }
    });
    calendar.render();

    // Handle Project Filter
    $('#projectCalendarFilter').on('change', function() {
        var pId = $(this).val();
        var newUrl = '<?= site_url('calendar/events') ?>' + (pId ? '?project_id=' + pId : '');
        calendar.removeAllEventSources();
        calendar.addEventSource(newUrl);
    });

    // Link manual Add Event button
    $('#addEventBtn').on('click', function() {
        $('#eventForm')[0].reset();
        $('#eventId').val('');
        $('#eventForm').attr('action', '<?= site_url('calendar/event/store') ?>');
        const modal = new bootstrap.Modal(document.getElementById('addEventModal'));
        modal.show();
    });

    // Save via Submit button
    $('#saveEventBtn').on('click', function() {
        $('#eventForm').submit();
    });

    // View event button in table
    $('.view-upcoming-btn').on('click', function() {
        const title = $(this).data('title');
        const desc = $(this).data('desc');
        const date = $(this).data('date');
        const type = $(this).data('type');

        $('#detailsTitle').text(title);
        $('#detailsDesc').text(desc || 'No description provided.');
        $('#detailsTime').text(date);
        
        $('#detailsType').text(type.toUpperCase()).removeClass('bg-primary bg-info bg-success').addClass(
            type === 'project' ? 'bg-info-lighten text-info' : 
            (type === 'milestone' ? 'bg-success-lighten text-success' : 'bg-primary-lighten text-primary')
        );

        $('#editEventBtn').hide();
        $('#deleteEventBtn').hide();
        const modal = new bootstrap.Modal(document.getElementById('eventDetailsModal'));
        modal.show();
    });
});
</script>

<?= $this->endSection() ?>
