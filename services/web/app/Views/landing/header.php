<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title><?= esc($pageTitle ?? (setting('App.siteName') . ' — Modern Agile Project Management Platform')) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= esc($metaDescription ?? 'Supercharge team velocity with interactive Kanban boards, sprint analytics, live stopwatch time tracking, and team workload coordination.') ?>" />
    <meta name="keywords" content="agile project management, kanban board, sprint planner, jira alternative, team collaboration, time tracker, codeigniter 4, developer workflow">
    <meta name="author" content="<?= esc(setting('App.siteName')) ?> Team">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="#727cf5">

    <!-- Open Graph / Social -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= current_url() ?>">
    <meta property="og:title" content="<?= esc($pageTitle ?? (setting('App.siteName') . ' — Modern Agile Project Management')) ?>">
    <meta property="og:description" content="<?= esc($metaDescription ?? 'Supercharge team velocity with interactive Kanban boards, sprint analytics, and live time tracking.') ?>">
    <meta property="og:image" content="<?= base_url('assets/img/app_hero.jpg') ?>">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="<?= current_url() ?>">
    <meta name="twitter:title" content="<?= esc($pageTitle ?? (setting('App.siteName') . ' — Modern Agile Project Management')) ?>">
    <meta name="twitter:description" content="<?= esc($metaDescription ?? 'Supercharge team velocity with interactive Kanban boards, sprint analytics, and live time tracking.') ?>">
    <meta name="twitter:image" content="<?= base_url('assets/img/app_hero.jpg') ?>">

    <!-- App favicon -->
    <link rel="icon" type="image/jpeg" href="<?= base_url('assets/img/app_logo.jpg') ?>">
    <link rel="apple-touch-icon" href="<?= base_url('assets/img/app_logo.jpg') ?>">

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
        .landing-navbar {
            padding: 14px 0;
            background-color: #1e293b;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 4px 20px 0 rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }
        .landing-navbar .nav-link { 
            color: rgba(255, 255, 255, 0.75); 
            font-weight: 500;
            padding: 8px 14px !important;
            transition: all 0.2s ease;
        }
        .landing-navbar .nav-link:hover,
        .landing-navbar .nav-link.active { 
            color: #ffffff; 
            font-weight: 600;
        }
        .landing-navbar .navbar-brand { 
            color: #ffffff; 
            font-weight: 700; 
            font-size: 20px; 
            letter-spacing: -0.5px;
        }
        #theme-toggle-btn {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 50%;
            width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            color: #ffffff;
        }
        #theme-toggle-btn:hover {
            background: rgba(255, 255, 255, 0.18);
            transform: scale(1.05);
        }
        .hero-section {
            padding: 70px 0 50px 0;
            position: relative;
        }
        .feature-card {
            border: 1px solid var(--bs-border-color, rgba(154, 161, 171, 0.2));
            border-radius: 12px;
            box-shadow: 0 4px 20px 0 rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease-in-out;
            height: 100%;
        }
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        }
        .avatar-title {
            align-items: center;
            display: flex;
            height: 100%;
            justify-content: center;
            width: 100%;
        }
        .bg-primary-lighten {
            background-color: rgba(114, 124, 245, 0.12) !important;
        }
        .bg-success-lighten {
            background-color: rgba(10, 207, 151, 0.12) !important;
        }
        .bg-danger-lighten {
            background-color: rgba(250, 92, 124, 0.12) !important;
        }
        .bg-warning-lighten {
            background-color: rgba(255, 188, 0, 0.12) !important;
        }
        .bg-info-lighten {
            background-color: rgba(57, 175, 209, 0.12) !important;
        }
        .bg-dark-lighten {
            background-color: rgba(49, 58, 70, 0.12) !important;
        }
    </style>
</head>
<body class="loading" data-layout-config='{"darkMode":false}'>

    <!-- NAVBAR START -->
    <nav class="navbar navbar-expand-lg landing-navbar sticky-top">
        <div class="container">
            <!-- logo -->
            <a href="<?= site_url() ?>" class="navbar-brand me-4 d-inline-flex align-items-center">
                <img src="<?= base_url('assets/img/app_logo.jpg') ?>" class="rounded-circle me-2" height="28" width="28" alt="Logo">
                <span><?= esc(setting('App.siteName')) ?></span>
            </a>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <i class="mdi mdi-menu text-white font-22"></i>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link <?= uri_string() === '' ? 'active' : '' ?>" href="<?= site_url() ?>">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= uri_string() === 'features' ? 'active' : '' ?>" href="<?= site_url('features') ?>">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= uri_string() === 'pricing' ? 'active' : '' ?>" href="<?= site_url('pricing') ?>">Pricing</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= uri_string() === 'compare' ? 'active' : '' ?>" href="<?= site_url('compare') ?>">Compare</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= uri_string() === 'community' ? 'active' : '' ?>" href="<?= site_url('community') ?>">Community</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= uri_string() === 'setup' ? 'active' : '' ?>" href="<?= site_url('setup') ?>">Setup</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= uri_string() === 'faqs' ? 'active' : '' ?>" href="<?= site_url('faqs') ?>">FAQs</a>
                    </li>
                    <!-- Theme Toggle Switch -->
                    <li class="nav-item ms-lg-2 my-2 my-lg-0">
                        <button type="button" id="theme-toggle-btn" title="Toggle Light/Dark Theme">
                            <i class="mdi mdi-weather-night font-18" id="theme-toggle-icon"></i>
                        </button>
                    </li>
                    <?php if (auth()->loggedIn()): ?>
                        <li class="nav-item ms-lg-3 mt-2 mt-lg-0">
                            <a href="<?= site_url('user/dashboard') ?>" class="btn btn-primary btn-sm rounded-pill px-3">
                                <i class="mdi mdi-view-dashboard me-1"></i> Dashboard
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item ms-lg-3 mt-2 mt-lg-0">
                            <a href="<?= site_url('auth/login') ?>" class="nav-link">Log In</a>
                        </li>
                        <li class="nav-item ms-lg-2 mt-2 mt-lg-0">
                            <a href="<?= site_url('auth/register') ?>" class="btn btn-primary btn-sm rounded-pill px-3">
                                <i class="mdi mdi-account-plus me-1"></i> Get Started Free
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <!-- NAVBAR END -->
