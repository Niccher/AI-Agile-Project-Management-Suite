<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class SeedUser extends BaseCommand
{
    protected $group       = 'Auth';
    protected $name        = 'user:seed';
    protected $description = 'Seed an initial admin user.';

    public function run(array $params)
    {
        $email = CLI::prompt('Email', 'admin@chegejira.local');
        $username = CLI::prompt('Username', 'admin');
        $password = CLI::prompt('Password', 'secret');

        $users = auth()->getProvider();
        
        $user = new \CodeIgniter\Shield\Entities\User([
            'username' => $username,
            'email'    => $email,
            'password' => $password,
        ]);

        if ($users->save($user)) {
            $user = $users->findById($users->getInsertID());
            $user->addGroup('admin');
            CLI::write('Admin user created successfully.', 'green');
        } else {
            CLI::write('Failed to create user.', 'red');
            foreach ($user->errors() as $error) {
                CLI::write($error, 'red');
            }
        }
    }
}
