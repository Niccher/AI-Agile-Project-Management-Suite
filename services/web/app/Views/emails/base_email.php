<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?= esc($subject ?? setting('App.siteName')) ?></title>
    <style type="text/css">
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; outline: none; text-decoration: none; }
        body { margin: 0; padding: 0; width: 100% !important; min-width: 100%; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #f1f5f9; padding: 40px 0; }
        .main-table { background-color: #ffffff; margin: 0 auto; width: 600px; max-width: 600px; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05); }
        .header { background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%); padding: 32px 40px; text-align: left; }
        .content { padding: 40px; color: #334155; font-size: 15px; line-height: 1.6; }
        .footer { background-color: #f8fafc; padding: 24px 40px; text-align: center; color: #64748b; font-size: 13px; border-top: 1px solid #e2e8f0; }
        .btn-primary { display: inline-block; background-color: #727cf5; color: #ffffff !important; text-decoration: none; padding: 12px 28px; border-radius: 8px; font-weight: 600; font-size: 14px; margin: 20px 0; box-shadow: 0 2px 6px rgba(114, 124, 245, 0.3); }
        .badge { display: inline-block; padding: 4px 10px; font-size: 12px; font-weight: 600; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.5px; }
        .badge-success { background-color: #d1fae5; color: #065f46; }
        .badge-danger { background-color: #fee2e2; color: #991b1b; }
        .badge-warning { background-color: #fef3c7; color: #92400e; }
        .badge-info { background-color: #e0f2fe; color: #0369a1; }
        .info-box { background-color: #f8fafc; border-left: 4px solid #727cf5; padding: 16px 20px; border-radius: 0 8px 8px 0; margin: 20px 0; }
        @media screen and (max-width: 620px) {
            .main-table { width: 92% !important; }
            .content { padding: 24px !important; }
            .header { padding: 24px !important; }
            .footer { padding: 20px !important; }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="main-table">
            <!-- Header -->
            <tr>
                <td class="header">
                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                        <tr>
                            <td>
                                <span style="font-size: 20px; font-weight: 700; color: #ffffff; letter-spacing: -0.5px;">
                                    <?= esc(setting('App.siteName')) ?>
                                </span>
                            </td>
                            <td align="right">
                                <span style="font-size: 12px; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px;">
                                    <?= esc($headerCategory ?? 'Workspace Notice') ?>
                                </span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <!-- Content -->
            <tr>
                <td class="content">
                    <?= $this->renderSection('email_content') ?>
                </td>
            </tr>

            <!-- Footer -->
            <tr>
                <td class="footer">
                    <p style="margin: 0 0 8px 0;">© <?= date('Y') ?> <strong><?= esc(setting('App.siteName')) ?></strong>. All rights reserved.</p>
                    <p style="margin: 0; font-size: 12px; color: #94a3b8;">
                        This notification was sent by your team workspace. Questions? Contact <a href="mailto:<?= esc(setting('App.supportEmail') ?? 'support@chege.local') ?>" style="color: #727cf5; text-decoration: none;"><?= esc(setting('App.supportEmail') ?? 'support@chege.local') ?></a>.
                    </p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>