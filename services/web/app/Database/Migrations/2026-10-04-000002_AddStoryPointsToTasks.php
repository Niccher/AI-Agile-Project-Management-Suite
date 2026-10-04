<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddStoryPointsToTasks extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('story_points', 'tasks')) {
            $this->forge->addColumn('tasks', [
                'story_points' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'null'       => true,
                    'default'    => null,
                    'after'      => 'priority',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('story_points', 'tasks')) {
            $this->forge->dropColumn('tasks', 'story_points');
        }
    }
}
