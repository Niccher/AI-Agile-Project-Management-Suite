<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

class ForgotPasswordController extends BaseController
{
    /**
     * Display forgot password form
     */
    public function forgotPasswordView(): ResponseInterface|string
    {
        if (auth()->loggedIn()) {
            return redirect()->to('/home');
        }

        $data = [
            'title' => 'Forgot Password • Chege JIRA',
        ];

        return view('auth/forgot_password', $data);
    }

    /**
     * Handle the forgot password form submission
     */
    public function forgotPasswordAction(): ResponseInterface
    {
        $rules = [
            'email' => [
                'label'  => 'Email',
                'rules'  => 'required|valid_email',
                'errors' => [
                    'required'    => 'Please enter your registered email address.',
                    'valid_email' => 'Please provide a valid email address.',
                ],
            ],
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email = trim((string) $this->request->getPost('email'));

        // Look up user via Shield provider
        $users = auth()->getProvider();
        $user = $users->findByCredentials(['email' => $email]);

        if (empty($user)) {
            // Security: Don't reveal whether the email exists
            return redirect()->back()->with('success', 'If an account with that email exists, we have sent password reset instructions.');
        }

        // Generate Reset Token
        $token = bin2hex(random_bytes(32));
        $user->reset_hash = $token;
        $user->reset_expires_at = date('Y-m-d H:i:s', time() + 3600); // 1 hour expiration
        $users->save($user);

        // Send Email
        helper('email');
        if (sendPasswordResetEmail($user, $token)) {
             return redirect()->back()->with('success', 'Password reset instructions have been sent to your email. Please check your inbox.');
        }

        return redirect()->back()->with('error', 'Unable to dispatch email. Please check mail settings or contact administrator.');
    }
}