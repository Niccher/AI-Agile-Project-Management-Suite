<?= $this->extend('layouts/hyper/auth_template') ?>

<?= $this->section('title') ?>Set New Password • <?= esc(setting('App.siteName')) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="text-center mb-4">
    <div class="avatar-md bg-primary-lighten text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 52px; height: 52px;">
        <i class="mdi mdi-shield-key-outline font-24"></i>
    </div>
    <h3 class="fw-bold mb-1">Set New Password</h3>
    <p class="text-muted font-14 mb-0">Enter your new secure password below to regain access</p>
</div>

<?php if (session()->has('error')) : ?>
    <div class="alert alert-danger d-flex align-items-center mb-3" role="alert">
        <i class="mdi mdi-alert-circle-outline font-18 me-2"></i>
        <div><?= session('error') ?></div>
    </div>
<?php endif ?>

<?php if (session()->has('errors')) : ?>
    <div class="alert alert-danger mb-3" role="alert">
        <div class="fw-semibold mb-1"><i class="mdi mdi-alert-circle-outline me-1"></i>Please fix the following:</div>
        <ul class="mb-0 ps-3 font-13">
            <?php foreach (session('errors') as $error) : ?>
                <li><?= esc($error) ?></li>
            <?php endforeach ?>
        </ul>
    </div>
<?php endif ?>

<?php if (session()->has('success')) : ?>
    <div class="alert alert-success d-flex align-items-center mb-3" role="alert">
        <i class="mdi mdi-check-circle-outline font-18 me-2"></i>
        <div><?= session('success') ?></div>
    </div>
<?php endif ?>

<form action="<?= site_url('auth/reset-password') ?>" method="POST" id="resetPasswordForm">
    <?= csrf_field() ?>
    <input type="hidden" name="token" value="<?= esc($token ?? old('token')) ?>">
    <input type="hidden" name="email" value="<?= esc($email ?? old('email')) ?>">

    <div class="mb-3">
        <label for="password" class="form-label fw-semibold font-13">New Password <span class="text-danger">*</span></label>
        <div class="input-group input-group-merge">
            <span class="input-group-text"><i class="mdi mdi-lock-outline"></i></span>
            <input type="password" id="password" name="password" class="form-control" placeholder="Minimum 8 characters" required minlength="8" autofocus autocomplete="new-password">
            <div class="input-group-text" data-password="false" style="cursor: pointer;">
                <span class="password-eye"></span>
            </div>
        </div>
    </div>

    <div class="mb-3">
        <label for="password_confirm" class="form-label fw-semibold font-13">Confirm New Password <span class="text-danger">*</span></label>
        <div class="input-group input-group-merge">
            <span class="input-group-text"><i class="mdi mdi-lock-check-outline"></i></span>
            <input type="password" id="password_confirm" name="password_confirm" class="form-control" placeholder="Confirm new password" required minlength="8" autocomplete="new-password">
            <div class="input-group-text" data-password="false" style="cursor: pointer;">
                <span class="password-eye"></span>
            </div>
        </div>
    </div>

    <div class="d-grid mb-3">
        <button class="btn btn-primary btn-lg rounded-pill fw-semibold" type="submit">
            <i class="mdi mdi-lock-reset me-1"></i> Update Password &amp; Sign In
        </button>
    </div>
</form>

<div class="text-center mt-3 pt-3 border-top">
    <p class="text-muted font-14 mb-0">
        Remembered your password? 
        <a href="<?= site_url('auth/login') ?>" class="text-primary fw-bold ms-1">Sign In</a>
    </p>
</div>

<?= $this->endSection() ?>
