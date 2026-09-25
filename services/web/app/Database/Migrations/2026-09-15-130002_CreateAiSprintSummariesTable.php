<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAiSprintSummariesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'sprint_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'summary_text' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'health_score' => [
                'type'       => 'TINYINT',
                'constraint' => 3,
                'unsigned'   => true,
                'null'       => true,
            ],
            'risk_flags' => [
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
        $this->forge->addKey('sprint_id');
        $this->forge->addForeignKey('sprint_id', 'sprints', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('ai_sprint_summaries', true);
    }

    public function down()
    {
        $this->forge->dropTable('ai_sprint_summaries', true);
    }
}
