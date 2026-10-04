<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAiTimeReportsTable extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('ai_time_reports')) {
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
                'period_start' => [
                    'type' => 'DATE',
                ],
                'period_end' => [
                    'type' => 'DATE',
                ],
                'summary_text' => [
                    'type' => 'LONGTEXT',
                    'null' => true,
                ],
                'productivity_score' => [
                    'type'       => 'TINYINT',
                    'constraint' => 3,
                    'unsigned'   => true,
                    'null'       => true,
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
            $this->forge->addKey('user_id');
            $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('ai_time_reports', true);
        }
    }

    public function down()
    {
        $this->forge->dropTable('ai_time_reports', true);
    }
}
