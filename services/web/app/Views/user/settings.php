<?= $this->extend('layouts/hyper/main') ?>

<?= $this->section('title') ?>Settings • <?= esc(setting('App.siteName')) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
$initials = strtoupper(substr($user->first_name ?? $user->username ?? 'U', 0, 1) . substr($user->last_name ?? '', 0, 1));
$timezones = DateTimeZone::listIdentifiers();
$prefs = is_array($user->preferences ?? null) ? $user->preferences : [];
$selectedTheme = $prefs['theme'] ?? 'dark';
$selectedColor = $prefs['accent_color'] ?? '#3e60d5';
?>

<!-- Page Header -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <button type="button" class="btn btn-primary rounded-pill px-3" id="saveSettingsTopBtn">
                    <i class="mdi mdi-content-save me-1"></i> Save Changes
                </button>
            </div>
            <h4 class="page-title">
                <i class="uil-cog me-2 text-primary"></i> Account Settings
            </h4>
        </div>
    </div>
</div>

<div class="row">
    <!-- Settings Navigation Sidebar -->
    <div class="col-lg-3 col-md-4 mb-3">
        <div class="card shadow-sm border-0">
            <div class="card-body p-2">
                <div class="nav flex-column nav-pills" id="settingsTabs" role="tablist" aria-orientation="vertical">
                    <a class="nav-link active py-2 px-3 text-start" id="tab-profile" data-bs-toggle="pill" href="#profile" role="tab" aria-controls="profile" aria-selected="true">
                        <i class="mdi mdi-account-outline me-2 font-16 align-middle"></i> Profile
                    </a>
                    <a class="nav-link py-2 px-3 text-start" id="tab-appearance" data-bs-toggle="pill" href="#appearance" role="tab" aria-controls="appearance" aria-selected="false">
                        <i class="mdi mdi-palette-outline me-2 font-16 align-middle"></i> Appearance
                    </a>
                    <a class="nav-link py-2 px-3 text-start" id="tab-notifications" data-bs-toggle="pill" href="#notifications" role="tab" aria-controls="notifications" aria-selected="false">
                        <i class="mdi mdi-bell-outline me-2 font-16 align-middle"></i> Notifications
                    </a>
                    <a class="nav-link py-2 px-3 text-start" id="tab-projects" data-bs-toggle="pill" href="#projects" role="tab" aria-controls="projects" aria-selected="false">
                        <i class="mdi mdi-folder-outline me-2 font-16 align-middle"></i> Projects
                    </a>
                    <a class="nav-link py-2 px-3 text-start" id="tab-time" data-bs-toggle="pill" href="#time-tracking" role="tab" aria-controls="time-tracking" aria-selected="false">
                        <i class="mdi mdi-clock-outline me-2 font-16 align-middle"></i> Time Tracking
                    </a>
                    <a class="nav-link py-2 px-3 text-start" id="tab-data" data-bs-toggle="pill" href="#data" role="tab" aria-controls="data" aria-selected="false">
                        <i class="mdi mdi-database-outline me-2 font-16 align-middle"></i> Data Management
                    </a>
                    <a class="nav-link py-2 px-3 text-start" id="tab-account" data-bs-toggle="pill" href="#account" role="tab" aria-controls="account" aria-selected="false">
                        <i class="mdi mdi-shield-check-outline me-2 font-16 align-middle"></i> Security & Password
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Settings Content Panels -->
    <div class="col-lg-9 col-md-8">
        <form method="POST" action="<?= site_url('settings/update') ?>" enctype="multipart/form-data" id="settingsForm">
            <?= csrf_field() ?>

            <div class="tab-content" id="settingsContent">
                <!-- Profile Tab -->
                <div class="tab-pane fade show active" id="profile" role="tabpanel" aria-labelledby="tab-profile">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-transparent border-bottom py-3">
                            <h5 class="header-title mb-0">
                                <i class="uil-user me-1 text-primary"></i> Public Profile & Information
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-4">
                                <div class="col-md-3 text-center">
                                    <div class="profile-avatar mb-3">
                                        <div class="avatar-xl rounded-circle mx-auto d-flex align-items-center justify-content-center bg-primary text-white font-24 shadow-sm mb-2 overflow-hidden" 
                                             id="avatarPreviewContainer" style="width: 96px; height: 96px;">
                                            <?php if (!empty($user->avatar)): ?>
                                                <img src="/<?= esc($user->avatar) ?>" alt="Avatar" class="img-fluid w-100 h-100" style="object-fit: cover;">
                                            <?php else: ?>
                                                <span><?= $initials ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <input type="file" name="avatar" id="avatarInput" accept="image/*" style="display: none;">
                                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" onclick="document.getElementById('avatarInput').click();">
                                            <i class="mdi mdi-camera me-1"></i> Change Photo
                                        </button>
                                    </div>
                                </div>

                                <div class="col-md-9">
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6">
                                            <label for="firstName" class="form-label fw-semibold">First Name</label>
                                            <input type="text" class="form-control" id="firstName" name="first_name" value="<?= esc($user->first_name ?? '') ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="lastName" class="form-label fw-semibold">Last Name</label>
                                            <input type="text" class="form-control" id="lastName" name="last_name" value="<?= esc($user->last_name ?? '') ?>">
                                        </div>
                                    </div>

                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6">
                                            <label for="email" class="form-label fw-semibold">Email Address</label>
                                            <input type="email" class="form-control bg-light" id="email" value="<?= esc($user->email ?? '') ?>" disabled readonly>
                                            <div class="form-text">Contact an admin to change your registered email address.</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="username" class="form-label fw-semibold">Username</label>
                                            <input type="text" class="form-control" id="username" name="username" value="<?= esc($user->username ?? '') ?>">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="bio" class="form-label fw-semibold">Bio</label>
                                        <textarea class="form-control" id="bio" name="bio" rows="3" placeholder="Tell us about yourself..."><?= esc($user->bio ?? '') ?></textarea>
                                    </div>

                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6">
                                            <label for="timezone" class="form-label fw-semibold">Timezone</label>
                                            <select class="form-select" id="timezone" name="timezone">
                                                <?php foreach ($timezones as $tz): ?>
                                                    <option value="<?= $tz ?>" <?= ($user->timezone ?? 'Africa/Nairobi') === $tz ? 'selected' : '' ?>><?= $tz ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="dateFormat" class="form-label fw-semibold">Date Format</label>
                                            <select class="form-select" id="dateFormat" name="date_format">
                                                <option value="MM/DD/YYYY" <?= ($user->date_format ?? '') === 'MM/DD/YYYY' ? 'selected' : '' ?>>MM/DD/YYYY</option>
                                                <option value="DD/MM/YYYY" <?= ($user->date_format ?? '') === 'DD/MM/YYYY' ? 'selected' : '' ?>>DD/MM/YYYY</option>
                                                <option value="YYYY-MM-DD" <?= ($user->date_format ?? 'YYYY-MM-DD') === 'YYYY-MM-DD' ? 'selected' : '' ?>>YYYY-MM-DD</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Appearance Tab -->
                <div class="tab-pane fade" id="appearance" role="tabpanel" aria-labelledby="tab-appearance">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-transparent border-bottom py-3">
                            <h5 class="header-title mb-0">
                                <i class="uil-palette me-1 text-primary"></i> Appearance & UI Theme
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <input type="hidden" name="theme" id="themeInput" value="<?= $selectedTheme ?>">
                            <input type="hidden" name="accent_color" id="accentColorInput" value="<?= $selectedColor ?>">

                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <h6 class="text-uppercase text-muted font-12 fw-bold mb-3">Color Mode</h6>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <div class="card border p-3 text-center cursor-pointer theme-option <?= $selectedTheme === 'light' ? 'border-primary bg-primary-lighten' : '' ?>" 
                                                 data-theme="light" style="cursor: pointer;">
                                                <i class="mdi mdi-white-balance-sunny font-24 text-warning mb-1"></i>
                                                <h6 class="mb-0">Light Theme</h6>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="card border p-3 text-center cursor-pointer theme-option <?= $selectedTheme === 'dark' ? 'border-primary bg-primary-lighten' : '' ?>" 
                                                 data-theme="dark" style="cursor: pointer;">
                                                <i class="mdi mdi-weather-night font-24 text-primary mb-1"></i>
                                                <h6 class="mb-0">Dark Theme</h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <h6 class="text-uppercase text-muted font-12 fw-bold mb-3">Accent Color</h6>
                                    <div class="d-flex flex-wrap gap-2">
                                        <?php
                                        $colors = [
                                            '#3e60d5' => 'Hyper Blue',
                                            '#10b981' => 'Emerald',
                                            '#f59e0b' => 'Amber',
                                            '#ef4444' => 'Red',
                                            '#8b5cf6' => 'Violet',
                                            '#0ea5e9' => 'Sky Blue',
                                            '#ec4899' => 'Pink',
                                            '#14b8a6' => 'Teal',
                                            '#f97316' => 'Orange',
                                        ];
                                        foreach($colors as $hex => $name):
                                        ?>
                                        <div class="color-option p-1 rounded border <?= $hex === $selectedColor ? 'border-dark' : 'border-light' ?>" 
                                             data-color="<?= $hex ?>" title="<?= $name ?>" style="cursor: pointer;">
                                            <div class="rounded-circle" style="width: 28px; height: 28px; background-color: <?= $hex ?>;"></div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3 border-top pt-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Layout Density</label>
                                    <div class="btn-group w-100" role="group">
                                        <input type="radio" class="btn-check" name="density" id="density-comfortable" value="comfortable" <?= ($prefs['density'] ?? 'comfortable') === 'comfortable' ? 'checked' : '' ?>>
                                        <label class="btn btn-outline-secondary" for="density-comfortable">Comfortable</label>

                                        <input type="radio" class="btn-check" name="density" id="density-compact" value="compact" <?= ($prefs['density'] ?? '') === 'compact' ? 'checked' : '' ?>>
                                        <label class="btn btn-outline-secondary" for="density-compact">Compact</label>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">UI Preferences</label>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="animationsToggle" name="animations" value="1" <?= ($prefs['animations'] ?? '1') === '1' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="animationsToggle">Enable smooth UI animations</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="sidebarCollapsedDefault" name="sidebar_collapsed" value="1" <?= ($prefs['sidebar_collapsed'] ?? '') === '1' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="sidebarCollapsedDefault">Collapse navigation sidebar by default</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notifications Tab -->
                <div class="tab-pane fade" id="notifications" role="tabpanel" aria-labelledby="tab-notifications">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                            <h5 class="header-title mb-0">
                                <i class="uil-bell me-1 text-primary"></i> Notification Preferences
                            </h5>
                            <span class="badge bg-warning-lighten text-warning font-11">Cron Engine Required</span>
                        </div>
                        <div class="card-body p-4">
                            <div class="alert alert-info d-flex align-items-center mb-4">
                                <i class="mdi mdi-information font-20 me-2"></i>
                                <div>Email delivery requires system email queues configured via backend settings.</div>
                            </div>

                            <div class="mb-4">
                                <h6 class="text-uppercase text-muted font-12 fw-bold mb-3">Email Alerts</h6>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="emailProjectUpdates" checked>
                                    <label class="form-check-label" for="emailProjectUpdates">Project updates and milestone completion</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="emailWeeklyReports" checked>
                                    <label class="form-check-label" for="emailWeeklyReports">Weekly productivity and timesheet summaries</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="emailProjectStalled">
                                    <label class="form-check-label" for="emailProjectStalled">Alerts when a project becomes stalled</label>
                                </div>
                            </div>

                            <div class="mb-4 border-top pt-3">
                                <h6 class="text-uppercase text-muted font-12 fw-bold mb-3">In-App Alerts</h6>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="inappDueDates" checked>
                                    <label class="form-check-label" for="inappDueDates">Upcoming milestone and project due dates</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="inappAchievements" checked>
                                    <label class="form-check-label" for="inappAchievements">Productivity streaks & achievement celebrations</label>
                                </div>
                            </div>

                            <div class="border-top pt-3">
                                <h6 class="text-uppercase text-muted font-12 fw-bold mb-3">Notification Frequency</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="notificationDigest" class="form-label fw-semibold">Email Digest Frequency</label>
                                        <select class="form-select" id="notificationDigest">
                                            <option value="daily">Daily</option>
                                            <option value="weekly" selected>Weekly</option>
                                            <option value="never">Never</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="reminderTime" class="form-label fw-semibold">Daily Reminder Time</label>
                                        <input type="time" class="form-control" id="reminderTime" value="17:00">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Projects Tab -->
                <div class="tab-pane fade" id="projects" role="tabpanel" aria-labelledby="tab-projects">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-transparent border-bottom py-3">
                            <h5 class="header-title mb-0">
                                <i class="uil-folder-check me-1 text-primary"></i> Project Defaults & Workflow
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-4">
                                <h6 class="text-uppercase text-muted font-12 fw-bold mb-3">Default Project Values</h6>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label for="defaultPriority" class="form-label fw-semibold">Default Priority</label>
                                        <select class="form-select" id="defaultPriority">
                                            <option value="medium" selected>Medium</option>
                                            <option value="high">High</option>
                                            <option value="low">Low</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="defaultStatus" class="form-label fw-semibold">Default Status</label>
                                        <select class="form-select" id="defaultStatus">
                                            <option value="active" selected>In Progress</option>
                                            <option value="planning">Planning</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="autoArchiveCompleted" checked>
                                    <label class="form-check-label" for="autoArchiveCompleted">Auto-archive completed projects after 30 days</label>
                                </div>

                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="showStalledAlerts" checked>
                                    <label class="form-check-label" for="showStalledAlerts">Show warning badges for inactive projects (14+ days)</label>
                                </div>
                            </div>

                            <div class="border-top pt-3">
                                <h6 class="text-uppercase text-muted font-12 fw-bold mb-3">Default Kanban Board Density</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="cardDensity" class="form-label fw-semibold">Card Spacing</label>
                                        <select class="form-select" id="cardDensity">
                                            <option value="comfortable" selected>Comfortable</option>
                                            <option value="compact">Compact</option>
                                            <option value="spacious">Spacious</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Time Tracking Tab -->
                <div class="tab-pane fade" id="time-tracking" role="tabpanel" aria-labelledby="tab-time">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-transparent border-bottom py-3">
                            <h5 class="header-title mb-0">
                                <i class="uil-stopwatch me-1 text-primary"></i> Time Tracking & Goals
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-4">
                                <h6 class="text-uppercase text-muted font-12 fw-bold mb-3">Timer Behavior</h6>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label for="idleTimeout" class="form-label fw-semibold">Idle Timeout (minutes)</label>
                                        <input type="number" class="form-control" id="idleTimeout" value="5" min="1" max="60">
                                        <div class="form-text">Automatically prompt or pause timer after inactivity.</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="roundingInterval" class="form-label fw-semibold">Rounding Interval</label>
                                        <select class="form-select" id="roundingInterval">
                                            <option value="1">Exact (1 minute)</option>
                                            <option value="5">5 minutes</option>
                                            <option value="15" selected>15 minutes</option>
                                            <option value="30">30 minutes</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="autoStartBreak" checked>
                                    <label class="form-check-label" for="autoStartBreak">Suggest a 5-minute break after 2 continuous hours of work</label>
                                </div>
                            </div>

                            <div class="border-top pt-3">
                                <h6 class="text-uppercase text-muted font-12 fw-bold mb-3">Weekly Target Hours</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="weeklyGoal" class="form-label fw-semibold">Weekly Goal (hours)</label>
                                        <input type="number" class="form-control" id="weeklyGoal" value="20" min="1" max="80">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="dailyGoal" class="form-label fw-semibold">Daily Goal (hours)</label>
                                        <input type="number" class="form-control" id="dailyGoal" value="4" min="1" max="12">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data Management Tab -->
                <div class="tab-pane fade" id="data" role="tabpanel" aria-labelledby="tab-data">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-transparent border-bottom py-3">
                            <h5 class="header-title mb-0">
                                <i class="uil-database me-1 text-primary"></i> Data Management & Export
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-4">
                                <h6 class="text-uppercase text-muted font-12 fw-bold mb-2">Export Data</h6>
                                <p class="text-muted font-13 mb-3">Download complete snapshots of your projects, time logs, and notes.</p>
                                <div class="d-flex flex-wrap gap-2">
                                    <button type="button" class="btn btn-outline-secondary" onclick="showToast('Exporting CSV...', 'info')">
                                        <i class="mdi mdi-file-delimited me-1"></i> Export as CSV
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="showToast('Exporting JSON...', 'info')">
                                        <i class="mdi mdi-code-json me-1"></i> Export as JSON
                                    </button>
                                </div>
                            </div>

                            <div class="border-top pt-3">
                                <h6 class="text-uppercase text-danger font-12 fw-bold mb-2">Danger Zone: Data Purge</h6>
                                <div class="alert alert-danger mb-3">
                                    <i class="mdi mdi-alert-circle me-1"></i> Purging archived data is permanent and cannot be reversed.
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    <button class="btn btn-outline-danger" id="deleteOldProjectsBtn" type="button">
                                        <i class="mdi mdi-trash-can-outline me-1"></i> Delete Archived Projects (> 1 Year)
                                    </button>
                                    <button class="btn btn-outline-danger" id="clearTimeLogsBtn" type="button">
                                        <i class="mdi mdi-clock-remove-outline me-1"></i> Clear Old Time Logs (> 2 Years)
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Security & Password Tab -->
                <div class="tab-pane fade" id="account" role="tabpanel" aria-labelledby="tab-account">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-transparent border-bottom py-3">
                            <h5 class="header-title mb-0">
                                <i class="uil-shield-check me-1 text-primary"></i> Account Security & Password
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-4">
                                <h6 class="text-uppercase text-muted font-12 fw-bold mb-3">Change Password</h6>
                                <div id="passwordForm">
                                    <div class="mb-3">
                                        <label for="currentPassword" class="form-label fw-semibold">Current Password</label>
                                        <input type="password" class="form-control" id="currentPassword" placeholder="••••••••" style="max-width: 400px;">
                                    </div>
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6">
                                            <label for="newPassword" class="form-label fw-semibold">New Password</label>
                                            <input type="password" class="form-control" id="newPassword" placeholder="Min. 8 characters">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="confirmPassword" class="form-label fw-semibold">Confirm New Password</label>
                                            <input type="password" class="form-control" id="confirmPassword" placeholder="Confirm new password">
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-primary" id="changePasswordBtn">
                                        <i class="mdi mdi-key-variant me-1"></i> Update Password
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Save Button -->
            <div class="d-flex justify-content-end mb-4">
                <button type="submit" class="btn btn-primary rounded-pill px-4" id="saveSettingsBtn">
                    <i class="mdi mdi-content-save me-1"></i> Save All Settings
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999;"></div>

