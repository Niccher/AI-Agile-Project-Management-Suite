<?= $this->extend('layouts/hyper/main') ?>

<?= $this->section('title') ?>New Project • <?= esc(setting('App.siteName')) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <a href="<?= site_url('projects') ?>" class="btn btn-outline-secondary rounded-pill">
                    <i class="mdi mdi-arrow-left me-1"></i> Back to Projects
                </a>
            </div>
            <h4 class="page-title"><i class="mdi mdi-plus-circle me-2 text-primary"></i> Create Project</h4>
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

<!-- Project Creation Form -->
<div class="row">
    <div class="col-xl-9 col-lg-10 mx-auto">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-transparent border-bottom py-3">
                <h5 class="header-title mb-0">
                    <i class="mdi mdi-folder-plus me-1 text-primary"></i> Project Information
                </h5>
            </div>
            <div class="card-body p-4">
                <form id="projectCreateForm" method="post" action="<?= site_url('projects/store') ?>">
                    <?= csrf_field() ?>

                    <!-- Basic Information Section -->
                    <div class="mb-4">
                        <h6 class="text-uppercase text-muted font-12 fw-bold mb-3">
                            <i class="mdi mdi-information-outline me-1"></i> Basic Details
                        </h6>

                        <!-- Project Name -->
                        <div class="mb-3">
                            <label for="projectName" class="form-label fw-semibold">
                                Project Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control form-control-lg" id="projectName" name="name"
                                   placeholder="e.g., E-commerce API, Agile Workspace, Mobile App" required autofocus>
                            <div class="form-text">Choose a descriptive name that clearly identifies your project.</div>
                        </div>

                        <!-- Short Description -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label for="projectDescription" class="form-label fw-semibold mb-0">
                                    Description / Goal <span class="text-danger">*</span>
                                </label>
                                <small class="text-muted" id="descCounter">0/500</small>
                            </div>
                            <textarea class="form-control" id="projectDescription" name="description" rows="3"
                                      placeholder="What are you building? What problem does it solve?" required></textarea>
                        </div>

                        <!-- Technology Stack with Autocomplete & Suggestions -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Technology Stack</label>
                            
                            <!-- Selected Tags -->
                            <div class="d-flex flex-wrap gap-1 mb-2" id="selectedTech">
                                <!-- Selected tech tags will appear here -->
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

                            <input type="hidden" name="tech_stack" id="techStack">
                        </div>
                    </div>

                    <!-- Project Parameters Section -->
                    <div class="mb-4 border-top pt-3">
                        <h6 class="text-uppercase text-muted font-12 fw-bold mb-3">
                            <i class="mdi mdi-tune-vertical me-1"></i> Status &amp; Scheduling
                        </h6>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="projectStatus" class="form-label fw-semibold">
                                    Status <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="projectStatus" name="status" required>
                                    <option value="planning">📋 Planning</option>
                                    <option value="in_progress" selected>🚀 In Progress</option>
                                    <option value="testing">🧪 Testing</option>
                                    <option value="completed">✅ Completed</option>
                                    <option value="on_hold">⏸️ On Hold</option>
                                    <option value="abandoned">❌ Abandoned</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="projectPriority" class="form-label fw-semibold">
                                    Priority <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="projectPriority" name="priority" required>
                                    <option value="low">🟢 Low</option>
                                    <option value="medium" selected>🟡 Medium</option>
                                    <option value="high">🟠 High</option>
                                    <option value="critical">🔴 Critical</option>
                                </select>
                            </div>
                        </div>

                        <!-- Dates (Default 2 months target completion) -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="projectStartDate" class="form-label fw-semibold">Start Date</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="mdi mdi-calendar"></i></span>
                                    <input type="date" class="form-control" id="projectStartDate" name="start_date"
                                           value="<?= date('Y-m-d') ?>">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="projectDueDate" class="form-label fw-semibold">Target Completion</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="mdi mdi-calendar-check"></i></span>
                                    <input type="date" class="form-control" id="projectDueDate" name="due_date" 
                                           value="<?= date('Y-m-d', strtotime('+2 months')) ?>">
                                </div>
                                <div class="form-text text-muted font-11">Defaulted to 2 months from today. You can adjust as needed.</div>
                            </div>
                        </div>

                        <!-- Progress -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label for="projectProgress" class="form-label fw-semibold mb-0">
                                    Initial Progress
                                </label>
                                <span id="progressValue" class="badge bg-primary-lighten text-primary font-12">0%</span>
                            </div>
                            <input type="range" class="form-range" id="projectProgress" name="progress"
                                   min="0" max="100" value="0" step="5">
                        </div>

                        <!-- Repository -->
                        <div class="mb-3">
                            <label for="projectRepo" class="form-label fw-semibold">Repository URL</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="mdi mdi-github"></i></span>
                                <input type="url" class="form-control" id="projectRepo" name="repository_url"
                                       placeholder="https://github.com/username/project">
                            </div>
                        </div>

                        <!-- Project Categories with Rich Icons -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold d-block">Project Categories</label>
                            <div class="d-flex flex-wrap gap-2">
                                <div class="form-check border rounded px-3 py-2 bg-light-subtle">
                                    <input class="form-check-input" type="checkbox" value="web_app" id="catWeb">
                                    <label class="form-check-label font-13" for="catWeb">
                                        <i class="mdi mdi-web text-primary me-1"></i> Web Application
                                    </label>
                                </div>
                                <div class="form-check border rounded px-3 py-2 bg-light-subtle">
                                    <input class="form-check-input" type="checkbox" value="api" id="catAPI">
                                    <label class="form-check-label font-13" for="catAPI">
                                        <i class="mdi mdi-server-network text-success me-1"></i> API / Backend
                                    </label>
                                </div>
                                <div class="form-check border rounded px-3 py-2 bg-light-subtle">
                                    <input class="form-check-input" type="checkbox" value="mobile" id="catMobile">
                                    <label class="form-check-label font-13" for="catMobile">
                                        <i class="mdi mdi-cellphone text-info me-1"></i> Mobile App
                                    </label>
                                </div>
                                <div class="form-check border rounded px-3 py-2 bg-light-subtle">
                                    <input class="form-check-input" type="checkbox" value="ai_ml" id="catAi">
                                    <label class="form-check-label font-13" for="catAi">
                                        <i class="mdi mdi-brain text-purple me-1"></i> AI / Machine Learning
                                    </label>
                                </div>
                                <div class="form-check border rounded px-3 py-2 bg-light-subtle">
                                    <input class="form-check-input" type="checkbox" value="devops" id="catDevops">
                                    <label class="form-check-label font-13" for="catDevops">
                                        <i class="mdi mdi-cloud-sync text-warning me-1"></i> DevOps &amp; CI/CD
                                    </label>
                                </div>
                                <div class="form-check border rounded px-3 py-2 bg-light-subtle">
                                    <input class="form-check-input" type="checkbox" value="database" id="catDb">
                                    <label class="form-check-label font-13" for="catDb">
                                        <i class="mdi mdi-database text-danger me-1"></i> Database &amp; Big Data
                                    </label>
                                </div>
                                <div class="form-check border rounded px-3 py-2 bg-light-subtle">
                                    <input class="form-check-input" type="checkbox" value="security" id="catSecurity">
                                    <label class="form-check-label font-13" for="catSecurity">
                                        <i class="mdi mdi-shield-lock text-danger me-1"></i> Security &amp; Compliance
                                    </label>
                                </div>
                                <div class="form-check border rounded px-3 py-2 bg-light-subtle">
                                    <input class="form-check-input" type="checkbox" value="ui_ux" id="catUi">
                                    <label class="form-check-label font-13" for="catUi">
                                        <i class="mdi mdi-palette text-pink me-1"></i> UI/UX Design
                                    </label>
                                </div>
                                <div class="form-check border rounded px-3 py-2 bg-light-subtle">
                                    <input class="form-check-input" type="checkbox" value="ecommerce" id="catEcom">
                                    <label class="form-check-label font-13" for="catEcom">
                                        <i class="mdi mdi-cart-outline text-success me-1"></i> E-Commerce
                                    </label>
                                </div>
                                <div class="form-check border rounded px-3 py-2 bg-light-subtle">
                                    <input class="form-check-input" type="checkbox" value="automation" id="catAuto">
                                    <label class="form-check-label font-13" for="catAuto">
                                        <i class="mdi mdi-robot text-primary me-1"></i> Automation &amp; Bots
                                    </label>
                                </div>
                                <div class="form-check border rounded px-3 py-2 bg-light-subtle">
                                    <input class="form-check-input" type="checkbox" value="open_source" id="catOs">
                                    <label class="form-check-label font-13" for="catOs">
                                        <i class="mdi mdi-github text-dark me-1"></i> Open Source
                                    </label>
                                </div>
                                <div class="form-check border rounded px-3 py-2 bg-light-subtle">
                                    <input class="form-check-input" type="checkbox" value="learning" id="catLearning">
                                    <label class="form-check-label font-13" for="catLearning">
                                        <i class="mdi mdi-school text-warning me-1"></i> Learning Project
                                    </label>
                                </div>
                                <div class="form-check border rounded px-3 py-2 bg-light-subtle">
                                    <input class="form-check-input" type="checkbox" value="portfolio" id="catPortfolio">
                                    <label class="form-check-label font-13" for="catPortfolio">
                                        <i class="mdi mdi-briefcase-check text-info me-1"></i> Portfolio Piece
                                    </label>
                                </div>
                                <div class="form-check border rounded px-3 py-2 bg-light-subtle">
                                    <input class="form-check-input" type="checkbox" value="freelance" id="catFreelance">
                                    <label class="form-check-label font-13" for="catFreelance">
                                        <i class="mdi mdi-account-tie text-secondary me-1"></i> Freelance Work
                                    </label>
                                </div>
                            </div>
                            <input type="hidden" name="categories" id="projectCategories">
                        </div>
                    </div>

                    <!-- Milestones Section -->
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
                            <div class="milestone-entry p-3 rounded border bg-light-subtle mb-2">
                                <div class="row g-2">
                                    <div class="col-md-5">
                                        <input type="text" class="form-control form-control-sm" placeholder="Milestone name (e.g., MVP Launch)"
                                               name="milestones[0][name]">
                                    </div>
                                    <div class="col-md-4">
                                        <input type="date" class="form-control form-control-sm" placeholder="Due date"
                                               name="milestones[0][due_date]" value="<?= date('Y-m-d', strtotime('+30 days')) ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <select class="form-select form-select-sm" name="milestones[0][status]">
                                            <option value="pending">Pending</option>
                                            <option value="in_progress">In Progress</option>
                                            <option value="completed">Completed</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <textarea class="form-control form-control-sm" rows="2" placeholder="Description or key deliverables (optional)"
                                                  name="milestones[0][description]"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notes Section -->
                    <div class="mb-4 border-top pt-3">
                        <h6 class="text-uppercase text-muted font-12 fw-bold mb-3">
                            <i class="mdi mdi-note-text-outline me-1"></i> Initial Notes &amp; Architecture
                        </h6>

                        <div class="mb-3">
                            <textarea class="form-control font-monospace" id="projectNotes" name="notes" rows="3"
                                      placeholder="Add initial thoughts, requirements, or architecture notes..."></textarea>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                        <a href="<?= site_url('projects') ?>" class="btn btn-light">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary rounded-pill px-4" id="createProjectBtn">
                            <i class="mdi mdi-rocket-launch me-1"></i> Create Project
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3"></div>

