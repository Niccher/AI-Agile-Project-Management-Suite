<?= $this->extend('layouts/hyper/auth_template') ?>

<?= $this->section('title') ?>Accept Invitation • <?= esc(setting('App.siteName')) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="text-center mb-4">
    <div class="avatar-md bg-success-lighten text-success rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 52px; height: 52px;">
        <i class="mdi mdi-account-plus font-24"></i>
    </div>
    <h3 class="fw-bold mb-1">Join the Team</h3>
    <p class="text-muted font-14 mb-0">You've been invited to join <strong><?= esc(setting('App.siteName')) ?></strong> as a <strong><?= esc(ucfirst($invite['role'])) ?></strong>.</p>
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

<form action="<?= site_url('auth/invite/' . esc($token)) ?>" method="POST" id="acceptInviteForm">
    <?= csrf_field() ?>

    <div class="mb-3">
        <label class="form-label fw-semibold font-13">Email Address</label>
        <input class="form-control bg-light" type="email" value="<?= esc($invite['email']) ?>" readonly>
        <div class="form-text font-12 text-muted">Your email address is pre-verified via this invitation.</div>
    </div>

    <div class="row g-2 mb-3">
        <div class="col-6">
            <label for="first_name" class="form-label fw-semibold font-13">First Name <span class="text-danger">*</span></label>
            <input class="form-control" type="text" id="first_name" name="first_name" value="<?= old('first_name') ?>" required placeholder="John">
        </div>
        <div class="col-6">
            <label for="last_name" class="form-label fw-semibold font-13">Last Name <span class="text-danger">*</span></label>
            <input class="form-control" type="text" id="last_name" name="last_name" value="<?= old('last_name') ?>" required placeholder="Doe">
        </div>
    </div>

    <div class="mb-3">
        <label for="username" class="form-label fw-semibold font-13">Choose Username <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text"><i class="mdi mdi-account-circle-outline"></i></span>
            <input class="form-control" type="text" id="username" name="username" value="<?= old('username') ?>" required placeholder="johndoe">
        </div>
    </div>

    <div class="mb-3">
        <label for="password" class="form-label fw-semibold font-13">Set Password <span class="text-danger">*</span></label>
        <div class="input-group input-group-merge">
            <span class="input-group-text"><i class="mdi mdi-lock-outline"></i></span>
            <input type="password" id="password" name="password" class="form-control" required placeholder="Minimum 8 characters" autocomplete="new-password">
        </div>
    </div>

    <div class="mb-4">
        <label for="password_confirm" class="form-label fw-semibold font-13">Confirm Password <span class="text-danger">*</span></label>
        <div class="input-group input-group-merge">
            <span class="input-group-text"><i class="mdi mdi-lock-check-outline"></i></span>
            <input type="password" id="password_confirm" name="password_confirm" class="form-control" required placeholder="Re-enter password" autocomplete="new-password">
        </div>
    </div>

    <div class="d-grid mb-3">
        <button class="btn btn-primary py-2 fw-semibold" type="submit">
            <i class="mdi mdi-check-circle me-1"></i> Complete Setup & Enter Workspace
        </button>
    </div>
</form>

<div class="text-center mt-3">
    <p class="text-muted font-13 mb-0">Already have an account? <a href="<?= site_url('auth/login') ?>" class="text-primary fw-semibold">Sign In</a></p>
</div>

<?= $this->endSection() ?>
