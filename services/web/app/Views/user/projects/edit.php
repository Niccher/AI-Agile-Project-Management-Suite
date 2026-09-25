<?= $this->extend('layouts/hyper/main') ?>

<?= $this->section('title') ?>Edit: <?= esc($project['name']) ?> • <?= esc(setting('App.siteName')) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
$initials = strtoupper(substr($user->first_name ?? $user->username ?? 'U', 0, 1) . substr($user->last_name ?? '', 0, 1));
$categories = is_array($categories ?? null) ? $categories : (!empty($project['categories']) ? explode(',', $project['categories']) : []);
$categories = array_map('trim', $categories);
$tech_stack = is_array($tech_stack ?? null) ? $tech_stack : (!empty($project['tech_stack']) ? explode(',', $project['tech_stack']) : []);
$tech_stack = array_map('trim', array_filter($tech_stack));
$milestones = is_array($milestones ?? null) ? $milestones : [];
$editSlug = !empty($project['slug']) ? $project['slug'] : $project['id'];

// Safe due date fallback
$dueDateVal = $project['due_date'] ?? '';
$hasValidDue = !empty($dueDateVal) && $dueDateVal !== '0000-00-00' && $dueDateVal !== '0000-00-00 00:00:00' && strtotime($dueDateVal) > 0;
$dueDateFormatted = $hasValidDue ? date('Y-m-d', strtotime($dueDateVal)) : date('Y-m-d', strtotime('+2 months'));

$startDateVal = $project['start_date'] ?? '';
$hasValidStart = !empty($startDateVal) && $startDateVal !== '0000-00-00' && $startDateVal !== '0000-00-00 00:00:00' && strtotime($startDateVal) > 0;
$startDateFormatted = $hasValidStart ? date('Y-m-d', strtotime($startDateVal)) : date('Y-m-d');
?>

<!-- Page Header -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <div class="btn-group">
                    <a href="<?= site_url('projects/view/' . $editSlug) ?>" class="btn btn-outline-primary btn-sm">
                        <i class="mdi mdi-eye me-1"></i> View Project
                    </a>
                    <a href="<?= site_url('projects') ?>" class="btn btn-outline-secondary btn-sm">
                        <i class="mdi mdi-arrow-left me-1"></i> All Projects
                    </a>
                </div>
            </div>
            <h4 class="page-title">
                <i class="mdi mdi-pencil me-2 text-primary"></i> Edit Project: <?= esc($project['name']) ?>
            </h4>
        </div>
    </div>
</div>

<!-- Live Container Telemetry Stream Widget -->
<div class="row mb-3">
    <div class="col-xl-9 col-lg-10 mx-auto">
        <div class="card shadow-sm border-0 bg-dark text-white mb-0" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
            <div class="card-body py-2 px-3">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 font-12">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-success-lighten text-success p-1 px-2 rounded-pill font-11">
                            <i class="mdi mdi-circle font-10 me-1" style="animation: pulse 1.5s infinite;"></i> Live Telemetry
                        </span>
                        <span class="text-white-50">Cluster Node Vitals:</span>
                    </div>
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <span id="liveCpu"><i class="mdi mdi-cpu-64-bit text-info me-1"></i> CPU: <strong>--%</strong></span>
                        <span id="liveRam"><i class="mdi mdi-memory text-warning me-1"></i> RAM: <strong>--</strong></span>
                        <span id="liveDisk"><i class="mdi mdi-harddisk text-success me-1"></i> Disk: <strong>--</strong></span>
                        <span id="liveDb"><i class="mdi mdi-database text-primary me-1"></i> MySQL: <strong class="text-success">Connected</strong></span>
                        <span class="text-white-50 font-11" id="liveTime">Updated: <?= date('H:i:s') ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Project Overview Bar -->
