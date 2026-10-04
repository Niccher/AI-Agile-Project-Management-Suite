<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProjectsTable extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('projects')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'user_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '255',
                ],
                'slug' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'short_code' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 16,
                    'null'       => true,
                ],
                'description' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'color' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '7',
                    'default'    => '#727cf5',
                ],
                'icon' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '50',
                    'default'    => 'mdi-folder',
                ],
                'status' => [
                    'type'       => 'ENUM',
                    'constraint' => ['planning', 'in_progress', 'on_hold', 'completed', 'cancelled'],
                    'default'    => 'planning',
                ],
                'priority' => [
                    'type'       => 'ENUM',
                    'constraint' => ['low', 'medium', 'high', 'urgent'],
                    'default'    => 'medium',
                ],
                'progress' => [
                    'type'       => 'INT',
                    'constraint' => 3,
                    'default'    => 0,
                ],
                'start_date' => [
                    'type' => 'DATE',
                    'null' => true,
                ],
                'due_date' => [
                    'type' => 'DATE',
                    'null' => true,
                ],
                'closed_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'budget' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '10,2',
                    'null'       => true,
                ],
                'is_archived' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 0,
                ],
                'categories' => [
                    'type' => 'JSON',
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
                'deleted_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);

            $this->forge->addKey('id', true);
            $this->forge->addKey('user_id');
            $this->forge->addKey('status');
            $this->forge->addKey('slug');
            $this->forge->addKey('short_code');
            $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('projects', true);
        }
    }

    public function down()
    {
        $this->forge->dropTable('projects', true);
    }
}
