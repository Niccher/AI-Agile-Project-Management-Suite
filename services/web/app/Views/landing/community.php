<?= $this->include('landing/header') ?>

<!-- START PAGE HEADER -->
<section class="py-5 bg-light border-bottom">
    <div class="container text-center py-4">
        <span class="badge bg-primary-lighten text-primary rounded-pill px-3 py-1 font-12 fw-semibold">Open Source Ecosystem</span>
        <h1 class="fw-bold mt-2 mb-2 display-6">Built in the Open by Developers, for Developers</h1>
        <p class="text-muted font-16 lead w-75 mx-auto mb-0">Join our open-source community. Contribute code, suggest features, or build plugins for <?= esc(setting('App.siteName')) ?>.</p>
    </div>
</section>
<!-- END PAGE HEADER -->

<section class="py-5">
    <div class="container py-lg-4">
        <div class="row g-4">
            <!-- Card 1: Contribute Code -->
            <div class="col-md-4">
                <div class="card feature-card text-center p-4">
                    <div class="avatar-md mx-auto mb-3">
                        <span class="avatar-title bg-dark-lighten text-dark rounded-circle font-24" style="width: 55px; height: 55px; line-height: 55px; display: inline-block;">
                            <i class="mdi mdi-github font-28"></i>
                        </span>
                    </div>
                    <h4 class="fw-bold">Contribute Code</h4>
                    <p class="text-muted font-14 mb-4">
                        We welcome pull requests for bug fixes, performance optimizations, and new features. Check out our contributing guide on GitHub.
                    </p>
                    <div class="mt-auto">
                        <a href="https://github.com/Niccher/Chege-Jira-WebApp" target="_blank" class="btn btn-outline-dark rounded-pill px-3">
                            <i class="mdi mdi-source-branch me-1"></i> View Repository
                        </a>
                    </div>
                </div>
            </div>

            <!-- Card 2: Report Issues -->
            <div class="col-md-4">
                <div class="card feature-card text-center p-4">
                    <div class="avatar-md mx-auto mb-3">
                        <span class="avatar-title bg-danger-lighten text-danger rounded-circle font-24" style="width: 55px; height: 55px; line-height: 55px; display: inline-block;">
                            <i class="mdi mdi-bug font-28"></i>
                        </span>
                    </div>
                    <h4 class="fw-bold">Report Issues & Ideas</h4>
                    <p class="text-muted font-14 mb-4">
                        Encountered a bug or have a feature idea? File an issue on our GitHub issue tracker to help us continuously improve the platform.
                    </p>
                    <div class="mt-auto">
                        <a href="https://github.com/Niccher/Chege-Jira-WebApp/issues" target="_blank" class="btn btn-outline-danger rounded-pill px-3">
                            <i class="mdi mdi-alert-circle-outline me-1"></i> Open Issue
                        </a>
                    </div>
                </div>
            </div>

            <!-- Card 3: MIT License -->
            <div class="col-md-4">
                <div class="card feature-card text-center p-4">
                    <div class="avatar-md mx-auto mb-3">
                        <span class="avatar-title bg-primary-lighten text-primary rounded-circle font-24" style="width: 55px; height: 55px; line-height: 55px; display: inline-block;">
                            <i class="mdi mdi-scale-balance font-28"></i>
                        </span>
                    </div>
                    <h4 class="fw-bold">MIT Open License</h4>
                    <p class="text-muted font-14 mb-4">
                        <?= esc(setting('App.siteName')) ?> is 100% free under the permissive MIT license. Use it internally, modify it, or deploy it for commercial projects.
                    </p>
                    <div class="mt-auto">
                        <a href="https://github.com/Niccher/Chege-Jira-WebApp/blob/master/LICENSE" target="_blank" class="btn btn-outline-primary rounded-pill px-3">
                            <i class="mdi mdi-file-document-outline me-1"></i> Read License
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card bg-light border p-4 mt-5 rounded text-center">
            <h3 class="fw-bold text-dark mb-2">Want to sponsor or build integrations?</h3>
            <p class="text-muted font-15 mb-0">Star us on GitHub and connect with fellow developers building the future of open-source agility.</p>
        </div>
    </div>
</section>

<?= $this->include('landing/footer') ?>
