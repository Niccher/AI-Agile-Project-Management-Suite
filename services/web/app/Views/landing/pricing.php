<?= $this->include('landing/header') ?>

<!-- START PAGE HEADER -->
<section class="py-5 bg-light border-bottom">
    <div class="container text-center py-4">
        <span class="badge bg-success-lighten text-success rounded-pill px-3 py-1 font-12 fw-semibold">Simple & Transparent</span>
        <h1 class="fw-bold mt-2 mb-2 display-6">100% Free & Open Source. No Gimmicks.</h1>
        <p class="text-muted font-16 lead w-75 mx-auto mb-0">We believe agile project management should be accessible to every engineering team on earth without per-seat tax.</p>
    </div>
</section>
<!-- END PAGE HEADER -->

<section class="py-5">
    <div class="container py-lg-4">
        <div class="row justify-content-center g-4">
            
            <!-- Community Tier -->
            <div class="col-lg-5 col-md-6">
                <div class="card feature-card border border-primary border-2 p-4 h-100 position-relative">
                    <div class="position-absolute top-0 end-0 mt-3 me-3">
                        <span class="badge bg-primary text-white rounded-pill px-3 py-1 font-12">Recommended</span>
                    </div>
                    <div class="mb-3">
                        <h3 class="fw-bold text-dark mb-1">Community Edition</h3>
                        <p class="text-muted font-14">For agile startups, engineering teams, and self-hosters.</p>
                    </div>

                    <div class="my-4 py-2 border-top border-bottom">
                        <h1 class="display-4 fw-bold text-primary mb-0">$0</h1>
                        <span class="text-muted font-13">Free Forever / No Credit Card Required</span>
                    </div>

                    <ul class="list-unstyled font-14 text-muted mb-4 flex-grow-1">
                        <li class="mb-3 d-flex align-items-center">
                            <i class="mdi mdi-check-circle text-success font-18 me-2"></i>
                            <strong>Unlimited Users & Projects</strong>
                        </li>
                        <li class="mb-3 d-flex align-items-center">
                            <i class="mdi mdi-check-circle text-success font-18 me-2"></i>
                            Interactive Kanban Boards with Drag & Drop
                        </li>
                        <li class="mb-3 d-flex align-items-center">
                            <i class="mdi mdi-check-circle text-success font-18 me-2"></i>
                            Integrated Time Tracking & Effort Estimates
                        </li>
                        <li class="mb-3 d-flex align-items-center">
                            <i class="mdi mdi-check-circle text-success font-18 me-2"></i>
                            Role-Based Access Control (Admin, Manager, Dev)
                        </li>
                        <li class="mb-3 d-flex align-items-center">
                            <i class="mdi mdi-check-circle text-success font-18 me-2"></i>
                            PDF Report Exports & Velocity Charts
                        </li>
                        <li class="mb-3 d-flex align-items-center">
                            <i class="mdi mdi-check-circle text-success font-18 me-2"></i>
                            1-Click Docker & Railway Deployment
                        </li>
                        <li class="d-flex align-items-center">
                            <i class="mdi mdi-check-circle text-success font-18 me-2"></i>
                            100% Data Privacy (On-Premise)
                        </li>
                    </ul>

                    <div class="mt-auto">
                        <a href="<?= site_url('auth/register') ?>" class="btn btn-primary btn-lg rounded-pill w-100">
                            <i class="mdi mdi-rocket me-1"></i> Get Started Free
                        </a>
                    </div>
                </div>
            </div>

            <!-- Enterprise Self-Hosted Tier -->
            <div class="col-lg-5 col-md-6">
                <div class="card feature-card border p-4 h-100 bg-light">
                    <div class="mb-3">
                        <h3 class="fw-bold text-dark mb-1">Enterprise Self-Hosted</h3>
                        <p class="text-muted font-14">For organizations with custom compliance and infrastructure needs.</p>
                    </div>

                    <div class="my-4 py-2 border-top border-bottom">
                        <h1 class="display-4 fw-bold text-dark mb-0">Custom</h1>
                        <span class="text-muted font-13">Deploy on your private VPC / Air-Gapped server</span>
                    </div>

                    <ul class="list-unstyled font-14 text-muted mb-4 flex-grow-1">
                        <li class="mb-3 d-flex align-items-center">
                            <i class="mdi mdi-check-circle text-primary font-18 me-2"></i>
                            Everything in Community Edition
                        </li>
                        <li class="mb-3 d-flex align-items-center">
                            <i class="mdi mdi-check-circle text-primary font-18 me-2"></i>
                            Custom LDAP / Single Sign-On (SSO) Support
                        </li>
                        <li class="mb-3 d-flex align-items-center">
                            <i class="mdi mdi-check-circle text-primary font-18 me-2"></i>
                            Automated High-Availability Database Replication
                        </li>
                        <li class="mb-3 d-flex align-items-center">
                            <i class="mdi mdi-check-circle text-primary font-18 me-2"></i>
                            Dedicated Compliance & Audit Log Stream
                        </li>
                        <li class="d-flex align-items-center">
                            <i class="mdi mdi-check-circle text-primary font-18 me-2"></i>
                            Priority SLA & Code Contribution Support
                        </li>
                    </ul>

                    <div class="mt-auto">
                        <a href="https://github.com/Niccher/Chege-Jira-WebApp/issues" target="_blank" class="btn btn-outline-dark btn-lg rounded-pill w-100">
                            <i class="mdi mdi-github me-1"></i> Contact on GitHub
                        </a>
                    </div>
                </div>
            </div>

        </div>

        <div class="row mt-5 pt-3 justify-content-center text-center">
            <div class="col-lg-8">
                <h4 class="fw-bold mb-2">Frequently Asked About Pricing</h4>
                <p class="text-muted font-14">Are there any hidden costs? No. The code is 100% open source under the MIT license. You only pay for your own hosting provider (VPS, server, or cloud).</p>
            </div>
        </div>
    </div>
</section>

<?= $this->include('landing/footer') ?>
