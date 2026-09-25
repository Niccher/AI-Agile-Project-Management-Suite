<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Commands\DatabaseBackup;

class SystemSettingsController extends BaseController
{
    public function index()
    {
        $currentTab = $this->request->getGet('tab') ?? 'general';
        $backups = DatabaseBackup::listBackups();

        return view('admin/settings/index', [
            'currentTab' => $currentTab,
            'backups'    => $backups,
        ]);
    }

    public function update()
    {
        $tab = $this->request->getPost('tab') ?? 'general';

        if ($tab === 'general') {
            $siteName = trim((string)$this->request->getPost('site_name'));
            $siteDesc = trim((string)$this->request->getPost('site_desc'));
            $supportEmail = trim((string)$this->request->getPost('support_email'));
            $timezone = trim((string)$this->request->getPost('timezone'));

            if (!empty($siteName)) {
                setting('App.siteName', $siteName);
            }
            if ($siteDesc !== '') {
                setting('App.siteDesc', $siteDesc);
            }
            if (!empty($supportEmail)) {
                setting('App.supportEmail', $supportEmail);
            }
            if (!empty($timezone)) {
                setting('App.defaultTimezone', $timezone);
            }

            return redirect()->to(site_url('admin/settings?tab=general'))->with('message', 'General settings saved successfully.');
        }

        if ($tab === 'security') {
            $allowRegistration = $this->request->getPost('allow_registration') ? 1 : 0;
            $requireEmailVerification = $this->request->getPost('require_verification') ? 1 : 0;
            $maxAttempts = (int)($this->request->getPost('max_login_attempts') ?? 5);
            $lockoutMinutes = (int)($this->request->getPost('lockout_minutes') ?? 15);
            $sessionTimeout = (int)($this->request->getPost('session_timeout') ?? 7200);

            setting('Auth.allowRegistration', $allowRegistration);
            setting('Auth.requireEmailVerification', $requireEmailVerification);
            setting('Auth.maxLoginAttempts', max(1, $maxAttempts));
            setting('Auth.lockoutMinutes', max(1, $lockoutMinutes));
            setting('Auth.sessionTimeout', max(300, $sessionTimeout));

            return redirect()->to(site_url('admin/settings?tab=security'))->with('message', 'Security & Session preferences updated.');
        }

        if ($tab === 'email') {
            $fromEmail = trim((string)$this->request->getPost('from_email'));
            $fromName = trim((string)$this->request->getPost('from_name'));
            $protocol = trim((string)$this->request->getPost('protocol') ?? 'smtp');
            $smtpHost = trim((string)$this->request->getPost('smtp_host'));
            $smtpPort = (int)($this->request->getPost('smtp_port') ?? 587);
            $smtpUser = trim((string)$this->request->getPost('smtp_user'));
            $smtpPass = (string)$this->request->getPost('smtp_pass');
            $smtpCrypto = trim((string)$this->request->getPost('smtp_crypto') ?? 'tls');

            if (!empty($fromEmail)) {
                setting('Email.fromEmail', $fromEmail);
            }
            if (!empty($fromName)) {
                setting('Email.fromName', $fromName);
            }
            setting('Email.protocol', $protocol);
            if (!empty($smtpHost)) {
                setting('Email.SMTPHost', $smtpHost);
            }
            setting('Email.SMTPPort', $smtpPort);
            setting('Email.SMTPUser', $smtpUser);
            if ($smtpPass !== '') {
                setting('Email.SMTPPass', $smtpPass);
            }
            setting('Email.SMTPCrypto', $smtpCrypto);

            return redirect()->to(site_url('admin/settings?tab=email'))->with('message', 'Email gateway configuration updated successfully.');
        }

        if ($tab === 'project') {
            $defaultPriority = trim((string)$this->request->getPost('default_priority') ?? 'medium');
            $requireReview = $this->request->getPost('require_review') ? 1 : 0;
            $maxAttachmentMb = (int)($this->request->getPost('max_attachment_mb') ?? 10);

            setting('Project.defaultPriority', $defaultPriority);
            setting('Project.requireReview', $requireReview);
            setting('Project.maxAttachmentMb', max(1, $maxAttachmentMb));

            return redirect()->to(site_url('admin/settings?tab=project'))->with('message', 'Project & Workflow defaults saved.');
        }

        if ($tab === 'maintenance') {
            $maintenanceMode = $this->request->getPost('maintenance_mode') ? 1 : 0;
            $maintenanceNotice = trim((string)$this->request->getPost('maintenance_notice'));

            setting('App.maintenanceMode', $maintenanceMode);
            setting('App.maintenanceNotice', $maintenanceNotice);

            return redirect()->to(site_url('admin/settings?tab=maintenance'))->with('message', 'Maintenance mode state updated.');
        }

        return redirect()->to(site_url('admin/settings'))->with('error', 'Unknown settings tab submitted.');
    }

