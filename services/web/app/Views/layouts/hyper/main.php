<?php
    $currentUser = auth()->user();
    $rawName = $currentUser->username ?? $currentUser->email ?? 'Guest';
    $initials = strtoupper(substr(trim($rawName), 0, 2));
    $isAdmin = $currentUser && $currentUser->inGroup('admin');
    $isManager = $currentUser && ($currentUser->inGroup('manager') || $isAdmin);

    $unreadNotificationsCount = 0;
    $initialNotifications = [];
    if ($currentUser) {
        $notifModel = new \App\Models\NotificationModel();
        $unreadNotificationsCount = $notifModel->getUnreadCountForUser((int)$currentUser->id);
        $initialNotifications = $notifModel->getRecentForUser((int)$currentUser->id, 8);
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title><?= $this->renderSection('title') ?> | <?= esc(setting('App.siteName')) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= esc(setting('App.siteName')) ?> - Agile Project Management" />

    <!-- App favicon -->
    <link rel="icon" type="image/jpeg" href="<?= base_url('assets/img/app_logo.jpg') ?>">
    <link rel="apple-touch-icon" href="<?= base_url('assets/img/app_logo.jpg') ?>">

    <!-- Primary Meta Tags & SEO -->
    <meta name="title" content="<?= $this->renderSection('title') ?> | <?= esc(setting('App.siteName')) ?>">
    <meta name="description" content="<?= esc(setting('App.siteDesc') ?? 'Agile Project Management & Team Velocity Platform') ?>">
    <meta name="keywords" content="agile, kanban board, sprint planning, project management, issue tracker, time tracking, jira alternative, team collaboration">
    <meta name="author" content="<?= esc(setting('App.siteName')) ?> Team">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#727cf5">

    <!-- Open Graph / Facebook / LinkedIn -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= current_url() ?>">
    <meta property="og:title" content="<?= $this->renderSection('title') ?> | <?= esc(setting('App.siteName')) ?>">
    <meta property="og:description" content="<?= esc(setting('App.siteDesc') ?? 'Agile Project Management & Team Velocity Platform') ?>">
    <meta property="og:image" content="<?= base_url('assets/img/app_hero.jpg') ?>">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="<?= current_url() ?>">
    <meta name="twitter:title" content="<?= $this->renderSection('title') ?> | <?= esc(setting('App.siteName')) ?>">
    <meta name="twitter:description" content="<?= esc(setting('App.siteDesc') ?? 'Agile Project Management & Team Velocity Platform') ?>">
    <meta name="twitter:image" content="<?= base_url('assets/img/app_hero.jpg') ?>">

    <!-- third party css -->
    <?= $this->renderSection('css') ?>
    
    <!-- App css -->
    <link href="<?= base_url('assets/hyper/css/icons.min.css') ?>" rel="stylesheet" type="text/css" />
    <link href="<?= base_url('assets/hyper/css/app.min.css') ?>" rel="stylesheet" type="text/css" id="light-style" />
    <link href="<?= base_url('assets/hyper/css/app-dark.min.css') ?>" rel="stylesheet" type="text/css" id="dark-style" disabled="disabled" />
    
    <script>
        (function() {
            var theme = localStorage.getItem('hyper_theme');
            if (theme === 'dark') {
                document.getElementById('light-style')?.setAttribute('disabled', 'disabled');
                document.getElementById('dark-style')?.removeAttribute('disabled');
                document.documentElement.setAttribute('data-bs-theme', 'dark');
                document.documentElement.classList.add('dark-theme');
            }
        })();
    </script>
    
    <style>
        .side-nav .side-nav-link i {
            font-size: 1.1rem;
            margin-right: 10px;
            vertical-align: middle;
        }
        .avatar-initials {
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .side-nav-title {
            letter-spacing: 0.05em;
            pointer-events: none;
            cursor: default;
            font-size: 11px;
            text-transform: uppercase;
            color: #8391a2;
            font-weight: 700;
            padding: 12px 20px 6px;
        }
        #theme-toggle-btn {
            cursor: pointer;
            padding: 0 12px;
            display: flex;
            align-items: center;
            height: 70px;
        }
        .navbar-custom {
            min-height: 70px;
        }
        .navbar-custom .topbar-menu .nav-link {
            height: 70px;
            display: flex;
            align-items: center;
        }
        .navbar-custom .topbar-menu .nav-user {
            padding: 0 12px;
        }
        /* Robust Sidebar Active Highlighting */
        .side-nav .side-nav-item.menuitem-active > .side-nav-link,
        .side-nav .side-nav-item > .side-nav-link.active {
            color: #727cf5 !important;
            font-weight: 600 !important;
            background-color: rgba(114, 124, 245, 0.12) !important;
            border-left: 3px solid #727cf5 !important;
        }
        .side-nav .side-nav-item.menuitem-active > .side-nav-link i,
        .side-nav .side-nav-item > .side-nav-link.active i {
            color: #727cf5 !important;
        }

        /* Layout & Guaranteed Visible Footer Structure */
        .wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: row;
        }
        .content-page {
            display: flex !important;
            flex-direction: column !important;
            min-height: 100vh !important;
            margin-left: 260px;
            padding: 70px 15px 0 15px !important;
            flex: 1 1 auto;
            width: calc(100% - 260px);
            overflow-x: hidden;
        }
        .content-page .content {
            flex: 1 0 auto !important;
        }
        .content-page .footer {
            position: static !important;
            left: auto !important;
            right: auto !important;
            bottom: auto !important;
            width: 100% !important;
            flex-shrink: 0 !important;
            margin-top: auto !important;
            background-color: #ffffff;
            border-top: 1px solid rgba(152, 166, 173, 0.2) !important;
            padding: 16px 24px !important;
            z-index: 10;
        }
        html.dark-theme .content-page .footer,
        body.dark-theme .content-page .footer,
        html[data-bs-theme="dark"] .content-page .footer,
        body[data-layout-config*='"darkMode":true'] .content-page .footer {
            background-color: #343a40 !important;
            border-top-color: rgba(255, 255, 255, 0.08) !important;
        }
        @media (max-width: 767.98px) {
            .content-page {
                margin-left: 0 !important;
                width: 100% !important;
                padding: 70px 10px 0 10px !important;
            }
        }
        body[data-leftbar-compact-mode="condensed"] .content-page {
            margin-left: 70px !important;
            width: calc(100% - 70px) !important;
        }

        /* Global Command Palette & Spotlight Search */
        .topbar-search-btn {
            background: rgba(152, 166, 173, 0.1);
            border: 1px solid rgba(152, 166, 173, 0.25);
            color: #6c757d;
            border-radius: 20px;
            padding: 6px 16px;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .topbar-search-btn:hover {
            background: rgba(114, 124, 245, 0.1);
            border-color: #727cf5;
            color: #727cf5;
        }
        body.dark-theme .topbar-search-btn,
        html.dark-theme .topbar-search-btn {
            background: rgba(255, 255, 255, 0.06);
            border-color: rgba(255, 255, 255, 0.15);
            color: #aab8c5;
        }
        .palette-item {
            transition: all 0.15s ease;
            color: inherit;
            cursor: pointer;
            border: 1px solid transparent;
        }
        .palette-item:hover, .palette-item.active {
            background-color: rgba(114, 124, 245, 0.12) !important;
            border-color: rgba(114, 124, 245, 0.25) !important;
        }
        .palette-item.active .palette-title {
            color: #727cf5 !important;
        }
        .palette-section-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #8391a2;
            padding: 10px 12px 4px;
        }
        body.dark-theme #commandPaletteModal .modal-content,
        html.dark-theme #commandPaletteModal .modal-content {
            background-color: #313a46 !important;
            color: #ced4da;
        }
        body.dark-theme #commandPaletteModal .modal-footer,
        html.dark-theme #commandPaletteModal .modal-footer {
            background-color: #272e38 !important;
            border-top-color: rgba(255, 255, 255, 0.08) !important;
        }
        body.dark-theme #commandPaletteModal input,
        html.dark-theme #commandPaletteModal input {
            color: #fff !important;
        }
        body.dark-theme .palette-item .palette-title,
        html.dark-theme .palette-item .palette-title {
            color: #dee2e6 !important;
        }
    </style>

    <?= $this->renderSection('head') ?>
