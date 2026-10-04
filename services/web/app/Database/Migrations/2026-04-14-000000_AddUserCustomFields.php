<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUserCustomFields extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('users')) {
            $fields = [];

            if (!$this->db->fieldExists('first_name', 'users')) {
                $fields['first_name'] = [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                    'after'      => 'username',
                ];
            }

            if (!$this->db->fieldExists('last_name', 'users')) {
                $fields['last_name'] = [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                    'after'      => 'first_name',
                ];
            }

            if (!$this->db->fieldExists('avatar', 'users')) {
                $fields['avatar'] = [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ];
            }

            if (!$this->db->fieldExists('timezone', 'users')) {
                $fields['timezone'] = [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'default'    => 'Africa/Nairobi',
                    'null'       => true,
                ];
            }

            if (!$this->db->fieldExists('date_format', 'users')) {
                $fields['date_format'] = [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'default'    => 'YYYY-MM-DD',
                    'null'       => true,
                ];
            }

            if (!$this->db->fieldExists('preferences', 'users')) {
                $fields['preferences'] = [
                    'type' => 'TEXT',
                    'null' => true,
                ];
            }

            if (!empty($fields)) {
                $this->forge->addColumn('users', $fields);
            }
        }
    }

    public function down()
    {
        // Keep user columns intact
    }
}
