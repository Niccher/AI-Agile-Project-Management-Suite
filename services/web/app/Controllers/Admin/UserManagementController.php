<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\UserInviteModel;

class UserManagementController extends BaseController
{
    protected $helpers = ['auth', 'form', 'url', 'solo', 'email'];

    public function index()
    {
        $userModel = new UserModel();
        $users = $userModel->findAll();

        $inviteModel = new UserInviteModel();
        $pendingInvites = [];
        try {
            $pendingInvites = $inviteModel->where('status', 'pending')
                ->where('expires_at >=', date('Y-m-d H:i:s'))
                ->orderBy('created_at', 'DESC')
                ->findAll();
        } catch (\Throwable $e) {
            // Unmigrated or error fallback
        }

        return view('admin/users/index', [
            'users'          => $users,
            'pendingInvites' => $pendingInvites,
            'isSoloMode'     => is_solo_mode(),
        ]);
    }

    public function invite()
    {
        $email = strtolower(trim((string)$this->request->getPost('email')));
        $role  = trim((string)$this->request->getPost('role') ?? 'user');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Please provide a valid email address.',
                ]);
            }
            return redirect()->back()->with('error', 'Please provide a valid email address.');
        }

        if (!in_array($role, ['admin', 'manager', 'user'])) {
            $role = 'user';
        }

        // Check if user with this email already exists
        if (function_exists('email_exists') && email_exists($email)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'A registered user with this email already exists.',
                ]);
            }
            return redirect()->back()->with('error', 'A registered user with this email already exists.');
        }

        $inviteModel = new UserInviteModel();
        $invite = $inviteModel->generateInvite($email, $role);

        // Attempt dispatching email via configured SMTP
        $emailSent = false;
        if (function_exists('sendInviteEmail')) {
            try {
                $emailSent = sendInviteEmail($email, $role, $invite['token']);
            } catch (\Throwable $e) {
                log_message('error', 'Invite email dispatch failed: ' . $e->getMessage());
            }
        }

        $msg = $emailSent
            ? "Invitation sent to {$email}. You can also copy the direct invite link below."
            : "Invitation created for {$email}. (SMTP email could not be sent; share the copyable link directly).";

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status'      => 'success',
                'invite_link' => $invite['invite_link'],
                'email_sent'  => $emailSent,
                'message'     => $msg,
            ]);
        }

        return redirect()->back()->with('message', $msg)->with('invite_link', $invite['invite_link']);
    }

    public function revokeInvite($id)
    {
        $inviteModel = new UserInviteModel();
        $invite = $inviteModel->find($id);

        if ($invite) {
            $inviteModel->update($id, [
                'status'     => 'revoked',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status'  => 'success',
                    'message' => 'Invitation link revoked.',
                ]);
            }
            return redirect()->back()->with('message', 'Invitation link revoked.');
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Invitation not found.',
            ]);
        }
        return redirect()->back()->with('error', 'Invitation not found.');
    }

    public function provision()
    {
        $users = auth()->getProvider();
        
        $user = new \CodeIgniter\Shield\Entities\User([
            'username' => $this->request->getPost('username'),
            'email'    => $this->request->getPost('email'),
            'password' => $this->request->getPost('password'),
            'active'   => 1,
        ]);

        if ($users->save($user)) {
            $user = $users->findById($users->getInsertID());
            $role = $this->request->getPost('role');
            if (in_array($role, ['admin', 'manager', 'user'])) {
                $user->addGroup($role);
            }
            return redirect()->back()->with('message', 'User provisioned successfully.');
        }

        return redirect()->back()->with('error', 'Failed to provision user.');
    }

    public function assignRole($id)
    {
        $users = auth()->getProvider();
        $user = $users->findById($id);

        if ($user) {
            $role = $this->request->getPost('role');
            
            // Prevent Admin self-demotion
            if ($id == auth()->id() && $role !== 'admin') {
                return redirect()->back()->with('error', 'You cannot demote your own administrator account.');
            }
            
            // Remove existing groups (roles)
            foreach ($user->getGroups() as $group) {
                $user->removeGroup($group);
            }
            
            // Add new role
            if (in_array($role, ['admin', 'manager', 'user'])) {
                $user->addGroup($role);
            }
            
            return redirect()->back()->with('message', 'Role updated successfully.');
        }

        return redirect()->back()->with('error', 'User not found.');
    }

    public function deactivate($id)
    {
        $users = auth()->getProvider();
        $user = $users->findById($id);

        if ($user) {
            // Prevent Admin self-deactivation
            if ($id == auth()->id()) {
                return redirect()->back()->with('error', 'You cannot deactivate your own account.');
            }

            if ($user->isBanned()) {
                $user->unBan();
                return redirect()->back()->with('message', 'User activated successfully.');
            } else {
                $user->ban('Deactivated by admin');
                return redirect()->back()->with('message', 'User deactivated successfully.');
            }
        }

        return redirect()->back()->with('error', 'User not found.');
    }
}
