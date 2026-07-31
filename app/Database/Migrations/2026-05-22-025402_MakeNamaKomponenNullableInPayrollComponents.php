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
        $db = \Config\Database::connect();
        $db->query("UPDATE payroll_components SET nama_komponen = '' WHERE nama_komponen IS NULL");
        $fields = [
            'nama_komponen' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => false,
                'default' => '',
            ],
        ];
        $this->forge->modifyColumn('payroll_components', $fields);
    }
}