<div class="row mb-3">
    <div class="col-xl-9 col-lg-10 mx-auto">
        <div class="card shadow-sm border-0 mb-0">
            <div class="card-body py-3">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div class="d-flex align-items-center">
                        <div class="avatar-md rounded-circle d-flex align-items-center justify-content-center text-white me-3" 
                             style="background-color: <?= esc($project['color'] ?? '#3e60d5') ?>; font-size: 22px; width: 48px; height: 48px;">
                            <i class="mdi <?= esc($project['icon'] ?? 'mdi-folder-outline') ?>"></i>
                        </div>
                        <div>
                            <h4 class="mb-1"><?= esc($project['name']) ?></h4>
                            <p class="text-muted font-13 mb-0">
                                Last updated: <?= !empty($project['updated_at']) ? date('M d, Y H:i', strtotime($project['updated_at'])) : 'Recently' ?>
                            </p>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <a href="<?= site_url('projects/time/' . $editSlug) ?>" class="btn btn-sm btn-outline-primary">
                            <i class="mdi mdi-clock-outline me-1"></i> Track Time
                        </a>
                        <a href="<?= site_url('projects/kanban/' . $editSlug) ?>" class="btn btn-sm btn-outline-info">
                            <i class="mdi mdi-view-column me-1"></i> Kanban
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Project Edit Form -->
<div class="row">
    <div class="col-xl-9 col-lg-10 mx-auto">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="header-title mb-0">
                    <i class="mdi mdi-tune-vertical me-1 text-primary"></i> Project Details &amp; Configuration
                </h5>
                <span class="badge bg-success-lighten text-success font-13">
                    Progress: <?= (int)($project['progress'] ?? 0) ?>%
                </span>
            </div>
            <div class="card-body p-4">
                <form id="projectEditForm" method="post" action="<?= site_url('projects/update/' . $editSlug) ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= $project['id'] ?>">

                    <!-- Basic Information -->
                    <div class="mb-4">
                        <h6 class="text-uppercase text-muted font-12 fw-bold mb-3">
                            <i class="mdi mdi-information-outline me-1"></i> Basic Information
                        </h6>

                        <!-- Project Name -->
                        <div class="mb-3">
                            <label for="projectName" class="form-label fw-semibold">
                                Project Name <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="mdi mdi-format-title"></i></span>
                                <input type="text" class="form-control form-control-lg" id="projectName" name="name"
                                       value="<?= esc($project['name']) ?>" required>
                            </div>
                        </div>

                        <!-- Short Description -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label for="projectDescription" class="form-label fw-semibold mb-0">
                                    Description / Goal <span class="text-danger">*</span>
                                </label>
                                <small class="text-muted" id="descCounter"><?= strlen($project['description'] ?? '') ?>/500</small>
                            </div>
                            <textarea class="form-control" id="projectDescription" name="description" rows="3"
                                      required><?= esc($project['description']) ?></textarea>
                            <div class="form-text">Briefly describe the project's purpose and target outcomes.</div>
                        </div>

                        <!-- Technology Stack with Autocomplete & Suggestions -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Technology Stack</label>
                            
                            <!-- Selected Tags -->
                            <div class="d-flex flex-wrap gap-1 mb-2" id="selectedTech">
                                <?php if (!empty($tech_stack)): ?>
                                    <?php foreach ($tech_stack as $tech): if (empty(trim($tech))) continue; ?>
                                    <span class="badge bg-primary-lighten text-primary font-12 d-inline-flex align-items-center gap-1 p-2 rounded">
                                        <i class="mdi mdi-code-tags font-12"></i> <?= esc(trim($tech)) ?>
                                        <span class="cursor-pointer remove-tech ms-1 text-danger font-14" data-tech="<?= esc(trim($tech)) ?>" style="cursor: pointer;">&times;</span>
                                    </span>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>

                            <!-- Input with Datalist Autocomplete -->
                            <div class="input-group mb-2">
                                <span class="input-group-text"><i class="mdi mdi-code-tags"></i></span>
                                <input type="text" class="form-control" id="techInput" list="techSuggestions"
                                       placeholder="Type or select a technology (e.g. PHP, React, FastAPI, Docker)..." autocomplete="off">
                                <button class="btn btn-primary" type="button" id="addTech">
                                    <i class="mdi mdi-plus me-1"></i> Add Tech
                                </button>
                            </div>

                            <datalist id="techSuggestions">
                                <option value="PHP"></option>
                                <option value="CodeIgniter 4"></option>
                                <option value="Laravel"></option>
                                <option value="Symfony"></option>
                                <option value="Python"></option>
                                <option value="FastAPI"></option>
                                <option value="Django"></option>
                                <option value="Flask"></option>
                                <option value="JavaScript"></option>
                                <option value="TypeScript"></option>
                                <option value="React"></option>
                                <option value="Vue.js"></option>
                                <option value="Angular"></option>
                                <option value="Next.js"></option>
                                <option value="Node.js"></option>
                                <option value="Express"></option>
                                <option value="NestJS"></option>
                                <option value="Docker"></option>
                                <option value="Kubernetes"></option>
                                <option value="MySQL"></option>
                                <option value="PostgreSQL"></option>
                                <option value="Redis"></option>
                                <option value="MongoDB"></option>
                                <option value="GraphQL"></option>
                                <option value="Tailwind CSS"></option>
                                <option value="Bootstrap 5"></option>
                                <option value="Go"></option>
                                <option value="Rust"></option>
                                <option value="Java"></option>
                                <option value="Spring Boot"></option>
                                <option value="Kotlin"></option>
                                <option value="Flutter"></option>
                                <option value="Swift"></option>
                                <option value="AWS"></option>
                                <option value="GCP"></option>
                                <option value="Railway"></option>
                                <option value="Linux"></option>
                                <option value="Git"></option>
                                <option value="llama.cpp"></option>
                                <option value="GGUF"></option>
                                <option value="PyTorch"></option>
                            </datalist>

                            <!-- Quick Suggestion Chips -->
                            <div class="d-flex flex-wrap gap-1 align-items-center">
                                <small class="text-muted me-1 font-11">Quick Add:</small>
                                <?php 
                                $popularTech = ['PHP', 'CodeIgniter 4', 'Python', 'FastAPI', 'React', 'Vue.js', 'TypeScript', 'Docker', 'MySQL', 'Redis', 'Tailwind CSS', 'Next.js', 'llama.cpp'];
                                foreach ($popularTech as $pt):
                                ?>
                                    <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill py-0 px-2 font-11 quick-tech-btn" data-tech="<?= esc($pt) ?>">
                                        + <?= esc($pt) ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>

                            <input type="hidden" name="tech_stack" id="techStack" value="<?= esc(implode(',', $tech_stack)) ?>">
                        </div>
                    </div>

                    <!-- Project Parameters -->
                    <div class="mb-4 border-top pt-3">
                        <h6 class="text-uppercase text-muted font-12 fw-bold mb-3">
                            <i class="mdi mdi-tune-vertical me-1"></i> Status &amp; Scheduling
                        </h6>

                        <div class="row g-3 mb-3">
                            <!-- Status & Priority -->
                            <div class="col-md-6">
                                <label for="projectStatus" class="form-label fw-semibold">
                                    Status <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="projectStatus" name="status" required>
                                    <option value="planning" <?= ($project['status'] ?? '') === 'planning' ? 'selected' : '' ?>>📋 Planning</option>
                                    <option value="in_progress" <?= ($project['status'] ?? '') === 'in_progress' ? 'selected' : '' ?>>🚀 In Progress</option>
                                    <option value="testing" <?= ($project['status'] ?? '') === 'testing' ? 'selected' : '' ?>>🧪 Testing</option>
                                    <option value="completed" <?= ($project['status'] ?? '') === 'completed' ? 'selected' : '' ?>>✅ Completed</option>
                                    <option value="on_hold" <?= ($project['status'] ?? '') === 'on_hold' ? 'selected' : '' ?>>⏸️ On Hold</option>
                                    <option value="abandoned" <?= ($project['status'] ?? '') === 'abandoned' ? 'selected' : '' ?>>❌ Abandoned</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="projectPriority" class="form-label fw-semibold">
                                    Priority <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="projectPriority" name="priority" required>
                                    <option value="low" <?= ($project['priority'] ?? '') === 'low' ? 'selected' : '' ?>>🟢 Low</option>
                                    <option value="medium" <?= ($project['priority'] ?? '') === 'medium' ? 'selected' : '' ?>>🟡 Medium</option>
                                    <option value="high" <?= ($project['priority'] ?? '') === 'high' ? 'selected' : '' ?>>🟠 High</option>
                                    <option value="critical" <?= ($project['priority'] ?? '') === 'critical' ? 'selected' : '' ?>>🔴 Critical</option>
                                </select>
                            </div>
                        </div>

                        <!-- Dates -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="projectStartDate" class="form-label fw-semibold">Start Date</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="mdi mdi-calendar"></i></span>
                                    <input type="date" class="form-control" id="projectStartDate" name="start_date"
                                           value="<?= esc($startDateFormatted) ?>">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="projectDueDate" class="form-label fw-semibold">Target Completion</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="mdi mdi-calendar-check"></i></span>
                                    <input type="date" class="form-control" id="projectDueDate" name="due_date"
                                           value="<?= esc($dueDateFormatted) ?>">
                                </div>
                                <div class="form-text d-flex justify-content-between align-items-center">
                                    <span class="font-11 text-muted">Defaulted to 2 months if unset.</span>
                                    <span id="daysRemaining" class="fw-semibold"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Progress Slider -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label for="projectProgress" class="form-label fw-semibold mb-0">
                                    Current Progress
                                </label>
                                <span id="progressValue" class="badge bg-primary-lighten text-primary font-12"><?= (int)($project['progress'] ?? 0) ?>%</span>
                            </div>
                            <input type="range" class="form-range" id="projectProgress" name="progress"
                                   min="0" max="100" value="<?= (int)($project['progress'] ?? 0) ?>" step="5">
                            <div class="d-flex justify-content-between font-12 text-muted">
                                <span>0% Not Started</span>
                                <span>50% In Progress</span>
                                <span>100% Complete</span>
                            </div>
                        </div>

                        <!-- Repository -->
                        <div class="mb-3">
                            <label for="projectRepo" class="form-label fw-semibold">Repository URL</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="mdi mdi-github"></i></span>
                                <input type="url" class="form-control" id="projectRepo" name="repository_url"
                                       value="<?= esc($project['repository_url'] ?? '') ?>" placeholder="https://github.com/username/project">
                            </div>
                            <?php if (!empty($project['repository_url'])): ?>
                            <div class="form-text">
                                <a href="<?= esc($project['repository_url']) ?>" target="_blank" class="text-primary">
                                    <i class="mdi mdi-open-in-new me-1"></i> Open Repository in New Tab
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Project Categories (All 14 Modern Categories) -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold d-block">Project Categories</label>
                            <div class="d-flex flex-wrap gap-2">
                                <div class="form-check border rounded px-3 py-2 bg-light-subtle">
                                    <input class="form-check-input" type="checkbox" value="web_app" id="catWeb" <?= in_array('web_app', $categories) ? 'checked' : '' ?>>
                                    <label class="form-check-label font-13" for="catWeb">
                                        <i class="mdi mdi-web text-primary me-1"></i> Web Application
                                    </label>
                                </div>
                                <div class="form-check border rounded px-3 py-2 bg-light-subtle">
                                    <input class="form-check-input" type="checkbox" value="api" id="catAPI" <?= in_array('api', $categories) ? 'checked' : '' ?>>
                                    <label class="form-check-label font-13" for="catAPI">
                                        <i class="mdi mdi-server-network text-success me-1"></i> API / Backend
                                    </label>
                                </div>
                                <div class="form-check border rounded px-3 py-2 bg-light-subtle">
                                    <input class="form-check-input" type="checkbox" value="mobile" id="catMobile" <?= in_array('mobile', $categories) ? 'checked' : '' ?>>
                                    <label class="form-check-label font-13" for="catMobile">
                                        <i class="mdi mdi-cellphone text-info me-1"></i> Mobile App
                                    </label>
                                </div>
                                <div class="form-check border rounded px-3 py-2 bg-light-subtle">
                                    <input class="form-check-input" type="checkbox" value="ai_ml" id="catAi" <?= in_array('ai_ml', $categories) ? 'checked' : '' ?>>
                                    <label class="form-check-label font-13" for="catAi">
                                        <i class="mdi mdi-brain text-purple me-1"></i> AI / Machine Learning
                                    </label>
                                </div>
                                <div class="form-check border rounded px-3 py-2 bg-light-subtle">
                                    <input class="form-check-input" type="checkbox" value="devops" id="catDevops" <?= in_array('devops', $categories) ? 'checked' : '' ?>>
                                    <label class="form-check-label font-13" for="catDevops">
                                        <i class="mdi mdi-cloud-sync text-warning me-1"></i> DevOps &amp; CI/CD
                                    </label>
                                </div>
                                <div class="form-check border rounded px-3 py-2 bg-light-subtle">
                                    <input class="form-check-input" type="checkbox" value="database" id="catDb" <?= in_array('database', $categories) ? 'checked' : '' ?>>
                                    <label class="form-check-label font-13" for="catDb">
                                        <i class="mdi mdi-database text-danger me-1"></i> Database &amp; Big Data
                                    </label>
                                </div>
                                <div class="form-check border rounded px-3 py-2 bg-light-subtle">
                                    <input class="form-check-input" type="checkbox" value="security" id="catSecurity" <?= in_array('security', $categories) ? 'checked' : '' ?>>
                                    <label class="form-check-label font-13" for="catSecurity">
                                        <i class="mdi mdi-shield-lock text-danger me-1"></i> Security &amp; Compliance
                                    </label>
                                </div>
                                <div class="form-check border rounded px-3 py-2 bg-light-subtle">
                                    <input class="form-check-input" type="checkbox" value="ui_ux" id="catUi" <?= in_array('ui_ux', $categories) ? 'checked' : '' ?>>
                                    <label class="form-check-label font-13" for="catUi">
                                        <i class="mdi mdi-palette text-pink me-1"></i> UI/UX Design
                                    </label>
                                </div>
                                <div class="form-check border rounded px-3 py-2 bg-light-subtle">
                                    <input class="form-check-input" type="checkbox" value="ecommerce" id="catEcom" <?= in_array('ecommerce', $categories) ? 'checked' : '' ?>>
                                    <label class="form-check-label font-13" for="catEcom">
                                        <i class="mdi mdi-cart-outline text-success me-1"></i> E-Commerce
                                    </label>
                                </div>
                                <div class="form-check border rounded px-3 py-2 bg-light-subtle">
                                    <input class="form-check-input" type="checkbox" value="automation" id="catAuto" <?= in_array('automation', $categories) ? 'checked' : '' ?>>
                                    <label class="form-check-label font-13" for="catAuto">
                                        <i class="mdi mdi-robot text-primary me-1"></i> Automation &amp; Bots
                                    </label>
                                </div>
                                <div class="form-check border rounded px-3 py-2 bg-light-subtle">
                                    <input class="form-check-input" type="checkbox" value="open_source" id="catOs" <?= in_array('open_source', $categories) ? 'checked' : '' ?>>
                                    <label class="form-check-label font-13" for="catOs">
                                        <i class="mdi mdi-github text-dark me-1"></i> Open Source
                                    </label>
                                </div>
                                <div class="form-check border rounded px-3 py-2 bg-light-subtle">
                                    <input class="form-check-input" type="checkbox" value="learning" id="catLearning" <?= in_array('learning', $categories) ? 'checked' : '' ?>>
                                    <label class="form-check-label font-13" for="catLearning">
                                        <i class="mdi mdi-school text-warning me-1"></i> Learning Project
                                    </label>
                                </div>
                                <div class="form-check border rounded px-3 py-2 bg-light-subtle">
                                    <input class="form-check-input" type="checkbox" value="portfolio" id="catPortfolio" <?= in_array('portfolio', $categories) ? 'checked' : '' ?>>
                                    <label class="form-check-label font-13" for="catPortfolio">
                                        <i class="mdi mdi-briefcase-check text-info me-1"></i> Portfolio Piece
                                    </label>
                                </div>
                                <div class="form-check border rounded px-3 py-2 bg-light-subtle">
                                    <input class="form-check-input" type="checkbox" value="freelance" id="catFreelance" <?= in_array('freelance', $categories) ? 'checked' : '' ?>>
                                    <label class="form-check-label font-13" for="catFreelance">
                                        <i class="mdi mdi-account-tie text-secondary me-1"></i> Freelance Work
                                    </label>
                                </div>
                            </div>
                            <input type="hidden" name="categories" id="projectCategories" value="<?= esc(implode(',', $categories)) ?>">
                        </div>
                    </div>

                    <!-- Milestones -->
                    <div class="mb-4 border-top pt-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="text-uppercase text-muted font-12 fw-bold mb-0">
                                <i class="mdi mdi-checkbox-marked-circle-outline me-1"></i> Project Milestones
                            </h6>
                            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill" id="addMilestone">
                                <i class="mdi mdi-plus me-1"></i> Add Milestone
                            </button>
                        </div>

                        <div id="milestonesContainer">
                            <?php if (!empty($milestones)): ?>
                                <?php foreach ($milestones as $index => $ms): ?>
                                <div class="milestone-entry card border mb-3 shadow-none">
                                    <div class="card-body p-3">
                                        <div class="row g-2">
                                            <div class="col-md-5">
                                                <label class="form-label font-12 mb-1">Milestone Name</label>
                                                <input type="text" class="form-control form-control-sm" value="<?= esc($ms['name'] ?? '') ?>"
                                                       name="milestones[<?= $index ?>][name]" placeholder="Milestone name">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label font-12 mb-1">Due Date</label>
                                                <input type="date" class="form-control form-control-sm" value="<?= esc($ms['due_date'] ?? '') ?>"
                                                       name="milestones[<?= $index ?>][due_date]">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label font-12 mb-1">Status</label>
                                                <select class="form-select form-select-sm" name="milestones[<?= $index ?>][status]">
                                                    <option value="pending" <?= ($ms['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                                                    <option value="in_progress" <?= ($ms['status'] ?? '') === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                                    <option value="completed" <?= ($ms['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                                                </select>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label font-12 mb-1">Description (Optional)</label>
                                                <textarea class="form-control form-control-sm" rows="2" name="milestones[<?= $index ?>][description]" placeholder="Key deliverables or notes..."><?= esc($ms['description'] ?? '') ?></textarea>
                                            </div>
                                            <div class="col-12 text-end">
                                                <button type="button" class="btn btn-sm btn-outline-danger remove-milestone">
                                                    <i class="mdi mdi-trash-can-outline me-1"></i> Remove
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Notes & Blockers -->
                    <div class="mb-4 border-top pt-3">
                        <h6 class="text-uppercase text-muted font-12 fw-bold mb-3">
                            <i class="mdi mdi-note-text-outline me-1"></i> Notes &amp; Observations
                        </h6>

                        <div class="mb-3">
                            <label for="projectNotes" class="form-label fw-semibold">Project Notes</label>
                            <textarea class="form-control font-monospace" id="projectNotes" name="notes" rows="4"
                                      placeholder="Track challenges, lessons learned, or architecture notes..."><?= esc($project['notes'] ?? '') ?></textarea>
                            <div class="form-text">Document technical decisions, next priorities, or blockers.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Current Blockers</label>
                            <div class="input-group mb-2">
                                <input type="text" class="form-control blocker-input" placeholder="Add a new blocker...">
                                <button class="btn btn-outline-danger" type="button" id="addBlocker">
                                    <i class="mdi mdi-plus"></i> Add Blocker
                                </button>
                            </div>
                            <div id="blockersList" class="d-flex flex-column gap-2">
                                <?php 
                                $blockers = !empty($project['blockers']) ? explode('|', $project['blockers']) : [];
                                foreach ($blockers as $bIdx => $bVal): if(empty(trim($bVal))) continue;
                                ?>
                                <div class="blocker-item p-2 rounded border bg-danger-lighten text-danger d-flex justify-content-between align-items-center">
                                    <span><i class="mdi mdi-alert-circle-outline me-1"></i> <?= esc(trim($bVal)) ?></span>
                                    <span class="remove cursor-pointer text-danger font-16" data-index="<?= $bIdx ?>" style="cursor: pointer;">&times;</span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <input type="hidden" name="blockers" id="projectBlockers" value="<?= esc($project['blockers'] ?? '') ?>">
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="d-flex flex-wrap justify-content-between align-items-center pt-3 border-top gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <a href="<?= site_url('projects/view/' . $editSlug) ?>" class="btn btn-light">
                                Cancel
                            </a>
                            <button type="button" class="btn btn-outline-danger" id="deleteProjectBtn">
                                <i class="mdi mdi-trash-can me-1"></i> Delete Project
                            </button>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="submit" class="btn btn-primary rounded-pill px-4" id="updateProjectBtn">
                                <i class="mdi mdi-check me-1"></i> Save Changes
                            </button>
                        </div>
                    </div>

                </form>

                <form id="deleteProjectForm" action="<?= site_url('projects/delete/' . $editSlug) ?>" method="post" style="display:none;">
                    <?= csrf_field() ?>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title text-white"><i class="mdi mdi-alert-triangle me-1"></i> Confirm Project Deletion</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="font-16">Are you sure you want to permanently delete <strong>"<?= esc($project['name']) ?>"</strong>?</p>
                <div class="alert alert-warning mb-3">
                    <i class="mdi mdi-alert-circle me-1"></i>
                    This will permanently delete all associated project records:
                    <ul class="mb-0 mt-1">
                        <li>All time tracking sessions and logs</li>
                        <li>Milestones and tasks</li>
                        <li>Project notes and observations</li>
                    </ul>
                </div>
                <p class="text-danger font-13 mb-0"><strong>Warning: This action cannot be reversed!</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="mdi mdi-trash-can me-1"></i> Delete Project
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999;"></div>

<script>
$(document).ready(function() {
    // Live Telemetry Stream AJAX every 5 seconds
    function pollTelemetry() {
        $.ajax({
            url: '<?= site_url('projects/telemetry-live') ?>',
            method: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res && res.status === 'online') {
                    $('#liveCpu strong').text(res.cpu_percent + '% (' + res.cpu_cores + 'c)');
                    $('#liveRam strong').text(res.ram_percent + '% (' + res.ram_used_human + ')');
                    $('#liveDisk strong').text(res.disk_percent + '% (' + res.disk_used_human + ')');
                    $('#liveDb strong').text(res.db_connected ? 'Connected (' + res.db_threads + ' th)' : 'Disconnected');
                    $('#liveTime').text('Live: ' + res.timestamp);
                }
            }
        });
    }
    pollTelemetry();
    setInterval(pollTelemetry, 5000);

    // Days remaining calculation
    function updateDaysRemaining() {
        const val = $('#projectDueDate').val();
        if (!val) {
            $('#daysRemaining').html('');
            return;
        }
        const dueDate = new Date(val);
        const today = new Date();
        today.setHours(0,0,0,0);
        dueDate.setHours(0,0,0,0);
        const diffTime = dueDate - today;
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

        let message = '';
        if (diffDays > 0) {
            message = `<span class="text-success">${diffDays} days remaining</span>`;
        } else if (diffDays === 0) {
            message = `<span class="text-warning fw-bold">Due today!</span>`;
        } else {
            message = `<span class="text-danger fw-bold">Overdue by ${Math.abs(diffDays)} days</span>`;
        }
        $('#daysRemaining').html(message);
    }

    $('#projectDueDate').on('change', updateDaysRemaining);
    updateDaysRemaining();

    // Character counter
    $('#projectDescription').on('input', function() {
        const length = $(this).val().length;
        $('#descCounter').text(length + '/500');
    });

    // Technology stack management
    let techStack = <?= json_encode(array_values(array_filter(array_map('trim', $tech_stack)))) ?>;

    $('#addTech').on('click', addTechnology);
    $('#techInput').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            addTechnology();
        }
    });

    $('.quick-tech-btn').on('click', function() {
        const t = $(this).data('tech');
        if (t && !techStack.includes(t)) {
            techStack.push(t);
            updateTechDisplay();
        }
    });

    function addTechnology() {
        const tech = $('#techInput').val().trim();
        if (tech && !techStack.includes(tech)) {
            techStack.push(tech);
            updateTechDisplay();
            $('#techInput').val('');
        }
    }

    function updateTechDisplay() {
        $('#selectedTech').empty();
        techStack.forEach(tech => {
            const tag = $(`
                <span class="badge bg-primary-lighten text-primary font-12 d-inline-flex align-items-center gap-1 p-2 rounded">
                    <i class="mdi mdi-code-tags font-12"></i> ${tech}
                    <span class="cursor-pointer remove-tech ms-1 text-danger font-14" data-tech="${tech}" style="cursor: pointer;">&times;</span>
                </span>
            `);
            $('#selectedTech').append(tag);
        });
        $('#techStack').val(techStack.join(','));
    }

    $(document).on('click', '.remove-tech', function() {
        const techToRemove = $(this).data('tech');
        const index = techStack.indexOf(techToRemove);
        if (index > -1) {
            techStack.splice(index, 1);
            updateTechDisplay();
        }
    });

    // Progress slider
    $('#projectProgress').on('input', function() {
        const value = $(this).val();
        $('#progressValue').text(value + '%');
    });

    // Categories
    $('.form-check-input[type="checkbox"]').on('change', function() {
        const categories = [];
        $('.form-check-input[type="checkbox"]:checked').each(function() {
            categories.push($(this).val());
        });
        $('#projectCategories').val(categories.join(','));
    });

    // Milestones
    let milestoneCount = <?= count($milestones) ?>;
    $('#addMilestone').on('click', function() {
        const template = `
            <div class="milestone-entry card border mb-3 shadow-none">
                <div class="card-body p-3">
                    <div class="row g-2">
                        <div class="col-md-5">
                            <label class="form-label font-12 mb-1">Milestone Name</label>
                            <input type="text" class="form-control form-control-sm" placeholder="Milestone name"
                                   name="milestones[${milestoneCount}][name]">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label font-12 mb-1">Due Date</label>
                            <input type="date" class="form-control form-control-sm" placeholder="Due date"
                                   name="milestones[${milestoneCount}][due_date]">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label font-12 mb-1">Status</label>
                            <select class="form-select form-select-sm" name="milestones[${milestoneCount}][status]">
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label font-12 mb-1">Description (Optional)</label>
                            <textarea class="form-control form-control-sm" rows="2" placeholder="Key deliverables or notes..."
                                      name="milestones[${milestoneCount}][description]"></textarea>
                        </div>
                        <div class="col-12 text-end">
                            <button type="button" class="btn btn-sm btn-outline-danger remove-milestone">
                                <i class="mdi mdi-trash-can-outline me-1"></i> Remove
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        $('#milestonesContainer').append(template);
        milestoneCount++;
    });

    $(document).on('click', '.remove-milestone', function() {
        $(this).closest('.milestone-entry').remove();
    });

    // Blockers
    let blockers = <?= json_encode(array_values(array_filter(array_map('trim', !empty($project['blockers']) ? explode('|', $project['blockers']) : [])))) ?>;

    $('#addBlocker').on('click', function() {
        const blocker = $('.blocker-input').val().trim();
        if (blocker) {
            blockers.push(blocker);
            updateBlockersDisplay();
            $('.blocker-input').val('');
        }
    });

    function updateBlockersDisplay() {
        $('#blockersList').empty();
        blockers.forEach((blocker, index) => {
            const item = $(`
                <div class="blocker-item p-2 rounded border bg-danger-lighten text-danger d-flex justify-content-between align-items-center">
                    <span><i class="mdi mdi-alert-circle-outline me-1"></i> ${blocker}</span>
                    <span class="remove cursor-pointer text-danger font-16" data-index="${index}" style="cursor: pointer;">&times;</span>
                </div>
            `);
            $('#blockersList').append(item);
        });
        $('#projectBlockers').val(blockers.join('|'));
    }

    $(document).on('click', '.blocker-item .remove', function() {
        const index = $(this).data('index');
        blockers.splice(index, 1);
        updateBlockersDisplay();
    });

    // Delete project modal
    $('#deleteProjectBtn').on('click', function() {
        const modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
        modal.show();
    });

    $('#confirmDeleteBtn').on('click', function() {
        $('#deleteProjectForm').submit();
    });

    // Form submission validation
    $('#projectEditForm').on('submit', function(e) {
        const projectName = $('#projectName').val().trim();
        const description = $('#projectDescription').val().trim();

        if (!projectName) {
            e.preventDefault();
            showToast('Please enter a project name', 'danger');
            $('#projectName').focus();
            return;
        }

        if (!description) {
            e.preventDefault();
            showToast('Please enter a project description', 'danger');
            $('#projectDescription').focus();
            return;
        }

        $('#updateProjectBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');
    });

    function showToast(message, type = 'info') {
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
    }
});
</script>

<?= $this->endSection() ?>