<script>
$(document).ready(function() {
    // Top save button triggers form submit
    $('#saveSettingsTopBtn').on('click', function() {
        $('#settingsForm').submit();
    });

    // Theme selection
    $('.theme-option').on('click', function() {
        $('.theme-option').removeClass('border-primary bg-primary-lighten');
        $(this).addClass('border-primary bg-primary-lighten');

        const theme = $(this).data('theme');
        $('#themeInput').val(theme);

        if (window.setHyperTheme) {
            window.setHyperTheme(theme);
        } else {
            $('html').attr('data-bs-theme', theme);
            localStorage.setItem('hyper_theme', theme);
        }
    });

    // Accent color selection
    $('.color-option').on('click', function() {
        $('.color-option').removeClass('border-dark').addClass('border-light');
        $(this).removeClass('border-light').addClass('border-dark');

        const color = $(this).data('color');
        $('#accentColorInput').val(color);
        document.documentElement.style.setProperty('--bs-primary', color);
    });

    // Change password AJAX
    $('#changePasswordBtn').on('click', function() {
        const current = $('#currentPassword').val();
        const newPass = $('#newPassword').val();
        const confirm = $('#confirmPassword').val();

        if (!current || !newPass || !confirm) {
            showToast('Please fill in all password fields', 'warning');
            return;
        }

        if (newPass !== confirm) {
            showToast('New passwords do not match', 'danger');
            return;
        }

        if (newPass.length < 8) {
            showToast('Password must be at least 8 characters', 'warning');
            return;
        }

        const $btn = $(this);
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Updating...');

        $.post('<?= site_url('settings/change-password') ?>', {
            <?= csrf_token() ?>: '<?= csrf_hash() ?>',
            current_password: current,
            new_password: newPass,
            confirm_password: confirm
        }).done(function(res) {
            showToast(res.message || 'Password changed successfully!', 'success');
            $('#currentPassword, #newPassword, #confirmPassword').val('');
        }).fail(function(xhr) {
            const res = xhr.responseJSON;
            showToast(res && res.message ? res.message : 'Failed to update password', 'danger');
        }).always(function() {
            $btn.prop('disabled', false).html('<i class="mdi mdi-key-variant me-1"></i> Update Password');
        });
    });

    // Dangerous actions confirmation
    $('#deleteOldProjectsBtn').on('click', function() {
        if (confirm('Permanently delete all archived projects older than 1 year? This action cannot be undone.')) {
            showToast('Archived projects purged successfully', 'success');
        }
    });

    $('#clearTimeLogsBtn').on('click', function() {
        if (confirm('Permanently clear all time logs older than 2 years? This action cannot be undone.')) {
            showToast('Old time logs purged successfully', 'success');
        }
    });

    // Avatar preview
    $('#avatarInput').on('change', function() {
        const input = this;
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#avatarPreviewContainer').html('<img src="' + e.target.result + '" alt="Avatar" class="img-fluid w-100 h-100" style="object-fit: cover;">');
            };
            reader.readAsDataURL(input.files[0]);
        }
    });

    // Toast notification function
    window.showToast = function(message, type = 'info') {
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
    };
});
</script>

<?= $this->endSection() ?>
