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
                <?php 
                    $pModel = new \App\Models\ProjectModel();
                    $userProjs = $pModel->where('user_id', auth()->id())->findAll();
                    foreach ($userProjs as $up): 
                ?>
                    <option value="<?= $up['id'] ?>"><?= esc($up['name']) ?></option>
                <?php endforeach; ?>
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
    <div class="card-body p-3 p-md-4">
        <div id="calendar" class="calendar-container"></div>
    </div>
</div>

<!-- Upcoming Events & Distribution -->
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-transparent border-bottom py-3">
                <h5 class="header-title mb-0">
                    <i class="uil-list-ul me-1 text-primary"></i> Upcoming Deadlines & Events
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-centered mb-0">
                        <thead class="table-light font-12 text-uppercase">
                            <tr>
                                <th>Date</th>
                                <th>Event</th>
                                <th>Project</th>
                                <th>Status</th>
                                <th>Time</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($upcoming_events)): ?>
                            <?php foreach ($upcoming_events as $event): ?>
                            <tr>
                                <td class="font-13 fw-semibold"><?= date('M d, Y', strtotime($event['date'])) ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-xs rounded bg-light text-secondary d-flex align-items-center justify-content-center me-2 font-12" style="width: 28px; height: 28px;">
                                            <i class="fas <?= esc($event['icon'] ?? 'fa-circle') ?>"></i>
                                        </div>
                                        <div>
                                            <span class="font-14 fw-semibold text-body"><?= esc($event['title']) ?></span>
                                            <?php if (!empty($event['desc'])): ?>
                                            <div class="text-muted font-12 text-truncate" style="max-width: 220px;"><?= esc($event['desc']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge" style="background-color: <?= esc($event['color'] ?? '#3e60d5') ?>; color: #fff;">
                                        <?= esc($event['project']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-lighten text-secondary font-12">
                                        <?= ucfirst(esc($event['type'])) ?>
                                    </span>
                                </td>
                                <td class="font-12 text-muted">All Day</td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-info view-upcoming-btn" 
                                            data-title="<?= esc($event['title']) ?>" 
                                            data-desc="<?= esc($event['desc']) ?>"
                                            data-date="<?= date('Y-m-d', strtotime($event['date'])) ?>"
                                            data-type="<?= esc($event['type']) ?>">
                                        <i class="mdi mdi-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="mdi mdi-calendar-blank font-24 d-block mb-1"></i>
                                    No upcoming events found.
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php if (($upcoming_total_pages ?? 1) > 1): ?>
            <div class="card-footer bg-transparent border-top py-2 d-flex justify-content-between align-items-center">
                <button class="btn btn-sm btn-outline-secondary <?= $upcoming_current_page <= 1 ? 'disabled' : '' ?>" 
                        onclick="window.location.search = '?page_upcoming=<?= $upcoming_current_page - 1 ?>'">
                    <i class="mdi mdi-chevron-left me-1"></i> Prev
                </button>
                <span class="font-12 text-muted">Page <?= $upcoming_current_page ?> of <?= $upcoming_total_pages ?></span>
                <button class="btn btn-sm btn-outline-secondary <?= $upcoming_current_page >= $upcoming_total_pages ? 'disabled' : '' ?>"
                        onclick="window.location.search = '?page_upcoming=<?= $upcoming_current_page + 1 ?>'">
                    Next <i class="mdi mdi-chevron-right ms-1"></i>
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-transparent border-bottom py-3">
                <h5 class="header-title mb-0">
                    <i class="uil-chart-pie me-1 text-primary"></i> Project Distribution
                </h5>
            </div>
            <div class="card-body p-3">
                <div class="project-distribution d-flex flex-column gap-3">
                    <?php if (!empty($distribution)): ?>
                        <?php 
                        $totalCount = array_sum(array_column($distribution, 'count'));
                        foreach ($distribution as $dist): 
                            $percent = ($totalCount > 0) ? round(($dist['count'] / $totalCount) * 100) : 0;
                        ?>
                        <div class="distribution-item">
                            <div class="d-flex justify-content-between align-items-center mb-1 font-13">
                                <span class="fw-semibold text-body"><?= esc($dist['name'] ?? 'General') ?></span>
                                <span class="text-muted"><?= $dist['count'] ?> event<?= $dist['count'] == 1 ? '' : 's' ?> (<?= $percent ?>%)</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar rounded" style="width: <?= $percent ?>%; background-color: <?= esc($dist['color'] ?? '#3e60d5') ?>;"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-center text-muted py-4">No project distribution data available.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
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

<!-- Event Details Modal -->
<div class="modal fade" id="eventDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailsTitle">Event Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p id="detailsDesc" class="font-14 text-body mb-3"></p>
                <div class="d-flex justify-content-between align-items-center font-12 text-muted border-top pt-2">
                    <span><i class="mdi mdi-clock-outline me-1"></i> <span id="detailsTime"></span></span>
                    <span id="detailsType" class="badge"></span>
                </div>
            </div>
            <div class="modal-footer">
                <a href="javascript:void(0);" class="btn btn-primary" id="openWorkspaceBtn" style="display: none;">
                    <i class="mdi mdi-open-in-new me-1"></i> Open in Workspace
                </a>
                <button type="button" class="btn btn-outline-danger" id="deleteEventBtn">Delete</button>
                <button type="button" class="btn btn-primary" id="editEventBtn">Edit</button>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999;"></div>

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
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
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
            
            if (props.url) {
                $('#openWorkspaceBtn').attr('href', props.url).show();
            } else {
                $('#openWorkspaceBtn').hide();
            }

            if (props.type === 'manual') {
                $('#detailsTitle').text(info.event.title);
                $('#detailsDesc').text(props.description || 'No description provided.');
                $('#detailsTime').text(info.event.start ? info.event.start.toLocaleString() : 'Scheduled');
                $('#detailsType').text('Personal Event').removeClass('bg-info bg-success').addClass('bg-primary-lighten text-primary');
                
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
                $('#detailsTitle').text(info.event.title);
                $('#detailsDesc').text(props.description || 'No details provided.');
                $('#detailsTime').text(info.event.start ? info.event.start.toLocaleDateString() : '');
                
                let badgeClass = 'bg-primary-lighten text-primary';
                if (props.type === 'sprint') badgeClass = 'bg-primary text-white';
                else if (props.type === 'task') badgeClass = 'bg-info-lighten text-info';
                else if (props.type === 'project') badgeClass = 'bg-secondary-lighten text-secondary';
                else if (props.type === 'milestone') badgeClass = 'bg-warning-lighten text-warning';

                $('#detailsType').text(props.type.toUpperCase()).attr('class', 'badge ' + badgeClass);
                
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
