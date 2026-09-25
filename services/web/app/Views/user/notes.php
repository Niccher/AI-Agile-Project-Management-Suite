<?= $this->extend('layouts/hyper/main') ?>

<?= $this->section('title') ?>Notes • <?= esc(setting('App.siteName')) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <button class="btn btn-primary rounded-pill" id="newNoteBtn">
                    <i class="mdi mdi-plus me-1"></i> New Note
                </button>
            </div>
            <h4 class="page-title"><i class="uil-notes me-2 text-primary"></i> Scratch Notes</h4>
        </div>
    </div>
</div>

<!-- Notes Stats -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card widget-flat h-100 mb-0 shadow-sm border-0">
            <div class="card-body">
                <div class="float-end">
                    <div class="avatar-sm bg-primary-lighten text-primary rounded-circle d-flex align-items-center justify-content-center">
                        <i class="uil-notes font-22"></i>
                    </div>
                </div>
                <h5 class="text-muted fw-normal mt-0" title="Total Notes">Total Notes</h5>
                <h3 class="mt-3 mb-1 fw-bold" id="totalNotes"><?= esc($stats['total'] ?? 0) ?></h3>
                <p class="mb-0 text-muted font-13">
                    <span class="text-primary me-1"><i class="mdi mdi-note-text-outline"></i> All active</span>
                    <span>scratchpads</span>
                </p>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card widget-flat h-100 mb-0 shadow-sm border-0">
            <div class="card-body">
                <div class="float-end">
                    <div class="avatar-sm bg-warning-lighten text-warning rounded-circle d-flex align-items-center justify-content-center">
                        <i class="uil-star font-22"></i>
                    </div>
                </div>
                <h5 class="text-muted fw-normal mt-0" title="Starred Notes">Starred</h5>
                <h3 class="mt-3 mb-1 fw-bold text-warning" id="starredNotes"><?= esc($stats['starred'] ?? 0) ?></h3>
                <p class="mb-0 text-muted font-13">
                    <span class="text-warning me-1"><i class="mdi mdi-star"></i> Pinned</span>
                    <span>high priority</span>
                </p>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card widget-flat h-100 mb-0 shadow-sm border-0">
            <div class="card-body">
                <div class="float-end">
                    <div class="avatar-sm bg-success-lighten text-success rounded-circle d-flex align-items-center justify-content-center">
                        <i class="uil-check-circle font-22"></i>
                    </div>
                </div>
                <h5 class="text-muted fw-normal mt-0" title="Completed Notes">Completed</h5>
                <h3 class="mt-3 mb-1 fw-bold text-success" id="completedNotes"><?= esc($stats['completed'] ?? 0) ?></h3>
                <p class="mb-0 text-muted font-13">
                    <span class="text-success me-1"><i class="mdi mdi-check-all"></i> Resolved</span>
                    <span>action items</span>
                </p>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card widget-flat h-100 mb-0 shadow-sm border-0">
            <div class="card-body">
                <div class="float-end">
                    <div class="avatar-sm bg-danger-lighten text-danger rounded-circle d-flex align-items-center justify-content-center">
                        <i class="uil-trash-alt font-22"></i>
                    </div>
                </div>
                <h5 class="text-muted fw-normal mt-0" title="Deleted Notes">Deleted</h5>
                <h3 class="mt-3 mb-1 fw-bold text-danger" id="deletedNotes"><?= esc($stats['deleted'] ?? 0) ?></h3>
                <p class="mb-0 text-muted font-13">
                    <span class="text-danger me-1"><i class="mdi mdi-delete-outline"></i> In trash</span>
                    <span>can be restored</span>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Row 1: Notes List & Editor -->
