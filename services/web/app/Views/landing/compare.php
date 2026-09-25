<?= $this->include('landing/header') ?>

<!-- START PAGE HEADER -->
<section class="py-5 bg-light border-bottom">
    <div class="container text-center py-4">
        <span class="badge bg-primary-lighten text-primary rounded-pill px-3 py-1 font-12 fw-semibold">Head-to-Head Comparison</span>
        <h1 class="fw-bold mt-2 mb-2 display-6"><?= esc(setting('App.siteName')) ?> vs. Commercial Cloud SaaS</h1>
        <p class="text-muted font-16 lead w-75 mx-auto mb-0">See why high-velocity engineering teams are switching from bloated cloud trackers to self-hosted simplicity.</p>
    </div>
</section>
<!-- END PAGE HEADER -->

<section class="py-5">
    <div class="container py-lg-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card feature-card border overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-hover table-centered mb-0 font-15">
                            <thead class="table-dark">
                                <tr>
                                    <th class="py-3 ps-4" style="width: 35%;">Key Capability</th>
                                    <th class="py-3 text-center text-success" style="width: 35%;">
                                        <i class="mdi mdi-leaf text-success me-1 font-18"></i> <?= esc(setting('App.siteName')) ?>
                                    </th>
                                    <th class="py-3 text-center text-muted" style="width: 30%;">
                                        Commercial Jira Cloud
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="ps-4 fw-semibold text-dark">Pricing Model</td>
                                    <td class="text-center">
                                        <span class="badge bg-success-lighten text-success font-13 px-3 py-1">100% Free Forever ($0)</span>
                                    </td>
                                    <td class="text-center text-danger font-14">
                                        $8.15 to $16+ / user / month
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-4 fw-semibold text-dark">Data Privacy & Sovereignty</td>
                                    <td class="text-center text-success fw-bold">
                                        <i class="mdi mdi-check-circle text-success me-1"></i> 100% On-Premise / Self-Hosted
                                    </td>
                                    <td class="text-center text-muted font-14">
                                        Hosted on third-party cloud
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-4 fw-semibold text-dark">User Limit</td>
                                    <td class="text-center text-success fw-bold">
                                        <i class="mdi mdi-infinity text-success me-1 font-18 align-middle"></i> Unlimited Seats
                                    </td>
                                    <td class="text-center text-muted font-14">
                                        Per-seat billing caps
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-4 fw-semibold text-dark">Page Load Speed & Bloat</td>
                                    <td class="text-center text-success fw-bold">
                                        <i class="mdi mdi-flash text-warning me-1"></i> Ultra Lightweight (Vanilla JS)
                                    </td>
                                    <td class="text-center text-muted font-14">
                                        Heavy, multi-megabyte bundle
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-4 fw-semibold text-dark">Vendor Lock-In</td>
                                    <td class="text-center text-success fw-bold">
                                        <i class="mdi mdi-check-circle text-success me-1"></i> Direct MySQL/Postgres Access
                                    </td>
                                    <td class="text-center text-danger font-14">
                                        Proprietary Export Formats
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-4 fw-semibold text-dark">Source Code Access</td>
                                    <td class="text-center text-success fw-bold">
                                        <span class="badge bg-primary-lighten text-primary font-13 px-3 py-1">Open Source (MIT)</span>
                                    </td>
                                    <td class="text-center text-muted font-14">
                                        Closed Proprietary
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="text-center mt-5">
                    <h3 class="fw-bold mb-3">Ready to reclaim ownership of your team's tracking?</h3>
                    <a href="<?= site_url('auth/register') ?>" class="btn btn-primary btn-lg rounded-pill px-4">
                        <i class="mdi mdi-rocket me-1"></i> Get Started Free
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?= $this->include('landing/footer') ?>
