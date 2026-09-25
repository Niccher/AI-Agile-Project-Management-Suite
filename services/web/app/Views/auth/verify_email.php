<?= $this->extend('layouts/hyper/auth_template') ?>

<?= $this->section('title') ?>Verify Email • <?= esc(setting('App.siteName')) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="text-center mb-4">
    <div class="mb-3">
        <div class="avatar-lg bg-primary-lighten text-primary rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 54px; height: 54px;">
            <i class="mdi mdi-email-seal-outline font-30"></i>
        </div>
    </div>
    <h3 class="fw-bold mb-1">Check Your Inbox</h3>
    <p class="text-muted font-14 mb-0">We have sent a verification link to your email address.</p>
</div>

<?php if (session()->has('error')) : ?>
    <div class="alert alert-danger d-flex align-items-center mb-3" role="alert">
        <i class="mdi mdi-alert-circle-outline font-18 me-2"></i>
        <div><?= session('error') ?></div>
    </div>
<?php endif ?>

<?php if (session()->has('success') || session()->has('message')) : ?>
    <div class="alert alert-success d-flex align-items-center mb-3" role="alert">
        <i class="mdi mdi-check-circle-outline font-18 me-2"></i>
        <div><?= session('success') ?? session('message') ?></div>
    </div>
<?php endif ?>

<form action="<?= site_url('auth/resend-verification') ?>" method="POST" class="mb-3">
    <?= csrf_field() ?>
    <?php if (session()->has('email')) : ?>
        <input type="hidden" name="email" value="<?= esc(session('email')) ?>">
    <?php else: ?>
        <div class="mb-3 text-start">
            <label for="email" class="form-label font-13 fw-semibold">Email address</label>
            <div class="input-group">
                <span class="input-group-text"><i class="mdi mdi-email-outline"></i></span>
                <input class="form-control" type="email" id="email" name="email" placeholder="name@company.com" required>
            </div>
        </div>
    <?php endif; ?>
    
    <div class="d-grid">
        <button type="submit" class="btn btn-primary btn-lg rounded-pill fw-semibold">
            <i class="mdi mdi-email-sync-outline me-1"></i> Resend Verification Link
        </button>
    </div>
</form>

<div class="pt-3 border-top text-center">
    <p class="text-muted font-14 mb-0">
        Back to <a href="<?= site_url('auth/login') ?>" class="text-primary fw-bold ms-1">Sign In</a>
    </p>
</div>
<?= $this->endSection() ?>
