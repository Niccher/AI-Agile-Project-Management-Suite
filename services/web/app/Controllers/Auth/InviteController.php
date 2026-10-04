<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Models\UserInviteModel;
use App\Models\UserModel;

class InviteController extends BaseController
{
    public function acceptView(string $token)
    {
        $inviteModel = new UserInviteModel();
        $invite = $inviteModel->findValidByToken($token);

        if (!$invite) {
            return redirect()->to(site_url('auth/login'))
                ->with('error', 'This invitation link is invalid or has expired. Please ask an administrator for a new invitation.');
        }

        return view('auth/invite_accept', [
            'invite' => $invite,
            'token'  => $token,
        ]);
    }

    public function acceptAction(string $token)
    {
        $inviteModel = new UserInviteModel();
        $invite = $inviteModel->findValidByToken($token);

        if (!$invite) {
            return redirect()->to(site_url('auth/login'))
                ->with('error', 'Invitation link is invalid or has expired.');
        }

        $rules = [
            'username'         => 'required|alpha_numeric_punct|min_length[3]|max_length[30]|is_unique[users.username]',
            'first_name'       => 'required|max_length[50]',
            'last_name'        => 'required|max_length[50]',
            'password'         => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $users = auth()->getProvider();
        $user = new \CodeIgniter\Shield\Entities\User([
            'username'   => $this->request->getPost('username'),
            'email'      => $invite['email'],
            'password'   => $this->request->getPost('password'),
            'first_name' => $this->request->getPost('first_name'),
            'last_name'  => $this->request->getPost('last_name'),
            'active'     => 1,
        ]);

        if ($users->save($user)) {
            $user = $users->findById($users->getInsertID());
            $role = in_array($invite['role'], ['admin', 'manager', 'user']) ? $invite['role'] : 'user';
            $user->addGroup($role);

            // Mark invite as accepted
            $inviteModel->update($invite['id'], [
                'status'     => 'accepted',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            // Auto-login
            auth()->login($user);

            return redirect()->to(site_url('dashboard'))
                ->with('message', 'Welcome to the team! Your account has been activated.');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to complete registration. Please try again.');
    }
}
