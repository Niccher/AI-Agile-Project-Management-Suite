<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddReportsCustomFields extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('reports')) {
            $fields = [];

            if (!$this->db->fieldExists('user_id', 'reports')) {
                $fields['user_id'] = [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'id',
                ];
            }

            if (!$this->db->fieldExists('name', 'reports')) {
                $fields['name'] = [
                    'type'       => 'VARCHAR',
                    'constraint' => '255',
                    'null'       => true,
                ];
            }

            if (!$this->db->fieldExists('type', 'reports')) {
                $fields['type'] = [
                    'type'       => 'VARCHAR',
                    'constraint' => '50',
                    'default'    => 'pdf',
                ];
            }

            if (!$this->db->fieldExists('parameters', 'reports')) {
                $fields['parameters'] = [
                    'type' => 'JSON',
                    'null' => true,
                ];
            }

            if (!$this->db->fieldExists('file_path', 'reports')) {
                $fields['file_path'] = [
                    'type'       => 'VARCHAR',
                    'constraint' => '255',
                    'null'       => true,
                ];
            }

            if (!$this->db->fieldExists('status', 'reports')) {
                $fields['status'] = [
                    'type'       => 'VARCHAR',
                    'constraint' => '20',
                    'default'    => 'completed',
                ];
            }

            if (!empty($fields)) {
                $this->forge->addColumn('reports', $fields);
            }
        }
    }

    public function down()
    {
        // Keep reports columns intact
    }
}
