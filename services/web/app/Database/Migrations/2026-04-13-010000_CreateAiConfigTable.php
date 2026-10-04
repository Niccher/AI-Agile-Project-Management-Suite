<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAiConfigTable extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('ai_config')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'key' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 64,
                ],
                'value' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey('key');
            $this->forge->createTable('ai_config', true);
        }
    }

    public function down()
    {
        $this->forge->dropTable('ai_config', true);
    }
}
