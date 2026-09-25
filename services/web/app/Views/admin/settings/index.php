<?= $this->extend('layouts/hyper/main') ?>

<?= $this->section('title') ?>System Settings • <?= esc(setting('App.siteName')) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid py-2">
    <!-- Page Header -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title mb-0">
                    <i class="uil-sliders-v-alt text-primary me-2"></i> System & Platform Configuration
                </h4>
                <p class="text-muted font-13 mb-0">Manage global workspace properties, session security, email delivery, and maintenance modes.</p>
            </div>
        </div>
    </div>

    <!-- Alert notifications -->
    <?php if (session()->has('message')) : ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="mdi mdi-check-circle me-2"></i>
            <?= session('message') ?>
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

    <div class="row">
        <!-- Settings Nav Pills (Left Sidebar) -->
        <div class="col-lg-3 col-xl-2 mb-3">
            <div class="card shadow-sm border-0">
                <div class="card-body p-2">
                    <div class="nav flex-column nav-pills" id="settingsTabs" role="tablist">
                        <a class="nav-link text-start py-2 px-3 <?= $currentTab === 'general' ? 'active' : '' ?>" href="<?= site_url('admin/settings?tab=general') ?>">
                            <i class="mdi mdi-tune me-2 font-16"></i> General
                        </a>
                        <a class="nav-link text-start py-2 px-3 <?= $currentTab === 'security' ? 'active' : '' ?>" href="<?= site_url('admin/settings?tab=security') ?>">
                            <i class="mdi mdi-shield-lock-outline me-2 font-16"></i> Security & Auth
                        </a>
                        <a class="nav-link text-start py-2 px-3 <?= $currentTab === 'email' ? 'active' : '' ?>" href="<?= site_url('admin/settings?tab=email') ?>">
                            <i class="mdi mdi-email-outline me-2 font-16"></i> Email / SMTP
                        </a>
                        <a class="nav-link text-start py-2 px-3 <?= $currentTab === 'project' ? 'active' : '' ?>" href="<?= site_url('admin/settings?tab=project') ?>">
                            <i class="mdi mdi-clipboard-check-outline me-2 font-16"></i> Workflows
                        </a>
                        <a class="nav-link text-start py-2 px-3 <?= $currentTab === 'maintenance' ? 'active' : '' ?>" href="<?= site_url('admin/settings?tab=maintenance') ?>">
                            <i class="mdi mdi-alert-octagon-outline me-2 font-16"></i> Maintenance
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Settings Tab Content Panes -->
        <div class="col-lg-9 col-xl-10">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">

                    <?php if ($currentTab === 'general'): ?>
                        <!-- 1. GENERAL SETTINGS -->
                        <h5 class="fw-bold text-body mb-3">
                            <i class="mdi mdi-tune text-primary me-2"></i> General Workspace Settings
                        </h5>
                        <form action="<?= site_url('admin/settings/update') ?>" method="POST">
                            <?= csrf_field() ?>
                            <input type="hidden" name="tab" value="general">

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold" for="site_name">Application Brand Name <span class="text-danger">*</span></label>
                                    <input type="text" id="site_name" name="site_name" class="form-control" value="<?= esc(setting('App.siteName')) ?>" required />
                                    <div class="form-text font-12 text-muted">Displayed in navigation bars, tab titles, and system footers.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold" for="support_email">Official Support Email</label>
                                    <input type="email" id="support_email" name="support_email" class="form-control" value="<?= esc(setting('App.supportEmail') ?? 'support@chege.local') ?>" />
                                    <div class="form-text font-12 text-muted">Used as the contact email for system inquiries.</div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold" for="site_desc">Workspace Tagline / Description</label>
                                <input type="text" id="site_desc" name="site_desc" class="form-control" value="<?= esc(setting('App.siteDesc') ?? 'Agile Project Management & Team Collaboration Platform') ?>" />
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold" for="timezone">Default System Timezone</label>
                                <select id="timezone" name="timezone" class="form-select">
                                    <option value="UTC" <?= (setting('App.defaultTimezone') === 'UTC') ? 'selected' : '' ?>>UTC (Coordinated Universal Time)</option>
                                    <option value="Africa/Nairobi" <?= (setting('App.defaultTimezone') === 'Africa/Nairobi' || !setting('App.defaultTimezone')) ? 'selected' : '' ?>>Africa/Nairobi (EAT, UTC+3)</option>
                                    <option value="America/New_York" <?= (setting('App.defaultTimezone') === 'America/New_York') ? 'selected' : '' ?>>America/New_York (EST/EDT)</option>
                                    <option value="Europe/London" <?= (setting('App.defaultTimezone') === 'Europe/London') ? 'selected' : '' ?>>Europe/London (GMT/BST)</option>
                                </select>
                            </div>

                            <div class="text-end border-top pt-3">
                                <button class="btn btn-primary px-4" type="submit">
                                    <i class="mdi mdi-content-save me-1"></i> Save General Settings
                                </button>
                            </div>
                        </form>

                    <?php elseif ($currentTab === 'security'): ?>
                        <!-- 2. SECURITY & AUTH SETTINGS -->
                        <h5 class="fw-bold text-body mb-3">
                            <i class="mdi mdi-shield-lock text-primary me-2"></i> Security, Sessions & Authentication Rules
                        </h5>
                        <form action="<?= site_url('admin/settings/update') ?>" method="POST">
                            <?= csrf_field() ?>
                            <input type="hidden" name="tab" value="security">

                            <div class="mb-3">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="allow_registration" name="allow_registration" value="1" <?= setting('Auth.allowRegistration') !== 0 ? 'checked' : '' ?>>
                                    <label class="form-check-label fw-bold" for="allow_registration">Enable Public User Registration</label>
                                </div>
                                <div class="form-text font-12 text-muted ps-4">When disabled, new accounts can only be provisioned by System Administrators.</div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="require_verification" name="require_verification" value="1" <?= setting('Auth.requireEmailVerification') ? 'checked' : '' ?>>
                                    <label class="form-check-label fw-bold" for="require_verification">Require Email Verification on Registration</label>
                                </div>
                                <div class="form-text font-12 text-muted ps-4">Forces newly registered users to confirm their email address before accessing project workspaces.</div>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold" for="max_login_attempts">Max Failed Login Attempts</label>
                                    <input type="number" id="max_login_attempts" name="max_login_attempts" class="form-control" min="1" max="20" value="<?= (int)(setting('Auth.maxLoginAttempts') ?? 5) ?>">
                                    <div class="form-text font-12 text-muted">Attempts before account lock.</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold" for="lockout_minutes">Lockout Duration (Minutes)</label>
                                    <input type="number" id="lockout_minutes" name="lockout_minutes" class="form-control" min="1" max="1440" value="<?= (int)(setting('Auth.lockoutMinutes') ?? 15) ?>">
                                    <div class="form-text font-12 text-muted">Temporary lockout cool-off time.</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold" for="session_timeout">Database Session Lifetime (Secs)</label>
                                    <input type="number" id="session_timeout" name="session_timeout" class="form-control" min="300" max="864000" value="<?= (int)(setting('Auth.sessionTimeout') ?? 7200) ?>">
                                    <div class="form-text font-12 text-muted">Stored in <code>ci_sessions</code> table.</div>
                                </div>
                            </div>

                            <div class="text-end border-top pt-3">
                                <button class="btn btn-primary px-4" type="submit">
                                    <i class="mdi mdi-content-save me-1"></i> Save Security Rules
                                </button>
                            </div>
                        </form>

                    <?php elseif ($currentTab === 'email'): ?>
                        <!-- 3. EMAIL & SMTP SETTINGS -->
                        <h5 class="fw-bold text-body mb-3">
                            <i class="mdi mdi-email-fast text-primary me-2"></i> Outbound Email & SMTP Gateway Configuration
                        </h5>
                        <form action="<?= site_url('admin/settings/update') ?>" method="POST" class="mb-4">
                            <?= csrf_field() ?>
                            <input type="hidden" name="tab" value="email">

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold" for="from_email">Sender Email Address <span class="text-danger">*</span></label>
                                    <input type="email" id="from_email" name="from_email" class="form-control" value="<?= esc(setting('Email.fromEmail') ?? 'notifications@chege.local') ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold" for="from_name">Sender Display Name <span class="text-danger">*</span></label>
                                    <input type="text" id="from_name" name="from_name" class="form-control" value="<?= esc(setting('Email.fromName') ?? setting('App.siteName')) ?>" required>
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold" for="protocol">Mail Transport Protocol</label>
                                    <select id="protocol" name="protocol" class="form-select">
                                        <option value="smtp" <?= (setting('Email.protocol') === 'smtp' || !setting('Email.protocol')) ? 'selected' : '' ?>>SMTP (Recommended)</option>
                                        <option value="mail" <?= (setting('Email.protocol') === 'mail') ? 'selected' : '' ?>>PHP Native Mail</option>
                                        <option value="sendmail" <?= (setting('Email.protocol') === 'sendmail') ? 'selected' : '' ?>>Sendmail</option>
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label fw-bold" for="smtp_host">SMTP Server Host</label>
                                    <input type="text" id="smtp_host" name="smtp_host" class="form-control font-monospace" placeholder="smtp.mailtrap.io or smtp.gmail.com" value="<?= esc(setting('Email.SMTPHost') ?? 'localhost') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold" for="smtp_port">SMTP Port</label>
                                    <input type="number" id="smtp_port" name="smtp_port" class="form-control" value="<?= (int)(setting('Email.SMTPPort') ?? 587) ?>">
                                </div>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-5">
                                    <label class="form-label fw-bold" for="smtp_user">SMTP Username</label>
                                    <input type="text" id="smtp_user" name="smtp_user" class="form-control" placeholder="API key or SMTP user" value="<?= esc(setting('Email.SMTPUser') ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold" for="smtp_pass">SMTP Password / API Key</label>
                                    <div class="input-group">
                                        <input type="password" id="smtp_pass" name="smtp_pass" class="form-control" placeholder="<?= setting('Email.SMTPPass') ? '••••••••••••' : 'Enter SMTP password' ?>">
                                    </div>
                                    <div class="form-text font-11 text-muted">Leave blank to keep existing password.</div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold" for="smtp_crypto">Security Protocol</label>
                                    <select id="smtp_crypto" name="smtp_crypto" class="form-select">
                                        <option value="tls" <?= (setting('Email.SMTPCrypto') === 'tls' || !setting('Email.SMTPCrypto')) ? 'selected' : '' ?>>TLS (Port 587)</option>
                                        <option value="ssl" <?= (setting('Email.SMTPCrypto') === 'ssl') ? 'selected' : '' ?>>SSL (Port 465)</option>
                                        <option value="none" <?= (setting('Email.SMTPCrypto') === 'none') ? 'selected' : '' ?>>None (Port 25)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="text-end border-top pt-3">
                                <button class="btn btn-primary px-4" type="submit">
                                    <i class="mdi mdi-content-save me-1"></i> Save SMTP Configuration
                                </button>
                            </div>
                        </form>

                        <!-- Send Test Email Tool -->
                        <div class="card bg-light border-0 mt-4">
                            <div class="card-body p-3">
                                <h6 class="fw-bold mb-2 text-dark">
                                    <i class="mdi mdi-send-check text-success me-1"></i> Dispatch Test Email
                                </h6>
                                <p class="text-muted font-13 mb-3">Send a styled verification email to confirm that your SMTP credentials and delivery routing are functioning properly.</p>
                                <form action="<?= site_url('admin/settings/send-test-email') ?>" method="POST" class="row g-2 align-items-center">
                                    <?= csrf_field() ?>
                                    <div class="col-md-8 col-lg-6">
                                        <input type="email" name="test_recipient" class="form-control form-control-sm" placeholder="recipient@example.com" value="<?= esc(auth()->user()->email ?? '') ?>" required>
                                    </div>
                                    <div class="col-auto">
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="mdi mdi-paper-plane me-1"></i> Send Test Message
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                    <?php elseif ($currentTab === 'project'): ?>
                        <!-- 4. WORKFLOWS & PROJECT SETTINGS -->
                        <h5 class="fw-bold text-body mb-3">
                            <i class="mdi mdi-clipboard-check text-primary me-2"></i> Agile Workflows & Project Defaults
                        </h5>
                        <form action="<?= site_url('admin/settings/update') ?>" method="POST">
                            <?= csrf_field() ?>
                            <input type="hidden" name="tab" value="project">

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold" for="default_priority">Default Task Priority</label>
                                    <select id="default_priority" name="default_priority" class="form-select">
                                        <option value="low" <?= (setting('Project.defaultPriority') === 'low') ? 'selected' : '' ?>>Low</option>
                                        <option value="medium" <?= (setting('Project.defaultPriority') === 'medium' || !setting('Project.defaultPriority')) ? 'selected' : '' ?>>Medium</option>
                                        <option value="high" <?= (setting('Project.defaultPriority') === 'high') ? 'selected' : '' ?>>High</option>
                                        <option value="critical" <?= (setting('Project.defaultPriority') === 'critical') ? 'selected' : '' ?>>Critical</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold" for="max_attachment_mb">Max Task Attachment Size (MB)</label>
                                    <input type="number" id="max_attachment_mb" name="max_attachment_mb" class="form-control" min="1" max="100" value="<?= (int)(setting('Project.maxAttachmentMb') ?? 10) ?>">
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="require_review" name="require_review" value="1" <?= setting('Project.requireReview') ? 'checked' : '' ?>>
                                    <label class="form-check-label fw-bold" for="require_review">Enforce Manager Approval Before Marking Tasks Done</label>
                                </div>
                                <div class="form-text font-12 text-muted ps-4">Requires completed developer tickets to pass through the Manager Approvals Queue before moving to Done.</div>
                            </div>

                            <div class="text-end border-top pt-3">
                                <button class="btn btn-primary px-4" type="submit">
                                    <i class="mdi mdi-content-save me-1"></i> Save Workflow Defaults
                                </button>
                            </div>
                        </form>

                    <?php elseif ($currentTab === 'maintenance'): ?>
                        <!-- 5. MAINTENANCE & BACKUPS -->
                        <h5 class="fw-bold text-body mb-3">
                            <i class="mdi mdi-alert-octagon text-danger me-2"></i> Platform Maintenance Controls
                        </h5>
                        <form action="<?= site_url('admin/settings/update') ?>" method="POST" class="mb-4">
                            <?= csrf_field() ?>
                            <input type="hidden" name="tab" value="maintenance">

                            <div class="alert alert-warning d-flex align-items-center mb-3">
                                <i class="mdi mdi-alert-outline font-22 me-2"></i>
                                <div>
                                    <strong>Caution:</strong> Enabling Maintenance Mode blocks non-administrator access and displays the custom maintenance notice across all landing and workspace routes.
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="maintenance_mode" name="maintenance_mode" value="1" <?= setting('App.maintenanceMode') ? 'checked' : '' ?>>
                                    <label class="form-check-label fw-bold text-danger" for="maintenance_mode">Activate System Maintenance Mode</label>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold" for="maintenance_notice">Maintenance Notice Broadcast</label>
                                <textarea id="maintenance_notice" name="maintenance_notice" rows="2" class="form-control" placeholder="We are currently performing scheduled maintenance. Please check back shortly."><?= esc(setting('App.maintenanceNotice') ?? 'We are currently performing scheduled system updates. Service will resume shortly.') ?></textarea>
                            </div>

                            <div class="text-end border-top pt-3">
                                <button class="btn btn-danger px-4" type="submit">
                                    <i class="mdi mdi-content-save me-1"></i> Update Maintenance Status
                                </button>
                            </div>
                        </form>

                        <!-- Database Backups Manager -->
                        <div class="border-top pt-4 mt-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <h5 class="fw-bold mb-1"><i class="mdi mdi-database-export text-primary me-2"></i> Database Snapshots & Backups</h5>
                                    <p class="text-muted font-13 mb-0">Generate instant compressed SQL database dumps or automate via CLI/Cron: <code>php spark db:backup</code></p>
                                </div>
                                <form action="<?= site_url('admin/settings/backup/create') ?>" method="POST" class="d-inline">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-primary rounded-pill shadow-sm">
                                        <i class="mdi mdi-plus-circle me-1"></i> Create Backup Now
                                    </button>
                                </form>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 font-13 border rounded">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-3">Backup File</th>
                                            <th>Size</th>
                                            <th>Created</th>
                                            <th class="text-end pe-3">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($backups)): ?>
                                            <tr>
                                                <td colspan="4" class="text-center py-4 text-muted">
                                                    <i class="mdi mdi-folder-zip-outline fa-2x mb-1 d-block opacity-50"></i>
                                                    No database snapshots generated yet. Click <strong>Create Backup Now</strong> above.
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($backups as $b): ?>
                                                <tr>
                                                    <td class="ps-3">
                                                        <i class="mdi mdi-file-document-outline text-primary me-1"></i>
                                                        <code><?= esc($b['filename']) ?></code>
                                                    </td>
                                                    <td><span class="badge bg-light text-secondary"><?= esc($b['size']) ?></span></td>
                                                    <td><?= esc($b['created']) ?></td>
                                                    <td class="text-end pe-3">
                                                        <a href="<?= site_url('admin/settings/backup/download/' . urlencode($b['filename'])) ?>" class="btn btn-xs btn-outline-primary py-1 px-2">
                                                            <i class="mdi mdi-download me-1"></i> Download
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
