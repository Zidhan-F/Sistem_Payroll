<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FixAttendanceLogsDecimalOverflow extends Migration
{
    public function up()
    {
        // Fix DECIMAL(4,1) columns yang overflow — ubah ke DECIMAL(8,2)
        // DECIMAL(4,1) hanya bisa simpan max 999.9
        // Nilai calculated_work_hours bisa lebih besar dari itu jika ada bug input

        $db = \Config\Database::connect();

        $cols = [
            'calculated_work_hours',
            'calculated_overtime_hours',
            'late_hours',
            'early_leave_hours',
        ];

        foreach ($cols as $col) {
            $exists = $db->query("SELECT COUNT(*) as cnt FROM sys.columns 
                                  WHERE object_id = OBJECT_ID('attendance_logs') 
                                  AND name = '{$col}'")->getRow();
            if ($exists && $exists->cnt > 0) {
                // Drop default constraint first if exists
                $constraintQuery = "SELECT dc.name 
                                   FROM sys.default_constraints dc
                                   INNER JOIN sys.columns c ON dc.parent_column_id = c.column_id
                                   WHERE dc.parent_object_id = OBJECT_ID('attendance_logs') 
                                   AND c.name = '{$col}'";
                $constraint = $db->query($constraintQuery)->getRow();
                
                if ($constraint) {
                    $db->query("ALTER TABLE attendance_logs DROP CONSTRAINT {$constraint->name}");
                }
                
                // Now alter the column
                $db->query("ALTER TABLE attendance_logs ALTER COLUMN {$col} DECIMAL(8,2) NULL");
                
                // Add back default constraint
                $db->query("ALTER TABLE attendance_logs ADD CONSTRAINT DF_attendance_logs_{$col} DEFAULT 0.00 FOR {$col}");
            }
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();
        $cols = ['calculated_work_hours', 'calculated_overtime_hours', 'late_hours', 'early_leave_hours'];
        foreach ($cols as $col) {
            $db->query("ALTER TABLE attendance_logs ALTER COLUMN {$col} DECIMAL(4,1) DEFAULT 0.0");
        }
    }
}
