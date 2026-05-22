<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCompensationAndAbsenToPayrollSchemes extends Migration
{
    public function up()
    {
        $allFields = [
            'compensation_scheme_id' => [
                'type' => 'INT',
                'null' => true,
            ],
            'prorate' => [
                'type' => 'TINYINT',
                'default' => 0,
            ],
            'absen_tidak_potong' => [
                'type' => 'TINYINT',
                'default' => 0,
            ],
            'nominal_potongan' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0.00,
            ],
        ];
        $fieldsToAdd = [];
        foreach ($allFields as $name => $def) {
            if (!$this->db->fieldExists($name, 'payroll_schemes')) {
                $fieldsToAdd[$name] = $def;
            }
        }
        if (!empty($fieldsToAdd)) {
            $this->forge->addColumn('payroll_schemes', $fieldsToAdd);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('payroll_schemes', ['compensation_scheme_id', 'prorate', 'absen_tidak_potong', 'nominal_potongan']);
    }
}
