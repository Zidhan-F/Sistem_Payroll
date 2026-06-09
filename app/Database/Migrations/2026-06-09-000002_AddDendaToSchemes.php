<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDendaToSchemes extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // Tambah kolom denda ke payroll_schemes
        $dendaCols = [
            'denda_terlambat_per_jam' => 'DECIMAL(15,2) DEFAULT 0',   // Nominal denda per jam keterlambatan
            'denda_alfa_per_hari'     => 'DECIMAL(15,2) DEFAULT 0',   // Nominal denda alfa/tidak masuk per hari
            'early_leave_threshold'   => 'INT DEFAULT 0',             // Menit: jika pulang lebih awal > ini, dihitung alfa
        ];

        foreach ($dendaCols as $col => $def) {
            $db->query("IF NOT EXISTS (SELECT * FROM sys.columns 
                        WHERE object_id = OBJECT_ID('payroll_schemes') AND name = '{$col}')
                        ALTER TABLE payroll_schemes ADD {$col} {$def}");

            $db->query("IF NOT EXISTS (SELECT * FROM sys.columns 
                        WHERE object_id = OBJECT_ID('payroll_scheme_templates') AND name = '{$col}')
                        ALTER TABLE payroll_scheme_templates ADD {$col} {$def}");
        }

        // Tambah kolom hasil hitungan denda ke attendance_logs
        $attendanceCols = [
            'late_minutes'           => 'INT DEFAULT 0',              // Total menit keterlambatan
            'late_penalty_hours'     => 'INT DEFAULT 0',              // Jam keterlambatan (ceiling)
            'denda_terlambat'        => 'DECIMAL(15,2) DEFAULT 0',    // Nominal denda keterlambatan
            'is_early_leave_alfa'    => 'BIT DEFAULT 0',              // 1 jika early leave > threshold (dihitung alfa)
            'denda_alfa'             => 'DECIMAL(15,2) DEFAULT 0',    // Nominal denda alfa/early leave
            'absent_penalty'         => 'DECIMAL(15,2) DEFAULT 0',    // Total denda absen (alfa + early leave)
        ];

        foreach ($attendanceCols as $col => $def) {
            $db->query("IF NOT EXISTS (SELECT * FROM sys.columns 
                        WHERE object_id = OBJECT_ID('attendance_logs') AND name = '{$col}')
                        ALTER TABLE attendance_logs ADD {$col} {$def}");
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();

        $dendaCols = ['denda_terlambat_per_jam', 'denda_alfa_per_hari', 'early_leave_threshold'];
        foreach ($dendaCols as $col) {
            $db->query("IF EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('payroll_schemes') AND name = '{$col}')
                        ALTER TABLE payroll_schemes DROP COLUMN {$col}");
            $db->query("IF EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('payroll_scheme_templates') AND name = '{$col}')
                        ALTER TABLE payroll_scheme_templates DROP COLUMN {$col}");
        }

        $attendanceCols = ['late_minutes', 'late_penalty_hours', 'denda_terlambat', 'is_early_leave_alfa', 'denda_alfa', 'absent_penalty'];
        foreach ($attendanceCols as $col) {
            $db->query("IF EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('attendance_logs') AND name = '{$col}')
                        ALTER TABLE attendance_logs DROP COLUMN {$col}");
        }
    }
}
