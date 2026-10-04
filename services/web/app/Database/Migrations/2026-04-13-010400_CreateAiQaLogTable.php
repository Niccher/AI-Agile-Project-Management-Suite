<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAiQaLogTable extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('ai_qa_log')) {
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
                    'null'       => true,
                ],
                'tests_generated' => [
                    'type' => 'LONGTEXT',
                    'null' => true,
                ],
                'edge_cases' => [
                    'type' => 'JSON',
                    'null' => true,
                ],
                'model_used' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 64,
                    'null'       => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('task_id');
            $this->forge->createTable('ai_qa_log', true);
        }
    }

    public function down()
    {
        $this->forge->dropTable('ai_qa_log', true);
    }
}
