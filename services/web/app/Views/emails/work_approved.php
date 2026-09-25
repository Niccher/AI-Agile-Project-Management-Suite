<?= $this->extend('emails/base_email') ?>

<?= $this->section('email_content') ?>
    <div style="margin-bottom: 20px;">
        <span class="badge badge-success">Task Approved</span>
    </div>

    <h2 style="color: #0f172a; margin-top: 0; font-size: 20px; font-weight: 700;">
        Great work! Your task was approved.
    </h2>

    <p>Your submitted task <strong>"<?= esc($taskTitle ?? 'Untitled Task') ?>"</strong> in project <em><?= esc($projectName ?? 'Project') ?></em> has been reviewed and approved by management.</p>

    <div class="info-box">
        <p style="margin: 0 0 8px 0;"><strong>Status:</strong> <span style="color: #059669; font-weight: 600;">Approved / Ready for Release</span></p>
        <?php if (!empty($approvedBy)): ?>
            <p style="margin: 0 0 8px 0;"><strong>Reviewed By:</strong> <?= esc($approvedBy) ?></p>
        <?php endif; ?>
        <p style="margin: 0;"><strong>Approved At:</strong> <?= date('F j, Y g:i A') ?></p>
    </div>

    <div style="text-align: center; margin: 30px 0;">
        <a href="<?= !empty($actionUrl) ? site_url($actionUrl) : site_url('kanban') ?>" class="btn-primary">
            View Task on Board &rarr;
        </a>
    </div>

    <p style="color: #64748b; font-size: 13px; margin-bottom: 0;">
        Thank you for keeping team velocity high!
    </p>
<?= $this->endSection() ?>