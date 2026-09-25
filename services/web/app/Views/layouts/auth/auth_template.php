<?= $this->extend('layouts/ace/auth') ?>

<?= $this->section('main_content') ?>
    <div class="login-box">
        <div class="login-box-body">
            <?= $this->renderSection('content') ?>
        </div>
    </div>
<?= $this->endSection() ?>
