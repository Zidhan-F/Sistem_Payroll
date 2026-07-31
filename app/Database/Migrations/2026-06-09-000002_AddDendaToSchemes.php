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

        $attendanceCols = [
            'late_minutes'        => 'INT DEFAULT 0',              // Total menit keterlambatan
            'late_penalty_hours'  => 'INT DEFAULT 0',              // Jam keterlambatan (ceiling)
            'denda_terlambat'     => 'DECIMAL(15,2) DEFAULT 0',    // Nominal denda keterlambatan
            'is_early_leave_alfa' => 'BIT DEFAULT 0',              // 1 jika early leave > threshold (dihitung alfa)
            'denda_alfa'          => 'DECIMAL(15,2) DEFAULT 0',    // Nominal denda alfa/early leave
            'absent_penalty'      => 'DECIMAL(15,2) DEFAULT 0',    // Total denda absen (alfa + early leave)
        ];

        if ($db->DBDriver === 'SQLSRV') {
            foreach ($dendaCols as $col => $def) {
                $db->query("IF NOT EXISTS (SELECT * FROM sys.columns 
                            WHERE object_id = OBJECT_ID('payroll_schemes') AND name = '{$col}')
                            ALTER TABLE payroll_schemes ADD {$col} {$def}");

                $db->query("IF NOT EXISTS (SELECT * FROM sys.columns 
                            WHERE object_id = OBJECT_ID('payroll_scheme_templates') AND name = '{$col}')
                            ALTER TABLE payroll_scheme_templates ADD {$col} {$def}");
            }

            foreach ($attendanceCols as $col => $def) {
                $db->query("IF NOT EXISTS (SELECT * FROM sys.columns 
                            WHERE object_id = OBJECT_ID('attendance_logs') AND name = '{$col}')
                            ALTER TABLE attendance_logs ADD {$col} {$def}");
            }
        } else {
            // MySQL / Universal Forge implementation
            $schemeFields = [
                'denda_terlambat_per_jam' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0.00, 'null' => true],
                'denda_alfa_per_hari'     => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0.00, 'null' => true],
                'early_leave_threshold'   => ['type' => 'INT', 'default' => 0, 'null' => true],
            ];
            foreach ($schemeFields as $col => $attr) {
                if (!$db->fieldExists($col, 'payroll_schemes')) {
                    $this->forge->addColumn('payroll_schemes', [$col => $attr]);
                }
                if (!$db->fieldExists($col, 'payroll_scheme_templates')) {
                    $this->forge->addColumn('payroll_scheme_templates', [$col => $attr]);
                }
            }

            $attFields = [
                'late_minutes'        => ['type' => 'INT', 'default' => 0, 'null' => true],
                'late_penalty_hours'  => ['type' => 'INT', 'default' => 0, 'null' => true],
                'denda_terlambat'     => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0.00, 'null' => true],
                'is_early_leave_alfa' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'null' => true],
                'denda_alfa'          => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0.00, 'null' => true],
                'absent_penalty'      => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0.00, 'null' => true],
            ];
            foreach ($attFields as $col => $attr) {
                if (!$db->fieldExists($col, 'attendance_logs')) {
                    $this->forge->addColumn('attendance_logs', [$col => $attr]);
                }
            }
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();

        $dendaCols = ['denda_terlambat_per_jam', 'denda_alfa_per_hari', 'early_leave_threshold'];
        $attendanceCols = ['late_minutes', 'late_penalty_hours', 'denda_terlambat', 'is_early_leave_alfa', 'denda_alfa', 'absent_penalty'];

        if ($db->DBDriver === 'SQLSRV') {
            foreach ($dendaCols as $col) {
                $c1 = $db->query("SELECT dc.name FROM sys.default_constraints dc JOIN sys.columns c ON dc.parent_object_id = c.object_id AND dc.parent_column_id = c.column_id WHERE c.object_id = OBJECT_ID('payroll_schemes') AND c.name = '{$col}'")->getRow();
                if ($c1) { $db->query("ALTER TABLE payroll_schemes DROP CONSTRAINT {$c1->name}"); }
                $db->query("IF EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('payroll_schemes') AND name = '{$col}') ALTER TABLE payroll_schemes DROP COLUMN {$col}");

                $c2 = $db->query("SELECT dc.name FROM sys.default_constraints dc JOIN sys.columns c ON dc.parent_object_id = c.object_id AND dc.parent_column_id = c.column_id WHERE c.object_id = OBJECT_ID('payroll_scheme_templates') AND c.name = '{$col}'")->getRow();
                if ($c2) { $db->query("ALTER TABLE payroll_scheme_templates DROP CONSTRAINT {$c2->name}"); }
                $db->query("IF EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('payroll_scheme_templates') AND name = '{$col}') ALTER TABLE payroll_scheme_templates DROP COLUMN {$col}");
            }
            foreach ($attendanceCols as $col) {
                $c3 = $db->query("SELECT dc.name FROM sys.default_constraints dc JOIN sys.columns c ON dc.parent_object_id = c.object_id AND dc.parent_column_id = c.column_id WHERE c.object_id = OBJECT_ID('attendance_logs') AND c.name = '{$col}'")->getRow();
                if ($c3) { $db->query("ALTER TABLE attendance_logs DROP CONSTRAINT {$c3->name}"); }
                $db->query("IF EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('attendance_logs') AND name = '{$col}') ALTER TABLE attendance_logs DROP COLUMN {$col}");
            }
        } else {
            foreach ($dendaCols as $col) {
                if ($db->fieldExists($col, 'payroll_schemes')) {
                    $this->forge->dropColumn('payroll_schemes', $col);
                }
                if ($db->fieldExists($col, 'payroll_scheme_templates')) {
                    $this->forge->dropColumn('payroll_scheme_templates', $col);
                }
            }
            foreach ($attendanceCols as $col) {
                if ($db->fieldExists($col, 'attendance_logs')) {
                    $this->forge->dropColumn('attendance_logs', $col);
                }
            }
        }
    }
}
