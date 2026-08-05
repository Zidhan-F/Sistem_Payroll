<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddStatusBackToAttendanceLogs extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        if (!$db->tableExists('attendance_logs')) {
            return;
        }

        // Add back status column if it does not exist
        if ($db->DBDriver === 'SQLSRV') {
            $db->query("IF NOT EXISTS (SELECT * FROM sys.columns 
                        WHERE object_id = OBJECT_ID('attendance_logs') AND name = 'status')
                        ALTER TABLE attendance_logs ADD status NVARCHAR(20) DEFAULT 'Hadir'");
        } else {
            if (!$db->fieldExists('status', 'attendance_logs')) {
                $this->forge->addColumn('attendance_logs', [
                    'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'Hadir', 'null' => true]
                ]);
            }
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();

        if ($db->DBDriver === 'SQLSRV') {
            $statusExists = $db->query("SELECT COUNT(*) as cnt FROM sys.columns 
                                         WHERE object_id = OBJECT_ID('attendance_logs') 
                                         AND name = 'status'")->getRow()->cnt;
            
            if ($statusExists > 0) {
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
        } else {
            if ($db->fieldExists('status', 'attendance_logs')) {
                $this->forge->dropColumn('attendance_logs', 'status');
            }
        }
    }
}
