<?php

use App\Entities\User;

if (!function_exists('sendActivationEmail')) {
    /**
     * Send activation email to user
     *
     * @param User $user
     * @param string $token
     * @return bool
     */
    function sendActivationEmail(User $user, string $token): bool
    {
        $email = \Config\Services::email();
        $siteName = setting('App.siteName') ?? 'Chege';

        $email->setFrom('noreply@chegeos.com', $siteName);
        $email->setTo($user->email);
        $email->setSubject('Activate Your ' . $siteName . ' Account');

        // Get the email content
        $emailContent = view('emails/activation', [
            'user' => $user,
            'token' => $token
        ]);

        $email->setMessage($emailContent);

        // Set email configuration
        $config = [
            'mailType' => 'html',
            'charset'  => 'utf-8',
            'wordWrap' => true,
        ];

        $email->initialize($config);

        return $email->send();
    }
}

if (!function_exists('sendWelcomeEmail')) {
    /**
     * Send welcome email after successful activation
     *
     * @param User $user
     * @return bool
     */
    function sendWelcomeEmail(User $user): bool
    {
        $email = \Config\Services::email();
        $siteName = setting('App.siteName') ?? 'Chege';

        $email->setFrom('welcome@chegeos.com', $siteName . ' Team');
        $email->setTo($user->email);
        $email->setSubject('Welcome to ' . $siteName . '! 🎉');

        $emailContent = view('emails/welcome', [
            'user' => $user
        ]);

        $email->setMessage($emailContent);

        $config = [
            'mailType' => 'html',
            'charset'  => 'utf-8',
            'wordWrap' => true,
        ];

        $email->initialize($config);

        return $email->send();
    }
}