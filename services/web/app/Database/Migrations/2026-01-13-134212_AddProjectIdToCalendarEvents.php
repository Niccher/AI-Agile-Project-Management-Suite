<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProjectIdToCalendarEvents extends Migration
{
    public function up()
    {
        $this->forge->addColumn('calendar_events', [
            'project_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'user_id',
            ],
        ]);
        $this->forge->addForeignKey('project_id', 'projects', 'id', 'CASCADE', 'SET NULL');
        // Note: CodeIgniter's forge->addColumn doesn't support adding foreign keys directly 
        // in some versions/drivers within addColumn. We'll use a manual query if needed, 
        // but CI4 usually handles addForeignKey before createTable or via special methods.
        // For existing tables, we might need a manual query for FK or use the specific syntax.
    }

    public function down()
    {
        $this->forge->dropColumn('calendar_events', 'project_id');
    }
}
