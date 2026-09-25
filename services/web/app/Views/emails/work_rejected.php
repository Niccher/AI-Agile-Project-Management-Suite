<?= $this->extend('emails/base_email') ?>

<?= $this->section('email_content') ?>
    <div style="margin-bottom: 20px;">
        <span class="badge badge-danger">Revision Requested</span>
    </div>

    <h2 style="color: #0f172a; margin-top: 0; font-size: 20px; font-weight: 700;">
        Action Needed: Task Revision Requested
    </h2>

    <p>Your task <strong>"<?= esc($taskTitle ?? 'Untitled Task') ?>"</strong> in project <em><?= esc($projectName ?? 'Project') ?></em> requires revisions before it can be approved.</p>

    <div class="info-box" style="border-left-color: #ef4444;">
        <p style="margin: 0 0 8px 0; font-weight: 600; color: #991b1b;">Feedback / Rejection Reason:</p>
        <p style="margin: 0; color: #475569; font-style: italic;">
            "<?= esc($reason ?? 'Please review the task specifications and make the required corrections.') ?>"
        </p>
    </div>

    <div style="text-align: center; margin: 30px 0;">
        <a href="<?= !empty($actionUrl) ? site_url($actionUrl) : site_url('kanban') ?>" class="btn-primary" style="background-color: #ef4444;">
            Open Task to Resolve &rarr;
        </a>
    </div>

    <p style="color: #64748b; font-size: 13px; margin-bottom: 0;">
        Once you've made the requested changes, move the task back to <em>In Review</em> for manager verification.
    </p>
<?= $this->endSection() ?>