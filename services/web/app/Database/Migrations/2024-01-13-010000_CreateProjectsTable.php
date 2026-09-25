<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProjectsTable extends Migration
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
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'comment'    => 'Owner of the project',
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'comment'    => 'Project name',
            ],
            'description' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'Project description/goal',
            ],
            'tech_stack' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'JSON array of technologies (PHP, React, MySQL, etc.)',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['planning', 'in_progress', 'testing', 'completed', 'on_hold', 'abandoned'],
                'default'    => 'in_progress',
                'comment'    => 'Current project status',
            ],
            'priority' => [
                'type'       => 'ENUM',
                'constraint' => ['low', 'medium', 'high', 'critical'],
                'default'    => 'medium',
                'comment'    => 'Project priority level',
            ],
            'start_date' => [
                'type'    => 'DATE',
                'null'    => true,
                'comment' => 'Project start date',
            ],
            'due_date' => [
                'type'    => 'DATE',
                'null'    => true,
                'comment' => 'Target completion date',
            ],
            'progress' => [
                'type'       => 'TINYINT',
                'constraint' => 3,
                'default'    => 0,
                'comment'    => 'Project completion percentage (0-100)',
            ],
            'repository_url' => [
                'type'       => 'VARCHAR',
                'constraint' => '500',
                'null'       => true,
                'comment'    => 'GitHub/GitLab/Bitbucket repository URL',
            ],
            'categories' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'JSON array of categories (web_app, api, mobile, learning, portfolio, freelance)',
            ],
            'icon' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
                'default'    => 'fa-project-diagram',
                'comment'    => 'FontAwesome icon class',
            ],
            'color' => [
                'type'       => 'VARCHAR',
                'constraint' => '7',
                'null'       => true,
                'default'    => '#6366f1',
                'comment'    => 'Project color (hex code)',
            ],
            'budget' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => true,
                'comment'    => 'Project budget amount',
            ],
            'is_archived' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'comment'    => 'Whether project is archived (0=active, 1=archived)',
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
        $this->forge->addKey('user_id');
        $this->forge->addKey('status');
        
        // Add foreign key constraint
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        
        $this->forge->createTable('projects');
    }

    public function down()
    {
        $this->forge->dropTable('projects');
    }
}
