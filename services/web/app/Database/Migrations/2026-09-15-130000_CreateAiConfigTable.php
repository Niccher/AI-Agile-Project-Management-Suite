<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAiConfigTable extends Migration
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
            'default_model' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'default'    => 'mistral-7b',
            ],
            'n_gpu_layers' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'n_threads' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 4,
            ],
            'n_ctx' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 4096,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('ai_config', true);

        // Seed initial row
        $this->db->table('ai_config')->insert([
            'id'            => 1,
            'default_model' => 'mistral-7b',
            'n_gpu_layers'  => 0,
            'n_threads'     => 4,
            'n_ctx'         => 4096,
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('ai_config', true);
    }
}
