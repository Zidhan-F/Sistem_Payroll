<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFieldsToUsersTable extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        $fieldsToAdd = [];

        if (!$db->fieldExists('full_name', 'users')) {
            $fieldsToAdd['full_name'] = [
                'type'       => 'VARCHAR',
                'constraint' => '200',
                'null'       => true,
            ];
        }

        if (!$db->fieldExists('is_active', 'users')) {
            $fieldsToAdd['is_active'] = [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ];
        }

        if (!$db->fieldExists('created_at', 'users')) {
            $fieldsToAdd['created_at'] = [
                'type' => 'DATETIME',
                'null' => true,
            ];
        }

        if (!$db->fieldExists('updated_at', 'users')) {
            $fieldsToAdd['updated_at'] = [
                'type' => 'DATETIME',
                'null' => true,
            ];
        }

        if (!empty($fieldsToAdd)) {
            $this->forge->addColumn('users', $fieldsToAdd);
        }

        // Set default is_active to 1 for existing users if any
        if ($db->fieldExists('is_active', 'users')) {
            $db->query("UPDATE users SET is_active = 1 WHERE is_active IS NULL");
        }
    }

    public function down()
    {
        $fieldsToDrop = ['full_name', 'is_active', 'created_at', 'updated_at'];
        foreach ($fieldsToDrop as $field) {
            if ($this->db->fieldExists($field, 'users')) {
                $this->forge->dropColumn('users', $field);
            }
        }
    }
}
