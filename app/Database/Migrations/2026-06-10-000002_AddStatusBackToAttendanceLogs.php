<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddStatusBackToAttendanceLogs extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // Add back status column if it does not exist
        $db->query("IF NOT EXISTS (SELECT * FROM sys.columns 
                    WHERE object_id = OBJECT_ID('attendance_logs') AND name = 'status')
                    ALTER TABLE attendance_logs ADD status NVARCHAR(20) DEFAULT 'Hadir'");
    }

    public function down()
    {
        $db = \Config\Database::connect();

        // Drop status column if exists
        $statusExists = $db->query("SELECT COUNT(*) as cnt FROM sys.columns 
                                     WHERE object_id = OBJECT_ID('attendance_logs') 
                                     AND name = 'status'")->getRow()->cnt;
        
        if ($statusExists > 0) {
            // Drop default constraint first if exists
            $constraint = $db->query("SELECT dc.name 
                                    FROM sys.default_constraints dc
                                    JOIN sys.columns c ON dc.parent_column_id = c.column_id AND dc.parent_object_id = c.object_id
                                    WHERE c.object_id = OBJECT_ID('attendance_logs') 
                                    AND c.name = 'status'")->getRow();
            
            if ($constraint) {
                $db->query("ALTER TABLE attendance_logs DROP CONSTRAINT {$constraint->name}");
            }
            
            $db->query("ALTER TABLE attendance_logs DROP COLUMN status");
        }
    }
}
