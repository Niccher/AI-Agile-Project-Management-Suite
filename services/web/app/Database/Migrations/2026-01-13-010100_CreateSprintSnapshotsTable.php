<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSprintSnapshotsTable extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('sprint_snapshots')) {
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
                'snapshot_date' => [
                    'type' => 'DATE',
                ],
                'remaining_points' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'default'    => 0,
                ],
                'completed_points' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'default'    => 0,
                ],
                'ideal_points' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '8,2',
                    'null'       => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);

            $this->forge->addKey('id', true);
            $this->forge->addKey('sprint_id');
            $this->forge->addKey('snapshot_date');
            $this->forge->addForeignKey('sprint_id', 'sprints', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('sprint_snapshots', true);
        }
    }

    public function down()
    {
        $this->forge->dropTable('sprint_snapshots', true);
    }
}