</head>

<body class="loading" data-layout-config='{"leftSideBarTheme":"dark","layoutBoxed":false, "leftSidebarCondensed":false, "leftSidebarScrollable":false,"darkMode":false, "showRightSidebarOnStart": true}'>
    <div class="wrapper">
        <!-- ========== Left Sidebar Start ========== -->
        <div class="leftside-menu">
            <a href="<?= site_url('dashboard') ?>" class="logo text-center logo-light py-3 d-flex align-items-center justify-content-center">
                <img src="<?= base_url('assets/img/app_logo.jpg') ?>" alt="Logo" class="rounded-circle me-2 shadow-sm" style="width: 32px; height: 32px; object-fit: cover;">
                <span class="logo-lg text-white font-18 fw-bold">
                    <?= esc(setting('App.siteName')) ?>
                </span>
                <span class="logo-sm text-white font-18 fw-bold">
                    <?= strtoupper(substr(setting('App.siteName') ?? 'C', 0, 1)) ?>
                </span>
            </a>

            <div class="h-100" id="leftside-menu-container" data-simplebar>
                <?php 
                    $uri = uri_string();
                    $isDash = in_array($uri, ['dashboard', 'home', 'user/dashboard', 'user/home', '']);
                    $isKanban = str_contains($uri, 'kanban');
                    $isProjects = (str_starts_with($uri, 'projects') && !$isKanban);
                    $isTimeReport = str_contains($uri, 'time/report');
                    $isTime = (str_starts_with($uri, 'time') && !$isTimeReport);
                    $isCal = str_starts_with($uri, 'calendar');
                    $isNotes = str_starts_with($uri, 'notes');
                    $isAnalytics = str_starts_with($uri, 'analytics');
                    $isTeam = str_contains($uri, 'team');
                    $isApprovals = str_contains($uri, 'approvals');
                    $isReports = str_contains($uri, 'reports');
                    $isAdminSettings = str_contains($uri, 'admin/settings');
                    $isAdminTelemetry = str_contains($uri, 'admin/telemetry');
                    $isAdminAiTelemetry = str_contains($uri, 'admin/ai/telemetry');
                    $isAdminAiSettings = str_contains($uri, 'admin/ai/settings');
                    $isProfile = ($uri === 'settings' || $uri === 'user/settings');
                ?>
                <!--- Sidemenu -->
                <ul class="side-nav">
                    <li class="side-nav-title side-nav-item">Core Workspace</li>
                    
                    <li class="side-nav-item <?= $isDash ? 'menuitem-active' : '' ?>">
                        <a href="<?= site_url('dashboard') ?>" class="side-nav-link <?= $isDash ? 'active' : '' ?>">
                            <i class="uil-home-alt text-primary"></i>
                            <span> Dashboard </span>
                        </a>
                    </li>
                    <li class="side-nav-item <?= $isProjects ? 'menuitem-active' : '' ?>">
                        <a href="<?= site_url('projects') ?>" class="side-nav-link <?= $isProjects ? 'active' : '' ?>">
                            <i class="uil-briefcase text-success"></i>
                            <span> Projects </span>
                        </a>
                    </li>
                    <li class="side-nav-item <?= $isKanban ? 'menuitem-active' : '' ?>">
                        <a href="<?= site_url('kanban') ?>" class="side-nav-link <?= $isKanban ? 'active' : '' ?>">
                            <i class="uil-clipboard-alt text-info"></i>
                            <span> Kanban Board </span>
                        </a>
                    </li>
                    <li class="side-nav-item <?= $isTime ? 'menuitem-active' : '' ?>">
                        <a href="<?= site_url('time') ?>" class="side-nav-link <?= $isTime ? 'active' : '' ?>">
                            <i class="uil-clock text-warning"></i>
                            <span> Time Tracker </span>
                        </a>
                    </li>
                    <li class="side-nav-item <?= $isTimeReport ? 'menuitem-active' : '' ?>">
                        <a href="<?= site_url('time/report') ?>" class="side-nav-link <?= $isTimeReport ? 'active' : '' ?>">
                            <i class="uil-file-check-alt text-success"></i>
                            <span> Time Reports </span>
                        </a>
                    </li>
                    <li class="side-nav-item <?= $isCal ? 'menuitem-active' : '' ?>">
                        <a href="<?= site_url('calendar') ?>" class="side-nav-link <?= $isCal ? 'active' : '' ?>">
                            <i class="uil-calender text-danger"></i>
                            <span> Calendar </span>
                        </a>
                    </li>
                    <li class="side-nav-item <?= $isNotes ? 'menuitem-active' : '' ?>">
                        <a href="<?= site_url('notes') ?>" class="side-nav-link <?= $isNotes ? 'active' : '' ?>">
                            <i class="uil-notes text-secondary"></i>
                            <span> Scratch Notes </span>
                        </a>
                    </li>
                    <li class="side-nav-item <?= $isAnalytics ? 'menuitem-active' : '' ?>">
                        <a href="<?= site_url('analytics') ?>" class="side-nav-link <?= $isAnalytics ? 'active' : '' ?>">
                            <i class="uil-chart-line text-purple"></i>
                            <span> Analytics </span>
                        </a>
                    </li>

                    <?php if ($isManager): ?>
                        <li class="side-nav-title side-nav-item mt-2">Team Management</li>
                        <li class="side-nav-item <?= $isTeam ? 'menuitem-active' : '' ?>">
                            <a href="<?= site_url('manager/team') ?>" class="side-nav-link <?= $isTeam ? 'active' : '' ?>">
                                <i class="uil-users-alt text-success"></i>
                                <span> Team Workload </span>
                            </a>
                        </li>
                        <li class="side-nav-item <?= $isApprovals ? 'menuitem-active' : '' ?>">
                            <a href="<?= site_url('manager/approvals') ?>" class="side-nav-link <?= $isApprovals ? 'active' : '' ?>">
                                <i class="uil-check-circle text-info"></i>
                                <span> Work Approvals </span>
                            </a>
                        </li>
                        <li class="side-nav-item <?= $isReports ? 'menuitem-active' : '' ?>">
                            <a href="<?= site_url('manager/reports') ?>" class="side-nav-link <?= $isReports ? 'active' : '' ?>">
                                <i class="uil-file-alt text-warning"></i>
                                <span> Sprint Reports </span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if ($isAdmin): ?>
                        <li class="side-nav-title side-nav-item mt-2">Administration</li>
                        <li class="side-nav-item <?= $isAdminSettings ? 'menuitem-active' : '' ?>">
                            <a href="<?= site_url('admin/settings') ?>" class="side-nav-link <?= $isAdminSettings ? 'active' : '' ?>">
                                <i class="uil-sliders-v-alt text-danger"></i>
                                <span> System Settings </span>
                            </a>
                        </li>
                        <li class="side-nav-item <?= ($isAdminTelemetry && !$isAdminAiTelemetry) ? 'menuitem-active' : '' ?>">
                            <a href="<?= site_url('admin/telemetry') ?>" class="side-nav-link <?= ($isAdminTelemetry && !$isAdminAiTelemetry) ? 'active' : '' ?>">
                                <i class="uil-server text-light"></i>
                                <span> Telemetry & Logs </span>
                            </a>
                        </li>
                        <li class="side-nav-item <?= $isAdminAiTelemetry ? 'menuitem-active' : '' ?>">
                            <a href="<?= site_url('admin/ai/telemetry') ?>" class="side-nav-link <?= $isAdminAiTelemetry ? 'active' : '' ?>">
                                <i class="mdi mdi-brain text-purple font-16"></i>
                                <span> AI Diagnostics </span>
                            </a>
                        </li>
                        <li class="side-nav-item <?= $isAdminAiSettings ? 'menuitem-active' : '' ?>">
                            <a href="<?= site_url('admin/ai/settings') ?>" class="side-nav-link <?= $isAdminAiSettings ? 'active' : '' ?>">
                                <i class="mdi mdi-tune-vertical text-info font-16"></i>
                                <span> AI Settings </span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <li class="side-nav-title side-nav-item mt-2">User Settings</li>
                    <li class="side-nav-item <?= $isProfile ? 'menuitem-active' : '' ?>">
                        <a href="<?= site_url('settings') ?>" class="side-nav-link <?= $isProfile ? 'active' : '' ?>">
                            <i class="uil-cog text-info"></i>
                            <span> Profile & Security </span>
                        </a>
                    </li>
                </ul>
                <div class="clearfix"></div>
            </div>
        </div>
        <!-- Left Sidebar End -->

        <!-- ============================================================== -->
        <!-- Start Page Content here -->
        <!-- ============================================================== -->
        <div class="content-page">
            <div class="content">
                <!-- Topbar Start -->
                <div class="navbar-custom d-flex align-items-center justify-content-between px-3">
                    <div class="d-flex align-items-center">
                        <button class="button-menu-mobile open-left me-2">
                            <i class="mdi mdi-menu font-20"></i>
                        </button>

                        <!-- Command Palette Trigger Button -->
                        <div class="topbar-search-btn d-none d-sm-inline-flex" id="global-search-trigger" title="Quick Search or Commands (Ctrl + K)">
                            <i class="uil-search me-2 font-16"></i>
                            <span>Search or type <strong class="text-primary">&gt;</strong> for commands...</span>
                            <span class="badge bg-secondary-lighten text-muted font-11 ms-3 px-1 border">Ctrl+K</span>
                        </div>
                    </div>

                    <ul class="list-unstyled topbar-menu mb-0 d-flex align-items-center ms-auto">
                        <!-- Mobile Search Trigger Icon -->
                        <li class="d-inline-block d-sm-none me-1">
                            <a class="nav-link" href="javascript:void(0);" id="mobile-search-trigger" title="Search (Ctrl + K)">
                                <i class="uil-search font-22"></i>
                            </a>
                        </li>

                        <li class="notification-list me-1">
                            <a class="nav-link end-bar-toggle" href="javascript:void(0);" id="theme-toggle-btn" title="Toggle Light / Dark Theme" role="button">
                                <i class="uil-moon font-22" id="theme-toggle-icon"></i>
                            </a>
                        </li>

                        <!-- Notifications Bell -->
                        <li class="dropdown notification-list me-1">
                            <a class="nav-link dropdown-toggle arrow-none position-relative d-flex align-items-center" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false" id="topbar-notification-dropdown" title="Notifications">
                                <i class="uil-bell font-22"></i>
                                <span class="position-absolute badge rounded-pill bg-danger font-10" id="notification-badge" style="top: 14px; right: 4px; display: <?= $unreadNotificationsCount > 0 ? 'inline-block' : 'none' ?>; font-size: 10px; padding: 2px 5px;">
                                    <?= $unreadNotificationsCount > 99 ? '99+' : $unreadNotificationsCount ?>
                                </span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end dropdown-menu-animated topbar-dropdown-menu shadow-lg border" style="width: 330px; max-width: 90vw;" aria-labelledby="topbar-notification-dropdown">
                                <div class="dropdown-header noti-title d-flex justify-content-between align-items-center py-2 px-3 border-bottom bg-light">
                                    <h6 class="m-0 font-13 fw-bold text-dark">
                                        <i class="mdi mdi-bell-ring-outline text-primary me-1"></i> Notifications
                                        <span class="badge bg-primary-lighten text-primary ms-1" id="dropdown-unread-count"><?= $unreadNotificationsCount ?></span>
                                    </h6>
                                    <a href="javascript:void(0);" id="mark-all-read-btn" class="text-primary font-11 text-decoration-none fw-semibold">
                                        Mark all read
                                    </a>
                                </div>

                                <div style="max-height: 280px; overflow-y: auto;" id="notification-items-container">
                                    <?php if (empty($initialNotifications)): ?>
                                        <div class="p-4 text-center text-muted" id="notif-empty-state">
                                            <i class="mdi mdi-bell-sleep-outline font-24 d-block mb-1 opacity-50"></i>
                                            <span class="font-12">No notifications yet</span>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($initialNotifications as $notif): 
                                            $isUnread = (int)$notif['is_read'] === 0;
                                            $icon = 'mdi-bell-outline';
                                            $iconColor = 'text-primary bg-primary-lighten';
                                            switch($notif['type']) {
                                                case 'task_assigned': $icon = 'mdi-clipboard-account-outline'; $iconColor = 'text-info bg-info-lighten'; break;
                                                case 'task_moved': case 'task_status': $icon = 'mdi-swap-horizontal'; $iconColor = 'text-warning bg-warning-lighten'; break;
                                                case 'work_approved': $icon = 'mdi-check-decagram-outline'; $iconColor = 'text-success bg-success-lighten'; break;
                                                case 'work_rejected': $icon = 'mdi-alert-circle-outline'; $iconColor = 'text-danger bg-danger-lighten'; break;
                                                case 'sprint': $icon = 'mdi-run-fast'; $iconColor = 'text-primary bg-primary-lighten'; break;
                                                case 'project': $icon = 'mdi-folder-star-outline'; $iconColor = 'text-secondary bg-secondary-lighten'; break;
                                            }
                                        ?>
                                            <a href="<?= !empty($notif['action_url']) ? base_url($notif['action_url']) : 'javascript:void(0);' ?>" 
                                               class="dropdown-item notify-item py-2 px-3 border-bottom notif-item <?= $isUnread ? 'bg-light-lighten' : 'opacity-75' ?>" 
                                               data-id="<?= $notif['id'] ?>">
                                                <div class="d-flex align-items-start">
                                                    <div class="avatar-xs me-2 flex-shrink-0">
                                                        <span class="avatar-title rounded-circle font-14 <?= $iconColor ?>">
                                                            <i class="mdi <?= $icon ?>"></i>
                                                        </span>
                                                    </div>
                                                    <div class="flex-grow-1 overflow-hidden">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <h6 class="m-0 font-12 fw-semibold text-truncate <?= $isUnread ? 'text-dark fw-bold' : 'text-muted' ?>">
                                                                <?= esc($notif['title']) ?>
                                                            </h6>
                                                            <?php if ($isUnread): ?>
                                                                <span class="badge bg-danger rounded-circle p-1 ms-1" style="width: 6px; height: 6px;"></span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <p class="font-11 text-muted mb-0 text-truncate"><?= esc($notif['body']) ?></p>
                                                        <small class="text-muted font-10"><i class="mdi mdi-clock-outline"></i> <?= date('M j, g:i a', strtotime($notif['created_at'])) ?></small>
                                                    </div>
                                                </div>
                                            </a>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>

                                <div class="p-1 border-top text-center bg-light">
                                    <small class="text-muted font-10"><i class="mdi mdi-refresh me-1"></i>Real-time team updates</small>
                                </div>
                            </div>
                        </li>

                        <li class="dropdown notification-list">
                            <a class="nav-link dropdown-toggle nav-user arrow-none me-0 d-flex align-items-center" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                                <span class="account-user-avatar me-2"> 
                                    <span class="avatar-initials bg-primary text-white rounded-circle shadow-sm">
                                        <?= esc($initials) ?>
                                    </span>
                                </span>
                                <span>
                                    <span class="account-user-name fw-bold"><?= esc($rawName) ?></span>
                                    <span class="account-position text-muted font-12">
                                        <?= $isAdmin ? 'Administrator' : ($isManager ? 'Manager' : 'Developer') ?>
                                    </span>
                                </span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end dropdown-menu-animated topbar-dropdown-menu profile-dropdown">
                                <div class="dropdown-header noti-title">
                                    <h6 class="text-overflow m-0">Signed in as <strong><?= esc($rawName) ?></strong></h6>
                                </div>
                                <a href="<?= site_url('settings') ?>" class="dropdown-item notify-item">
                                    <i class="mdi mdi-account-circle me-1"></i>
                                    <span>My Account</span>
                                </a>
                                <a href="<?= site_url('auth/logout') ?>" class="dropdown-item notify-item text-danger">
                                    <i class="mdi mdi-logout me-1"></i>
                                    <span>Logout</span>
                                </a>
                            </div>
                        </li>
                    </ul>
                </div>
                <!-- end Topbar -->

                <!-- Start Content-->
                <div class="container-fluid py-3">
                    <?= $this->renderSection('content') ?>
                </div>
                <!-- container -->
            </div>
            <!-- content -->

            <!-- Footer Start -->
            <footer class="footer py-4 border-top">
                <div class="container-fluid">
                    <div class="row align-items-center">
                        <div class="col-md-4 text-center text-md-start mb-2 mb-md-0 font-14">
                            <span><?= date('Y') ?> © <strong><?= esc(setting('App.siteName')) ?></strong></span>
                            <br>
                            <span class="text-muted font-12">Agile Workspace & ML Telemetry Platform</span>
                        </div>
                        <div class="col-md-4 text-center mb-2 mb-md-0 font-14">
                            <a href="<?= site_url('features') ?>" class="text-muted text-decoration-none mx-2 hover-primary">Features</a>
                            <a href="<?= site_url('setup') ?>" class="text-muted text-decoration-none mx-2 hover-primary">Setup Guide</a>
                            <a href="<?= site_url('admin/telemetry') ?>" class="text-muted text-decoration-none mx-2 hover-primary">System Status</a>
                        </div>
                        <div class="col-md-4 text-center text-md-end font-14">
                            <span class="badge bg-primary-lighten text-primary text-decoration-none px-2 py-1 font-12 me-1">
                                WebApp: v1.2.0
                            </span>
                            <span class="badge bg-success-lighten text-success text-decoration-none px-2 py-1 font-12">
                                ML Engine: v1.2.0
                            </span>
                        </div>
                    </div>
                </div>
            </footer>
            <!-- end Footer -->
        </div>
        <!-- ============================================================== -->
        <!-- End Page content -->
        <!-- ============================================================== -->
    </div>
    <!-- END wrapper -->

    <!-- App Version & Changelog Modal -->
    <div class="modal fade" id="appVersionModal" tabindex="-1" aria-labelledby="appVersionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white" id="appVersionModalLabel">
                        <i class="mdi mdi-information-outline me-1"></i> <?= esc(setting('App.siteName')) ?> v1.2.0
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <span class="badge bg-success-lighten text-success font-13 px-3 py-1">Latest Release</span>
                        <span class="text-muted font-13 ms-auto"><i class="mdi mdi-calendar-outline me-1"></i> September 2026</span>
                    </div>
                    
                    <h5 class="fw-bold mb-2 text-dark">Release Highlights:</h5>
                    <ul class="font-14 text-muted ps-3 mb-3">
                        <li class="mb-1"><strong>Hyper SaaS Theme:</strong> Upgraded to Bootstrap 5 with responsive dark/light layouts.</li>
                        <li class="mb-1"><strong>Interactive Drag & Drop Kanban:</strong> Real-time ticket management with visual priority badges.</li>
                        <li class="mb-1"><strong>Automated Database Seeding:</strong> 1-click startup with default accounts & demo projects.</li>
                        <li class="mb-1"><strong>Dynamic Colored Avatars:</strong> Initials-based profile avatars for all team members.</li>
                        <li class="mb-1"><strong>Time Tracker & PDF Reports:</strong> Integrated effort tracking and printable sprint reports.</li>
                    </ul>
                    
                    <div class="alert alert-light border font-13 text-muted mb-0">
                        <i class="mdi mdi-shield-check text-success me-1"></i> Production Build running on Railway / Docker.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Command Palette Spotlight Modal -->
    <div class="modal fade" id="commandPaletteModal" tabindex="-1" aria-labelledby="commandPaletteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-top modal-lg" style="margin-top: 7vh; max-width: 680px;">
            <div class="modal-content shadow-lg border-0" style="border-radius: 14px; overflow: hidden;">
                <div class="modal-header border-0 pb-0 pt-3 px-3">
                    <div class="input-group input-group-lg border-bottom pb-2 w-100 align-items-center">
                        <span class="input-group-text bg-transparent border-0 pe-2 text-primary font-22">
                            <i class="uil-search"></i>
                        </span>
                        <input type="text" class="form-control bg-transparent border-0 font-16 shadow-none ps-1 text-dark" id="command-palette-input" placeholder="Search tasks, projects, people, or type > for actions..." autocomplete="off" spellcheck="false">
                        <span class="badge bg-light text-muted border font-11 align-self-center px-2 py-1 me-1" style="cursor: pointer;" data-bs-dismiss="modal">ESC</span>
                    </div>
                </div>
                <div class="modal-body p-2" id="command-palette-results" style="max-height: 440px; min-height: 160px; overflow-y: auto;">
                    <!-- Dynamically populated via JS -->
                </div>
                <div class="modal-footer border-top py-2 px-3 bg-light d-flex justify-content-between font-12 text-muted">
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <span><kbd class="bg-white text-dark border px-1">↑</kbd> <kbd class="bg-white text-dark border px-1">↓</kbd> navigate</span>
                        <span><kbd class="bg-white text-dark border px-1">↵</kbd> select</span>
                        <span><kbd class="bg-white text-dark border px-1">&gt;</kbd> commands</span>
                    </div>
                    <div class="d-none d-sm-block font-11">
                        <strong>Chege Jira</strong> Spotlight
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- bundle -->
    <script src="<?= base_url('assets/hyper/js/vendor.min.js') ?>"></script>
    <script src="<?= base_url('assets/hyper/js/app.min.js') ?>"></script>
    
    <script>
        (function() {
            var toggleBtn = document.getElementById('theme-toggle-btn');
            var toggleIcon = document.getElementById('theme-toggle-icon');
            var lightStyle = document.getElementById('light-style');
            var darkStyle = document.getElementById('dark-style');

            function applyTheme(theme) {
                if (theme === 'dark') {
                    if (lightStyle) lightStyle.setAttribute('disabled', 'disabled');
                    if (darkStyle) darkStyle.removeAttribute('disabled');
                    if (toggleIcon) {
                        toggleIcon.className = 'uil-sun font-22 text-warning';
                    }
                    document.documentElement.setAttribute('data-bs-theme', 'dark');
                    document.body.classList.add('dark-theme');
                    document.body.setAttribute('data-layout-config', JSON.stringify({
                        "leftSideBarTheme": "dark",
                        "layoutBoxed": false,
                        "leftSidebarCondensed": false,
                        "leftSidebarScrollable": false,
                        "darkMode": true,
                        "showRightSidebarOnStart": true
                    }));
                } else {
                    if (darkStyle) darkStyle.setAttribute('disabled', 'disabled');
                    if (lightStyle) lightStyle.removeAttribute('disabled');
                    if (toggleIcon) {
                        toggleIcon.className = 'uil-moon font-22';
                    }
                    document.documentElement.setAttribute('data-bs-theme', 'light');
                    document.body.classList.remove('dark-theme');
                    document.body.setAttribute('data-layout-config', JSON.stringify({
                        "leftSideBarTheme": "dark",
                        "layoutBoxed": false,
                        "leftSidebarCondensed": false,
                        "leftSidebarScrollable": false,
                        "darkMode": false,
                        "showRightSidebarOnStart": true
                    }));
                }
            }

            var currentTheme = localStorage.getItem('hyper_theme') || 'light';
            applyTheme(currentTheme);

            if (toggleBtn) {
                toggleBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    var newTheme = (localStorage.getItem('hyper_theme') === 'dark') ? 'light' : 'dark';
                    localStorage.setItem('hyper_theme', newTheme);
                    applyTheme(newTheme);
                });
            }
        })();

        // Command Palette Spotlight Controller
        (function() {
            var modalEl = document.getElementById('commandPaletteModal');
            if (!modalEl) return;

            var paletteModal = null;
            var inputEl = document.getElementById('command-palette-input');
            var resultsEl = document.getElementById('command-palette-results');
            var desktopTrigger = document.getElementById('global-search-trigger');
            var mobileTrigger = document.getElementById('mobile-search-trigger');

            var currentItems = [];
            var activeIndex = 0;
            var debounceTimer = null;

            function getBootstrapModal() {
                if (!paletteModal && window.bootstrap && window.bootstrap.Modal) {
                    paletteModal = new bootstrap.Modal(modalEl, { backdrop: true, keyboard: true });
                }
                return paletteModal;
            }

            function openPalette(initialQuery) {
                initialQuery = initialQuery || '';
                var modal = getBootstrapModal();
                if (modal) modal.show();
                setTimeout(function() {
                    if (inputEl) {
                        inputEl.value = initialQuery;
                        inputEl.focus();
                        executeSearch(initialQuery);
                    }
                }, 150);
            }

            if (desktopTrigger) desktopTrigger.addEventListener('click', function() { openPalette(); });
            if (mobileTrigger) mobileTrigger.addEventListener('click', function() { openPalette(); });

            // Global shortcut Ctrl+K, Cmd+K, or /
            document.addEventListener('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && (e.key === 'k' || e.key === 'K')) {
                    e.preventDefault();
                    openPalette();
                } else if (e.key === '/' && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
                    e.preventDefault();
                    openPalette();
                }
            });

            if (inputEl) {
                inputEl.addEventListener('input', function() {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(function() {
                        executeSearch(inputEl.value);
                    }, 150);
                });

                inputEl.addEventListener('keydown', function(e) {
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        if (currentItems.length > 0) {
                            activeIndex = (activeIndex + 1) % currentItems.length;
                            updateActiveItem();
                        }
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        if (currentItems.length > 0) {
                            activeIndex = (activeIndex - 1 + currentItems.length) % currentItems.length;
                            updateActiveItem();
                        }
                    } else if (e.key === 'Enter') {
                        e.preventDefault();
                        if (currentItems[activeIndex]) {
                            selectItem(currentItems[activeIndex]);
                        }
                    }
                });
            }

            function executeSearch(query) {
                var q = (query || '').trim();
                if (q.length === 0) {
                    renderDefaultState();
                    return;
                }

                resultsEl.innerHTML = '<div class="text-center py-4 text-muted font-14"><i class="mdi mdi-loading mdi-spin me-1 font-18 text-primary"></i> Searching workspace...</div>';

                fetch('<?= site_url("api/search") ?>?q=' + encodeURIComponent(q))
                    .then(function(res) { return res.json(); })
                    .then(function(data) {
                        if (data.status === 'success' && data.results && data.results.length > 0) {
                            renderResults(data.results, q);
                        } else {
                            resultsEl.innerHTML = '<div class="text-center py-4 text-muted">' +
                                '<i class="uil-search-alt font-24 mb-1 d-block"></i>' +
                                '<div class="font-14">No matching items found for "<strong>' + escapeHtml(q) + '</strong>"</div>' +
                                '<div class="font-12 mt-1">Try searching for a ticket title, project name, or type <strong>&gt;</strong> for action commands.</div>' +
                            '</div>';
                            currentItems = [];
                        }
                    })
                    .catch(function(err) {
                        console.error('Search error:', err);
                        resultsEl.innerHTML = '<div class="text-center py-3 text-danger font-13">Failed to load search results. Please try again.</div>';
                    });
            }

            function renderDefaultState() {
                var recents = getRecents();
                var html = '';

                if (recents.length > 0) {
                    html += '<div class="palette-section-title">Recent Searches</div>';
                    recents.forEach(function(item, idx) {
                        html += renderItemHtml(item, idx);
                    });
                }

                var suggestedActions = [
                    { title: 'Create New Project', subtitle: 'Launch a new agile sprint or kanban project', icon: 'uil-plus-circle text-primary', badge: 'Action', badge_class: 'bg-info-lighten text-info', url: '<?= site_url("projects/create") ?>' },
                    { title: 'Go to Kanban Board', subtitle: 'Interactive drag-and-drop workspace', icon: 'uil-clipboard-alt text-info', badge: 'Action', badge_class: 'bg-info-lighten text-info', url: '<?= site_url("kanban") ?>' },
                    { title: 'Time Tracker & Timesheets', subtitle: 'Log hours and track activity', icon: 'uil-clock text-warning', badge: 'Action', badge_class: 'bg-info-lighten text-info', url: '<?= site_url("time") ?>' },
                    { title: 'Velocity & Analytics', subtitle: 'Sprint metrics and team performance', icon: 'uil-chart-line text-purple', badge: 'Action', badge_class: 'bg-info-lighten text-info', url: '<?= site_url("analytics") ?>' },
                    { title: 'Toggle Dark / Light Theme', subtitle: 'Switch interface color scheme', icon: 'uil-moon text-warning', badge: 'Action', badge_class: 'bg-info-lighten text-info', url: 'javascript:void(0);', action: 'toggleTheme' }
                ];

                html += '<div class="palette-section-title">Quick Actions</div>';
                var startIdx = recents.length;
                suggestedActions.forEach(function(act, idx) {
                    html += renderItemHtml(act, startIdx + idx);
                });

                resultsEl.innerHTML = html;
                currentItems = recents.concat(suggestedActions);
                activeIndex = 0;
                updateActiveItem();
            }

            function renderResults(results, query) {
                var groups = {};
                results.forEach(function(item) {
                    var cat = item.category || 'Results';
                    if (!groups[cat]) groups[cat] = [];
                    groups[cat].push(item);
                });

                var html = '';
                currentItems = [];
                var indexCounter = 0;

                for (var category in groups) {
                    html += '<div class="palette-section-title">' + escapeHtml(category) + '</div>';
                    groups[category].forEach(function(item) {
                        currentItems.push(item);
                        html += renderItemHtml(item, indexCounter++);
                    });
                }

                resultsEl.innerHTML = html;
                activeIndex = 0;
                updateActiveItem();
            }

            function renderItemHtml(item, index) {
                return '<a href="' + (item.url || 'javascript:void(0);') + '" class="palette-item d-flex align-items-center px-3 py-2 text-decoration-none rounded-2 mb-1" data-index="' + index + '" onclick="handlePaletteClick(event, ' + index + ')">' +
                    '<div class="palette-icon me-3 font-20"><i class="' + (item.icon || 'uil-angle-right') + '"></i></div>' +
                    '<div class="flex-grow-1 text-truncate">' +
                        '<div class="fw-bold font-14 palette-title">' + escapeHtml(item.title) + '</div>' +
                        '<div class="font-12 text-muted palette-subtitle">' + escapeHtml(item.subtitle || '') + '</div>' +
                    '</div>' +
                    (item.badge ? '<span class="badge ' + (item.badge_class || 'bg-light text-dark') + ' font-11 ms-2">' + escapeHtml(item.badge) + '</span>' : '') +
                '</a>';
            }

            window.handlePaletteClick = function(e, index) {
                e.preventDefault();
                if (currentItems[index]) {
                    selectItem(currentItems[index]);
                }
            };

            function selectItem(item) {
                if (!item) return;

                if (!item.action) {
                    saveRecent(item);
                }

                var modal = getBootstrapModal();
                if (modal) modal.hide();

                if (item.action === 'toggleTheme') {
                    document.getElementById('theme-toggle-btn')?.click();
                } else if (item.url && item.url !== 'javascript:void(0);') {
                    window.location.href = item.url;
                }
            }

            function updateActiveItem() {
                var itemEls = resultsEl.querySelectorAll('.palette-item');
                itemEls.forEach(function(el, idx) {
                    if (idx === activeIndex) {
                        el.classList.add('active');
                        el.scrollIntoView({ block: 'nearest' });
                    } else {
                        el.classList.remove('active');
                    }
                });
            }

            function getRecents() {
                try {
                    return JSON.parse(localStorage.getItem('cj_recent_searches') || '[]');
                } catch(e) {
                    return [];
                }
            }

            function saveRecent(item) {
                try {
                    var recents = getRecents();
                    recents = recents.filter(function(r) { return r.url !== item.url; });
                    recents.unshift({
                        title: item.title,
                        subtitle: item.subtitle,
                        icon: item.icon,
                        badge: item.badge,
                        badge_class: item.badge_class,
                        url: item.url
                    });
                    localStorage.setItem('cj_recent_searches', JSON.stringify(recents.slice(0, 4)));
                } catch(e) {}
            }

            function escapeHtml(text) {
                if (!text) return '';
                var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
                return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
            }
        })();

        // ==========================================
        // Live Notification Bell System
        // ==========================================
        (function() {
            var badgeEl = document.getElementById('notification-badge');
            var dropdownCountEl = document.getElementById('dropdown-unread-count');
            var containerEl = document.getElementById('notification-items-container');
            var markAllBtn = document.getElementById('mark-all-read-btn');

            if (!badgeEl || !containerEl) return;

            // Handle mark single notification as read on click
            containerEl.addEventListener('click', function(e) {
                var item = e.target.closest('.notif-item');
                if (!item) return;
                var notifId = item.getAttribute('data-id');
                if (notifId) {
                    fetch('<?= base_url('api/notifications/mark-read') ?>/' + notifId, {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    }).then(function(r) { return r.json(); })
                      .then(function(data) {
                          if (data.status === 'success') {
                              updateBadge(data.unread_count);
                              item.classList.remove('bg-light-lighten');
                              item.classList.add('opacity-75');
                              var dot = item.querySelector('.bg-danger');
                              if (dot) dot.remove();
                          }
                      }).catch(function() {});
                }
            });

            // Handle Mark All Read
            if (markAllBtn) {
                markAllBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    fetch('<?= base_url('api/notifications/mark-all-read') ?>', {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    }).then(function(r) { return r.json(); })
                      .then(function(data) {
                          if (data.status === 'success') {
                              updateBadge(0);
                              var items = containerEl.querySelectorAll('.notif-item');
                              items.forEach(function(el) {
                                  el.classList.remove('bg-light-lighten');
                                  el.classList.add('opacity-75');
                                  var title = el.querySelector('h6');
                                  if (title) { title.classList.remove('fw-bold', 'text-dark'); title.classList.add('text-muted'); }
                                  var dot = el.querySelector('.bg-danger');
                                  if (dot) dot.remove();
                              });
                          }
                      }).catch(function() {});
                });
            }

            function updateBadge(count) {
                if (count > 0) {
                    badgeEl.style.display = 'inline-block';
                    badgeEl.textContent = count > 99 ? '99+' : count;
                } else {
                    badgeEl.style.display = 'none';
                    badgeEl.textContent = '0';
                }
                if (dropdownCountEl) dropdownCountEl.textContent = count;
            }

            // Polling for live notifications every 30 seconds
            function pollNotifications() {
                fetch('<?= base_url('api/notifications/unread-count') ?>', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.status === 'success') {
                        updateBadge(data.unread_count);
                        if (data.notifications && data.notifications.length > 0) {
                            renderNotifications(data.notifications);
                        }
                    }
                })
                .catch(function() {});
            }

            function renderNotifications(items) {
                var html = '';
                items.forEach(function(notif) {
                    var isUnread = notif.is_read === 0;
                    html += '<a href="' + (notif.action_url || 'javascript:void(0);') + '" class="dropdown-item notify-item py-2 px-3 border-bottom notif-item ' + (isUnread ? 'bg-light-lighten' : 'opacity-75') + '" data-id="' + notif.id + '">' +
                        '<div class="d-flex align-items-start">' +
                            '<div class="avatar-xs me-2 flex-shrink-0">' +
                                '<span class="avatar-title rounded-circle font-14 ' + notif.bg_class + '">' +
                                    '<i class="mdi ' + notif.icon + '"></i>' +
                                '</span>' +
                            '</div>' +
                            '<div class="flex-grow-1 overflow-hidden">' +
                                '<div class="d-flex justify-content-between align-items-center">' +
                                    '<h6 class="m-0 font-12 fw-semibold text-truncate ' + (isUnread ? 'text-dark fw-bold' : 'text-muted') + '">' +
                                        notif.title +
                                    '</h6>' +
                                    (isUnread ? '<span class="badge bg-danger rounded-circle p-1 ms-1" style="width: 6px; height: 6px;"></span>' : '') +
                                '</div>' +
                                '<p class="font-11 text-muted mb-0 text-truncate">' + notif.body + '</p>' +
                                '<small class="text-muted font-10"><i class="mdi mdi-clock-outline"></i> ' + notif.time_ago + '</small>' +
                            '</div>' +
                        '</div>' +
                    '</a>';
                });
                containerEl.innerHTML = html;
            }

            // Start polling timer
            setInterval(pollNotifications, 30000);
        })();
    </script>
    
    <?= $this->renderSection('js') ?>
</body>
</html>
