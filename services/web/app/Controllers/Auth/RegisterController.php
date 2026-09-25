<?php

namespace App\Controllers\Auth;

use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UserModel;
use App\Entities\User;
use App\Controllers\BaseController;
use CodeIgniter\Shield\Authentication\Passwords;

class RegisterController extends BaseController
{
    use \CodeIgniter\Shield\Traits\Viewable;

    /**
     * Display registration form
     */
    public function registerView(): ResponseInterface|string
    {
        if (auth()->loggedIn()) {
            return redirect()->to(config('Auth')->registerRedirect());
        }

        $data = [
            'title' => 'Register • Chege JIRA',
        ];

        return view('auth/register', $data);
    }

    /**
     * Handle registration using Shield's proper flow
     */
    public function registerAction(): ResponseInterface
    {
        if (auth()->loggedIn()) {
            return redirect()->to(config('Auth')->registerRedirect());
        }

        // Check if registration is allowed
        if (! setting('Auth.allowRegistration')) {
            return redirect()->back()->withInput()
                ->with('error', 'Registration is currently disabled.');
        }

        // Normalize input fields to support both snake_case and camelCase
        $firstName = trim((string) ($this->request->getPost('first_name') ?? $this->request->getPost('firstName') ?? ''));
        $lastName  = trim((string) ($this->request->getPost('last_name') ?? $this->request->getPost('lastName') ?? ''));
        $username  = trim((string) ($this->request->getPost('username') ?? ''));
        $email     = trim((string) ($this->request->getPost('email') ?? ''));
        $password  = (string) ($this->request->getPost('password') ?? '');
        $passwordConfirm = (string) ($this->request->getPost('password_confirm') ?? $this->request->getPost('confirmPassword') ?? '');
        $terms     = $this->request->getPost('terms');

        $validationData = [
            'first_name'       => $firstName,
            'last_name'        => $lastName,
            'username'         => $username,
            'email'            => $email,
            'password'         => $password,
            'password_confirm' => $passwordConfirm,
            'terms'            => $terms,
        ];

        // Validate
        $rules = [
            'first_name' => [
                'label'  => 'First Name',
                'rules'  => 'required|max_length[50]',
            ],
            'last_name'  => [
                'label'  => 'Last Name',
                'rules'  => 'required|max_length[50]',
            ],
            'username'   => [
                'label'  => 'Username',
                'rules'  => 'required|alpha_numeric|min_length[3]|max_length[30]|is_unique[users.username]',
                'errors' => [
                    'is_unique' => 'This username is already taken. Please choose another.',
                ],
            ],
            'email'      => [
                'label'  => 'Email',
                'rules'  => 'required|valid_email',
                'errors' => [
                    'required'    => 'Email is required',
                    'valid_email' => 'Please provide a valid email address',
                ],
            ],
            'password'   => [
                'label'  => 'Password',
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
                    'matches' => 'Passwords do not match',
                ],
            ],
            'terms' => [
                'label'  => 'Terms and Conditions',
                'rules'  => 'required',
                'errors' => [
                    'required' => 'You must agree to the Terms and Conditions to create an account.',
                ],
            ],
        ];

        if (!$this->validateData($validationData, $rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Check if email already registered via Shield provider
        $users = auth()->getProvider();
        $existingUser = $users->findByCredentials(['email' => $email]);

        if ($existingUser) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'An account with this email already exists. Please sign in or use a different email.');
        }

        // Prepare user data
        $userData = [
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'username'   => $username,
            'email'      => $email,
            'password'   => $password,
            'newsletter' => $this->request->getPost('newsletter') ? 1 : 0,
            'active'     => 0, // Will be activated after email verification or auto-login
        ];

        // Save the user using Shield's method
        $user = $users->createNewUser($userData);

        try {
            $users->save($user);
        } catch (\CodeIgniter\Shield\Exceptions\ValidationException $e) {
            return redirect()->back()->withInput()->with('errors', $users->errors());
        }

        // To get the complete user object with ID, we retrieve from the database
        $user = $users->findById($users->getInsertID());

        // Add to default group
        $users->addToDefaultGroup($user);

        // Trigger registration event
        \CodeIgniter\Events\Events::trigger('register', $user);

        /** @var \CodeIgniter\Shield\Authentication\Authenticators\Session $authenticator */
        $authenticator = auth('session')->getAuthenticator();

        // Prevent "User Info in Session" LogicException by clearing any existing state
        if ($authenticator->loggedIn() || $authenticator->isPending()) {
            $authenticator->logout();
        }

        $authenticator->startLogin($user);

        // If an action has been defined for register, start it up.
        $hasAction = $authenticator->startUpAction('register', $user);
        if ($hasAction) {
            return redirect()->route('auth-action-show');
        }

        // Set the user active if no action is configured
        $user->activate();

        $authenticator->completeLogin($user);

        // Success!
        return redirect()->to(config('Auth')->registerRedirect())
            ->with('message', 'Registration successful! Welcome to ' . setting('App.siteName'));
    }
}