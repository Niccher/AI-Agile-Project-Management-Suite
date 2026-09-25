<?= $this->extend('emails/base_email') ?>

<?= $this->section('email_content') ?>
    <div style="margin-bottom: 20px;">
        <span class="badge badge-info">Kanban Update</span>
    </div>

    <h2 style="color: #0f172a; margin-top: 0; font-size: 20px; font-weight: 700;">
        Task Status Updated
    </h2>

    <p>The task <strong>"<?= esc($taskTitle ?? 'Untitled Task') ?>"</strong> has been moved to a new stage on the sprint board.</p>

    <div class="info-box" style="border-left-color: #0284c7;">
        <p style="margin: 0 0 8px 0;"><strong>Project:</strong> <?= esc($projectName ?? 'Project') ?></p>
        <p style="margin: 0 0 8px 0;"><strong>New Stage:</strong> <span class="badge badge-info"><?= esc(strtoupper(str_replace('_', ' ', $newStatus ?? 'In Progress'))) ?></span></p>
        <?php if (!empty($movedBy)): ?>
            <p style="margin: 0 0 8px 0;"><strong>Moved By:</strong> <?= esc($movedBy) ?></p>
        <?php endif; ?>
        <p style="margin: 0;"><strong>Timestamp:</strong> <?= date('F j, Y g:i A') ?></p>
    </div>

    <div style="text-align: center; margin: 30px 0;">
        <a href="<?= !empty($actionUrl) ? site_url($actionUrl) : site_url('kanban') ?>" class="btn-primary">
            View Live Kanban Board &rarr;
        </a>
    </div>
<?= $this->endSection() ?>