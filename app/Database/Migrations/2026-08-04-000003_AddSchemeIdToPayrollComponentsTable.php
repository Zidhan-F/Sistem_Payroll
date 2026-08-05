<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSchemeIdToPayrollComponentsTable extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        if (!$db->fieldExists('scheme_id', 'payroll_components')) {
            $this->forge->addColumn('payroll_components', [
                'scheme_id' => [
                    'type' => 'INT',
                    'null' => true,
                ]
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('scheme_id', 'payroll_components')) {
            $this->forge->dropColumn('payroll_components', 'scheme_id');
        }
    }
}
