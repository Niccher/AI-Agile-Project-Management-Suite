<?php

namespace App\Controllers\Auth;

use CodeIgniter\Shield\Controllers\LoginController as ShieldLogin;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\RedirectResponse;

class LoginController extends ShieldLogin
{
    use ResponseTrait;

    /**
     * Override the login view
     */
    public function loginView(): string|RedirectResponse
    {
        // Prevent logged-in users from accessing login page
        if (auth()->loggedIn()) {
            return redirect()->to(config('Auth')->loginRedirect());
        }

        // If user is pending (e.g. from registration), logout to allow fresh login
        if (auth()->isPending()) {
            auth()->logout();
        }
        $data = [
            'title' => 'Login • Chege JIRA',
            'config' => config('Auth'),
        ];
        // Add session messages if any
        if (session()->has('error')) {
            $data['error'] = session('error');
        }
        if (session()->has('success')) {
            $data['success'] = session('success');
        }
        if (session()->has('message')) {
            $data['message'] = session('message');
        }
        // Return custom view
        return view('auth/login', $data);
    }

    /**
     * Override login action to handle custom validation
     */
    public function loginAction(): RedirectResponse
    {
        // Validate
        $rules = $this->getValidationRules();
        if (!$this->validateData($this->request->getPost(), $rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        // Check credentials using Shield
        $credentials = [
            'email' => $this->request->getPost('email'),
            'password' => $this->request->getPost('password'),
        ];
        $remember = (bool) $this->request->getPost('remember');
        // Attempt login
        $result = auth('session')->attempt($credentials, $remember);
        if (!$result->isOK()) {
            // Check if account is locked
            $user = auth()->getProvider()->findByCredentials(['email' => $credentials['email']]);
            if ($user && $user->isBanned()) {
                return redirect()->to('/auth/locked')->with('email', $user->email);
            }
            return redirect()->back()->withInput()->with('error', $result->reason());
        }
        // Success - redirect to dashboard
        return redirect()->to(config('Auth')->loginRedirect())->with('success', 'Welcome back!');
    }

    /**
     * Handle user logout
     */
    public function logoutAction(): RedirectResponse
    {
        if (auth()->loggedIn()) {
            auth()->logout();
        }
        session()->destroy();

        return redirect()->to(site_url('auth/login'))->with('success', 'You have been successfully logged out.');
    }

    /**
     * Custom validation rules
     */
    protected function getValidationRules(): array
    {
        return [
            'email' => [
                'label' => 'Email',
                'rules' => 'required|valid_email',
            ],
            'password' => [
                'label' => 'Password',
                'rules' => 'required',
            ],
        ];
    }
}