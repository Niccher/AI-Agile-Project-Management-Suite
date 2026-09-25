<?= $this->include('landing/header') ?>

<!-- START PAGE HEADER -->
<section class="py-5 bg-light border-bottom">
    <div class="container text-center py-4">
        <span class="badge bg-primary-lighten text-primary rounded-pill px-3 py-1 font-12 fw-semibold">Knowledge Base</span>
        <h1 class="fw-bold mt-2 mb-2 display-6">Frequently Asked Questions</h1>
        <p class="text-muted font-16 lead w-75 mx-auto mb-0">Everything you need to know about self-hosting, licensing, and migrating to <?= esc(setting('App.siteName')) ?>.</p>
    </div>
</section>
<!-- END PAGE HEADER -->

<section class="py-5">
    <div class="container py-lg-4">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="accordion accordion-flush" id="faqAccordion">
                    
                    <!-- FAQ 1 -->
                    <div class="accordion-item card mb-3 border">
                        <h2 class="accordion-header" id="headingOne">
                            <button class="accordion-button fw-bold text-dark font-16" type="button" data-bs-toggle="collapse" data-bs-target="#faqOne" aria-expanded="true" aria-controls="faqOne">
                                <i class="mdi mdi-help-circle-outline text-primary me-2 font-18"></i> Is <?= esc(setting('App.siteName')) ?> truly 100% free?
                            </button>
                        </h2>
                        <div id="faqOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted font-14 lh-base">
                                <strong>Yes, absolutely.</strong> <?= esc(setting('App.siteName')) ?> is distributed under the open-source MIT License. You can run it on your own hardware or cloud server for unlimited users without ever paying a single cent in licensing fees or subscription costs.
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 2 -->
                    <div class="accordion-item card mb-3 border">
                        <h2 class="accordion-header" id="headingTwo">
                            <button class="accordion-button collapsed fw-bold text-dark font-16" type="button" data-bs-toggle="collapse" data-bs-target="#faqTwo" aria-expanded="false" aria-controls="faqTwo">
                                <i class="mdi mdi-server-network text-primary me-2 font-18"></i> What tech stack does the platform run on?
                            </button>
                        </h2>
                        <div id="faqTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted font-14 lh-base">
                                <?= esc(setting('App.siteName')) ?> is engineered for speed, minimal resource consumption, and ease of deployment:
                                <ul class="mt-2 mb-0">
                                    <li><strong>Backend:</strong> CodeIgniter 4 (PHP 8.1+)</li>
                                    <li><strong>Database:</strong> MySQL 8.0 / MariaDB or PostgreSQL</li>
                                    <li><strong>Frontend:</strong> Hyper SaaS Theme (Bootstrap 5, Vanilla JS, Material Design Icons)</li>
                                    <li><strong>Authentication:</strong> CodeIgniter Shield (Session-based, RBAC, 2FA ready)</li>
                                    <li><strong>Containerization:</strong> Docker & Docker Compose support out-of-the-box</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 3 -->
                    <div class="accordion-item card mb-3 border">
                        <h2 class="accordion-header" id="headingThree">
                            <button class="accordion-button collapsed fw-bold text-dark font-16" type="button" data-bs-toggle="collapse" data-bs-target="#faqThree" aria-expanded="false" aria-controls="faqThree">
                                <i class="mdi mdi-docker text-primary me-2 font-18"></i> How do I deploy it on my server or Railway?
                            </button>
                        </h2>
                        <div id="faqThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted font-14 lh-base">
                                The fastest method is using Docker. Simply clone the repository, run <code>docker-compose up -d</code>, and visit <code>http://localhost:8080</code>. Database migrations and demo seeders will execute automatically on container startup. For step-by-step instructions, visit our <a href="<?= site_url('setup') ?>">Setup Guide</a>.
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 4 -->
                    <div class="accordion-item card mb-3 border">
                        <h2 class="accordion-header" id="headingFour">
                            <button class="accordion-button collapsed fw-bold text-dark font-16" type="button" data-bs-toggle="collapse" data-bs-target="#faqFour" aria-expanded="false" aria-controls="faqFour">
                                <i class="mdi mdi-shield-lock-outline text-primary me-2 font-18"></i> How is our proprietary data protected?
                            </button>
                        </h2>
                        <div id="faqFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted font-14 lh-base">
                                Because <?= esc(setting('App.siteName')) ?> is completely self-hosted, your data resides strictly within your own infrastructure (VPS, private cloud, or on-premise server). No third-party AI models or external trackers have access to your confidential issues, bug reports, or roadmap plans.
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 5 -->
                    <div class="accordion-item card mb-3 border">
                        <h2 class="accordion-header" id="headingFive">
                            <button class="accordion-button collapsed fw-bold text-dark font-16" type="button" data-bs-toggle="collapse" data-bs-target="#faqFive" aria-expanded="false" aria-controls="faqFive">
                                <i class="mdi mdi-database-export text-primary me-2 font-18"></i> Can I export or back up my database?
                            </button>
                        </h2>
                        <div id="faqFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted font-14 lh-base">
                                <strong>Yes.</strong> You have full, unrestricted access to your underlying MySQL database. You can run standard <code>mysqldump</code> commands or automated backup cron jobs at any time with zero vendor lock-in.
                            </div>
                        </div>
                    </div>

                </div>

                <div class="card bg-primary text-white text-center p-4 mt-5 rounded">
                    <h4 class="text-white fw-bold mb-2">Have a question not answered here?</h4>
                    <p class="text-white-50 font-14 mb-3">Check out our community repository on GitHub or start a discussion.</p>
                    <div>
                        <a href="https://github.com/Niccher/Chege-Jira-WebApp/issues" target="_blank" class="btn btn-light rounded-pill px-4">
                            <i class="mdi mdi-github me-1"></i> Open GitHub Issue
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<?= $this->include('landing/footer') ?>
