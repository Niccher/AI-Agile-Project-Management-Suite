<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserInvitesTable extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('user_invites')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'email' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                ],
                'role' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'default'    => 'user',
                ],
                'token' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 64,
                ],
                'status' => [
                    'type'       => 'ENUM',
                    'constraint' => ['pending', 'accepted', 'revoked'],
                    'default'    => 'pending',
                ],
                'expires_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);

            $this->forge->addPrimaryKey('id');
            $this->forge->addUniqueKey('token');
            $this->forge->addKey('email');
            $this->forge->createTable('user_invites', true);
        }
    }

    public function down()
    {
        $this->forge->dropTable('user_invites', true);
    }
}
