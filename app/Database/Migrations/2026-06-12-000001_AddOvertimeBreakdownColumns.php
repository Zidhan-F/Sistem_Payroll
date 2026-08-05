<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddOvertimeBreakdownColumns extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // Add breakdown columns to payroll_attendance
        if ($db->tableExists('payroll_attendance')) {
            $atts = [];
            if (!$db->fieldExists('jam_lembur_hari_biasa', 'payroll_attendance')) {
                $atts['jam_lembur_hari_biasa'] = [
                    'type'       => 'DECIMAL',
                    'constraint' => '10,2',
                    'default'    => 0,
                    'null'       => true,
                ];
            }
            if (!$db->fieldExists('jam_lembur_hari_libur', 'payroll_attendance')) {
                $atts['jam_lembur_hari_libur'] = [
                    'type'       => 'DECIMAL',
                    'constraint' => '10,2',
                    'default'    => 0,
                    'null'       => true,
                ];
            }
            if (!empty($atts)) {
                $this->forge->addColumn('payroll_attendance', $atts);
            }
        }

        // Add breakdown columns to payroll_final
        if ($db->tableExists('payroll_final')) {
            $finals = [];
            if (!$db->fieldExists('jam_lembur_biasa', 'payroll_final')) {
                $finals['jam_lembur_biasa'] = [
                    'type'       => 'DECIMAL',
                    'constraint' => '10,2',
                    'default'    => 0,
                    'null'       => true,
                ];
            }
            if (!$db->fieldExists('jam_lembur_libur', 'payroll_final')) {
                $finals['jam_lembur_libur'] = [
                    'type'       => 'DECIMAL',
                    'constraint' => '10,2',
                    'default'    => 0,
                    'null'       => true,
                ];
            }
            if (!empty($finals)) {
                $this->forge->addColumn('payroll_final', $finals);
            }
        }
    }

    public function down()
    {
        $this->forge->dropColumn('payroll_attendance', 'jam_lembur_hari_biasa');
        $this->forge->dropColumn('payroll_attendance', 'jam_lembur_hari_libur');
        $this->forge->dropColumn('payroll_final', 'jam_lembur_biasa');
        $this->forge->dropColumn('payroll_final', 'jam_lembur_libur');
    }
}
