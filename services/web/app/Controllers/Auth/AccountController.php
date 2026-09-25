<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

class AccountController extends BaseController
{
    /**
     * Display verify email page
     */
    public function verifyEmailView(): string
    {
        return view('\App\Views\auth\verify_email');
    }

    /**
     * Display email verification success page
     */
    public function verifyEmailSuccess(): string
    {
        return view('\App\Views\auth\activate_account', [
            'status' => 'success',
            'email' => session()->get('email') ?? ''
        ]);
    }

    public function verifyEmailAction(): ResponseInterface|string
    {
        $token = trim($this->request->getGet('token'));
        $email = trim($this->request->getGet('email'));
        
        if (empty($token) || empty($email)) {
             return redirect()->to('/auth/login')->with('error', 'Invalid verification link.');
        }

        /** @var UserModel $userModel */
        $userModel = model(UserModel::class);
        $user = $userModel->findByCredentials(['email' => $email]);
        
        if (! $user) {
             return redirect()->to('/auth/login')->with('error', 'User not found.');
        }
        
        if ($user->active) {
             return redirect()->to('/auth/login')->with('message', 'Account already active.');
        }

        /** @var \CodeIgniter\Shield\Models\UserIdentityModel $identityModel */
        $identityModel = model(\CodeIgniter\Shield\Models\UserIdentityModel::class);
        
        // Check for the identity
        $identity = $identityModel->getIdentityBySecret('email_activate', $token);
        
        // Verify identity checks
        if (! $identity) {
             return redirect()->to('/auth/login')->with('error', 'Authentication token not found or expired.');
        }
        
        // Loose comparison for ID to handle string/int types from DB drivers
        if ($identity->user_id != $user->id) {
             return redirect()->to('/auth/login')->with('error', 'Token does not match user account.');
        }
        
        // Activate User
        $user->activate();
        
        // Clean up identity
        $identityModel->delete($identity->id);

        // Helper to get correct authenticator
        $auth = auth();

        // Logout any existing session (including pending registration sessions)
        // to prevent LogicException in startLogin
        if ($auth->loggedIn() || $auth->isPending()) {
            $auth->logout();
        }

        // Auto-login the user
        $auth->login($user);

        // Success - redirect to dashboard (or wherever)
        return redirect()->to('/home')->with('message', 'Account activated successfully!');
    }

    /**
     * Activate account with token
     * @deprecated Legacy - replaced by verifyEmailAction
     */
    public function activateAccount($token = null): string
    {
        $userModel = new UserModel();

        if (empty($token)) {
            return view('\App\Views\auth\activate_account', [
                'status' => 'invalid_token',
                'email' => $this->request->getGet('email')
            ]);
        }

        // Find user by activation token
        $user = $userModel->where('activate_hash', $token)
            ->first();

        if (!$user) {
            return view('\App\Views\auth\activate_account', [
                'status' => 'invalid_token',
                'email' => $this->request->getGet('email')
            ]);
        }

        // Check if already activated
        if ($user->active) {
            return view('\App\Views\auth\activate_account', [
                'status' => 'already_activated',
                'email' => $user->email
            ]);
        }

        // Check if token expired
        if ($user->activate_hash_expires && strtotime($user->activate_hash_expires) < time()) {
            return view('\App\Views\auth\activate_account', [
                'status' => 'expired_token',
                'email' => $user->email
            ]);
        }

        // Activate user
        $user->active = 1;
        $user->activate_hash = null;
        $user->activate_hash_expires = null;
        $userModel->save($user);

        // Send welcome email
        helper('email');
        sendWelcomeEmail($user);

        return view('\App\Views\auth\activate_account', [
            'status' => 'success',
            'email' => $user->email
        ]);
    }

    /**
     * Resend activation email using Shield
     */
    public function resendShieldActivation(): ResponseInterface
    {
        if ($this->request->getMethod() === 'post') {
            $email = $this->request->getPost('email');
            
            /** @var UserModel $userModel */
            $userModel = model(UserModel::class);
            $user = $userModel->findByCredentials(['email' => $email]);

            if (!$user) {
                return redirect()->back()->with('error', 'Email not found');
            }

            if ($user->active) {
                return redirect()->to('/auth/login')->with('error', 'Account already activated');
            }
            
            // Get the authenticator
            /** @var \CodeIgniter\Shield\Authentication\Authenticators\Session $authenticator */
            $authenticator = auth('session')->getAuthenticator();
            
            // Generate a specialized class for the action
            $actionClass = setting('Auth.actions')['register'];
            
            /** @var \CodeIgniter\Shield\Authentication\Actions\EmailActivator $action */
            $action = new $actionClass($authenticator, $user);
            
            // Create the identity and send the email
            $action->createIdentity($user);

            // Send activation email
            return redirect()->to('/auth/verify-email')
                ->with('success', 'Activation email sent!')
                ->with('email', $user->email);
        }

        return redirect()->back()->with('error', 'Failed to resend activation email');
    }

    /**
     * Resend activation email
     * @deprecated Use resendShieldActivation instead
     */
    public function resendActivation(): ResponseInterface
    {
        // Legacy implementation kept for reference or fallback
        if ($this->request->getMethod() === 'post') {
            $email = $this->request->getPost('email');
            $userModel = new UserModel();
            $user = $userModel->where('email', $email)->first();
    
            if (!$user) {
                return redirect()->back()->with('error', 'Email not found');
            }
    
            if ($user->active) {
                return redirect()->to('/auth/login')->with('error', 'Account already activated');
            }
    
            // Generate new activation token
            $user->activate_hash = bin2hex(random_bytes(32));
            $user->activate_hash_expires = date('Y-m-d H:i:s', time() + 86400);
            $userModel->save($user);
    
            // Send activation email
            helper('email');
            if (sendActivationEmail($user, $user->activate_hash)) {
                return redirect()->to('/auth/verify-email')
                    ->with('success', 'Activation email sent!')
                    ->with('email', $user->email);
            }
        }
    
        return redirect()->back()->with('error', 'Failed to resend activation email');
    }

    /**
     * Display locked account page
     */
    public function lockedView(): string
    {
        return view('\App\Views\auth\locked');
    }
}