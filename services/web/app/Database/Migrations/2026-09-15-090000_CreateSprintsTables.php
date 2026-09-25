<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSprintsTables extends Migration
{
    public function up()
    {
        // 1. Create sprints table
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'project_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'goal' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['planning', 'active', 'completed'],
                'default'    => 'planning',
            ],
            'start_date' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'end_date' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'total_points' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'completed_points' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
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
        $this->forge->addKey('project_id');
        $this->forge->addKey('status');
        $this->forge->createTable('sprints', true);

        // 2. Create sprint_snapshots table (for burndown tracking)
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
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['sprint_id', 'snapshot_date']);
        $this->forge->createTable('sprint_snapshots', true);

        // 3. Alter tasks table to add sprint_id and story_points
        $fields = [
            'sprint_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'story_points' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
        ];

        // Check if columns exist before adding
        if (!$this->db->fieldExists('sprint_id', 'tasks')) {
            $this->forge->addColumn('tasks', ['sprint_id' => $fields['sprint_id']]);
        }
        if (!$this->db->fieldExists('story_points', 'tasks')) {
            $this->forge->addColumn('tasks', ['story_points' => $fields['story_points']]);
        }
    }

    public function down()
    {
        $this->forge->dropTable('sprint_snapshots', true);
        $this->forge->dropTable('sprints', true);
        if ($this->db->fieldExists('sprint_id', 'tasks')) {
            $this->forge->dropColumn('tasks', 'sprint_id');
        }
        if ($this->db->fieldExists('story_points', 'tasks')) {
            $this->forge->dropColumn('tasks', 'story_points');
        }
    }
}
