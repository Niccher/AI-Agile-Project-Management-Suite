<?= $this->extend('layouts/hyper/auth_template') ?>

<?= $this->section('title') ?>Account Activation • <?= esc(setting('App.siteName')) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
    <?php if($status === 'success'): ?>
        <div class="text-center mb-4">
            <div class="mb-3">
                <div class="avatar-lg bg-success-lighten text-success rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 54px; height: 54px;">
                    <i class="mdi mdi-check-decagram-outline font-32"></i>
                </div>
            </div>
            <h3 class="fw-bold mb-1">Account Activated!</h3>
            <p class="text-muted font-14 mb-0">Your workspace account has been verified and activated successfully.</p>
        </div>

        <div class="d-grid gap-2 mb-3">
            <a href="<?= site_url('home') ?>" class="btn btn-primary btn-lg rounded-pill fw-semibold">
                <i class="mdi mdi-view-dashboard-outline me-1"></i> Go to Dashboard
            </a>
            <a href="<?= site_url('auth/login') ?>" class="btn btn-outline-secondary rounded-pill font-13">
                <i class="mdi mdi-login me-1"></i> Sign In Directly
            </a>
        </div>

    <?php elseif($status === 'already_activated'): ?>
        <div class="text-center mb-4">
            <div class="mb-3">
                <div class="avatar-lg bg-info-lighten text-info rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 54px; height: 54px;">
                    <i class="mdi mdi-information-outline font-32"></i>
                </div>
            </div>
            <h3 class="fw-bold mb-1">Already Activated</h3>
            <p class="text-muted font-14 mb-0">This account is active. You can sign in immediately.</p>
        </div>

        <div class="d-grid mb-3">
            <a href="<?= site_url('auth/login') ?>" class="btn btn-primary btn-lg rounded-pill fw-semibold">
                <i class="mdi mdi-login me-1"></i> Sign In to Workspace
            </a>
        </div>

    <?php elseif($status === 'invalid_token'): ?>
        <div class="text-center mb-4">
            <div class="mb-3">
                <div class="avatar-lg bg-danger-lighten text-danger rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 54px; height: 54px;">
                    <i class="mdi mdi-alert-circle-outline font-32"></i>
                </div>
            </div>
            <h3 class="fw-bold mb-1">Invalid Activation Link</h3>
            <p class="text-muted font-14 mb-0">The link is invalid or may have already been used.</p>
        </div>

        <div class="mb-3">
            <form action="<?= site_url('auth/resend-verification') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="email" value="<?= esc($email ?? '') ?>">
                <button type="submit" class="btn btn-primary btn-lg rounded-pill w-100 fw-semibold mb-2">
                    <i class="mdi mdi-email-sync-outline me-1"></i> Resend Activation Link
                </button>
            </form>
            <a href="<?= site_url('auth/login') ?>" class="btn btn-outline-secondary rounded-pill w-100 font-13">
                <i class="mdi mdi-login me-1"></i> Return to Sign In
            </a>
        </div>

    <?php elseif($status === 'expired_token'): ?>
        <div class="text-center mb-4">
            <div class="mb-3">
                <div class="avatar-lg bg-warning-lighten text-warning rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 54px; height: 54px;">
                    <i class="mdi mdi-timer-sand-empty font-32"></i>
                </div>
            </div>
            <h3 class="fw-bold mb-1">Activation Link Expired</h3>
            <p class="text-muted font-14 mb-0">Activation links expire after 24 hours.</p>
        </div>

        <div class="mb-3">
            <form action="<?= site_url('auth/resend-verification') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="email" value="<?= esc($email ?? '') ?>">
                <button type="submit" class="btn btn-primary btn-lg rounded-pill w-100 fw-semibold mb-2">
                    <i class="mdi mdi-email-sync-outline me-1"></i> Resend Activation Link
                </button>
            </form>
            <a href="<?= site_url('auth/register') ?>" class="btn btn-outline-secondary rounded-pill w-100 font-13">
                <i class="mdi mdi-account-plus-outline me-1"></i> Register New Account
            </a>
        </div>

    <?php else: ?>
        <div class="text-center mb-4">
            <div class="mb-3">
                <div class="avatar-lg bg-danger-lighten text-danger rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 54px; height: 54px;">
                    <i class="mdi mdi-alert-circle-outline font-32"></i>
                </div>
            </div>
            <h3 class="fw-bold mb-1">Activation Error</h3>
            <p class="text-muted font-14 mb-0">An unexpected issue occurred while processing your activation.</p>
        </div>

        <div class="d-grid gap-2 mb-3">
            <a href="<?= site_url('auth/login') ?>" class="btn btn-primary btn-lg rounded-pill fw-semibold">
                <i class="mdi mdi-login me-1"></i> Return to Sign In
            </a>
        </div>
    <?php endif; ?>

    <div class="pt-3 border-top text-center">
        <p class="text-muted font-13 mb-0">
            Need help? Visit our <a href="<?= site_url('faqs') ?>" class="text-primary text-decoration-none fw-semibold">Help Center &amp; FAQs</a>
        </p>
    </div>
<?= $this->endSection() ?>