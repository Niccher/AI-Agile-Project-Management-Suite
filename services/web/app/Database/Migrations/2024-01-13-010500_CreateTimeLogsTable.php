<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTimeLogsTable extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('time_logs')) {
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
                'project_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'task_name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '255',
                ],
                'start_time' => [
                    'type' => 'DATETIME',
                ],
                'end_time' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'duration' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'comment'    => 'Duration in seconds',
                ],
                'notes' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'is_billable' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 1,
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
            $this->forge->addKey('id', true);
            $this->forge->addKey('user_id');
            $this->forge->addKey('project_id');
            $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('time_logs', true);
        }
    }

    public function down()
    {
        $this->forge->dropTable('time_logs', true);
    }
}
