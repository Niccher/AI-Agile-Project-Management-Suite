<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTaskAttachmentsTable extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('task_attachments')) {
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
                'file_name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                ],
                'original_name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                ],
                'mime_type' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                ],
                'file_size' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'file_path' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 500,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);

            $this->forge->addPrimaryKey('id');
            $this->forge->addKey('task_id');
            $this->forge->addKey('user_id');
            $this->forge->addForeignKey('task_id', 'tasks', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('task_attachments', true);
        }
    }

    public function down()
    {
        $this->forge->dropTable('task_attachments', true);
    }
}
