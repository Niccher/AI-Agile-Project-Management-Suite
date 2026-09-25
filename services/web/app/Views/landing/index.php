<?= $this->include('landing/header') ?>

<!-- START HERO -->
<section class="hero-section py-5 position-relative overflow-hidden bg-light">
    <div class="container py-lg-5">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="mt-md-2">
                    <div>
                        <span class="badge bg-success-lighten text-success rounded-pill px-3 py-1 font-13 fw-semibold">
                            <i class="mdi mdi-tag-outline me-1"></i> v1.0 — Free & Open Source
                        </span>
                    </div>
                    <h1 class="text-dark fw-bold mb-3 mt-3 display-5">
                        Agile Project Management Without the Bloat or Fees.
                    </h1>

                    <p class="mb-4 font-16 text-muted lead">
                        Plan sprints, track issues on an interactive Kanban board, log billable hours, and manage project milestones. 100% self-hosted so your team's code and proprietary roadmap stay entirely private.
                    </p>

                    <div class="d-flex flex-wrap gap-2">
                        <a href="<?= site_url('auth/register') ?>" class="btn btn-primary btn-lg rounded-pill px-4">
                            <i class="mdi mdi-rocket me-1"></i> Get Started Free
                        </a>
                        <a href="<?= site_url('features') ?>" class="btn btn-outline-secondary btn-lg rounded-pill px-4">
                            <i class="mdi mdi-play-circle-outline me-1"></i> Explore Features
                        </a>
                    </div>

                    <div class="row mt-4 pt-2">
                        <div class="col-auto">
                            <p class="text-muted mb-0 font-13"><i class="mdi mdi-check-circle text-success me-1"></i> 100% Free Forever</p>
                        </div>
                        <div class="col-auto">
                            <p class="text-muted mb-0 font-13"><i class="mdi mdi-check-circle text-success me-1"></i> Self-Hosted (Docker Ready)</p>
                        </div>
                        <div class="col-auto">
                            <p class="text-muted mb-0 font-13"><i class="mdi mdi-check-circle text-success me-1"></i> Unlimited Users</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 text-center text-lg-end mt-4 mt-lg-0">
                <img src="<?= base_url('assets/hyper/images/startup.svg') ?>" alt="Agile Software Workflow" class="img-fluid" style="max-height: 420px;" />
            </div>
        </div>
    </div>
</section>
<!-- END HERO -->

<!-- METRICS BANNER -->
<section class="py-4 bg-primary text-white">
    <div class="container">
        <div class="row text-center">
            <div class="col-6 col-md-3 py-2">
                <h3 class="fw-bold mb-0 text-white">$0</h3>
                <p class="mb-0 text-white-50 font-13">Monthly License Cost</p>
            </div>
            <div class="col-6 col-md-3 py-2">
                <h3 class="fw-bold mb-0 text-white">100%</h3>
                <p class="mb-0 text-white-50 font-13">Data Ownership & Privacy</p>
            </div>
            <div class="col-6 col-md-3 py-2">
                <h3 class="fw-bold mb-0 text-white">1-Click</h3>
                <p class="mb-0 text-white-50 font-13">Docker & Railway Deploy</p>
            </div>
            <div class="col-6 col-md-3 py-2">
                <h3 class="fw-bold mb-0 text-white">MIT</h3>
                <p class="mb-0 text-white-50 font-13">Open Source License</p>
            </div>
        </div>
    </div>
</section>

