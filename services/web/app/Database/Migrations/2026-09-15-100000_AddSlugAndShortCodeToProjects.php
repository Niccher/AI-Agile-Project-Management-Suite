<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSlugAndShortCodeToProjects extends Migration
{
    public function up()
    {
        $fields = [
            'slug' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'Hybrid SEO slug (e.g. mobile-app-redesign-8f9c1b)',
            ],
            'short_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 16,
                'null'       => true,
                'comment'    => 'Unique short identifier (e.g. 8f9c1b)',
            ],
        ];

        $this->forge->addColumn('projects', $fields);

        // Populate existing projects with unique hybrid slugs
        $db = \Config\Database::connect();
        $projects = $db->table('projects')->select('id, name')->get()->getResultArray();

        helper('url');

        foreach ($projects as $proj) {
            $name = !empty($proj['name']) ? $proj['name'] : 'project';
            $baseSlug = url_title(strtolower(trim($name)), '-', true);
            if (empty($baseSlug)) {
                $baseSlug = 'project';
            }

            try {
                $shortCode = bin2hex(random_bytes(3)); // 6 hex characters
            } catch (\Exception $e) {
                $shortCode = substr(md5($proj['id'] . microtime()), 0, 6);
            }

            $hybridSlug = $baseSlug . '-' . $shortCode;

            $db->table('projects')
                ->where('id', $proj['id'])
                ->update([
                    'slug'       => $hybridSlug,
                    'short_code' => $shortCode,
                ]);
        }

        // Add indices for fast lookup
        $db->query("ALTER TABLE `projects` ADD INDEX `idx_projects_slug` (`slug`)");
        $db->query("ALTER TABLE `projects` ADD INDEX `idx_projects_short_code` (`short_code`)");
    }

    public function down()
    {
        $db = \Config\Database::connect();
        // Drop indices if they exist
        try {
            $db->query("ALTER TABLE `projects` DROP INDEX `idx_projects_slug`");
        } catch (\Exception $e) {}
        try {
            $db->query("ALTER TABLE `projects` DROP INDEX `idx_projects_short_code`");
        } catch (\Exception $e) {}

        $this->forge->dropColumn('projects', ['slug', 'short_code']);
    }
}
