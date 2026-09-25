<?= $this->include('landing/header', [
    'pageTitle' => trim($this->renderSection('title')) ?: (setting('App.siteName') . ' — Authentication')
]) ?>

<style>
    .auth-page-container {
        min-height: calc(100vh - 160px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 3rem 1.25rem;
    }
    .auth-main-card {
        width: 100%;
        max-width: 480px;
        background: var(--bs-card-bg, #ffffff);
        border-radius: 16px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.06);
        border: 1px solid var(--bs-border-color, rgba(226, 232, 240, 0.8));
        padding: 2.5rem 2.25rem;
        transition: background-color 0.3s ease, border-color 0.3s ease;
    }
    .auth-main-card.auth-wide {
        max-width: 560px;
    }
    @media (max-width: 576px) {
        .auth-page-container {
            padding: 1.5rem 1rem;
        }
        .auth-main-card {
            padding: 1.75rem 1.25rem;
            border-radius: 12px;
        }
    }
</style>

<div class="auth-page-container">
    <div class="auth-main-card <?= (uri_string() === 'auth/register' || uri_string() === 'register') ? 'auth-wide' : '' ?>">
        <?= $this->renderSection('content') ?>
    </div>
</div>

<?= $this->include('landing/footer') ?>
