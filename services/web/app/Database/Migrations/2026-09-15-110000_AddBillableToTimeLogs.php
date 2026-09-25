<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBillableToTimeLogs extends Migration
{
    public function up()
    {
        $fields = [
            'is_billable' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'after'      => 'duration',
            ],
            'hourly_rate' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 50.00,
                'after'      => 'is_billable',
            ],
        ];

        if (! $this->db->fieldExists('is_billable', 'time_logs')) {
            $this->forge->addColumn('time_logs', $fields);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('is_billable', 'time_logs')) {
            $this->forge->dropColumn('time_logs', ['is_billable', 'hourly_rate']);
        }
    }
}
