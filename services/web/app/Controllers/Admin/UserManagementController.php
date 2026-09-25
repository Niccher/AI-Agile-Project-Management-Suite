<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;

class UserManagementController extends BaseController
{
    public function index()
    {
        $userModel = new UserModel();
        $users = $userModel->findAll();

        return view('admin/users/index', ['users' => $users]);
    }

    public function provision()
    {
        $users = auth()->getProvider();
        
        $user = new \CodeIgniter\Shield\Entities\User([
            'username' => $this->request->getPost('username'),
            'email'    => $this->request->getPost('email'),
            'password' => $this->request->getPost('password'),
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
