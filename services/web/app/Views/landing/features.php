<?= $this->include('landing/header') ?>

<!-- START PAGE HEADER -->
<section class="py-5 bg-light border-bottom">
    <div class="container text-center py-4">
        <span class="badge bg-primary-lighten text-primary rounded-pill px-3 py-1 font-12 fw-semibold">Feature Deep Dive</span>
        <h1 class="fw-bold mt-2 mb-2 display-6">Everything You Need to Ship High-Impact Software</h1>
        <p class="text-muted font-16 lead w-75 mx-auto mb-0">Explore the full suite of agile sprint management, time tracking, and team collaboration tools built into <?= esc(setting('App.siteName')) ?>.</p>
    </div>
</section>
<!-- END PAGE HEADER -->

<section class="py-5">
    <div class="container py-lg-4">
        <div class="row g-4">
            <!-- Feature 1 -->
            <div class="col-md-6 col-lg-4">
                <div class="card feature-card p-4">
                    <div class="avatar-sm mb-3">
                        <span class="avatar-title bg-primary-lighten text-primary rounded-circle font-20">
                            <i class="mdi mdi-view-column"></i>
                        </span>
                    </div>
                    <h4 class="fw-bold">Interactive Kanban Boards</h4>
                    <p class="text-muted font-14">
                        Drag and drop issues across customizable sprint columns. Filter tickets by assignee, priority, or milestone with instant reactive updates.
                    </p>
                </div>
            </div>

            <!-- Feature 2 -->
            <div class="col-md-6 col-lg-4">
                <div class="card feature-card p-4">
                    <div class="avatar-sm mb-3">
                        <span class="avatar-title bg-success-lighten text-success rounded-circle font-20">
                            <i class="mdi mdi-clock-outline"></i>
                        </span>
                    </div>
                    <h4 class="fw-bold">Time Logging & Billing</h4>
                    <p class="text-muted font-14">
                        Keep track of active work hours on every issue. View detailed logs per developer, calculate burn rates, and ensure accurate client billing.
                    </p>
                </div>
            </div>

            <!-- Feature 3 -->
            <div class="col-md-6 col-lg-4">
                <div class="card feature-card p-4">
                    <div class="avatar-sm mb-3">
                        <span class="avatar-title bg-warning-lighten text-warning rounded-circle font-20">
                            <i class="mdi mdi-shield-account"></i>
                        </span>
                    </div>
                    <h4 class="fw-bold">Granular RBAC Permissions</h4>
                    <p class="text-muted font-14">
                        Role-Based Access Control out-of-the-box. Separate permissions for System Admins, Project Managers, and Developers to protect confidential tasks.
                    </p>
                </div>
            </div>

            <!-- Feature 4 -->
            <div class="col-md-6 col-lg-4">
                <div class="card feature-card p-4">
                    <div class="avatar-sm mb-3">
                        <span class="avatar-title bg-danger-lighten text-danger rounded-circle font-20">
                            <i class="mdi mdi-bug-outline"></i>
                        </span>
                    </div>
                    <h4 class="fw-bold">Issue & Bug Tracking</h4>
                    <p class="text-muted font-14">
                        Categorize tickets as Bugs, Features, or Epics. Attach screenshots, track reproduction steps, and link directly to GitHub pull requests.
                    </p>
                </div>
            </div>

            <!-- Feature 5 -->
            <div class="col-md-6 col-lg-4">
                <div class="card feature-card p-4">
                    <div class="avatar-sm mb-3">
                        <span class="avatar-title bg-info-lighten text-info rounded-circle font-20">
                            <i class="mdi mdi-calendar-month"></i>
                        </span>
                    </div>
                    <h4 class="fw-bold">Milestones & Deadlines</h4>
                    <p class="text-muted font-14">
                        Map releases against timeline milestones. Use the integrated project calendar to stay ahead of upcoming sprint deadlines.
                    </p>
                </div>
            </div>

            <!-- Feature 6 -->
            <div class="col-md-6 col-lg-4">
                <div class="card feature-card p-4">
                    <div class="avatar-sm mb-3">
                        <span class="avatar-title bg-secondary-lighten text-secondary rounded-circle font-20">
                            <i class="mdi mdi-file-pdf-box"></i>
                        </span>
                    </div>
                    <h4 class="fw-bold">PDF Report Generation</h4>
                    <p class="text-muted font-14">
                        Generate professional stakeholder summary reports with one click. Export complete sprint velocity and hours metrics to PDF.
                    </p>
                </div>
            </div>
        </div>

        <div class="text-center mt-5 pt-3">
            <h3 class="fw-bold mb-3">Ready to upgrade your team's agility?</h3>
            <a href="<?= site_url('auth/register') ?>" class="btn btn-primary btn-lg rounded-pill px-4">
                <i class="mdi mdi-rocket me-1"></i> Get Started Free
            </a>
        </div>
    </div>
</section>

<?= $this->include('landing/footer') ?>
