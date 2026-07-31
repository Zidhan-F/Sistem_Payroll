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

        if ($db->DBDriver === 'SQLSRV') {
            foreach ($renames as $oldName => $newName) {
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

            $db->query("IF NOT EXISTS (SELECT * FROM sys.columns 
                        WHERE object_id = OBJECT_ID('attendance_logs') AND name = 'early_leave_minutes')
                        ALTER TABLE attendance_logs ADD early_leave_minutes INT DEFAULT 0");
        } else {
            // MySQL / Universal Forge implementation
            $colTypes = [
                'tanggal'    => ['name' => 'log_date', 'type' => 'DATE', 'null' => true],
                'jam_masuk'  => ['name' => 'check_in', 'type' => 'TIME', 'null' => true],
                'jam_keluar' => ['name' => 'check_out', 'type' => 'TIME', 'null' => true],
                'keterangan' => ['name' => 'notes', 'type' => 'TEXT', 'null' => true],
            ];
            foreach ($renames as $oldName => $newName) {
                if ($db->fieldExists($oldName, 'attendance_logs') && !$db->fieldExists($newName, 'attendance_logs')) {
                    $this->forge->modifyColumn('attendance_logs', [
                        $oldName => $colTypes[$oldName] ?? ['name' => $newName, 'type' => 'VARCHAR', 'constraint' => 255, 'null' => true]
                    ]);
                }
            }

            if ($db->fieldExists('status', 'attendance_logs')) {
                $this->forge->dropColumn('attendance_logs', 'status');
            }

            if (!$db->fieldExists('early_leave_minutes', 'attendance_logs')) {
                $this->forge->addColumn('attendance_logs', [
                    'early_leave_minutes' => ['type' => 'INT', 'default' => 0, 'null' => true]
                ]);
            }
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();

        $renames = [
            'log_date'    => 'tanggal',
            'check_in'    => 'jam_masuk',
            'check_out'   => 'jam_keluar',
            'notes'       => 'keterangan',
        ];

        if ($db->DBDriver === 'SQLSRV') {
            foreach ($renames as $oldName => $newName) {
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

            $db->query("IF NOT EXISTS (SELECT * FROM sys.columns 
                        WHERE object_id = OBJECT_ID('attendance_logs') AND name = 'status')
                        ALTER TABLE attendance_logs ADD status NVARCHAR(20) DEFAULT 'Hadir'");

            $constraint = $db->query("SELECT dc.name 
                                    FROM sys.default_constraints dc
                                    JOIN sys.columns c ON dc.parent_column_id = c.column_id AND dc.parent_object_id = c.object_id
                                    WHERE c.object_id = OBJECT_ID('attendance_logs') 
                                    AND c.name = 'early_leave_minutes'")->getRow();
            
            if ($constraint) {
                $db->query("ALTER TABLE attendance_logs DROP CONSTRAINT {$constraint->name}");
            }

            $db->query("IF EXISTS (SELECT * FROM sys.columns 
                        WHERE object_id = OBJECT_ID('attendance_logs') AND name = 'early_leave_minutes')
                        ALTER TABLE attendance_logs DROP COLUMN early_leave_minutes");
        } else {
            $colTypes = [
                'log_date'  => ['name' => 'tanggal', 'type' => 'DATE', 'null' => true],
                'check_in'  => ['name' => 'jam_masuk', 'type' => 'TIME', 'null' => true],
                'check_out' => ['name' => 'jam_keluar', 'type' => 'TIME', 'null' => true],
                'notes'     => ['name' => 'keterangan', 'type' => 'TEXT', 'null' => true],
            ];
            foreach ($renames as $oldName => $newName) {
                if ($db->fieldExists($oldName, 'attendance_logs') && !$db->fieldExists($newName, 'attendance_logs')) {
                    $this->forge->modifyColumn('attendance_logs', [
                        $oldName => $colTypes[$oldName] ?? ['name' => $newName, 'type' => 'VARCHAR', 'constraint' => 255, 'null' => true]
                    ]);
                }
            }

            if (!$db->fieldExists('status', 'attendance_logs')) {
                $this->forge->addColumn('attendance_logs', [
                    'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'Hadir', 'null' => true]
                ]);
            }

            if ($db->fieldExists('early_leave_minutes', 'attendance_logs')) {
                $this->forge->dropColumn('attendance_logs', 'early_leave_minutes');
            }
        }
    }
}
