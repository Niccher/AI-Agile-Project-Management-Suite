<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProjectWikiVersionsTable extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('project_wiki_versions')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'page_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'version' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                ],
                'title' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                ],
                'content' => [
                    'type' => 'LONGTEXT',
                    'null' => true,
                ],
                'change_summary' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'created_by' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);

            $this->forge->addKey('id', true);
            $this->forge->addKey(['page_id', 'version']);
            $this->forge->addForeignKey('page_id', 'project_wiki_pages', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('project_wiki_versions', true);
        }
    }

    public function down()
    {
        $this->forge->dropTable('project_wiki_versions', true);
    }
}