<!-- VISUAL FEATURE 1: KANBAN -->
<section class="py-5">
    <div class="container py-lg-4">
        <div class="row align-items-center">
            <div class="col-lg-5">
                <span class="badge bg-primary-lighten text-primary rounded-pill px-3 py-1 font-12 fw-semibold">Interactive Workflow</span>
                <h2 class="fw-bold mt-2 mb-3">Fluid Kanban Boards with Drag & Drop Agility</h2>
                <p class="text-muted font-15 mb-4">
                    Eliminate spreadsheet chaos and rigid tracking software. Visualize your team's backlog, active sprint items, and completed tasks at a single glance.
                </p>
                <div class="d-flex align-items-start mb-3">
                    <div class="avatar-xs me-3 flex-shrink-0">
                        <span class="avatar-title bg-primary-lighten rounded-circle text-primary font-16">
                            <i class="mdi mdi-view-column"></i>
                        </span>
                    </div>
                    <div>
                        <h5 class="mt-0 mb-1">Customizable Column States</h5>
                        <p class="text-muted font-13 mb-0">Define your sprint stages from Backlog, In Progress, Review, to Done.</p>
                    </div>
                </div>
                <div class="d-flex align-items-start mb-3">
                    <div class="avatar-xs me-3 flex-shrink-0">
                        <span class="avatar-title bg-primary-lighten rounded-circle text-primary font-16">
                            <i class="mdi mdi-account-switch"></i>
                        </span>
                    </div>
                    <div>
                        <h5 class="mt-0 mb-1">Instant Re-assignment</h5>
                        <p class="text-muted font-13 mb-0">Assign tasks to developers and set priority flags (Low, Medium, Critical) with one click.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 offset-lg-1 text-center mt-4 mt-lg-0">
                <img src="<?= base_url('assets/hyper/images/features-1.svg') ?>" alt="Kanban & Task Management" class="img-fluid" style="max-height: 350px;" />
            </div>
        </div>
    </div>
</section>

<!-- VISUAL FEATURE 2: TIME TRACKING -->
<section class="py-5 bg-light">
    <div class="container py-lg-4">
        <div class="row align-items-center">
            <div class="col-lg-6 text-center order-2 order-lg-1 mt-4 mt-lg-0">
                <img src="<?= base_url('assets/hyper/images/features-2.svg') ?>" alt="Time Tracking and Velocity" class="img-fluid" style="max-height: 350px;" />
            </div>
            <div class="col-lg-5 offset-lg-1 order-1 order-lg-2">
                <span class="badge bg-success-lighten text-success rounded-pill px-3 py-1 font-12 fw-semibold">Accountability</span>
                <h2 class="fw-bold mt-2 mb-3">Integrated Time Tracking & Effort Estimates</h2>
                <p class="text-muted font-15 mb-4">
                    Track every billable minute without needing separate third-party extensions. Log hours directly against specific issues and milestones.
                </p>
                <div class="d-flex align-items-start mb-3">
                    <div class="avatar-xs me-3 flex-shrink-0">
                        <span class="avatar-title bg-success-lighten rounded-circle text-success font-16">
                            <i class="mdi mdi-clock-check-outline"></i>
                        </span>
                    </div>
                    <div>
                        <h5 class="mt-0 mb-1">Estimated vs. Actual Hours</h5>
                        <p class="text-muted font-13 mb-0">Compare initial task estimates against real logged hours to improve future sprint forecasts.</p>
                    </div>
                </div>
                <div class="d-flex align-items-start mb-3">
                    <div class="avatar-xs me-3 flex-shrink-0">
                        <span class="avatar-title bg-success-lighten rounded-circle text-success font-16">
                            <i class="mdi mdi-calendar-text"></i>
                        </span>
                    </div>
                    <div>
                        <h5 class="mt-0 mb-1">Interactive Team Calendar</h5>
                        <p class="text-muted font-13 mb-0">View project deadlines and release schedules organized neatly on a full-screen calendar view.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- VISUAL FEATURE 3: REPORTS & ANALYTICS -->
