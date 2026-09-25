<?= $this->extend('emails/base_email') ?>

<?= $this->section('email_content') ?>
    <h2 style="color: #0f172a; margin-top: 0; font-size: 20px; font-weight: 700;">
        Welcome aboard, <?= esc($user->first_name ?? $user->username ?? 'Team Member') ?>! 👋
    </h2>

    <p>Your account has been provisioned on <strong><?= esc(setting('App.siteName')) ?></strong> — your new agile project management and velocity hub.</p>

    <p>Here is how you can jump right in:</p>

    <div class="info-box">
        <ul style="margin: 0; padding-left: 20px; color: #475569;">
            <li style="margin-bottom: 8px;"><strong>Sprint Kanban:</strong> Drag-and-drop tasks across custom workflow lanes.</li>
            <li style="margin-bottom: 8px;"><strong>Time Tracker:</strong> Log effort with live stopwatch timers and auto-sync.</li>
            <li style="margin-bottom: 0;"><strong>Work Approvals:</strong> Coordinate deliverable reviews directly with team managers.</li>
        </ul>
    </div>

    <div style="text-align: center; margin: 30px 0;">
        <a href="<?= site_url('dashboard') ?>" class="btn-primary">
            Access Your Workspace &rarr;
        </a>
    </div>

    <p style="color: #64748b; font-size: 13px; margin-bottom: 0;">
        If you have any questions or need onboarding assistance, reply to this email or speak with your team administrator.
    </p>
<?= $this->endSection() ?>
