<?php

use CodeIgniter\Email\Email;

if (! function_exists('sendActivationEmail')) {
    /**
     * Sends an activation email to the user.
     *
     * @param object $user   The user entity
     * @param string $token  The activation token
     *
     * @return bool
     */
    function sendActivationEmail($user, string $token): bool
    {
        $email = \Config\Services::email();

        // Ensure HTML mail
        $config = [
            'mailType' => 'html',
            'wordWrap' => true,
        ];
        $email->initialize($config);

        // Try to get settings dynamically with fallbacks
        $siteName  = function_exists('setting') ? (setting('App.siteName') ?? 'Chege Jira') : 'Chege Jira';
        $fromEmail = function_exists('setting') ? (setting('Email.fromEmail') ?? (setting('App.supportEmail') ?? 'no-reply@chege.local')) : 'no-reply@chege.local';
        $fromName  = function_exists('setting') ? (setting('Email.fromName') ?? ($siteName . ' Team')) : ($siteName . ' Team');

        $email->setFrom($fromEmail, $fromName);
        $email->setTo($user->email);
        $email->setSubject('Activate your ' . $siteName . ' Account');

        // Build the activation link
        $link = site_url("auth/activate/{$token}");
        
        // Simple HTML Body
        $message = "<h2>Welcome to " . esc($siteName) . ", " . esc($user->first_name) . "!</h2>";
        $message .= "<p>Thank you for joining us. To get started, please activate your account by clicking the button below:</p>";
        $message .= "<p style='margin: 20px 0;'><a href='{$link}' style='display:inline-block; padding: 12px 24px; background-color: #4F46E5; color: white; text-decoration: none; border-radius: 6px; font-weight: bold;'>Activate Account</a></p>";
        $message .= "<p>Or copy and paste this link into your browser:</p>";
        $message .= "<p><a href='{$link}'>{$link}</a></p>";
        $message .= "<br><p>If you did not create this account, please ignore this email.</p>";
        $message .= "<p>Best regards,<br>The " . esc($siteName) . " Team</p>";

        $email->setMessage($message);

        if ($email->send()) {
            return true;
        }

        // Log error if sending fails
        log_message('error', 'Activation email failed to send to ' . $user->email);
        log_message('error', $email->printDebugger(['headers']));
        
        return false;
    }
}

if (! function_exists('sendWelcomeEmail')) {
    /**
     * Sends a welcome email to the user after activation.
     *
     * @param object $user The user entity
     *
     * @return bool
     */
    function sendWelcomeEmail($user): bool
    {
        $email = \Config\Services::email();

        $config = [
            'mailType' => 'html',
            'wordWrap' => true,
        ];
        $email->initialize($config);

        $siteName  = function_exists('setting') ? (setting('App.siteName') ?? 'Chege Jira') : 'Chege Jira';
        $fromEmail = function_exists('setting') ? (setting('Email.fromEmail') ?? (setting('App.supportEmail') ?? 'no-reply@chege.local')) : 'no-reply@chege.local';
        $fromName  = function_exists('setting') ? (setting('Email.fromName') ?? ($siteName . ' Team')) : ($siteName . ' Team');

        $email->setFrom($fromEmail, $fromName);
        $email->setTo($user->email);
        $email->setSubject('Welcome to ' . $siteName . '!');

        $loginLink = site_url("auth/login");
        
        $message = "<h2>Welcome Aboard, " . esc($user->first_name) . "!</h2>";
        $message .= "<p>Your account has been successfully activated.</p>";
        $message .= "<p>You can now log in and start using your dashboard.</p>";
        $message .= "<p style='margin: 20px 0;'><a href='{$loginLink}' style='display:inline-block; padding: 12px 24px; background-color: #4F46E5; color: white; text-decoration: none; border-radius: 6px; font-weight: bold;'>Log In to Dashboard</a></p>";
        $message .= "<p>Best regards,<br>The " . esc($siteName) . " Team</p>";

        $email->setMessage($message);

        if ($email->send()) {
            return true;
        }

        log_message('error', 'Welcome email failed to send to ' . $user->email);
        return false;
    }
}

