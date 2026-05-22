<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ModifyPayrollComponentsSchema extends Migration
{
    public function up()
    {
        $fields = [
            'nama' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
            'is_persentase' => [
                'type' => 'TINYINT',
                'default' => 0,
            ],
            'jenis_komponen' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => true,
            ],
            'sumber_nilai' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => true,
            ],
            'periode' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => true,
            ],
            'sifat_kompensasi' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => true,
            ],
        ];
        $this->forge->addColumn('payroll_components', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('payroll_components', ['nama', 'is_persentase', 'jenis_komponen', 'sumber_nilai', 'periode', 'sifat_kompensasi']);
    }
}
