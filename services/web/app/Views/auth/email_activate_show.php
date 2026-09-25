<?= $this->extend('layouts/hyper/auth_template') ?>

<?= $this->section('title') ?>Verify Your Email • <?= esc(setting('App.siteName')) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="text-center mb-4">
    <div class="avatar-md bg-primary-lighten text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 54px; height: 54px;">
        <i class="mdi mdi-email-check-outline font-30"></i>
    </div>
    <h3 class="fw-bold mb-1">Verify Your Email</h3>
    <p class="text-muted font-14 mb-2">We've sent an activation link to:</p>
    <div class="badge bg-light text-primary font-13 px-3 py-2 border rounded-pill text-break">
        <i class="mdi mdi-email-outline me-1"></i> <?= esc($user->email ?? session('email') ?? '') ?>
    </div>
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

<div class="alert alert-info d-flex align-items-start mb-4" role="alert">
    <i class="mdi mdi-information-outline font-20 me-2 text-info"></i>
    <div class="font-13">
        Open your inbox and click the verification button to activate your workspace access.
    </div>
</div>

<div class="mb-3">
    <form action="<?= site_url('auth/resend-verification') ?>" method="POST" id="resendForm">
        <?= csrf_field() ?>
        <input type="hidden" name="email" value="<?= esc($user->email ?? session('email') ?? '') ?>">
        <button type="submit" class="btn btn-primary btn-lg rounded-pill w-100 fw-semibold" id="resendBtn">
            <i class="mdi mdi-email-sync-outline me-1"></i> Resend Verification Email
        </button>
    </form>
</div>

<div class="text-center font-12 text-muted mb-3">
    <i class="mdi mdi-help-circle-outline me-1"></i> Check your Spam / Junk folder if you do not see it.
</div>

<div class="pt-3 border-top text-center">
    <p class="text-muted font-14 mb-0">
        Already verified? 
        <a href="<?= site_url('auth/login') ?>" class="text-primary fw-bold ms-1">Sign In</a>
    </p>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var canResend = true;
        var countdown = 60;
        var resendForm = document.getElementById('resendForm');
        var resendBtn = document.getElementById('resendBtn');

        if (resendForm && resendBtn) {
            resendForm.addEventListener('submit', function(e) {
                if (!canResend) {
                    e.preventDefault();
                    return;
                }
                canResend = false;
                resendBtn.disabled = true;
                var originalHtml = resendBtn.innerHTML;

                var timer = setInterval(function() {
                    countdown--;
                    resendBtn.innerHTML = '<i class="mdi mdi-timer-sand me-1"></i> Resend in ' + countdown + 's';
                    if (countdown <= 0) {
                        clearInterval(timer);
                        canResend = true;
                        countdown = 60;
                        resendBtn.disabled = false;
                        resendBtn.innerHTML = originalHtml;
                    }
                }, 1000);
            });
        }
    });
</script>
<?= $this->endSection() ?>
