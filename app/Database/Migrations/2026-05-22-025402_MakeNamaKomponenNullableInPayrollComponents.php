<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MakeNamaKomponenNullableInPayrollComponents extends Migration
{
    public function up()
    {
        $fields = [
            'nama_komponen' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => true,
            ],
        ];
        $this->forge->modifyColumn('payroll_components', $fields);
    }

    public function down()
    {
        $fields = [
            'nama_komponen' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => false,
            ],
        ];
        $this->forge->modifyColumn('payroll_components', $fields);
    }
}
