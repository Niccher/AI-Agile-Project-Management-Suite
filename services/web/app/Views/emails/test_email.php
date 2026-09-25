<?= $this->extend('emails/base_email') ?>

<?= $this->section('email_content') ?>
    <div style="margin-bottom: 20px;">
        <span class="badge badge-success">Diagnostic Test</span>
    </div>

    <h2 style="color: #0f172a; margin-top: 0; font-size: 20px; font-weight: 700;">
        SMTP Gateway Connectivity Verified! 🚀
    </h2>

    <p>This is a test notification dispatched from <strong><?= esc(setting('App.siteName')) ?></strong> to verify outbound mail delivery and SMTP credentials.</p>

    <div class="info-box">
        <p style="margin: 0 0 6px 0;"><strong>Recipient:</strong> <code><?= esc($recipient) ?></code></p>
        <p style="margin: 0 0 6px 0;"><strong>SMTP Server:</strong> <code><?= esc($smtpHost) ?>:<?= esc($smtpPort) ?> (<?= esc(strtoupper($smtpCrypto)) ?>)</code></p>
        <p style="margin: 0 0 6px 0;"><strong>Dispatched At:</strong> <?= esc($dispatchedAt) ?></p>
        <p style="margin: 0;"><strong>Transport Engine:</strong> CodeIgniter 4 Mailer Service</p>
    </div>

    <p style="color: #64748b; font-size: 13px; margin-bottom: 0;">
        Your outbound notification pipeline is configured correctly and ready for automated task alerts and password resets.
    </p>
<?= $this->endSection() ?>