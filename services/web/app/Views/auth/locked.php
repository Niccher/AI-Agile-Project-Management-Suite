<?= $this->extend('layouts/hyper/auth_template') ?>

<?= $this->section('title') ?>Account Locked • <?= esc(setting('App.siteName')) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="text-center mb-4">
    <div class="mb-3">
        <div class="avatar-lg bg-danger-lighten text-danger rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 54px; height: 54px;">
            <i class="mdi mdi-lock-alert-outline font-32"></i>
        </div>
    </div>
    <h3 class="fw-bold text-danger mb-1">Account Locked</h3>
    <p class="text-muted font-14 mb-0">Your account has been temporarily locked due to multiple failed login attempts.</p>
</div>

<?php if (session()->has('error')) : ?>
    <div class="alert alert-danger d-flex align-items-center mb-3" role="alert">
        <i class="mdi mdi-alert-circle-outline font-18 me-2"></i>
        <div><?= session('error') ?></div>
    </div>
<?php endif ?>

<?php if (session()->has('success')) : ?>
    <div class="alert alert-success d-flex align-items-center mb-3" role="alert">
        <i class="mdi mdi-check-circle-outline font-18 me-2"></i>
        <div><?= session('success') ?></div>
    </div>
<?php endif ?>

<?php if (session()->has('email')) : ?>
    <form action="<?= site_url('auth/unlock-account') ?>" method="POST" class="mb-3">
        <?= csrf_field() ?>
        <input type="hidden" name="email" value="<?= esc(session('email')) ?>">
        <button type="submit" class="btn btn-danger btn-lg rounded-pill w-100 fw-semibold">
            <i class="mdi mdi-lock-open-outline me-1"></i> Request Account Unlock
        </button>
    </form>
<?php endif; ?>

<div class="d-grid mb-3">
    <a href="<?= site_url('auth/login') ?>" class="btn btn-outline-secondary rounded-pill font-13">
        <i class="mdi mdi-arrow-left me-1"></i> Return to Sign In
    </a>
</div>

<div class="pt-3 border-top text-center">
    <p class="text-muted font-13 mb-0">
        Need assistance? <a href="<?= site_url('faqs') ?>" class="text-primary text-decoration-none fw-semibold">Contact Support</a>
    </p>
</div>
<?= $this->endSection() ?>
