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
        
        $admin = $users->findByCredentials(['email' => 'admin@chegejira.local']);
        if (!$admin) {
            $user = new User([
                'username' => 'admin',
                'email'    => 'admin@chegejira.local',
                'password' => 'secret',
            ]);
            $users->save($user);
            $admin = $users->findById($users->getInsertID());
            $admin->addGroup('admin');
        }

        $dev = $users->findByCredentials(['email' => 'dev@chegejira.local']);
        if (!$dev) {
            $user = new User([
                'username' => 'developer',
                'email'    => 'dev@chegejira.local',
                'password' => 'secret',
            ]);
            $users->save($user);
            $dev = $users->findById($users->getInsertID());
            $dev->addGroup('user');
        }

        // 2. Seed Projects only if table is currently empty
        if ($this->db->table('projects')->countAllResults() === 0) {
            $projects = [
                [
                    'name' => 'Hyper Theme Migration',
                    'description' => 'Migrate the entire application to the new Bootstrap 5 Hyper SaaS Theme.',
                    'status' => 'in_progress',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ],
                [
                    'name' => 'Mobile App API',
                    'description' => 'Build the REST API for the new iOS and Android applications.',
                    'status' => 'planning',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ],
                [
                    'name' => 'Q3 Marketing Website',
                    'description' => 'Redesign the public facing marketing website to increase conversion rates.',
                    'status' => 'completed',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]
            ];
            $this->db->table('projects')->insertBatch($projects);
        }
        
        echo "Demo data seeded successfully! (Admin: admin@chegejira.local / secret)\n";
    }
}
