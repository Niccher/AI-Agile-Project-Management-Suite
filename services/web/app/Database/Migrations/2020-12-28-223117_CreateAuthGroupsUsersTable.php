<?php

namespace CodeIgniter\Shield\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuthGroupsUsersTable extends Migration
{
    public function up(): void
    {
        if (!$this->db->tableExists('auth_groups_users')) {
            $this->forge->addField([
                'id'         => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'user_id'    => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
                'group'      => ['type' => 'varchar', 'constraint' => 255],
                'created_at' => ['type' => 'datetime', 'null' => true],
            ]);
            $this->forge->addPrimaryKey('id');
            $this->forge->addKey('user_id');
            $this->forge->createTable('auth_groups_users', true);
        }
    }

    public function down(): void
    {
        $this->forge->dropTable('auth_groups_users', true);
    }
}