    public function sendTestEmail()
    {
        $recipient = trim((string)$this->request->getPost('test_recipient'));
        if (empty($recipient) || !filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            return redirect()->to(site_url('admin/settings?tab=email'))->with('error', 'Please provide a valid recipient email address.');
        }

        $email = \Config\Services::email();
        $email->initialize([
            'protocol'   => setting('Email.protocol') ?? 'smtp',
            'SMTPHost'   => setting('Email.SMTPHost') ?? 'localhost',
            'SMTPPort'   => (int)(setting('Email.SMTPPort') ?? 587),
            'SMTPUser'   => setting('Email.SMTPUser') ?? '',
            'SMTPPass'   => setting('Email.SMTPPass') ?? '',
            'SMTPCrypto' => setting('Email.SMTPCrypto') ?? 'tls',
            'mailType'   => 'html',
            'charset'    => 'utf-8',
            'newline'    => "\r\n",
            'CRLF'       => "\r\n",
        ]);

        $email->setFrom(setting('Email.fromEmail') ?? 'notifications@chege.local', setting('Email.fromName') ?? setting('App.siteName'));
        $email->setTo($recipient);
        $email->setSubject('SMTP Gateway Test • ' . setting('App.siteName'));
        $email->setMessage(view('emails/test_email', [
            'recipient'   => $recipient,
            'smtpHost'    => setting('Email.SMTPHost') ?? 'localhost',
            'smtpPort'    => setting('Email.SMTPPort') ?? 587,
            'smtpCrypto'  => setting('Email.SMTPCrypto') ?? 'tls',
            'dispatchedAt'=> date('Y-m-d H:i:s T')
        ]));

        try {
            if ($email->send(false)) {
                return redirect()->to(site_url('admin/settings?tab=email'))->with('message', 'Test email successfully dispatched to ' . esc($recipient));
            } else {
                $debugger = $email->printDebugger(['headers', 'subject']);
                return redirect()->to(site_url('admin/settings?tab=email'))->with('error', 'Failed to send test email. Server debug notice: ' . strip_tags($debugger));
            }
        } catch (\Throwable $e) {
            return redirect()->to(site_url('admin/settings?tab=email'))->with('error', 'SMTP Connection Exception: ' . $e->getMessage());
        }
    }

    public function createBackup()
    {
        $res = DatabaseBackup::createBackup();
        if ($res['status'] === 'success') {
            return redirect()->to(site_url('admin/settings?tab=maintenance'))->with('message', "Database backup created successfully: {$res['filename']} ({$res['filesize']})");
        }
        return redirect()->to(site_url('admin/settings?tab=maintenance'))->with('error', 'Failed to create backup: ' . ($res['message'] ?? 'Unknown error'));
    }

    public function downloadBackup($filename)
    {
        $filename = basename($filename);
        $filepath = WRITEPATH . 'backups/' . $filename;
        if (file_exists($filepath) && is_readable($filepath)) {
            return $this->response->download($filepath, null);
        }
        return redirect()->to(site_url('admin/settings?tab=maintenance'))->with('error', 'Backup file not found.');
    }
}
