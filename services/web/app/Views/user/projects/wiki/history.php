<?= $this->extend('layouts/hyper/main') ?>

<?= $this->section('title') ?>History: <?= esc($page['title']) ?> • <?= esc($project['name']) ?><?= $this->endSection() ?>

<?= $this->section('css') ?>
<style>
    .version-timeline-card {
        border-left: 3px solid #727cf5;
    }
    .version-timeline-card.current {
        border-left-color: #0acf97;
    }
    .markdown-snapshot {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 14px 18px;
        max-height: 300px;
        overflow-y: auto;
    }
    body.dark-theme .markdown-snapshot,
    html.dark-theme .markdown-snapshot {
        background-color: #272e38;
        border-color: rgba(255, 255, 255, 0.08);
        color: #ced4da;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Page Title & Navigation Header -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <a href="<?= site_url('projects/wiki/' . (!empty($project['slug']) ? $project['slug'] : $project['id']) . '/page/' . $page['slug']) ?>" class="btn btn-outline-secondary rounded-pill">
                    <i class="mdi mdi-arrow-left me-1"></i> Back to Document
                </a>
            </div>
            <h4 class="page-title">
                <i class="mdi mdi-history text-primary me-1"></i> Revision History
                <span class="text-muted font-16 ms-2">/ <?= esc($page['title']) ?></span>
            </h4>
        </div>
    </div>
</div>

<!-- Flash Alerts -->
<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="mdi mdi-check-all me-1"></i> <?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="mdi mdi-block-helper me-1"></i> <?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-10 mx-auto">
        <div class="alert alert-light border font-13 text-muted mb-4 d-flex align-items-center">
            <i class="mdi mdi-shield-check text-success font-20 me-2"></i>
            <div>
                Every time this document is edited, an immutable snapshot is preserved. You can inspect past revisions below and safely <strong>revert</strong> to any version with 1-click.
            </div>
        </div>

        <?php if (empty($versions)): ?>
            <div class="card shadow-sm border-0 text-center py-5">
                <div class="text-muted font-14">No previous revisions recorded yet.</div>
            </div>
        <?php else: ?>
            <?php foreach ($versions as $idx => $v): ?>
                <?php $isCurrent = ($idx === 0); ?>
                <div class="card shadow-sm border-0 mb-3 version-timeline-card <?= $isCurrent ? 'current' : '' ?>">
                    <div class="card-body">
                        <div class="d-flex flex-wrap align-items-center justify-content-between mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge <?= $isCurrent ? 'bg-success' : 'bg-primary' ?> font-13 px-2 py-1">
                                    Version <?= $v['version_number'] ?> <?= $isCurrent ? '(Current)' : '' ?>
                                </span>
                                <span class="fw-bold font-14 text-dark"><?= esc($v['change_summary'] ?: 'Revision snapshot') ?></span>
                            </div>
                            <div class="mt-2 mt-md-0 d-flex align-items-center gap-2">
                                <span class="text-muted font-12">
                                    <i class="uil-user me-1"></i><?= esc($v['author_name'] ?? 'Team Member') ?> • 
                                    <i class="mdi mdi-clock-outline me-1"></i><?= date('M j, Y \a\t g:i A', strtotime($v['created_at'])) ?>
                                </span>
                                <?php if (!$isCurrent): ?>
                                    <form action="<?= site_url('projects/wiki/page/' . $page['id'] . '/rollback/' . $v['version_number']) ?>" method="POST" onsubmit="return confirm('Restore document to version <?= $v['version_number'] ?>?');">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-warning btn-xs rounded-pill">
                                            <i class="mdi mdi-restore me-1"></i> Restore
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Collapsible snapshot preview -->
                        <div class="mt-3">
                            <button class="btn btn-xs btn-light border" type="button" data-bs-toggle="collapse" data-bs-target="#snapshot-<?= $v['id'] ?>" aria-expanded="false">
                                <i class="mdi mdi-eye-outline me-1"></i> View Snapshot Content
                            </button>
                            <div class="collapse mt-2" id="snapshot-<?= $v['id'] ?>">
                                <div class="markdown-snapshot font-13">
                                    <pre class="m-0" style="white-space: pre-wrap; font-family: monospace;"><?= esc($v['content']) ?></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>
