<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SyncShieldMigrations extends Migration
{
    public function up(): void
    {
        // Check if Shield tables already exist (e.g. users table)
        if ($this->db->tableExists('users')) {
            // Check if Shield vendor migration is already registered in migrations table
            $exists = $this->db->table('migrations')
                ->where('namespace', 'CodeIgniter\\Shield')
                ->where('version', '2020-12-28-223112')
                ->countAllResults();

            if ($exists === 0) {
                $this->db->table('migrations')->insert([
                    'version'   => '2020-12-28-223112',
                    'class'     => 'CodeIgniter\\Shield\\Database\\Migrations\\CreateAuthTables',
                    'group'     => 'default',
                    'namespace' => 'CodeIgniter\\Shield',
                    'time'      => time(),
                    'batch'     => 1,
                ]);
            }
        }
    }

    public function down(): void
    {
        // No-op to preserve auth history
    }
}
