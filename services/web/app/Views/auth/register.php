<?= $this->extend('layouts/hyper/auth_template') ?>

<?= $this->section('title') ?>Create Account • <?= esc(setting('App.siteName')) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="text-center mb-4">
    <div class="avatar-md bg-primary-lighten text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 52px; height: 52px;">
        <i class="mdi mdi-account-plus-outline font-24"></i>
    </div>
    <h3 class="fw-bold mb-1">Create Account</h3>
    <p class="text-muted font-14 mb-0">Get started in less than a minute</p>
</div>

<?php if(session()->has('error')): ?>
    <div class="alert alert-danger d-flex align-items-center mb-3" role="alert">
        <i class="mdi mdi-alert-circle-outline font-18 me-2"></i>
        <div><?= session('error') ?></div>
    </div>
<?php endif; ?>

<?php if(session()->has('errors')): ?>
    <div class="alert alert-danger mb-3" role="alert">
        <div class="fw-semibold mb-1"><i class="mdi mdi-alert-circle-outline me-1"></i>Please fix the following:</div>
        <ul class="mb-0 ps-3 font-13">
            <?php foreach(session('errors') as $err): ?>
                <li><?= esc($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if(session()->has('success') || session()->has('message')): ?>
    <div class="alert alert-success d-flex align-items-center mb-3" role="alert">
        <i class="mdi mdi-check-circle-outline font-18 me-2"></i>
        <div><?= session('success') ?? session('message') ?></div>
    </div>
<?php endif; ?>

<form action="<?= site_url('auth/register') ?>" method="POST" id="registerForm">
    <?= csrf_field() ?>

    <div class="row g-2 mb-3">
        <div class="col-sm-6">
            <label for="first_name" class="form-label fw-semibold font-13">First Name <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text"><i class="mdi mdi-account-outline"></i></span>
                <input class="form-control" type="text" id="first_name" name="first_name" value="<?= old('first_name') ?>" placeholder="John" required autofocus>
            </div>
        </div>
        <div class="col-sm-6">
            <label for="last_name" class="form-label fw-semibold font-13">Last Name <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text"><i class="mdi mdi-account-outline"></i></span>
                <input class="form-control" type="text" id="last_name" name="last_name" value="<?= old('last_name') ?>" placeholder="Doe" required>
            </div>
        </div>
    </div>

    <div class="mb-3">
        <label for="username" class="form-label fw-semibold font-13">Username <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text"><i class="mdi mdi-at"></i></span>
            <input class="form-control" type="text" id="username" name="username" value="<?= old('username') ?>" placeholder="johndoe" required autocomplete="username">
        </div>
    </div>

    <div class="mb-3">
        <label for="email" class="form-label fw-semibold font-13">Work Email <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text"><i class="mdi mdi-email-outline"></i></span>
            <input class="form-control" type="email" id="email" name="email" value="<?= old('email') ?>" placeholder="john.doe@company.com" required autocomplete="email">
        </div>
    </div>

    <div class="mb-3">
        <label for="password" class="form-label fw-semibold font-13">Password <span class="text-danger">*</span></label>
        <div class="input-group input-group-merge">
            <span class="input-group-text"><i class="mdi mdi-lock-outline"></i></span>
            <input type="password" id="password" name="password" class="form-control" placeholder="Minimum 8 characters" required minlength="8" autocomplete="new-password">
            <div class="input-group-text" data-password="false" style="cursor: pointer;">
                <span class="password-eye"></span>
            </div>
        </div>
    </div>

    <div class="mb-3">
        <label for="password_confirm" class="form-label fw-semibold font-13">Confirm Password <span class="text-danger">*</span></label>
        <div class="input-group input-group-merge">
            <span class="input-group-text"><i class="mdi mdi-lock-check-outline"></i></span>
            <input type="password" id="password_confirm" name="password_confirm" class="form-control" placeholder="Re-enter your password" required minlength="8" autocomplete="new-password">
            <div class="input-group-text" data-password="false" style="cursor: pointer;">
                <span class="password-eye"></span>
            </div>
        </div>
    </div>

    <div class="mb-3">
        <div class="form-check">
            <input type="checkbox" class="form-check-input" id="terms" name="terms" value="1" <?= old('terms') ? 'checked' : '' ?> required>
            <label class="form-check-label font-13" for="terms">
                I agree to the <a href="<?= site_url('faqs') ?>" target="_blank" class="text-primary text-decoration-none">Terms of Service</a> &amp; <a href="<?= site_url('faqs') ?>" target="_blank" class="text-primary text-decoration-none">Privacy Policy</a>
            </label>
        </div>
    </div>

    <div class="d-grid mb-3">
        <button class="btn btn-primary btn-lg rounded-pill fw-semibold" type="submit">
            <i class="mdi mdi-account-plus-outline me-1"></i> Create Free Account
        </button>
    </div>
</form>

<div class="text-center mt-3 pt-3 border-top">
    <p class="text-muted font-14 mb-0">
        Already have an account? 
        <a href="<?= site_url('auth/login') ?>" class="text-primary fw-bold ms-1">Sign In</a>
    </p>
</div>

<?= $this->endSection() ?>
