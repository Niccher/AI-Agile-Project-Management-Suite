<?= $this->extend('layouts/hyper/auth_template') ?>

<?= $this->section('title') ?>Sign In • <?= esc(setting('App.siteName')) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="text-center mb-4">
    <div class="avatar-md bg-primary-lighten text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 52px; height: 52px;">
        <i class="mdi mdi-login-variant font-24"></i>
    </div>
    <h3 class="fw-bold mb-1">Welcome Back</h3>
    <p class="text-muted font-14 mb-0">Enter your credentials to access your workspace</p>
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

<form action="<?= site_url('auth/login') ?>" method="POST" id="loginForm">
    <?= csrf_field() ?>

    <div class="mb-3">
        <label for="email" class="form-label fw-semibold font-13">Email Address <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text"><i class="mdi mdi-email-outline"></i></span>
            <input class="form-control" type="email" id="email" name="email" value="<?= old('email') ?>" required placeholder="name@company.com" autofocus autocomplete="email">
        </div>
    </div>

    <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <label for="password" class="form-label fw-semibold font-13 mb-0">Password <span class="text-danger">*</span></label>
            <a href="<?= site_url('auth/forgot-password') ?>" class="text-primary font-12 text-decoration-none">Forgot password?</a>
        </div>
        <div class="input-group input-group-merge">
            <span class="input-group-text"><i class="mdi mdi-lock-outline"></i></span>
            <input type="password" id="password" name="password" class="form-control" required placeholder="Enter your password" autocomplete="current-password">
            <div class="input-group-text" data-password="false" style="cursor: pointer;">
                <span class="password-eye"></span>
            </div>
        </div>
    </div>

    <div class="mb-3">
        <div class="form-check">
            <input type="checkbox" class="form-check-input" id="remember" name="remember" <?= old('remember', 'on') === 'on' ? 'checked' : '' ?>>
            <label class="form-check-label font-13" for="remember">Keep me signed in</label>
        </div>
    </div>

    <div class="d-grid mb-3">
        <button class="btn btn-primary btn-lg rounded-pill fw-semibold" type="submit">
            <i class="mdi mdi-login me-1"></i> Sign In to Workspace
        </button>
    </div>
</form>

<div class="text-center mt-3 pt-3 border-top">
    <p class="text-muted font-14 mb-0">
        Don't have an account? 
        <a href="<?= site_url('auth/register') ?>" class="text-primary fw-bold ms-1">Create Account</a>
    </p>
</div>

<?= $this->endSection() ?>
