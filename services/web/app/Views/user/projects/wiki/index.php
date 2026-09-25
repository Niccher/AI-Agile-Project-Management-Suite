<?= $this->extend('layouts/hyper/main') ?>

<?= $this->section('title') ?><?= esc($project['name']) ?> • Wiki & Documentation<?= $this->endSection() ?>

<?= $this->section('css') ?>
<style>
    .wiki-sidebar-tree {
        border-right: 1px solid rgba(152, 166, 173, 0.2);
    }
    .wiki-tree-link {
        color: #495057;
        padding: 6px 12px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        text-decoration: none;
        font-size: 13px;
        transition: all 0.15s ease;
        margin-bottom: 2px;
    }
    .wiki-tree-link:hover {
        background-color: rgba(114, 124, 245, 0.08);
        color: #727cf5;
    }
    .wiki-tree-link.active {
        background-color: rgba(114, 124, 245, 0.15);
        color: #727cf5;
        font-weight: 700;
    }
    body.dark-theme .wiki-tree-link,
    html.dark-theme .wiki-tree-link {
        color: #ced4da;
    }
    body.dark-theme .wiki-sidebar-tree,
    html.dark-theme .wiki-sidebar-tree {
        border-right-color: rgba(255, 255, 255, 0.08);
    }
    .markdown-body {
        font-size: 14.5px;
        line-height: 1.7;
        color: #313a46;
    }
    body.dark-theme .markdown-body,
    html.dark-theme .markdown-body {
        color: #ced4da;
    }
    .markdown-body h1, .markdown-body h2, .markdown-body h3 {
        font-weight: 700;
        margin-top: 1.5rem;
        margin-bottom: 0.75rem;
    }
    .markdown-body pre {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 14px 16px;
        font-size: 13px;
        overflow-x: auto;
    }
    body.dark-theme .markdown-body pre,
    html.dark-theme .markdown-body pre {
        background-color: #272e38;
        border-color: rgba(255, 255, 255, 0.08);
        color: #f1f5f9;
    }
    .markdown-body code {
        font-family: monospace;
        font-size: 87.5%;
        color: #e83e8c;
    }
    .markdown-body pre code {
        color: inherit;
        font-size: inherit;
    }
    .markdown-body blockquote {
        border-left: 4px solid #727cf5;
        padding-left: 14px;
        color: #64748b;
        margin: 1rem 0;
    }
    .markdown-body table {
        width: 100%;
        border-collapse: collapse;
        margin: 1rem 0;
    }
    .markdown-body th, .markdown-body td {
        border: 1px solid rgba(152, 166, 173, 0.25);
        padding: 8px 12px;
    }
    .markdown-body th {
        background-color: rgba(152, 166, 173, 0.08);
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
$wikiProjectSlug = !empty($project['slug']) ? $project['slug'] : $project['id'];
?>

<!-- Page Title & Navigation Header -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <a href="<?= site_url('projects/wiki/' . $wikiProjectSlug . '/create') ?>" class="btn btn-primary rounded-pill">
                    <i class="mdi mdi-plus me-1"></i> New Document
                </a>
            </div>
            <h4 class="page-title">
                <i class="uil-briefcase text-primary me-1"></i> <?= esc($project['name']) ?>
                <span class="text-muted font-16 ms-2">/ Wiki & Documentation</span>
            </h4>
        </div>
    </div>
</div>

<!-- Project Sub-Nav Tabs -->
<div class="row mb-3">
    <div class="col-12">
        <ul class="nav nav-tabs nav-bordered">
            <li class="nav-item">
                <a href="<?= site_url('projects/view/' . $wikiProjectSlug) ?>" class="nav-link">
                    <i class="uil-eye me-1"></i> Overview
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= site_url('projects/kanban/' . $wikiProjectSlug) ?>" class="nav-link">
                    <i class="uil-clipboard-alt me-1"></i> Kanban Board
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= site_url('projects/sprints/' . $wikiProjectSlug) ?>" class="nav-link">
                    <i class="uil-layer-group me-1"></i> Sprints & Backlog
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= site_url('projects/wiki/' . $wikiProjectSlug) ?>" class="nav-link active">
                    <i class="uil-book-open me-1"></i> Wiki & Docs
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= site_url('projects/time/' . $wikiProjectSlug) ?>" class="nav-link">
                    <i class="uil-clock me-1"></i> Time Tracker
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= site_url('projects/analytics/' . $wikiProjectSlug) ?>" class="nav-link">
                    <i class="uil-chart-line me-1"></i> Analytics
                </a>
            </li>
        </ul>
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

<!-- Main Knowledge Base Layout -->
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="row g-0">
            <!-- Left Sidebar Navigation Tree -->
            <div class="col-md-4 col-lg-3 p-3 wiki-sidebar-tree bg-light bg-opacity-25">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="m-0 fw-bold text-dark font-14"><i class="uil-folder font-16 text-primary me-1"></i> Project Docs</h5>
                    <a href="<?= site_url('projects/wiki/' . $wikiProjectSlug . '/create') ?>" class="btn btn-xs btn-outline-primary rounded-pill">
                        <i class="mdi mdi-plus"></i> New
                    </a>
                </div>

                <!-- Live Document Filter -->
                <div class="mb-3">
                    <input type="text" class="form-control form-control-sm" id="wiki-doc-search" placeholder="Filter documents...">
                </div>

                <!-- Document Tree -->
                <div id="wiki-tree-list">
                    <?php if (empty($tree)): ?>
                        <div class="text-muted font-12 py-3 text-center">No documents found.</div>
                    <?php else: ?>
                        <?php foreach ($tree as $page): ?>
                            <?php $isActive = $activePage && ($activePage['id'] == $page['id']); ?>
                            <a href="<?= site_url('projects/wiki/' . $wikiProjectSlug . '/page/' . $page['slug']) ?>" class="wiki-tree-link <?= $isActive ? 'active' : '' ?>" data-title="<?= esc(strtolower($page['title'])) ?>">
                                <i class="uil-file-alt me-2 font-16 text-muted"></i>
                                <span class="text-truncate flex-grow-1"><?= esc($page['title']) ?></span>
                                <span class="badge bg-secondary-lighten text-muted font-10">v<?= $page['version'] ?></span>
                            </a>

                            <?php if (!empty($page['children'])): ?>
                                <div class="ps-3 border-start ms-2 mb-1">
                                    <?php foreach ($page['children'] as $child): ?>
                                        <?php $isChildActive = $activePage && ($activePage['id'] == $child['id']); ?>
                                        <a href="<?= site_url('projects/wiki/' . $wikiProjectSlug . '/page/' . $child['slug']) ?>" class="wiki-tree-link <?= $isChildActive ? 'active' : '' ?>" data-title="<?= esc(strtolower($child['title'])) ?>">
                                            <i class="uil-corner-down-right me-1 font-12 text-muted"></i>
                                            <span class="text-truncate flex-grow-1"><?= esc($child['title']) ?></span>
                                            <span class="badge bg-secondary-lighten text-muted font-10">v<?= $child['version'] ?></span>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Reading Area -->
            <div class="col-md-8 col-lg-9 p-4">
                <?php if ($activePage): ?>
                    <!-- Document Header Bar -->
                    <div class="d-flex flex-wrap align-items-center justify-content-between pb-3 border-bottom mb-4">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <h2 class="fw-bold text-dark m-0"><?= esc($activePage['title']) ?></h2>
                                <span class="badge bg-primary-lighten text-primary font-12 px-2">v<?= $activePage['version'] ?></span>
                            </div>
                            <div class="font-12 text-muted">
                                <span><i class="uil-user me-1"></i>Last updated by <strong><?= esc($updater) ?></strong></span>
                                <span class="mx-2">•</span>
                                <span><i class="mdi mdi-clock-outline me-1"></i><?= date('F j, Y \a\t g:i A', strtotime($activePage['updated_at'] ?? $activePage['created_at'])) ?></span>
                            </div>
                        </div>
                        <div class="mt-2 mt-md-0 d-flex gap-2">
                            <a href="<?= site_url('projects/wiki/page/' . $activePage['id'] . '/history') ?>" class="btn btn-outline-secondary btn-sm rounded-pill" title="View Version Revisions">
                                <i class="mdi mdi-history me-1"></i> History
                            </a>
                            <a href="<?= site_url('projects/wiki/page/' . $activePage['id'] . '/edit') ?>" class="btn btn-primary btn-sm rounded-pill px-3">
                                <i class="mdi mdi-pencil me-1"></i> Edit Page
                            </a>
                            <form action="<?= site_url('projects/wiki/page/' . $activePage['id'] . '/delete') ?>" method="POST" onsubmit="return confirm('Are you sure you want to delete this document page?');" class="d-inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill" title="Delete Page">
                                    <i class="mdi mdi-trash-can-outline"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Rendered Markdown Body -->
                    <div class="markdown-body" id="rendered-doc-content">
                        <!-- Content will be rendered dynamically via Marked.js or fallback -->
                        <textarea id="raw-markdown-content" style="display: none;"><?= esc($activePage['content'] ?? '') ?></textarea>
                    </div>

                <?php else: ?>
                    <div class="text-center py-5 text-muted">
                        <i class="uil-book-open font-36 d-block mb-2 text-primary"></i>
                        <h4 class="fw-bold text-dark">No Document Selected</h4>
                        <p class="font-14">Select a document from the left sidebar or create a new page for your project wiki.</p>
                        <a href="<?= site_url('projects/wiki/' . $wikiProjectSlug . '/create') ?>" class="btn btn-primary rounded-pill px-4">
                            <i class="mdi mdi-plus me-1"></i> Create First Page
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('js') ?>
<!-- Marked.js Markdown Parser -->
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Render Markdown content
        const rawEl = document.getElementById('raw-markdown-content');
        const targetEl = document.getElementById('rendered-doc-content');
        if (rawEl && targetEl && window.marked) {
            targetEl.innerHTML = marked.parse(rawEl.value);
        }

        // Live Document Search Filter
        const searchInput = document.getElementById('wiki-doc-search');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const term = searchInput.value.toLowerCase().trim();
                const links = document.querySelectorAll('.wiki-tree-link');
                links.forEach(link => {
                    const title = link.dataset.title || '';
                    if (title.includes(term) || term === '') {
                        link.style.display = 'flex';
                    } else {
                        link.style.display = 'none';
                    }
                });
            });
        }
    });
</script>
<?= $this->endSection() ?>
