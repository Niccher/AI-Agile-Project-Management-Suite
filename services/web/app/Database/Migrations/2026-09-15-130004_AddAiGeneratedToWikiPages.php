<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAiGeneratedToWikiPages extends Migration
{
    public function up()
    {
        $fields = [
            'ai_generated' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => false,
            ],
        ];

        if (!$this->db->fieldExists('ai_generated', 'project_wiki_pages')) {
            $this->forge->addColumn('project_wiki_pages', $fields);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('ai_generated', 'project_wiki_pages')) {
            $this->forge->dropColumn('project_wiki_pages', 'ai_generated');
        }
    }
}
