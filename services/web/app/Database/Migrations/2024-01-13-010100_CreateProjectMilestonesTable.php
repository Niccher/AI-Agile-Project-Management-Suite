<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProjectMilestonesTable extends Migration
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
            'project_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'comment'    => 'Associated project',
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'comment'    => 'Milestone name',
            ],
            'description' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'Milestone description',
            ],
            'due_date' => [
                'type'    => 'DATE',
                'null'    => true,
                'comment' => 'Milestone due date',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'in_progress', 'completed'],
                'default'    => 'pending',
                'comment'    => 'Milestone status',
            ],
            'progress' => [
                'type'       => 'TINYINT',
                'constraint' => 3,
                'default'    => 0,
                'comment'    => 'Milestone completion percentage (0-100)',
            ],
            'completed_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'When milestone was completed',
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
        
        // Add foreign key constraint
        $this->forge->addForeignKey('project_id', 'projects', 'id', 'CASCADE', 'CASCADE');
        
        $this->forge->createTable('project_milestones');
    }

    public function down()
    {
        $this->forge->dropTable('project_milestones');
    }
}
