<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>500 Internal Server Error | <?= esc(setting('App.siteName')) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/jpeg" href="<?= base_url('assets/img/app_logo.jpg') ?>">
    <link href="<?= base_url('assets/hyper/css/icons.min.css') ?>" rel="stylesheet" type="text/css" />
    <link href="<?= base_url('assets/hyper/css/app.min.css') ?>" rel="stylesheet" type="text/css" id="light-style" />
</head>

<body class="loading authentication-bg" data-layout-config='{"darkMode":false}'>
    <div class="account-pages pt-2 pt-sm-5 pb-4 pb-sm-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xxl-5 col-lg-6">
                    <div class="card shadow-sm border-0">
                        <div class="card-header pt-4 pb-4 text-center bg-primary">
                            <a href="<?= site_url() ?>">
                                <span class="text-white font-22 fw-bold">
                                    <i class="mdi mdi-leaf"></i> <?= esc(setting('App.siteName')) ?>
                                </span>
                            </a>
                        </div>

                        <div class="card-body p-4 text-center">
                            <img src="<?= base_url('assets/hyper/images/maintenance.svg') ?>" height="110" alt="Internal Server Error" class="my-3">
                            <h1 class="text-danger fw-bold mt-2 display-6">500</h1>
                            <h4 class="text-dark mt-2 fw-semibold">Internal Server Error</h4>
                            <p class="text-muted font-14 mt-2 mb-4">
                                Whoops! Something went wrong on the server while processing your request. Please try again shortly.
                            </p>

                            <div class="d-flex justify-content-center gap-2">
                                <a class="btn btn-info rounded-pill px-4" href="javascript:history.back()">
                                    <i class="mdi mdi-arrow-left me-1"></i> Go Back
                                </a>
                                <a class="btn btn-primary rounded-pill px-4" href="<?= site_url('dashboard') ?>">
                                    <i class="mdi mdi-home me-1"></i> Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer footer-alt text-muted text-center">
        <?= date('Y') ?> © <?= esc(setting('App.siteName')) ?>
    </footer>

    <script src="<?= base_url('assets/hyper/js/vendor.min.js') ?>"></script>
    <script src="<?= base_url('assets/hyper/js/app.min.js') ?>"></script>
</body>
</html>
