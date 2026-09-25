<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

class ResetPasswordController extends BaseController
{
    /**
     * Display reset password form
     */
    public function resetPasswordView(): ResponseInterface|string
    {
        $token = trim((string) $this->request->getGet('token'));
        $email = trim((string) $this->request->getGet('email'));

        // Basic validation that token exists
        if (empty($token) || empty($email)) {
             return redirect()->to('/auth/login')->with('error', 'Invalid or missing password reset link.');
        }

        $data = [
            'title' => 'Reset Password • Chege JIRA',
            'token' => $token,
            'email' => $email,
        ];

        return view('auth/reset_password', $data);
    }

    /**
     * Handle reset password
     */
    public function resetPasswordAction(): ResponseInterface
    {
        $email = trim((string) $this->request->getPost('email'));
        $token = trim((string) $this->request->getPost('token'));
        $password = (string) $this->request->getPost('password');
        $passwordConfirm = (string) ($this->request->getPost('password_confirm') ?? $this->request->getPost('confirmPassword') ?? '');

        $validationData = [
            'email'            => $email,
            'token'            => $token,
            'password'         => $password,
            'password_confirm' => $passwordConfirm,
        ];

        $rules = [
            'token' => [
                'label'  => 'Security Token',
                'rules'  => 'required',
            ],
            'email' => [
                'label'  => 'Email',
                'rules'  => 'required|valid_email',
            ],
            'password' => [
                'label'  => 'New Password',
                'rules'  => 'required|min_length[8]|strong_password',
                'errors' => [
                    'min_length'      => 'Password must be at least 8 characters long',
                    'strong_password' => 'Password must contain at least one letter and one number',
                ],
            ],
            'password_confirm' => [
                'label'  => 'Confirm Password',
                'rules'  => 'required|matches[password]',
                'errors' => [
                    'matches' => 'Password confirmation does not match the new password.',
                ],
            ],
        ];

        if (!$this->validateData($validationData, $rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // Find user by email
        $users = auth()->getProvider();
        $user = $users->findByCredentials(['email' => $email]);

        if (!$user) {
             return redirect()->to('/auth/login')->with('error', 'Unable to find an account associated with this request.');
        }

        // Verify Token and Expiry
        if (empty($user->reset_hash) || $user->reset_hash !== $token) {
            return redirect()->back()->withInput()->with('error', 'Invalid or expired password reset link. Please request a new one.');
        }

        if ($user->reset_expires_at && strtotime($user->reset_expires_at) < time()) {
             return redirect()->back()->withInput()->with('error', 'This password reset link has expired. Please request a new one.');
        }

        // Update Password and Clear Reset Token
        $user->password = $password;
        $user->reset_hash = null;
        $user->reset_expires_at = null;

        if ($users->save($user)) {
            return redirect()->to('/auth/login')
                ->with('success', 'Your password has been successfully reset! You may now sign in.');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to update password. Please try again or contact support.');
    }
}