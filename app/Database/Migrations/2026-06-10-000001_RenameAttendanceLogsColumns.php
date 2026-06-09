<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RenameAttendanceLogsColumns extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // Rename columns to match the code expectations
        $renames = [
            'tanggal'     => 'log_date',
            'jam_masuk'   => 'check_in',
            'jam_keluar'  => 'check_out',
            'keterangan'  => 'notes',
        ];

        foreach ($renames as $oldName => $newName) {
            // Check if old column exists and new column doesn't
            $oldExists = $db->query("SELECT COUNT(*) as cnt FROM sys.columns 
                                    WHERE object_id = OBJECT_ID('attendance_logs') 
                                    AND name = '{$oldName}'")->getRow()->cnt;
            
            $newExists = $db->query("SELECT COUNT(*) as cnt FROM sys.columns 
                                    WHERE object_id = OBJECT_ID('attendance_logs') 
                                    AND name = '{$newName}'")->getRow()->cnt;

            if ($oldExists > 0 && $newExists == 0) {
                $db->query("EXEC sp_rename 'attendance_logs.{$oldName}', '{$newName}', 'COLUMN'");
            }
        }

        // Drop status column if it exists (not needed anymore)
        $statusExists = $db->query("SELECT COUNT(*) as cnt FROM sys.columns 
                                    WHERE object_id = OBJECT_ID('attendance_logs') 
                                    AND name = 'status'")->getRow()->cnt;
        
        if ($statusExists > 0) {
            // Drop default constraint first if exists
            $constraint = $db->query("SELECT dc.name 
                                    FROM sys.default_constraints dc
                                    JOIN sys.columns c ON dc.parent_column_id = c.column_id
                                    WHERE c.object_id = OBJECT_ID('attendance_logs') 
                                    AND c.name = 'status'")->getRow();
            
            if ($constraint) {
                $db->query("ALTER TABLE attendance_logs DROP CONSTRAINT {$constraint->name}");
            }
            
            $db->query("ALTER TABLE attendance_logs DROP COLUMN status");
        }

        // Add early_leave_minutes if not exists
        $db->query("IF NOT EXISTS (SELECT * FROM sys.columns 
                    WHERE object_id = OBJECT_ID('attendance_logs') AND name = 'early_leave_minutes')
                    ALTER TABLE attendance_logs ADD early_leave_minutes INT DEFAULT 0");
    }

    public function down()
    {
        $db = \Config\Database::connect();

        // Rename back
        $renames = [
            'log_date'    => 'tanggal',
            'check_in'    => 'jam_masuk',
            'check_out'   => 'jam_keluar',
            'notes'       => 'keterangan',
        ];

        foreach ($renames as $oldName => $newName) {
            $db->query("EXEC sp_rename 'attendance_logs.{$oldName}', '{$newName}', 'COLUMN'");
        }

        // Add back status column
        $db->query("IF NOT EXISTS (SELECT * FROM sys.columns 
                    WHERE object_id = OBJECT_ID('attendance_logs') AND name = 'status')
                    ALTER TABLE attendance_logs ADD status NVARCHAR(20) DEFAULT 'Hadir'");

        // Drop early_leave_minutes
        $db->query("IF EXISTS (SELECT * FROM sys.columns 
                    WHERE object_id = OBJECT_ID('attendance_logs') AND name = 'early_leave_minutes')
                    ALTER TABLE attendance_logs DROP COLUMN early_leave_minutes");
    }
}
