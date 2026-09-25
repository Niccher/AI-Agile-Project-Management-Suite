    <!-- FOOTER START -->
    <footer class="bg-dark py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-5">
                    <h4 class="text-white mb-3 d-inline-flex align-items-center">
                        <img src="<?= base_url('assets/img/app_logo.jpg') ?>" class="rounded-circle me-2" height="26" width="26" alt="Logo">
                        <span><?= esc(setting('App.siteName')) ?></span>
                    </h4>
                    <p class="text-muted w-75">Self-hosted, open-source agile project management platform built for engineering teams who value speed, flexibility, and complete data privacy.</p>
                </div>
                <div class="col-lg-2 col-md-4 mt-4 mt-lg-0">
                    <h5 class="text-white mb-3">Product</h5>
                    <ul class="list-unstyled text-muted mb-0">
                        <li class="mb-2"><a href="<?= site_url('features') ?>" class="text-muted">Features</a></li>
                        <li class="mb-2"><a href="<?= site_url('pricing') ?>" class="text-muted">Pricing</a></li>
                        <li class="mb-2"><a href="<?= site_url('compare') ?>" class="text-muted">Compare</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-4 mt-4 mt-lg-0">
                    <h5 class="text-white mb-3">Resources</h5>
                    <ul class="list-unstyled text-muted mb-0">
                        <li class="mb-2"><a href="<?= site_url('setup') ?>" class="text-muted">Setup Guide</a></li>
                        <li class="mb-2"><a href="<?= site_url('faqs') ?>" class="text-muted">FAQs</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-4 mt-4 mt-lg-0">
                    <h5 class="text-white mb-3">Open Source</h5>
                    <ul class="list-unstyled text-muted mb-0">
                        <li class="mb-2"><a href="<?= site_url('community') ?>" class="text-muted">Community &amp; Contributing</a></li>
                        <li class="mb-2"><a href="https://github.com/Niccher/Chege-Jira-WebApp" target="_blank" class="text-muted"><i class="mdi mdi-github me-1"></i> GitHub Repository</a></li>
                        <li class="mb-2"><span class="badge bg-success-lighten text-success">MIT License</span></li>
                    </ul>
                </div>
            </div>
            
            <div class="row mt-5 pt-3 border-top border-secondary">
                <div class="col-12 text-center">
                    <p class="text-muted mb-0">&copy; <?= date('Y') ?> <?= esc(setting('App.siteName')) ?>. Released under the MIT Open Source License.</p>
                </div>
            </div>
        </div>
    </footer>
    <!-- FOOTER END -->

    <!-- bundle -->
    <script src="<?= base_url('assets/hyper/js/vendor.min.js') ?>"></script>
    <script src="<?= base_url('assets/hyper/js/app.min.js') ?>"></script>

    <!-- Theme Toggle Handler -->
    <script>
    (function() {
        var toggleBtn = document.getElementById('theme-toggle-btn');
        var toggleIcon = document.getElementById('theme-toggle-icon');
        var lightStyle = document.getElementById('light-style');
        var darkStyle = document.getElementById('dark-style');

        function updateIcon(theme) {
            if (!toggleIcon) return;
            if (theme === 'dark') {
                toggleIcon.className = 'mdi mdi-weather-sunny font-18 text-warning';
            } else {
                toggleIcon.className = 'mdi mdi-weather-night font-18 text-white';
            }
        }

        function applyTheme(theme) {
            if (theme === 'dark') {
                if (lightStyle) lightStyle.setAttribute('disabled', 'disabled');
                if (darkStyle) darkStyle.removeAttribute('disabled');
                document.documentElement.setAttribute('data-bs-theme', 'dark');
                document.body.classList.add('dark-theme');
            } else {
                if (darkStyle) darkStyle.setAttribute('disabled', 'disabled');
                if (lightStyle) lightStyle.removeAttribute('disabled');
                document.documentElement.setAttribute('data-bs-theme', 'light');
                document.body.classList.remove('dark-theme');
            }
            updateIcon(theme);
        }

        var currentTheme = localStorage.getItem('hyper_theme') || 'light';
        applyTheme(currentTheme);

        if (toggleBtn) {
            toggleBtn.addEventListener('click', function(e) {
                e.preventDefault();
                var current = localStorage.getItem('hyper_theme') || 'light';
                var next = (current === 'dark') ? 'light' : 'dark';
                localStorage.setItem('hyper_theme', next);
                applyTheme(next);
            });
        }
    })();
    </script>
</body>
</html>
