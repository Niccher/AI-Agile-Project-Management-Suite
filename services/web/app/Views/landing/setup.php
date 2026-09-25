<?= $this->include('landing/header') ?>

<style>
    .terminal-window {
        background: #1e222d;
        border-radius: 8px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        overflow: hidden;
        border: 1px solid #2d3243;
    }
    .terminal-header {
        background: #141720;
        padding: 10px 16px;
        display: flex;
        align-items: center;
        border-bottom: 1px solid #2d3243;
    }
    .terminal-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 6px;
    }
    .dot-red { background: #ff5f56; }
    .dot-yellow { background: #ffbd2e; }
    .dot-green { background: #27c93f; }
    .terminal-title {
        color: #94a3b8;
        font-size: 12px;
        font-family: monospace;
        margin-left: 10px;
    }
    .terminal-body {
        padding: 16px 20px;
        font-family: 'Consolas', 'Courier New', monospace;
        font-size: 13px;
        line-height: 1.6;
        color: #e2e8f0;
        margin: 0;
        overflow-x: auto;
    }
    .terminal-cmd {
        color: #38bdf8;
    }
    .terminal-comment {
        color: #64748b;
    }
    .terminal-success {
        color: #4ade80;
    }
    .btn-copy-cmd {
        background: rgba(255,255,255,0.08);
        border: 1px solid rgba(255,255,255,0.15);
        color: #cbd5e1;
        font-size: 12px;
        padding: 3px 10px;
        border-radius: 4px;
        transition: all 0.2s;
    }
    .btn-copy-cmd:hover {
        background: rgba(255,255,255,0.18);
        color: #fff;
    }
    .nav-pills-custom .nav-link {
        color: #475569;
        font-weight: 600;
        padding: 10px 20px;
        border-radius: 8px;
        transition: all 0.2s;
        border: 1px solid transparent;
    }
    .nav-pills-custom .nav-link.active {
        background-color: #727cf5;
        color: #fff;
        box-shadow: 0 4px 12px rgba(114, 124, 245, 0.35);
    }
    .nav-pills-custom .nav-link:hover:not(.active) {
        background-color: #f1f5f9;
        color: #1e293b;
    }
</style>

<!-- START PAGE HEADER -->
<section class="py-5 bg-light border-bottom">
    <div class="container text-center py-4">
        <span class="badge bg-primary-lighten text-primary rounded-pill px-3 py-1 font-12 fw-semibold">Quick Start</span>
        <h1 class="fw-bold mt-2 mb-2 display-6">Self-Hosting & Deployment Guide</h1>
        <p class="text-muted font-16 lead w-75 mx-auto mb-0">Deploy <?= esc(setting('App.siteName')) ?> to your local machine, private cloud, or Railway instance in under 2 minutes.</p>
    </div>
</section>
<!-- END PAGE HEADER -->

<section class="py-5">
    <div class="container py-lg-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">

                <!-- DEPLOYMENT OPTION TABS -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body p-4">
                        <ul class="nav nav-pills nav-pills-custom mb-4 justify-content-center" id="setupTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active d-flex align-items-center" id="docker-tab" data-bs-toggle="pill" data-bs-target="#docker-pane" type="button" role="tab">
                                    <i class="mdi mdi-docker font-18 me-2"></i> Docker & Compose
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link d-flex align-items-center" id="railway-tab" data-bs-toggle="pill" data-bs-target="#railway-pane" type="button" role="tab">
                                    <i class="mdi mdi-cloud-upload-outline font-18 me-2"></i> Railway & Cloud
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link d-flex align-items-center" id="manual-tab" data-bs-toggle="pill" data-bs-target="#manual-pane" type="button" role="tab">
                                    <i class="mdi mdi-server-network font-18 me-2"></i> Manual LAMP / VPS
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content" id="setupTabsContent">
                            
                            <!-- TAB 1: DOCKER COMPOSE -->
                            <div class="tab-pane fade show active" id="docker-pane" role="tabpanel">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h4 class="fw-bold text-dark mb-0">
                                        <i class="mdi mdi-docker text-primary me-2"></i> Option 1: Docker Compose (Recommended)
                                    </h4>
                                    <span class="badge bg-success-lighten text-success font-12">Fastest (2 min)</span>
                                </div>
                                <p class="text-muted font-14 mb-4">
                                    The recommended zero-configuration setup. Spins up Nginx, PHP 8.1-FPM, and MySQL in isolated containers with automatic database migrations and initial demo seeding.
                                </p>

                                <div class="mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="fw-bold text-dark font-14">1. Clone the repository & enter directory:</label>
                                        <button class="btn-copy-cmd" onclick="copyCode('cmd-clone', this)"><i class="mdi mdi-content-copy me-1"></i>Copy</button>
                                    </div>
                                    <div class="terminal-window">
                                        <div class="terminal-header">
                                            <span class="terminal-dot dot-red"></span>
                                            <span class="terminal-dot dot-yellow"></span>
                                            <span class="terminal-dot dot-green"></span>
                                            <span class="terminal-title">bash — clone</span>
                                        </div>
                                        <pre class="terminal-body" id="cmd-clone"><code><span class="terminal-cmd">git clone</span> https://github.com/Niccher/Chege-Jira-WebApp.git
<span class="terminal-cmd">cd</span> Chege-Jira-WebApp</code></pre>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="fw-bold text-dark font-14">2. Copy environment template:</label>
                                        <button class="btn-copy-cmd" onclick="copyCode('cmd-env', this)"><i class="mdi mdi-content-copy me-1"></i>Copy</button>
                                    </div>
                                    <div class="terminal-window">
                                        <div class="terminal-header">
                                            <span class="terminal-dot dot-red"></span>
                                            <span class="terminal-dot dot-yellow"></span>
                                            <span class="terminal-dot dot-green"></span>
                                            <span class="terminal-title">bash — env</span>
                                        </div>
                                        <pre class="terminal-body" id="cmd-env"><code><span class="terminal-cmd">cp</span> env .env</code></pre>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="fw-bold text-dark font-14">3. Launch container stack:</label>
                                        <button class="btn-copy-cmd" onclick="copyCode('cmd-docker-up', this)"><i class="mdi mdi-content-copy me-1"></i>Copy</button>
                                    </div>
                                    <div class="terminal-window">
                                        <div class="terminal-header">
                                            <span class="terminal-dot dot-red"></span>
                                            <span class="terminal-dot dot-yellow"></span>
                                            <span class="terminal-dot dot-green"></span>
                                            <span class="terminal-title">bash — docker</span>
                                        </div>
                                        <pre class="terminal-body" id="cmd-docker-up"><code><span class="terminal-cmd">docker-compose</span> up -d --build</code></pre>
                                    </div>
                                </div>

                                <div class="alert alert-primary border-0 d-flex align-items-center" role="alert">
                                    <i class="mdi mdi-check-circle-outline font-22 text-primary me-3"></i>
                                    <div class="font-14">
                                        Container startup automatically runs <code>php spark migrate --all</code> and <code>php spark db:seed DemoSeeder</code>. Once running, access the application at <a href="http://localhost:8080" class="fw-bold text-primary text-decoration-underline" target="_blank">http://localhost:8080</a>.
                                    </div>
                                </div>
                            </div>

                            <!-- TAB 2: RAILWAY & CLOUD -->
                            <div class="tab-pane fade" id="railway-pane" role="tabpanel">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h4 class="fw-bold text-dark mb-0">
                                        <i class="mdi mdi-cloud-upload-outline text-info me-2"></i> Option 2: Railway & Cloud PaaS
                                    </h4>
                                    <span class="badge bg-info-lighten text-info font-12">Cloud Production</span>
                                </div>
                                <p class="text-muted font-14 mb-4">
                                    Deploy directly to Railway, Render, or Fly.io using the integrated Dockerfile and entrypoint script.
                                </p>

                                <div class="card bg-light border-0 p-3 mb-4">
                                    <h5 class="fw-bold text-dark font-15 mb-2"><i class="mdi mdi-database me-1 text-primary"></i> 1. Attach MySQL Database</h5>
                                    <p class="text-muted font-14 mb-0">In your Railway project, click <strong>New &rarr; Database &rarr; MySQL</strong>. Railway automatically generates connection variables.</p>
                                </div>

                                <div class="card bg-light border-0 p-3 mb-4">
                                    <h5 class="fw-bold text-dark font-15 mb-2"><i class="mdi mdi-tune-variant me-1 text-warning"></i> 2. Configure Environment Variables</h5>
                                    <p class="text-muted font-14 mb-2">Set the following variables in your Railway web service settings:</p>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered bg-white mb-0 font-13">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Variable Key</th>
                                                    <th>Value / Description</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><code>MYSQLHOST</code></td>
                                                    <td><code>${{MySQL.MYSQLHOST}}</code> (Auto-provided by Railway MySQL plugin)</td>
                                                </tr>
                                                <tr>
                                                    <td><code>MYSQLPORT</code></td>
                                                    <td><code>${{MySQL.MYSQLPORT}}</code></td>
                                                </tr>
                                                <tr>
                                                    <td><code>MYSQLUSER</code></td>
                                                    <td><code>${{MySQL.MYSQLUSER}}</code></td>
                                                </tr>
                                                <tr>
                                                    <td><code>MYSQLPASSWORD</code></td>
                                                    <td><code>${{MySQL.MYSQLPASSWORD}}</code></td>
                                                </tr>
                                                <tr>
                                                    <td><code>app.baseURL</code></td>
                                                    <td><code>https://${{RAILWAY_PUBLIC_DOMAIN}}</code> or custom domain</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="alert alert-info border-0 d-flex align-items-center" role="alert">
                                    <i class="mdi mdi-rocket-launch-outline font-22 text-info me-3"></i>
                                    <div class="font-14">
                                        The production entrypoint script (<code>entrypoint.sh</code>) detects MySQL availability on boot, applies schema migrations, and seeds the initial administrator account automatically!
                                    </div>
                                </div>
                            </div>

                            <!-- TAB 3: MANUAL LAMP / VPS -->
                            <div class="tab-pane fade" id="manual-pane" role="tabpanel">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h4 class="fw-bold text-dark mb-0">
                                        <i class="mdi mdi-server-network text-warning me-2"></i> Option 3: Manual LAMP / VPS
                                    </h4>
                                    <span class="badge bg-warning-lighten text-warning font-12">Custom Hosting</span>
                                </div>
                                <p class="text-muted font-14 mb-4">
                                    For dedicated Linux VPS or classic web servers running Apache or Nginx with PHP 8.1+.
                                </p>

                                <div class="mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="fw-bold text-dark font-14">1. Install Dependencies & Setup Environment:</label>
                                        <button class="btn-copy-cmd" onclick="copyCode('cmd-manual-deps', this)"><i class="mdi mdi-content-copy me-1"></i>Copy</button>
                                    </div>
                                    <div class="terminal-window">
                                        <pre class="terminal-body" id="cmd-manual-deps"><code><span class="terminal-comment"># Clone repository</span>
<span class="terminal-cmd">git clone</span> https://github.com/Niccher/Chege-Jira-WebApp.git
<span class="terminal-cmd">cd</span> Chege-Jira-WebApp

<span class="terminal-comment"># Install Composer packages</span>
<span class="terminal-cmd">composer install</span> --no-dev --optimize-autoloader

<span class="terminal-comment"># Copy & configure database in .env</span>
<span class="terminal-cmd">cp</span> env .env</code></pre>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="fw-bold text-dark font-14">2. Run Migrations & Database Seeding:</label>
                                        <button class="btn-copy-cmd" onclick="copyCode('cmd-manual-migrate', this)"><i class="mdi mdi-content-copy me-1"></i>Copy</button>
                                    </div>
                                    <div class="terminal-window">
                                        <pre class="terminal-body" id="cmd-manual-migrate"><code><span class="terminal-comment"># Execute migrations</span>
<span class="terminal-cmd">php spark migrate</span> --all

<span class="terminal-comment"># Seed demo accounts and projects</span>
<span class="terminal-cmd">php spark db:seed</span> DemoSeeder

<span class="terminal-comment"># Set writable folder permissions</span>
<span class="terminal-cmd">chmod -R 775</span> writable/</code></pre>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- DEFAULT CREDENTIALS CARD -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                        <h4 class="fw-bold text-dark mb-0 font-16">
                            <i class="mdi mdi-key-variant text-warning me-2"></i> Default Seeded Accounts
                        </h4>
                        <a href="<?= site_url('auth/login') ?>" class="btn btn-primary btn-sm rounded-pill px-3">
                            <i class="mdi mdi-login me-1"></i> Go to Sign In &rarr;
                        </a>
                    </div>
                    <div class="card-body p-4">
                        <p class="text-muted font-14 mb-3">When seeded via <code>DemoSeeder</code>, you can log in immediately using these default accounts:</p>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded border h-100 position-relative">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge bg-danger-lighten text-danger">Administrator</span>
                                        <button class="btn btn-outline-secondary btn-sm py-0 px-2 font-11" onclick="copyCreds('admin@chegejira.local', 'secret', this)">
                                            <i class="mdi mdi-content-copy me-1"></i>Copy Creds
                                        </button>
                                    </div>
                                    <p class="mb-1 font-13"><strong>Email:</strong> <code>admin@chegejira.local</code></p>
                                    <p class="mb-2 font-13"><strong>Password:</strong> <code>secret</code></p>
                                    <span class="text-muted font-11"><i class="mdi mdi-shield-crown-outline me-1"></i> Full access to Admin, Manager & User workspaces.</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded border h-100 position-relative">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge bg-primary-lighten text-primary">Standard Developer</span>
                                        <button class="btn btn-outline-secondary btn-sm py-0 px-2 font-11" onclick="copyCreds('dev@chegejira.local', 'secret', this)">
                                            <i class="mdi mdi-content-copy me-1"></i>Copy Creds
                                        </button>
                                    </div>
                                    <p class="mb-1 font-13"><strong>Email:</strong> <code>dev@chegejira.local</code></p>
                                    <p class="mb-2 font-13"><strong>Password:</strong> <code>secret</code></p>
                                    <span class="text-muted font-11"><i class="mdi mdi-account-cog-outline me-1"></i> Full access to Kanban, Projects, Time Tracking & Notes.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SYSTEM REQUIREMENTS -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h4 class="fw-bold text-dark mb-0 font-16">
                            <i class="mdi mdi-check-network-outline text-success me-2"></i> System Requirements & Tech Stack
                        </h4>
                    </div>
                    <div class="card-body p-4">
                        <div class="row font-14 text-muted g-3">
                            <div class="col-md-6">
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-2"><i class="mdi mdi-check-circle text-success me-2"></i><strong>PHP:</strong> 8.1 or 8.2+</li>
                                    <li class="mb-2"><i class="mdi mdi-check-circle text-success me-2"></i><strong>PHP Extensions:</strong> <code>intl</code>, <code>mbstring</code>, <code>mysqli</code>, <code>curl</code></li>
                                    <li class="mb-2"><i class="mdi mdi-check-circle text-success me-2"></i><strong>Database:</strong> MySQL 8.0+ / MariaDB 10.5+</li>
                                    <li class="mb-2"><i class="mdi mdi-check-circle text-success me-2"></i><strong>Session Store:</strong> Redis 6.0+ (Production Ready)</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-2"><i class="mdi mdi-check-circle text-success me-2"></i><strong>Web Server:</strong> Nginx or Apache with <code>mod_rewrite</code></li>
                                    <li class="mb-2"><i class="mdi mdi-check-circle text-success me-2"></i><strong>Framework:</strong> CodeIgniter 4.6.4 + Shield</li>
                                    <li class="mb-2"><i class="mdi mdi-check-circle text-success me-2"></i><strong>Frontend UI:</strong> Hyper SaaS Theme (Bootstrap 5)</li>
                                    <li class="mb-2"><i class="mdi mdi-check-circle text-success me-2"></i><strong>Microservice:</strong> ML AI Container (FastAPI)</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<script>
function copyCode(elementId, btn) {
    const el = document.getElementById(elementId);
    if (!el) return;
    const text = el.innerText || el.textContent;
    navigator.clipboard.writeText(text).then(() => {
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="mdi mdi-check text-success me-1"></i>Copied!';
        btn.classList.add('border-success');
        setTimeout(() => {
            btn.innerHTML = originalHtml;
            btn.classList.remove('border-success');
        }, 2000);
    });
}

function copyCreds(email, password, btn) {
    const text = `Email: ${email}\nPassword: ${password}`;
    navigator.clipboard.writeText(text).then(() => {
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="mdi mdi-check text-success me-1"></i>Copied!';
        setTimeout(() => {
            btn.innerHTML = originalHtml;
        }, 2000);
    });
}
</script>

<?= $this->include('landing/footer') ?>
