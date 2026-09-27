<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\Shield\Entities\User;

class DemoSeeder extends Seeder
{
    public function run()
    {
        // 1. Create a Test Admin and Test User if they don't exist
        $users = auth()->getProvider();
        $adminPass = env('ADMIN_PASSWORD', 'admin_password_123');
        
        $admin = $users->findByCredentials(['email' => 'admin@chegejira.local'])
            ?? $users->findByCredentials(['username' => 'admin']);

        if (!$admin) {
            $user = new User([
                'username'   => 'admin',
                'email'      => 'admin@chegejira.local',
                'password'   => $adminPass,
                'first_name' => 'System',
                'last_name'  => 'Admin',
                'active'     => 1,
            ]);
            $users->save($user);
            $admin = $users->findById($users->getInsertID()) ?? $users->findByCredentials(['username' => 'admin']);
            if ($admin) {
                $admin->addGroup('admin');
                $admin->addGroup('superadmin');
            }
        }

        $dev = $users->findByCredentials(['email' => 'dev@chegejira.local'])
            ?? $users->findByCredentials(['username' => 'developer']);

        if (!$dev) {
            $user = new User([
                'username'   => 'developer',
                'email'      => 'dev@chegejira.local',
                'password'   => 'secret',
                'first_name' => 'Dev',
                'last_name'  => 'User',
                'active'     => 1,
            ]);
            $users->save($user);
            $dev = $users->findById($users->getInsertID()) ?? $users->findByCredentials(['username' => 'developer']);
            if ($dev) {
                $dev->addGroup('user');
            }
        }

        $adminId = $admin ? $admin->id : 1;

        // 2. Seed Projects only if table is currently empty
        if ($this->db->table('projects')->countAllResults() === 0) {
            $projects = [
                [
                    'user_id'     => $adminId,
                    'name'        => 'Hyper Theme Migration',
                    'slug'        => 'hyper-theme-migration-a1b2c3',
                    'short_code'  => 'a1b2c3',
                    'description' => 'Migrate the entire application to the new Bootstrap 5 Hyper SaaS Theme.',
                    'status'      => 'in_progress',
                    'priority'    => 'high',
                    'created_at'  => date('Y-m-d H:i:s'),
                    'updated_at'  => date('Y-m-d H:i:s'),
                ],
                [
                    'user_id'     => $adminId,
                    'name'        => 'Mobile App API',
                    'slug'        => 'mobile-app-api-d4e5f6',
                    'short_code'  => 'd4e5f6',
                    'description' => 'Build the REST API for the new iOS and Android applications.',
                    'status'      => 'planning',
                    'priority'    => 'medium',
                    'created_at'  => date('Y-m-d H:i:s'),
                    'updated_at'  => date('Y-m-d H:i:s'),
                ],
                [
                    'user_id'     => $adminId,
                    'name'        => 'Q3 Marketing Website',
                    'slug'        => 'q3-marketing-website-789abc',
                    'short_code'  => '789abc',
                    'description' => 'Redesign the public facing marketing website to increase conversion rates.',
                    'status'      => 'completed',
                    'priority'    => 'low',
                    'created_at'  => date('Y-m-d H:i:s'),
                    'updated_at'  => date('Y-m-d H:i:s'),
                ]
            ];
            $this->db->table('projects')->insertBatch($projects);
        }
        
        echo "Demo data seeded successfully! (Admin: admin / {$adminPass} or admin@chegejira.local / {$adminPass})\n";
    }
}
