<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddOvertimeBreakdownColumns extends Migration
{
    public function up()
    {
        // Add breakdown columns to payroll_attendance
        $this->forge->addColumn('payroll_attendance', [
            'jam_lembur_hari_biasa' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0,
                'null'       => true,
                'after'      => 'jam_lembur',
            ],
            'jam_lembur_hari_libur' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0,
                'null'       => true,
                'after'      => 'jam_lembur_hari_biasa',
            ],
        ]);

        // Add breakdown columns to payroll_final
        $this->forge->addColumn('payroll_final', [
            'jam_lembur_biasa' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0,
                'null'       => true,
                'after'      => 'jam_lembur',
            ],
            'jam_lembur_libur' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0,
                'null'       => true,
                'after'      => 'jam_lembur_biasa',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('payroll_attendance', 'jam_lembur_hari_biasa');
        $this->forge->dropColumn('payroll_attendance', 'jam_lembur_hari_libur');
        $this->forge->dropColumn('payroll_final', 'jam_lembur_biasa');
        $this->forge->dropColumn('payroll_final', 'jam_lembur_libur');
    }
}