if (! function_exists('sendPasswordResetEmail')) {
    /**
     * Sends a password reset email to the user.
     *
     * @param object $user   The user entity
     * @param string $token  The reset token
     *
     * @return bool
     */
    function sendPasswordResetEmail($user, string $token): bool
    {
        $email = \Config\Services::email();

        $config = [
            'mailType' => 'html',
            'wordWrap' => true,
        ];
        $email->initialize($config);

        $siteName  = function_exists('setting') ? (setting('App.siteName') ?? 'Chege Jira') : 'Chege Jira';
        $fromEmail = function_exists('setting') ? (setting('Email.fromEmail') ?? (setting('App.supportEmail') ?? 'no-reply@chege.local')) : 'no-reply@chege.local';
        $fromName  = function_exists('setting') ? (setting('Email.fromName') ?? ($siteName . ' Team')) : ($siteName . ' Team');

        $email->setFrom($fromEmail, $fromName);
        $email->setTo($user->email);
        $email->setSubject('Reset Your Password - ' . $siteName);

        $link = site_url("auth/reset-password?token={$token}&email={$user->email}");
        
        $message = "<h2>Reset Password Request</h2>";
        $message .= "<p>Hi " . esc($user->first_name) . ",</p>";
        $message .= "<p>We received a request to reset your password. Click the link below to verify your email and set a new password:</p>";
        $message .= "<p style='margin: 20px 0;'><a href='{$link}' style='display:inline-block; padding: 12px 24px; background-color: #EF4444; color: white; text-decoration: none; border-radius: 6px; font-weight: bold;'>Reset Password</a></p>";
        $message .= "<p>Or copy and paste this link:</p>";
        $message .= "<p><a href='{$link}'>{$link}</a></p>";
        $message .= "<p>This link is valid for 1 hour.</p>";
        $message .= "<p>If you did not request this, please ignore this email.</p>";
        $message .= "<p>Best regards,<br>The " . esc($siteName) . " Team</p>";

        $email->setMessage($message);

        if ($email->send()) {
            return true;
        }

        log_message('error', 'Password reset email failed to send to ' . $user->email);
        return false;
    }
}

if (! function_exists('sendInviteEmail')) {
    /**
     * Sends an invitation email to a newly invited team member.
     *
     * @param string $recipientEmail
     * @param string $role
     * @param string $token
     * @return bool
     */
    function sendInviteEmail(string $recipientEmail, string $role, string $token): bool
    {
        $email = \Config\Services::email();

        $config = [
            'mailType' => 'html',
            'wordWrap' => true,
        ];
        $email->initialize($config);

        $siteName  = function_exists('setting') ? (setting('App.siteName') ?? 'AI Agile Suite') : 'AI Agile Suite';
        $fromEmail = function_exists('setting') ? (setting('Email.fromEmail') ?? (setting('App.supportEmail') ?? 'notifications@chege.local')) : 'notifications@chege.local';
        $fromName  = function_exists('setting') ? (setting('Email.fromName') ?? ($siteName . ' Team')) : ($siteName . ' Team');

        $email->setFrom($fromEmail, $fromName);
        $email->setTo($recipientEmail);
        $email->setSubject('You have been invited to join ' . $siteName);

        $inviteLink = site_url("auth/invite/{$token}");

        $message = "<h2>You're Invited!</h2>";
        $message .= "<p>You have been invited to join <strong>" . esc($siteName) . "</strong> with the role of <strong>" . esc(ucfirst($role)) . "</strong>.</p>";
        $message .= "<p>Click the link below to accept the invitation and set up your password:</p>";
        $message .= "<p style='margin: 20px 0;'><a href='{$inviteLink}' style='display:inline-block; padding: 12px 24px; background-color: #727cf5; color: white; text-decoration: none; border-radius: 6px; font-weight: bold;'>Accept Invitation & Get Started</a></p>";
        $message .= "<p>Or copy and paste this link into your browser:</p>";
        $message .= "<p><a href='{$inviteLink}'>{$inviteLink}</a></p>";
        $message .= "<p>This invitation link is valid for 7 days.</p>";
        $message .= "<p>Best regards,<br>The " . esc($siteName) . " Team</p>";

        $email->setMessage($message);

        if ($email->send()) {
            return true;
        }

        log_message('error', 'Invite email failed to send to ' . $recipientEmail);
        return false;
    }
}

