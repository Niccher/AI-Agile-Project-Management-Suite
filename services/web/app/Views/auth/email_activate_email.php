<?= $this->extend('emails/base_email') ?>

<?= $this->section('email_content') ?>
    <div style="margin-bottom: 20px;">
        <span class="badge badge-info">Account Activation</span>
    </div>

    <h2 style="color: #0f172a; margin-top: 0; font-size: 20px; font-weight: 700;">
        Verify Your Email Address
    </h2>

    <p>Hi <strong><?= esc($user->first_name ?? $user->username ?? 'there') ?></strong>,</p>

    <p>Thank you for registering on <strong><?= esc(setting('App.siteName')) ?></strong>. To finish setting up your workspace account, please confirm your email address below.</p>

    <div style="text-align: center; margin: 30px 0;">
        <a href="<?= site_url('auth/verify-email?token=' . $code . '&email=' . urlencode($user->email)) ?>" class="btn-primary">
            Activate Account &rarr;
        </a>
    </div>

    <div class="info-box">
        <p style="margin: 0 0 6px 0; font-size: 13px; color: #64748b;">Or enter this verification code directly:</p>
        <p style="margin: 0; font-family: monospace; font-size: 20px; font-weight: 700; letter-spacing: 3px; color: #727cf5;">
            <?= esc($code) ?>
        </p>
    </div>

    <p style="color: #64748b; font-size: 13px;">
        This activation link is valid for 24 hours. If you did not register for an account, no further action is required.
    </p>
<?= $this->endSection() ?>
