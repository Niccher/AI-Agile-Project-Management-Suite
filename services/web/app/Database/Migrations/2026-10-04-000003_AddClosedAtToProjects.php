<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddClosedAtToProjects extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('closed_at', 'projects')) {
            $this->forge->addColumn('projects', [
                'closed_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                    'after' => 'is_archived',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('closed_at', 'projects')) {
            $this->forge->dropColumn('projects', 'closed_at');
        }
    }
}
