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
        // We cannot reliably revert to NOT NULL without knowing a safe default or removing data,
        // so leaving it as is or doing a best effort.
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