<!-- JavaScript -->
<script>
    $(document).ready(function() {
        // Description counter
        $('#projectDescription').on('input', function() {
            const length = $(this).val().length;
            $('#descCounter').text(length + '/500');
        });

        // Tech stack management
        const techStack = [];
        $('#addTech').click(addTechnology);
        $('#techInput').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                addTechnology();
            }
        });

        $('.quick-tech-btn').click(function() {
            const t = $(this).data('tech');
            if (t && !techStack.includes(t.toLowerCase())) {
                techStack.push(t.toLowerCase());
                updateTechDisplay();
            }
        });

        function addTechnology() {
            const tech = $('#techInput').val().trim();
            if (tech && !techStack.includes(tech.toLowerCase())) {
                techStack.push(tech.toLowerCase());
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
        $('.form-check-input').change(function() {
            const categories = [];
            $('input[type="checkbox"]:checked').each(function() {
                categories.push($(this).val());
            });
            $('#projectCategories').val(categories.join(','));
        });

        // Milestones
        let milestoneCount = 1;
        $('#addMilestone').click(function() {
            const template = `
                <div class="milestone-entry p-3 rounded border bg-light-subtle mb-2">
                    <div class="row g-2">
                        <div class="col-md-5">
                            <input type="text" class="form-control form-control-sm" placeholder="Milestone name"
                                   name="milestones[${milestoneCount}][name]">
                        </div>
                        <div class="col-md-4">
                            <input type="date" class="form-control form-control-sm" placeholder="Due date"
                                   name="milestones[${milestoneCount}][due_date]">
                        </div>
                        <div class="col-md-3">
                            <select class="form-select form-select-sm" name="milestones[${milestoneCount}][status]">
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <textarea class="form-control form-control-sm" rows="2" placeholder="Description (optional)"
                                      name="milestones[${milestoneCount}][description]"></textarea>
                        </div>
                        <div class="col-12 text-end">
                            <button type="button" class="btn btn-sm btn-outline-danger remove-milestone">
                                <i class="mdi mdi-trash-can-outline me-1"></i> Remove
                            </button>
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

        // Form submission
        $('#projectCreateForm').on('submit', function(e) {
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

            $('#createProjectBtn').prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin me-1"></i> Creating...');
        });

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
