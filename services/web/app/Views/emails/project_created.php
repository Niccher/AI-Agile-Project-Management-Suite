<?= $this->extend('emails/base_email') ?>

<?= $this->section('email_content') ?>
    <div style="margin-bottom: 20px;">
        <span class="badge badge-success">New Project</span>
    </div>

    <h2 style="color: #0f172a; margin-top: 0; font-size: 20px; font-weight: 700;">
        New Project Created: <?= esc($projectName ?? 'Untitled Project') ?>
    </h2>

    <p>A new project workspace has been launched on <strong><?= esc(setting('App.siteName')) ?></strong>.</p>

    <div class="info-box">
        <p style="margin: 0 0 8px 0;"><strong>Project Name:</strong> <?= esc($projectName ?? 'Untitled Project') ?></p>
        <?php if (!empty($description)): ?>
            <p style="margin: 0 0 8px 0;"><strong>Summary:</strong> <?= esc($description) ?></p>
        <?php endif; ?>
        <?php if (!empty($ownerName)): ?>
            <p style="margin: 0 0 8px 0;"><strong>Project Lead:</strong> <?= esc($ownerName) ?></p>
        <?php endif; ?>
        <p style="margin: 0;"><strong>Created Date:</strong> <?= date('F j, Y') ?></p>
    </div>

    <div style="text-align: center; margin: 30px 0;">
        <a href="<?= !empty($actionUrl) ? site_url($actionUrl) : site_url('projects') ?>" class="btn-primary">
            Explore Project Workspace &rarr;
        </a>
    </div>
<?= $this->endSection() ?>