<?php

namespace CodeIgniter\Shield\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuthRememberTokensTable extends Migration
{
    public function up(): void
    {
        if (!$this->db->tableExists('auth_remember_tokens')) {
            $this->forge->addField([
                'id'         => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'selector'   => ['type' => 'varchar', 'constraint' => 255],
                'hashedValidator' => ['type' => 'varchar', 'constraint' => 255],
                'user_id'    => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
                'expires'    => ['type' => 'datetime'],
                'created_at' => ['type' => 'datetime', 'null' => true],
                'updated_at' => ['type' => 'datetime', 'null' => true],
            ]);
            $this->forge->addPrimaryKey('id');
            $this->forge->addUniqueKey('selector');
            $this->forge->addKey('user_id');
            $this->forge->createTable('auth_remember_tokens', true);
        }
    }

    public function down(): void
    {
        $this->forge->dropTable('auth_remember_tokens', true);
    }
}