<section class="py-5">
    <div class="container py-lg-4">
        <div class="row align-items-center">
            <div class="col-lg-5">
                <span class="badge bg-warning-lighten text-warning rounded-pill px-3 py-1 font-12 fw-semibold">Actionable Insights</span>
                <h2 class="fw-bold mt-2 mb-3">Instant Velocity & Burn-Down Reporting</h2>
                <p class="text-muted font-15 mb-4">
                    Keep engineering leads and stakeholders fully aligned with real-time visual charts and automated PDF summary reports.
                </p>
                <div class="d-flex align-items-start mb-3">
                    <div class="avatar-xs me-3 flex-shrink-0">
                        <span class="avatar-title bg-warning-lighten rounded-circle text-warning font-16">
                            <i class="mdi mdi-chart-donut"></i>
                        </span>
                    </div>
                    <div>
                        <h5 class="mt-0 mb-1">Issue Distribution Charts</h5>
                        <p class="text-muted font-13 mb-0">Identify bottleneck areas by breaking down tasks by status, assignee, and priority.</p>
                    </div>
                </div>
                <div class="d-flex align-items-start mb-3">
                    <div class="avatar-xs me-3 flex-shrink-0">
                        <span class="avatar-title bg-warning-lighten rounded-circle text-warning font-16">
                            <i class="mdi mdi-file-pdf-box"></i>
                        </span>
                    </div>
                    <div>
                        <h5 class="mt-0 mb-1">1-Click PDF Report Exports</h5>
                        <p class="text-muted font-13 mb-0">Export comprehensive sprint work summaries ready for management reviews.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 offset-lg-1 text-center mt-4 mt-lg-0">
                <img src="<?= base_url('assets/hyper/images/report.svg') ?>" alt="Reporting & Analytics" class="img-fluid" style="max-height: 350px;" />
            </div>
        </div>
    </div>
</section>

<!-- 3-STEP ONBOARDING -->
<section class="py-5 bg-light">
    <div class="container py-lg-4">
        <div class="row">
            <div class="col-12 text-center">
                <span class="badge bg-primary-lighten text-primary rounded-pill px-3 py-1 font-12 fw-semibold">Simple Onboarding</span>
                <h2 class="fw-bold mt-2 mb-2">Get Up and Running in Under 2 Minutes</h2>
                <p class="text-muted font-15 mb-5">No complex enterprise setup consultants required.</p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="card feature-card text-center p-4">
                    <div class="avatar-md mx-auto mb-3">
                        <span class="avatar-title bg-primary-lighten text-primary rounded-circle font-24" style="width: 50px; height: 50px; line-height: 50px; display: inline-block;">1</span>
                    </div>
                    <h4 class="fw-bold">Deploy Container</h4>
                    <p class="text-muted font-14 mb-0">Run a single <code>docker-compose up -d</code> command on your local machine, VPS, or Railway.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card feature-card text-center p-4">
                    <div class="avatar-md mx-auto mb-3">
                        <span class="avatar-title bg-success-lighten text-success rounded-circle font-24" style="width: 50px; height: 50px; line-height: 50px; display: inline-block;">2</span>
                    </div>
                    <h4 class="fw-bold">Create Projects</h4>
                    <p class="text-muted font-14 mb-0">Set up your agile boards, configure project milestones, and define team capacity.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card feature-card text-center p-4">
                    <div class="avatar-md mx-auto mb-3">
                        <span class="avatar-title bg-warning-lighten text-warning rounded-circle font-24" style="width: 50px; height: 50px; line-height: 50px; display: inline-block;">3</span>
                    </div>
                    <h4 class="fw-bold">Invite & Collaborate</h4>
                    <p class="text-muted font-14 mb-0">Add unlimited team members with granular role permissions (Admin, Manager, User) for $0.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CALL TO ACTION -->
<section class="py-5 bg-dark text-white text-center">
    <div class="container py-lg-4">
        <h2 class="fw-bold text-white mb-3 display-6">Ready to Take Back Control of Your Engineering Workflow?</h2>
        <p class="lead text-white-50 w-75 mx-auto mb-4 font-16">
            Join thousands of teams choosing self-hosted agility over bloated enterprise subscriptions. Free forever under the MIT license.
        </p>
        <div class="d-flex justify-content-center gap-2">
            <a href="<?= site_url('auth/register') ?>" class="btn btn-primary btn-lg rounded-pill px-4">
                <i class="mdi mdi-rocket me-1"></i> Create Your Account
            </a>
            <a href="<?= site_url('setup') ?>" class="btn btn-outline-light btn-lg rounded-pill px-4">
                <i class="mdi mdi-cog-outline me-1"></i> View Setup Guide
            </a>
        </div>
    </div>
</section>

<?= $this->include('landing/footer') ?>
