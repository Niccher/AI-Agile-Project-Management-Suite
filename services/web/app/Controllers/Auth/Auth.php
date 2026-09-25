<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;

class Auth extends BaseController
{
    public function login()
    {
        // If user is already logged in, redirect to dashboard
        if (session()->has('user_id')) {
            return redirect()->to('/dashboard');
        }

        $data = [
            'title' => 'Login • Chege JIRA',
        ];

        return view('auth/login', $data);
    }

    public function processLogin()
    {
        // Validate input
        $rules = [
            'email' => 'required|valid_email',
            'password' => 'required|min_length[8]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Get input
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        $remember = $this->request->getPost('remember');

        // In a real app, you would check against database
        // For now, we'll use demo credentials
        $demoEmail = 'demo@chegeos.com';
        $demoPassword = 'demopassword';

        if ($email === $demoEmail && $password === $demoPassword) {
            // Set session
            session()->set([
                'user_id' => 1,
                'user_email' => $email,
                'user_name' => 'John Developer',
                'user_initials' => 'JD',
                'logged_in' => true
            ]);

            // Set remember me cookie if checked
            if ($remember) {
                // Set cookie for 30 days
                helper('cookie');
                set_cookie('remember_token', 'demo_token', 30 * 24 * 60 * 60);
            }

            return redirect()->to('/dashboard')->with('success', 'Welcome back!');
        } else {
            return redirect()->back()->withInput()->with('error', 'Invalid email or password');
        }
    }

    public function logout()
    {
        // Destroy session
        session()->destroy();

        // Delete remember me cookie
        helper('cookie');
        delete_cookie('remember_token');

        return redirect()->to('/auth/login')->with('success', 'You have been logged out successfully.');
    }

    public function register()
    {
        // If user is already logged in, redirect to dashboard
        if (session()->has('user_id')) {
            return redirect()->to('/dashboard');
        }

        $data = [
            'title' => 'Register • Chege JIRA',
        ];

        return view('auth/register', $data);
    }

    public function processRegistration()
    {
        // Validate input
        $rules = [
            'firstName' => 'required|min_length[2]|max_length[50]',
            'lastName' => 'required|min_length[2]|max_length[50]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'username' => 'required|alpha_numeric|min_length[3]|max_length[30]|is_unique[users.username]',
            'password' => 'required|min_length[8]|strong_password',
            'confirmPassword' => 'required|matches[password]',
            'terms' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Get input
        $userData = [
            'first_name' => $this->request->getPost('firstName'),
            'last_name' => $this->request->getPost('lastName'),
            'email' => $this->request->getPost('email'),
            'username' => $this->request->getPost('username'),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'newsletter' => $this->request->getPost('newsletter') ? 1 : 0,
            'activation_token' => bin2hex(random_bytes(32)),
            'created_at' => date('Y-m-d H:i:s'),
        ];

        // In a real app, save to database
        // $userModel = new \App\Models\UserModel();
        // $userModel->insert($userData);

        // Send verification email
        // $this->sendVerificationEmail($userData['email'], $userData['activation_token']);

        // Redirect to verification page
        return redirect()->to('/auth/verify-email')->with('email', $userData['email']);
    }

    public function verifyEmail()
    {
        $data = [
            'title' => 'Verify Email • Chege JIRA',
            'email' => session()->getFlashdata('email') ?? '',
        ];

        return view('auth/verify_email', $data);
    }

    public function forgotPassword()
    {
        // If user is already logged in, redirect to dashboard
        if (session()->has('user_id')) {
            return redirect()->to('/dashboard');
        }

        $data = [
            'title' => 'Forgot Password • Chege JIRA',
        ];

        return view('auth/forgot_password', $data);
    }

    public function processForgotPassword()
    {
        // Validate email
        $rules = [
            'email' => 'required|valid_email',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email = $this->request->getPost('email');

        // In a real app, check if email exists in database
        // Generate reset token and send email

        // For demo purposes, we'll just show success
        return redirect()->back()->with('success', 'Password reset instructions have been sent to your email.');
    }

    public function resetPassword($token = null)
    {
        // If user is already logged in, redirect to dashboard
        if (session()->has('user_id')) {
            return redirect()->to('/dashboard');
        }

        // In a real app, validate token
        $validToken = true; // This would check against database
        $email = 'demo@chegeos.com'; // This would come from database

        if (!$validToken) {
            return redirect()->to('/auth/login')->with('error', 'Invalid or expired reset token.');
        }

        $data = [
            'title' => 'Reset Password • Chege JIRA',
            'token' => $token,
            'email' => $email,
        ];

        return view('auth/reset_password', $data);
    }

    public function processResetPassword()
    {
        // Validate input
        $rules = [
            'token' => 'required',
            'email' => 'required|valid_email',
            'password' => 'required|min_length[8]|strong_password',
            'confirmPassword' => 'required|matches[password]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $token = $this->request->getPost('token');
        $password = $this->request->getPost('password');

        // In a real app, validate token and update password in database

        return redirect()->to('/auth/login')->with('success', 'Password has been reset successfully. Please login with your new password.');
    }

    public function activateAccount($token = null)
    {
        // In a real app, check token against database
        $validToken = ($token === 'valid_token'); // Demo check

        if (!$validToken) {
            $status = 'invalid_token';
        } else {
            $status = 'success';
        }

        $data = [
            'title' => 'Activate Account • Chege JIRA',
            'status' => $status,
            'email' => 'demo@chegeos.com', // Would come from database
        ];

        return view('auth/activate_account', $data);
    }

    public function lockedAccount()
    {
        $data = [
            'title' => 'Account Locked • Chege JIRA',
        ];

        return view('auth/locked', $data);
    }

    // Helper method to send verification email
    private function sendVerificationEmail($email, $token)
    {
        $verificationLink = site_url("auth/activate/{$token}");

        // In a real app, you would use an email library
        // For demo, we just log it
        log_message('info', "Verification email would be sent to {$email} with link: {$verificationLink}");
    }
}