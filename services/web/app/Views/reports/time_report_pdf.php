<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Timesheet & Billing Invoice</title>
    <style>
        @page {
            margin: 25px 30px;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #2b2d42;
            font-size: 11px;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border-bottom: 2px solid #727cf5;
            padding-bottom: 12px;
        }
        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #727cf5;
            letter-spacing: 0.5px;
        }
        .report-title {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            text-align: right;
        }
        .meta-text {
            color: #6c757d;
            font-size: 10px;
        }
        .summary-box-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .summary-card {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 4px;
            padding: 10px 14px;
            text-align: center;
        }
        .summary-card-title {
            font-size: 9px;
            text-transform: uppercase;
            color: #6c757d;
            font-weight: bold;
            margin-bottom: 4px;
        }
        .summary-card-value {
            font-size: 16px;
            font-weight: bold;
            color: #2b2d42;
        }
        .summary-card-value.highlight {
            color: #0acf97;
        }
        .summary-card-value.primary {
            color: #727cf5;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .data-table th {
            background-color: #727cf5;
            color: #ffffff;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 7px 8px;
            text-align: left;
        }
        .data-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #eef2f7;
            font-size: 10px;
        }
        .data-table tr:nth-child(even) {
            background-color: #fbfbfd;
        }
        .data-table tr.total-row td {
            background-color: #f1f3fa;
            font-weight: bold;
            border-top: 2px solid #dee2e6;
            border-bottom: 2px solid #dee2e6;
        }
        .badge {
            display: inline-block;
            padding: 2px 5px;
            font-size: 9px;
            font-weight: bold;
            border-radius: 3px;
        }
        .badge-billable {
            background-color: #d4edda;
            color: #155724;
        }
        .badge-nonbillable {
            background-color: #e2e3e5;
            color: #383d41;
        }
        .footer-note {
            margin-top: 30px;
            border-top: 1px dashed #dee2e6;
            padding-top: 10px;
            color: #8a92a6;
            font-size: 9px;
            text-align: center;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <table class="header-table">
        <tr>
            <td style="vertical-align: top;">
                <div class="company-name"><?= esc($siteName) ?></div>
                <div class="meta-text">Agile Project Timesheet & Invoicing</div>
                <div class="meta-text">Generated on <?= date('F j, Y, g:i a') ?></div>
            </td>
            <td style="vertical-align: top; text-align: right;">
                <div class="report-title">TIME REPORT</div>
                <div class="meta-text"><strong>Period:</strong> <?= date('M j, Y', strtotime($filters['from_date'])) ?> — <?= date('M j, Y', strtotime($filters['to_date'])) ?></div>
                <?php if ($selectedProject): ?>
                    <div class="meta-text"><strong>Project:</strong> <?= esc($selectedProject['name']) ?></div>
                <?php endif; ?>
                <div class="meta-text"><strong>Prepared For / By:</strong> <?= esc($currentUser->username ?? $currentUser->email ?? 'Admin') ?></div>
            </td>
        </tr>
    </table>

    <!-- Summary Metrics Cards -->
    <table class="summary-box-table">
        <tr>
            <td style="width: 25%; padding-right: 5px;">
                <div class="summary-card">
                    <div class="summary-card-title">Total Hours Tracked</div>
                    <div class="summary-card-value primary"><?= $summary['total_hours'] ?> hrs</div>
                </div>
            </td>
            <td style="width: 25%; padding-left: 5px; padding-right: 5px;">
                <div class="summary-card">
                    <div class="summary-card-title">Billable Hours</div>
                    <div class="summary-card-value highlight"><?= $summary['billable_hours'] ?> hrs</div>
                </div>
            </td>
            <td style="width: 25%; padding-left: 5px; padding-right: 5px;">
                <div class="summary-card">
                    <div class="summary-card-title">Non-Billable Hours</div>
                    <div class="summary-card-value"><?= $summary['non_billable_hours'] ?> hrs</div>
                </div>
            </td>
            <td style="width: 25%; padding-left: 5px;">
                <div class="summary-card">
                    <div class="summary-card-title">Total Billable Value</div>
                    <div class="summary-card-value highlight">$<?= number_format($summary['total_amount'], 2) ?></div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Project Breakdown Summary Table -->
    <?php if (!empty($summary['by_project'])): ?>
    <table class="data-table" style="margin-bottom: 15px;">
        <thead>
            <tr>
                <th style="width: 50%;">Project Summary</th>
                <th style="width: 25%; text-align: right;">Total Hours</th>
                <th style="width: 25%; text-align: right;">Billable Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($summary['by_project'] as $pData): ?>
                <tr>
                    <td><strong><?= esc($pData['name']) ?></strong></td>
                    <td style="text-align: right;"><?= round($pData['duration'] / 3600, 2) ?> hrs</td>
                    <td style="text-align: right; font-weight: bold;">$<?= number_format($pData['billable_amount'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <!-- Detailed Time Entries -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 15%;">Date</th>
                <th style="width: 18%;">Project</th>
                <th style="width: 27%;">Activity / Notes</th>
                <th style="width: 12%;">Duration</th>
                <th style="width: 12%;">Type</th>
                <th style="width: 16%; text-align: right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($logs)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px; color: #8a92a6;">
                        No time records found for this period.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($logs as $log): 
                    $hrs = round(($log['duration'] ?? 0) / 3600, 2);
                    $isBillable = (int)($log['is_billable'] ?? 1) === 1;
                    $rate = (float)($log['hourly_rate'] ?? 50.00);
                    $amt = $isBillable ? ($hrs * $rate) : 0.00;
                ?>
                    <tr>
                        <td><?= date('M j, Y', strtotime($log['start_time'])) ?></td>
                        <td><strong><?= esc($log['project_name'] ?: 'General') ?></strong></td>
                        <td>
                            <div><?= esc($log['task_name']) ?></div>
                            <?php if (!empty($log['notes'])): ?>
                                <div style="color: #6c757d; font-size: 9px;"><?= esc($log['notes']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td><?= $hrs ?> hrs</td>
                        <td>
                            <span class="badge <?= $isBillable ? 'badge-billable' : 'badge-nonbillable' ?>">
                                <?= $isBillable ? 'Billable' : 'Non-Billable' ?>
                            </span>
                        </td>
                        <td style="text-align: right; font-weight: bold; <?= $isBillable ? 'color: #0acf97;' : 'color: #6c757d;' ?>">
                            $<?= number_format($amt, 2) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tr class="total-row">
                    <td colspan="3">GRAND TOTAL (<?= count($logs) ?> entries)</td>
                    <td><?= $summary['total_hours'] ?> hrs</td>
                    <td><?= $summary['billable_hours'] ?> billable</td>
                    <td style="text-align: right; color: #0acf97; font-size: 11px;">
                        $<?= number_format($summary['total_amount'], 2) ?>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer-note">
        This document was automatically generated by <?= esc($siteName) ?> on <?= date('Y-m-d H:i:s') ?>. All times and billings are subject to client service agreement terms.
    </div>

</body>
</html>
