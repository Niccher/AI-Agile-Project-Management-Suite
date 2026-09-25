<?= $this->extend('layouts/hyper/main') ?>

<?php $isEdit = !empty($page); ?>
<?= $this->section('title') ?><?= $isEdit ? 'Edit: ' . esc($page['title']) : 'Create New Document' ?> • <?= esc($project['name']) ?><?= $this->endSection() ?>

<?= $this->section('css') ?>
<style>
    .editor-toolbar button {
        padding: 4px 10px;
        font-size: 13px;
    }
    #wiki-editor-textarea {
        font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, Courier, monospace;
        font-size: 13.5px;
        line-height: 1.6;
        min-height: 480px;
        resize: vertical;
    }
    #wiki-live-preview {
        min-height: 480px;
        border: 1px solid rgba(152, 166, 173, 0.25);
        border-radius: 6px;
        padding: 16px 20px;
        overflow-y: auto;
        background-color: #fafbfe;
    }
    body.dark-theme #wiki-live-preview,
    html.dark-theme #wiki-live-preview {
        background-color: #272e38;
        border-color: rgba(255, 255, 255, 0.08);
        color: #ced4da;
    }
    .markdown-body h1, .markdown-body h2, .markdown-body h3 {
        font-weight: 700;
        margin-top: 1.25rem;
        margin-bottom: 0.5rem;
    }
    .markdown-body pre {
        background-color: #f1f5f9;
        padding: 12px 14px;
        border-radius: 6px;
        font-size: 12.5px;
    }
    body.dark-theme .markdown-body pre,
    html.dark-theme .markdown-body pre {
        background-color: #1e242d;
        color: #f8fafc;
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
                <a href="<?= site_url('projects/wiki/' . $wikiProjectSlug) ?>" class="btn btn-outline-secondary rounded-pill">
                    <i class="mdi mdi-arrow-left me-1"></i> Back to Docs
                </a>
            </div>
            <h4 class="page-title">
                <i class="uil-book-open text-primary me-1"></i> <?= esc($project['name']) ?>
                <span class="text-muted font-16 ms-2">/ <?= $isEdit ? 'Edit Document' : 'Create New Document' ?></span>
            </h4>
        </div>
    </div>
</div>

<form action="<?= $isEdit ? site_url('projects/wiki/page/' . $page['id'] . '/update') : site_url('projects/wiki/' . $wikiProjectSlug . '/store') ?>" method="POST">
    <?= csrf_field() ?>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-8">
                    <label class="form-label fw-bold">Document Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-lg fw-bold" name="title" id="wiki-title-input" value="<?= esc($page['title'] ?? '') ?>" placeholder="e.g. Authentication API Contract" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Parent Category / Nesting</label>
                    <select class="form-select form-control-lg" name="parent_id">
                        <option value="">-- None (Top Level Document) --</option>
                        <?php if (!empty($allPages)): ?>
                            <?php foreach ($allPages as $p): ?>
                                <option value="<?= $p['id'] ?>" <?= (!empty($page['parent_id']) && $page['parent_id'] == $p['id']) ? 'selected' : '' ?>>
                                    <?= esc($p['title']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </div>

            <!-- Formatting Toolbar -->
            <div class="editor-toolbar d-flex flex-wrap gap-1 p-2 bg-light border rounded mb-2 align-items-center">
                <button type="button" class="btn btn-sm btn-light border" onclick="insertMarkdown('# ', '')" title="Heading 1"><i class="mdi mdi-format-header-1"></i> H1</button>
                <button type="button" class="btn btn-sm btn-light border" onclick="insertMarkdown('## ', '')" title="Heading 2"><i class="mdi mdi-format-header-2"></i> H2</button>
                <button type="button" class="btn btn-sm btn-light border" onclick="insertMarkdown('### ', '')" title="Heading 3"><i class="mdi mdi-format-header-3"></i> H3</button>
                <div class="vr mx-1"></div>
                <button type="button" class="btn btn-sm btn-light border" onclick="insertMarkdown('**', '**')" title="Bold"><i class="mdi mdi-format-bold"></i></button>
                <button type="button" class="btn btn-sm btn-light border" onclick="insertMarkdown('*', '*')" title="Italic"><i class="mdi mdi-format-italic"></i></button>
                <button type="button" class="btn btn-sm btn-light border" onclick="insertMarkdown('`', '`')" title="Inline Code"><i class="mdi mdi-code-tags"></i></button>
                <button type="button" class="btn btn-sm btn-light border" onclick="insertMarkdown('```bash\n', '\n```')" title="Code Block"><i class="mdi mdi-code-braces"></i> Block</button>
                <div class="vr mx-1"></div>
                <button type="button" class="btn btn-sm btn-light border" onclick="insertMarkdown('- ', '')" title="Unordered List"><i class="mdi mdi-format-list-bulleted"></i></button>
                <button type="button" class="btn btn-sm btn-light border" onclick="insertMarkdown('1. ', '')" title="Numbered List"><i class="mdi mdi-format-list-numbered"></i></button>
                <button type="button" class="btn btn-sm btn-light border" onclick="insertMarkdown('- [ ] ', '')" title="Checklist"><i class="mdi mdi-checkbox-marked-outline"></i> Task</button>
                <button type="button" class="btn btn-sm btn-light border" onclick="insertMarkdown('> [!TIP]\n> ', '')" title="Alert Callout"><i class="mdi mdi-alert-circle-outline"></i> Tip</button>
                <button type="button" class="btn btn-sm btn-light border" onclick="insertMarkdown('| Header 1 | Header 2 |\n| --- | --- |\n| Cell 1 | Cell 2 |', '')" title="Table"><i class="mdi mdi-table"></i> Table</button>
            </div>

            <!-- Split Screen Editor & Live Preview -->
            <div class="row g-3">
                <div class="col-lg-6">
                    <label class="form-label font-12 fw-bold text-muted text-uppercase">Markdown Source</label>
                    <textarea class="form-control" name="content" id="wiki-editor-textarea" placeholder="Write your document content in Markdown format..."><?= esc($page['content'] ?? '') ?></textarea>
                </div>
                <div class="col-lg-6">
                    <label class="form-label font-12 fw-bold text-muted text-uppercase">Live Formatted Preview</label>
                    <div class="markdown-body" id="wiki-live-preview">
                        <!-- Preview will render in real time -->
                    </div>
                </div>
            </div>

            <!-- Change Summary & Save Bar -->
            <div class="row mt-4 pt-3 border-top align-items-center">
                <div class="col-md-7 mb-2 mb-md-0">
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted font-13"><i class="mdi mdi-tag-outline me-1"></i> Revision Note</span>
                        <input type="text" class="form-control" name="change_summary" placeholder="e.g. Added JWT authentication flow and schema diagrams">
                    </div>
                </div>
                <div class="col-md-5 text-md-end">
                    <a href="<?= site_url('projects/wiki/' . $project['id']) ?>" class="btn btn-secondary rounded-pill px-3 me-2">Cancel</a>
                    <button type="submit" class="btn btn-success rounded-pill px-4">
                        <i class="mdi mdi-content-save me-1"></i> Save Document
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<?= $this->endSection() ?>

<?= $this->section('js') ?>
<!-- Marked.js Markdown Parser -->
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>

<script>
    const textarea = document.getElementById('wiki-editor-textarea');
    const preview = document.getElementById('wiki-live-preview');

    function updatePreview() {
        if (textarea && preview && window.marked) {
            const raw = textarea.value;
            preview.innerHTML = raw.trim() ? marked.parse(raw) : '<div class="text-muted font-13 fst-italic">Preview will appear here as you type...</div>';
        }
    }

    function insertMarkdown(prefix, suffix) {
        if (!textarea) return;
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const text = textarea.value;
        const selected = text.substring(start, end);
        
        textarea.value = text.substring(0, start) + prefix + selected + suffix + text.substring(end);
        textarea.focus();
        textarea.setSelectionRange(start + prefix.length, end + prefix.length);
        updatePreview();
    }

    if (textarea) {
        textarea.addEventListener('input', updatePreview);
        updatePreview();
    }
</script>
<?= $this->endSection() ?>
