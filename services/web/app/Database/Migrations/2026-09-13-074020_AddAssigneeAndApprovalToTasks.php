<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAssigneeAndApprovalToTasks extends Migration
{
    public function up()
    {
        $fields = [
            'assigned_to' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'assigned_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'approved_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'approved_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'rejected_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'rejected_reason' => [
                'type' => 'TEXT',
                'null' => true,
            ],
        ];

        $this->forge->addColumn('tasks', $fields);

        // Update the ENUM status field to include 'approved' and 'rejected'
        $this->db->query("ALTER TABLE `tasks` MODIFY `status` ENUM('todo', 'in_progress', 'review', 'done', 'approved', 'rejected') NOT NULL DEFAULT 'todo'");
    }

    public function down()
    {
        $this->forge->dropColumn('tasks', 'assigned_to, assigned_by, approved_by, approved_at, rejected_by, rejected_reason');
        $this->db->query("ALTER TABLE `tasks` MODIFY `status` ENUM('todo', 'in_progress', 'review', 'done') NOT NULL DEFAULT 'todo'");
    }
}