<div class="row" id="row_1">
    <!-- Notes List -->
    <div class="col-xl-4 col-lg-5 mb-3 mb-lg-0">
        <div class="card shadow-sm border-0" style="min-height: 600px;">
            <div class="card-header d-flex justify-content-between align-items-center bg-transparent border-bottom py-3">
                <h5 class="header-title mb-0">
                    <i class="uil-list-ul me-1 text-primary"></i> All Notes
                </h5>
                <div class="d-flex gap-1">
                    <input type="text" id="notesSearch" class="form-control form-control-sm" placeholder="Search notes..." style="max-width: 130px;">
                </div>
            </div>

            <div class="card-body p-3" style="max-height: 520px; overflow-y: auto;">
                <div id="notesList">
                    <?php if (!empty($notes)): ?>
                        <?php foreach ($notes as $note): ?>
                        <?php $noteTags = json_decode($note['tags'], true) ?? []; ?>
                        <div class="note-item p-3 mb-2 rounded border cursor-pointer bg-light-subtle transition-all <?= $note['is_completed'] ? 'opacity-75 border-success-subtle' : '' ?>" 
                             style="cursor: pointer;"
                             data-note-id="<?= $note['id'] ?>" 
                             data-project-id="<?= $note['project_id'] ?>"
                             data-title="<?= esc($note['title']) ?>"
                             data-content="<?= esc($note['content']) ?>"
                             data-tags="<?= esc(implode(', ', $noteTags)) ?>"
                             data-starred="<?= $note['is_starred'] ?>"
                             data-completed="<?= $note['is_completed'] ?>"
                             data-created-at="<?= date('M d, Y', strtotime($note['created_at'])) ?>">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <div class="text-truncate flex-grow-1 pe-2">
                                    <h6 class="mb-1 text-body fw-bold text-truncate <?= $note['is_completed'] ? 'text-decoration-line-through' : '' ?>">
                                        <?= esc($note['title']) ?>
                                    </h6>
                                    <small class="text-muted d-flex align-items-center gap-1 font-12">
                                        <i class="uil-folder font-13"></i> <?= esc($note['project_name'] ?? 'General') ?>
                                    </small>
                                </div>
                                <div class="note-meta text-end flex-shrink-0">
                                    <i class="fa<?= $note['is_starred'] ? 's' : 'r' ?> fa-star text-warning font-14"></i>
                                    <div class="small text-muted font-11 mt-1"><?= date('M d', strtotime($note['created_at'])) ?></div>
                                </div>
                            </div>
                            <p class="note-preview small text-muted mb-2 text-truncate" style="max-width: 100%;">
                                <?= esc(substr(strip_tags($note['content']), 0, 80)) ?><?= strlen($note['content']) > 80 ? '...' : '' ?>
                            </p>
                            <div class="note-tags d-flex flex-wrap gap-1">
                                <?php foreach ($noteTags as $tag): ?>
                                    <span class="badge bg-secondary-lighten text-secondary font-11">
                                        <i class="uil-tag-alt me-1"></i><?= esc($tag) ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <div class="avatar-lg bg-light rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3">
                                <i class="uil-notes font-28 text-muted"></i>
                            </div>
                            <h5>No notes yet</h5>
                            <p class="text-muted font-13 mb-0">Type in the editor to create your first scratch note.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php if (!empty($pager)): ?>
            <div class="card-footer bg-transparent border-top py-2">
                <?= $pager->links('notes', 'bootstrap_full') ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Note Editor Form -->
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow-sm border-0" style="min-height: 600px;">
            <div class="card-body d-flex flex-column p-4">
                <form action="<?= site_url('notes/store') ?>" method="POST" id="newNoteForm" class="d-flex flex-column h-100 flex-grow-1">
                    <?= csrf_field() ?>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="header-title mb-0">
                            <i class="uil-edit-alt me-1 text-primary"></i> Create Note
                        </h5>
                        <button type="submit" class="btn btn-primary rounded-pill btn-sm px-3" id="saveNoteBtn">
                            <i class="mdi mdi-content-save me-1"></i> Save Note
                        </button>
                    </div>

                    <div class="mb-3">
                        <input type="text" name="title" class="form-control form-control-lg fw-bold" id="noteTitle" placeholder="Note Title..." required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <select name="project_id" class="form-select" id="noteProject">
                                <option value="">General (No Project)</option>
                                <?php if (!empty($projects)): ?>
                                    <?php foreach ($projects as $project): ?>
                                    <?php 
                                        $pId = is_array($project) ? ($project['id'] ?? '') : ($project->id ?? '');
                                        $pName = is_array($project) ? ($project['name'] ?? '') : ($project->name ?? '');
                                    ?>
                                    <option value="<?= $pId ?>"><?= esc($pName) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="tags" class="form-control" id="noteTags" placeholder="Tags (comma separated, e.g. bug, idea)">
                        </div>
                    </div>

                    <div class="flex-grow-1 mb-3">
                        <textarea name="content" class="form-control h-100 font-monospace" id="noteContent" rows="12" placeholder="Start typing your note here... (Markdown supported)"></textarea>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-auto pt-2 border-top">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="markdownMode" checked>
                            <label class="form-check-label small text-muted" for="markdownMode">Markdown formatting enabled</label>
                        </div>
                        <div class="small text-muted font-12">
                            <i class="mdi mdi-information-outline me-1"></i> Click on any note in the list to view or edit it.
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Note View Modal -->
<div class="modal fade" id="noteModal" tabindex="-1" aria-labelledby="noteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="noteEditForm" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header border-bottom py-3">
                    <input type="text" name="title" id="modalNoteTitle" class="form-control form-control-lg border-0 fw-bold px-0 text-body bg-transparent" placeholder="Note Title" required>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label small text-muted mb-1">Project</label>
                            <select name="project_id" id="modalNoteProject" class="form-select form-select-sm">
                                <option value="">General</option>
                                <?php if (!empty($projects)): ?>
                                    <?php foreach ($projects as $project): ?>
                                    <?php 
                                        $pId = is_array($project) ? ($project['id'] ?? '') : ($project->id ?? '');
                                        $pName = is_array($project) ? ($project['name'] ?? '') : ($project->name ?? '');
                                    ?>
                                    <option value="<?= $pId ?>"><?= esc($pName) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted mb-1">Tags (comma separated)</label>
                            <input type="text" name="tags" id="modalNoteTags" class="form-control form-control-sm" placeholder="e.g. design, logic">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted mb-1">Created At</label>
                            <div id="modalNoteDate" class="form-control-plaintext small text-muted py-1"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted mb-1">Content (Markdown supported)</label>
                        <textarea name="content" id="modalNoteContent" class="form-control font-monospace" rows="10" placeholder="Write your note here..."></textarea>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-warning flex-fill" id="modalToggleStar">
                            <i class="far fa-star me-1"></i> Starred
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-success flex-fill" id="modalToggleComplete">
                            <i class="mdi mdi-check me-1"></i> Completed
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger flex-fill" id="modalDeleteBtn">
                            <i class="mdi mdi-trash-can-outline me-1"></i> Delete
                        </button>
                    </div>
                </div>
                <div class="modal-footer border-top py-2">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3"></div>

