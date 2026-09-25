<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUserSettings extends Migration
{
    public function up()
    {
        // First check if the column exists so it doesn't fail on local where it might exist
        $fields = [];
        
        if (! $this->db->fieldExists('timezone', 'users')) {
            $fields['timezone'] = [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'default'    => 'Africa/Nairobi',
                'null'       => true,
            ];
        }
        
        if (! $this->db->fieldExists('date_format', 'users')) {
            $fields['date_format'] = [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'YYYY-MM-DD',
                'null'       => true,
            ];
        }
        
        if (! $this->db->fieldExists('preferences', 'users')) {
            $fields['preferences'] = [
                'type' => 'TEXT',
                'null' => true,
            ];
        }

        if (! empty($fields)) {
            $this->forge->addColumn('users', $fields);
        }
    }

    public function down()
    {
        $fieldsToDrop = [];
        if ($this->db->fieldExists('timezone', 'users')) $fieldsToDrop[] = 'timezone';
        if ($this->db->fieldExists('date_format', 'users')) $fieldsToDrop[] = 'date_format';
        if ($this->db->fieldExists('preferences', 'users')) $fieldsToDrop[] = 'preferences';
        
        if (! empty($fieldsToDrop)) {
            $this->forge->dropColumn('users', $fieldsToDrop);
        }
    }
}
