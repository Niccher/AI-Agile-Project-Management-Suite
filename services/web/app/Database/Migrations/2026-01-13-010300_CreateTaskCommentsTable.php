<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTaskCommentsTable extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('task_comments')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'task_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'user_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'body' => [
                    'type' => 'TEXT',
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
            $this->forge->addKey('task_id');
            $this->forge->addKey('user_id');
            $this->forge->addForeignKey('task_id', 'tasks', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('task_comments', true);
        }
    }

    public function down()
    {
        $this->forge->dropTable('task_comments', true);
    }
}
