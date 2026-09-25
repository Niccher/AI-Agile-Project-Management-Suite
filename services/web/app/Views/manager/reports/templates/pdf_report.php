<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Team Performance Report</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #0056b3;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #0056b3;
            margin: 0 0 10px 0;
            font-size: 24px;
        }
        .meta-info {
            font-size: 14px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            font-size: 14px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px 15px;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
            color: #495057;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12px;
        }
        tr:nth-child(even) {
            background-color: #fcfcfc;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1><?= esc(setting('App.siteName')) ?> - Team Performance Report</h1>
        <div class="meta-info">
            Period: <?= date('F j, Y', strtotime($start)) ?> to <?= date('F j, Y', strtotime($end)) ?><br>
            Generated on: <?= date('F j, Y, g:i a') ?>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <?php foreach ($headers as $header): ?>
                    <th><?= esc($header) ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data as $row): ?>
                <tr>
                    <?php foreach ($row as $index => $cell): ?>
                        <td>
                            <?php if ($index === 3): // Status column styling ?>
                                <?= strtoupper(esc($cell)) ?>
                            <?php else: ?>
                                <?= esc($cell) ?>
                            <?php endif; ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer">
        Confidential Internal Report • <?= esc(setting('App.siteName')) ?> Project Management System
    </div>

</body>
</html>
