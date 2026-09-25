<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAiTimeReportsAndQaLogTable extends Migration
{
    public function up()
    {
        // 1. Create ai_time_reports table
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
            'period_from' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'period_to' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'report_text' => [
                'type' => 'LONGTEXT',
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
        $this->forge->addKey(['user_id', 'project_id']);
        $this->forge->createTable('ai_time_reports', true);

        // 2. Create ai_qa_log table
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'question' => [
                'type' => 'TEXT',
            ],
            'context_json' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'answer' => [
                'type' => 'LONGTEXT',
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
        $this->forge->createTable('ai_qa_log', true);
    }

    public function down()
    {
        $this->forge->dropTable('ai_qa_log', true);
        $this->forge->dropTable('ai_time_reports', true);
    }
}
