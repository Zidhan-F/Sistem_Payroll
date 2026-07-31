<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MakeClientIdNullableInPayrollComponents extends Migration
{
    public function up()
    {
        $fields = [
            'client_id' => [
                'type' => 'INT',
                'null' => true,
            ],
        ];
        $this->forge->modifyColumn('payroll_components', $fields);
    }

    public function down()
    {
        $db = \Config\Database::connect();
        $db->query("UPDATE payroll_components SET client_id = 0 WHERE client_id IS NULL");
        $fields = [
            'client_id' => [
                'type' => 'INT',
                'null' => false,
                'default' => 0,
            ],
        ];
        $this->forge->modifyColumn('payroll_components', $fields);
    }
}
