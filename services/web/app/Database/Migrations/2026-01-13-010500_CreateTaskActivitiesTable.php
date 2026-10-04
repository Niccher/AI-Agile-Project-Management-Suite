<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTaskActivitiesTable extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('task_activities')) {
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
                    'null'       => true,
                ],
                'activity_type' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                ],
                'details' => [
                    'type' => 'JSON',
                    'null' => true,
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
            $this->forge->createTable('task_activities', true);
        }
    }

    public function down()
    {
        $this->forge->dropTable('task_activities', true);
    }
}