<!-- Notes JavaScript -->
<script>
    $(document).ready(function() {
        // Note item click -> Open Modal (Using Event Delegation)
        $(document).on('click', '.note-item', function() {
            const noteId = $(this).data('note-id');
            const title = $(this).data('title');
            const content = $(this).data('content');
            const projectId = $(this).data('project-id');
            const tags = $(this).data('tags');
            const isStarred = $(this).data('starred');
            const isCompleted = $(this).data('completed');
            const createdAt = $(this).data('created-at');

            // Fill Modal
            $('#modalNoteTitle').val(title);
            $('#modalNoteContent').val(content);
            $('#modalNoteProject').val(projectId);
            $('#modalNoteTags').val(tags);
            $('#modalNoteDate').text(createdAt);
            $('#noteEditForm').attr('action', '<?= site_url('notes/update/') ?>' + noteId);
            $('#modalDeleteBtn').attr('data-id', noteId);
            $('#modalToggleStar').attr('data-id', noteId);
            $('#modalToggleComplete').attr('data-id', noteId);

            // Update Button States
            updateModalButtonStates(isStarred, isCompleted);
            
            // Show Modal using Bootstrap 5 Native API
            const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('noteModal'));
            modal.show();
        });

        function updateModalButtonStates(isStarred, isCompleted) {
            if (isStarred) {
                $('#modalToggleStar').removeClass('btn-outline-warning').addClass('btn-warning text-dark');
                $('#modalToggleStar i').removeClass('far').addClass('fas');
            } else {
                $('#modalToggleStar').removeClass('btn-warning text-dark').addClass('btn-outline-warning');
                $('#modalToggleStar i').removeClass('fas').addClass('far');
            }

            if (isCompleted) {
                $('#modalToggleComplete').removeClass('btn-outline-success').addClass('btn-success');
            } else {
                $('#modalToggleComplete').removeClass('btn-success').addClass('btn-outline-success');
            }
        }

        // Star Toggle (AJAX)
        $('#modalToggleStar').click(function() {
            const id = $(this).data('id');
            $.post('<?= site_url('notes/star/') ?>' + id, {
                <?= csrf_token() ?>: '<?= csrf_hash() ?>'
            }, function(res) {
                if (res.status === 'success') {
                    updateModalButtonStates(res.is_starred, null);
                }
            });
        });

        // Complete Toggle (AJAX)
        $('#modalToggleComplete').click(function() {
            const id = $(this).data('id');
            $.post('<?= site_url('notes/complete/') ?>' + id, {
                <?= csrf_token() ?>: '<?= csrf_hash() ?>'
            }, function(res) {
                if (res.status === 'success') {
                    location.reload();
                }
            });
        });

        // Delete (Form Submission)
        $('#modalDeleteBtn').click(function() {
            if (confirm('Move this note to trash?')) {
                const id = $(this).data('id');
                const form = $('<form>', {
                    'method': 'POST',
                    'action': '<?= site_url('notes/delete/') ?>' + id
                }).append($('<input>', {
                    'type': 'hidden',
                    'name': '<?= csrf_token() ?>',
                    'value': '<?= csrf_hash() ?>'
                }));
                $('body').append(form);
                form.submit();
            }
        });

        // Note List Search
        $('#notesSearch').on('keyup', function() {
            const term = $(this).val().toLowerCase();
            $('.note-item').each(function() {
                const text = $(this).text().toLowerCase();
                $(this).toggle(text.includes(term));
            });
        });

        // New Note Button
        $('#newNoteBtn').click(function() {
            $('#noteTitle').focus();
            $('html, body').animate({
                scrollTop: $("#noteTitle").offset().top - 100
            }, 500);
        });

        // Toast notification function
        function showToast(message, type = 'info') {
            const toastId = 'toast-' + Date.now();
            const toastHtml = `
                <div id="${toastId}" class="toast align-items-center text-bg-${type} border-0" role="alert">
                    <div class="d-flex">
                        <div class="toast-body">${message}</div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>`;
            $('.toast-container').append(toastHtml);
            new bootstrap.Toast(document.getElementById(toastId)).show();
        }
    });
</script>

<?= $this->endSection() ?>
