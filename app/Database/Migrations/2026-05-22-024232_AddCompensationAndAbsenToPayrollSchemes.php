<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCompensationAndAbsenToPayrollSchemes extends Migration
{
    public function up()
    {
        $fields = [
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
        $this->forge->addColumn('payroll_schemes', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('payroll_schemes', ['compensation_scheme_id', 'prorate', 'absen_tidak_potong', 'nominal_potongan']);
    }
}
