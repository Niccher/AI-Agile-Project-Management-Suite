<?= $this->extend('layouts/hyper/main') ?>

<?= $this->section('title') ?><?= $isSoloMode ? 'Account & Team Settings' : 'User Management' ?> • <?= esc(setting('App.siteName')) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid py-3">

    <!-- Header -->
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="page-title mb-0">
                    <i class="uil-users-alt text-primary me-2"></i> 
                    <?= $isSoloMode ? 'Account Profile & Team Expansion' : 'User & Access Management' ?>
                </h4>
                <p class="text-muted font-13 mb-0">
                    <?= $isSoloMode 
                        ? 'Manage your personal account settings or invite team members to transition to Team Mode.' 
                        : 'Manage team accounts, assign roles, and dispatch email invitations.' ?>
                </p>
            </div>
            <div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#inviteUserModal">
                    <i class="mdi mdi-email-plus me-1"></i> Invite Member
                </button>
                <a href="<?= site_url('admin/settings?tab=email') ?>" class="btn btn-outline-secondary ms-1" title="Configure SMTP Email Delivery">
                    <i class="mdi mdi-email-fast-outline me-1"></i> SMTP Settings
                </a>
            </div>
        </div>
    </div>

    <!-- Alerts -->
    <?php if (session()->has('message')) : ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="mdi mdi-check-circle me-2"></i>
            <?= session('message') ?>
            <?php if (session()->has('invite_link')): ?>
                <div class="mt-2 input-group input-group-sm">
                    <input type="text" class="form-control" id="flashInviteLink" value="<?= esc(session('invite_link')) ?>" readonly>
                    <button class="btn btn-dark" type="button" onclick="navigator.clipboard.writeText('<?= esc(session('invite_link')) ?>'); Swal.fire('Copied!', 'Invite link copied to clipboard', 'success');">
                        <i class="mdi mdi-content-copy me-1"></i> Copy Link
                    </button>
                </div>
            <?php endif; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->has('error')) : ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="mdi mdi-alert-circle me-2"></i>
            <?= session('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($isSoloMode): ?>
        <!-- SOLO MODE DASHBOARD -->
        <div class="row g-3 mb-4">
            <div class="col-lg-7">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3 border-0">
                        <h5 class="mb-0 fw-bold"><i class="mdi mdi-account-star text-warning me-2"></i> Solo Administrator Profile</h5>
                    </div>
                    <div class="card-body pt-0">
                        <div class="d-flex align-items-center mb-4 p-3 bg-light rounded">
                            <div class="avatar-lg bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3 font-24 fw-bold" style="width: 56px; height: 56px;">
                                <?= strtoupper(substr(auth()->user()->username ?? 'U', 0, 1)) ?>
                            </div>
                            <div>
                                <h5 class="mb-1"><?= esc(auth()->user()->username) ?> <span class="badge bg-success ms-1">Active Admin</span></h5>
                                <p class="text-muted font-13 mb-0"><i class="mdi mdi-email-outline me-1"></i><?= esc(auth()->user()->email) ?></p>
                            </div>
                        </div>

                        <div class="alert alert-info border-0 mb-0">
                            <h6 class="fw-bold mb-1"><i class="mdi mdi-information-outline me-1"></i> Solo Programmer Mode is Active</h6>
                            <p class="font-13 mb-0">
                                Since you are currently the only active user in the system, you hold full composite privileges (Admin, Manager, and Developer). 
                                Team-oriented ceremonies are streamlined into personal workflows.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card shadow-sm border-0 border-start border-primary border-4 h-100">
                    <div class="card-body">
                        <h5 class="fw-bold mb-2"><i class="mdi mdi-account-group text-primary me-2"></i> Ready to Collaborate?</h5>
                        <p class="text-muted font-13 mb-3">
                            You can invite colleagues or clients at any time. When a new user accepts their invitation, the workspace will smoothly unlock team-wide sprint workflows, approvals, and assignee swimlanes.
                        </p>
                        <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#inviteUserModal">
                            <i class="mdi mdi-email-plus me-1"></i> Send Team Invitation
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- USERS TABLE -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><i class="mdi mdi-account-multiple text-primary me-2"></i> Active Workspace Users (<?= count($users) ?>)</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 font-13">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">User</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Last Active</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <?php 
                                $userEntity = auth()->getProvider()->findById($u->id);
                                $groups = $userEntity ? $userEntity->getGroups() : [];
                                $userRole = !empty($groups) ? $groups[0] : 'user';
                                $isSelf = ($u->id == auth()->id());
                            ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar bg-primary-lighten text-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 34px; height: 34px; font-weight: bold;">
                                            <?= strtoupper(substr($u->username ?? 'U', 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-dark"><?= esc($u->username) ?> <?= $isSelf ? '<span class="badge bg-secondary font-10">You</span>' : '' ?></div>
                                            <div class="text-muted font-12"><?= esc($u->email ?? '') ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($isSelf): ?>
                                        <span class="badge bg-primary px-2 py-1"><?= ucfirst($userRole) ?></span>
                                    <?php else: ?>
                                        <form action="<?= site_url('admin/users/' . $u->id . '/role') ?>" method="POST" class="d-inline">
                                            <?= csrf_field() ?>
                                            <select name="role" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">
                                                <option value="user" <?= $userRole === 'user' ? 'selected' : '' ?>>User / Developer</option>
                                                <option value="manager" <?= $userRole === 'manager' ? 'selected' : '' ?>>Manager</option>
                                                <option value="admin" <?= $userRole === 'admin' ? 'selected' : '' ?>>Administrator</option>
                                            </select>
                                        </form>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($u->active): ?>
                                        <span class="badge bg-success-lighten text-success"><i class="mdi mdi-check-circle-outline me-1"></i>Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-lighten text-danger"><i class="mdi mdi-close-circle-outline me-1"></i>Deactivated</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted"><?= $u->last_active ? esc($u->last_active) : 'Never' ?></td>
                                <td class="text-end pe-4">
                                    <?php if (!$isSelf): ?>
                                        <form action="<?= site_url('admin/users/' . $u->id . '/deactivate') ?>" method="POST" class="d-inline">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-xs <?= $u->active ? 'btn-outline-danger' : 'btn-outline-success' ?>" onclick="return confirm('Change status for this user?');">
                                                <?= $u->active ? '<i class="mdi mdi-account-off me-1"></i>Deactivate' : '<i class="mdi mdi-account-check me-1"></i>Activate' ?>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted font-12">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- PENDING INVITATIONS -->
    <?php if (!empty($pendingInvites)): ?>
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold"><i class="mdi mdi-clock-outline text-warning me-2"></i> Pending Invitations (<?= count($pendingInvites) ?>)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 font-13">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Recipient Email</th>
                                <th>Assigned Role</th>
                                <th>Sent Date</th>
                                <th>Expires At</th>
                                <th class="text-end pe-4">Invite Link & Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingInvites as $inv): ?>
                                <?php $invLink = site_url('auth/invite/' . $inv['token']); ?>
                                <tr>
                                    <td class="ps-4 fw-semibold"><i class="mdi mdi-email-outline text-muted me-1"></i><?= esc($inv['email']) ?></td>
                                    <td><span class="badge bg-info-lighten text-info"><?= ucfirst($inv['role']) ?></span></td>
                                    <td class="text-muted"><?= esc($inv['created_at']) ?></td>
                                    <td class="text-muted"><?= esc($inv['expires_at']) ?></td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-xs btn-outline-primary me-1" onclick="navigator.clipboard.writeText('<?= esc($invLink) ?>'); Swal.fire('Copied!', 'Invitation link copied to clipboard', 'success');">
                                            <i class="mdi mdi-content-copy me-1"></i> Copy Link
                                        </button>
                                        <form action="<?= site_url('admin/users/invite/revoke/' . $inv['id']) ?>" method="POST" class="d-inline">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-xs btn-outline-danger" onclick="return confirm('Revoke this invite?');">
                                                <i class="mdi mdi-trash-can-outline me-1"></i> Revoke
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>

<!-- Invite User Modal -->
<div class="modal fade" id="inviteUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="mdi mdi-email-plus text-primary me-2"></i> Invite Team Member</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= site_url('admin/users/invite') ?>" method="POST" id="inviteUserForm">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="invite_email" class="form-label fw-semibold font-13">User Email Address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="invite_email" name="email" placeholder="colleague@example.com" required>
                        <div class="form-text font-12 text-muted">An email invite will be sent via SMTP, and an instant copyable link will also be generated.</div>
                    </div>

                    <div class="mb-3">
                        <label for="invite_role" class="form-label fw-semibold font-13">Role & Permissions <span class="text-danger">*</span></label>
                        <select class="form-select" id="invite_role" name="role">
                            <option value="user" selected>User / Developer (Projects, Tasks, Time, Wiki)</option>
                            <option value="manager">Manager (Team Dashboard, Approvals, Reports)</option>
                            <option value="admin">Administrator (Full System Configuration)</option>
                        </select>
                    </div>

                    <!-- Instant Link Result Placeholder -->
                    <div id="inviteResult" class="d-none alert alert-success mt-3 mb-0">
                        <div class="fw-bold mb-1"><i class="mdi mdi-check-circle me-1"></i> Invitation Created!</div>
                        <p class="font-12 mb-2" id="inviteResultMessage"></p>
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control" id="generatedInviteLink" readonly>
                            <button class="btn btn-dark" type="button" id="copyGenLinkBtn">
                                <i class="mdi mdi-content-copy me-1"></i> Copy
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="sendInviteBtn">
                        <i class="mdi mdi-send me-1"></i> Generate & Send Invite
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const inviteForm = document.getElementById('inviteUserForm');
    if (inviteForm) {
        inviteForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('sendInviteBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Processing...';

            const fd = new FormData(inviteForm);
            fetch(inviteForm.action, {
                method: 'POST',
                body: fd,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(res => {
                btn.disabled = false;
                btn.innerHTML = '<i class="mdi mdi-send me-1"></i> Generate & Send Invite';

                if (res.status === 'success') {
                    const resBox = document.getElementById('inviteResult');
                    const msgEl = document.getElementById('inviteResultMessage');
                    const linkEl = document.getElementById('generatedInviteLink');
                    
                    resBox.classList.remove('d-none');
                    msgEl.textContent = res.message;
                    linkEl.value = res.invite_link;

                    document.getElementById('copyGenLinkBtn').onclick = function() {
                        navigator.clipboard.writeText(res.invite_link);
                        Swal.fire({ icon: 'success', title: 'Copied!', text: 'Invite link copied to clipboard.', timer: 1500, showConfirmButton: false });
                    };
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: res.message });
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = '<i class="mdi mdi-send me-1"></i> Generate & Send Invite';
                Swal.fire({ icon: 'error', title: 'Error', text: err.message });
            });
        });
    }
});
</script>

<?= $this->endSection() ?>
