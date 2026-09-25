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

        // Try to get settings from Config or Shield settings, with fallbacks
        $fromEmail = 'no-reply@chegeos.com';
        $fromName  = 'Chege OS Team';
        
        if (function_exists('setting')) {
            $fromEmail = setting('Email.fromEmail') ?? $fromEmail;
            $fromName  = setting('Email.fromName') ?? $fromName;
        }

        $email->setFrom($fromEmail, $fromName);
        $email->setTo($user->email);
        $email->setSubject('Activate your Chege OS Account');

        // Build the activation link
        $link = site_url("auth/activate/{$token}");
        
        // Simple HTML Body
        // Ideally checking for a view file first would be better, but inline is safe for now to fix the crash.
        $message = "<h2>Welcome to Chege OS, " . esc($user->first_name) . "!</h2>";
        $message .= "<p>Thank you for joining us. To get started, please activate your account by clicking the button below:</p>";
        $message .= "<p style='margin: 20px 0;'><a href='{$link}' style='display:inline-block; padding: 12px 24px; background-color: #4F46E5; color: white; text-decoration: none; border-radius: 6px; font-weight: bold;'>Activate Account</a></p>";
        $message .= "<p>Or copy and paste this link into your browser:</p>";
        $message .= "<p><a href='{$link}'>{$link}</a></p>";
        $message .= "<br><p>If you did not create this account, please ignore this email.</p>";
        $message .= "<p>Best regards,<br>The Chege OS Team</p>";

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

        $fromEmail = 'no-reply@chegeos.com';
        $fromName  = 'Chege OS Team';
        
        if (function_exists('setting')) {
            $fromEmail = setting('Email.fromEmail') ?? $fromEmail;
            $fromName  = setting('Email.fromName') ?? $fromName;
        }

        $email->setFrom($fromEmail, $fromName);
        $email->setTo($user->email);
        $email->setSubject('Welcome to Chege OS!');

        $loginLink = site_url("auth/login");
        
        $message = "<h2>Welcome Aboard, " . esc($user->first_name) . "!</h2>";
        $message .= "<p>Your account has been successfully activated.</p>";
        $message .= "<p>You can now log in and start using your dashboard.</p>";
        $message .= "<p style='margin: 20px 0;'><a href='{$loginLink}' style='display:inline-block; padding: 12px 24px; background-color: #4F46E5; color: white; text-decoration: none; border-radius: 6px; font-weight: bold;'>Log In to Dashboard</a></p>";
        $message .= "<p>Best regards,<br>The Chege OS Team</p>";

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

        $fromEmail = 'no-reply@chegeos.com';
        $fromName  = 'Chege OS Team';
        
        if (function_exists('setting')) {
            $fromEmail = setting('Email.fromEmail') ?? $fromEmail;
            $fromName  = setting('Email.fromName') ?? $fromName;
        }

        $email->setFrom($fromEmail, $fromName);
        $email->setTo($user->email);
        $email->setSubject('Reset Your Password');

        $link = site_url("auth/reset-password?token={$token}&email={$user->email}");
        
        $message = "<h2>Reset Password Request</h2>";
        $message .= "<p>Hi " . esc($user->first_name) . ",</p>";
        $message .= "<p>We received a request to reset your password. Click the link below to verify your email and set a new password:</p>";
        $message .= "<p style='margin: 20px 0;'><a href='{$link}' style='display:inline-block; padding: 12px 24px; background-color: #EF4444; color: white; text-decoration: none; border-radius: 6px; font-weight: bold;'>Reset Password</a></p>";
        $message .= "<p>Or copy and paste this link:</p>";
        $message .= "<p><a href='{$link}'>{$link}</a></p>";
        $message .= "<p>This link is valid for 1 hour.</p>";
        $message .= "<p>If you did not request this, please ignore this email.</p>";
        $message .= "<p>Best regards,<br>The Chege OS Team</p>";

        $email->setMessage($message);

        if ($email->send()) {
            return true;
        }

        log_message('error', 'Password reset email failed to send to ' . $user->email);
        return false;
    }
}
