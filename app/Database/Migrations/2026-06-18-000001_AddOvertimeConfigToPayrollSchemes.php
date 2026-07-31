<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddOvertimeConfigToPayrollSchemes extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        
        $columns = [
            'overtime_type'    => "VARCHAR(20) DEFAULT 'standard' NOT NULL",
            'lumpsum_subtype'  => "VARCHAR(20) NULL",
            'lumpsum_nominal'  => "DECIMAL(15,2) DEFAULT 0 NOT NULL",
        ];

        foreach ($columns as $col => $def) {
            // Check if column already exists (SQL Server)
            $result = $db->query("SELECT 1 FROM sys.columns WHERE object_id = OBJECT_ID('payroll_schemes') AND name = '{$col}'")->getRow();
            if (!$result) {
                $db->query("ALTER TABLE payroll_schemes ADD {$col} {$def}");
            }
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();
        $columns = ['overtime_type', 'lumpsum_subtype', 'lumpsum_nominal'];
        foreach ($columns as $col) {
            $result = $db->query("SELECT 1 FROM sys.columns WHERE object_id = OBJECT_ID('payroll_schemes') AND name = '{$col}'")->getRow();
            if ($result) {
                if ($db->DBDriver === 'SQLSRV') {
                    $constraint = $db->query("
                        SELECT dc.name 
                        FROM sys.default_constraints dc
                        JOIN sys.columns c ON dc.parent_object_id = c.object_id AND dc.parent_column_id = c.column_id
                        WHERE c.object_id = OBJECT_ID('payroll_schemes') AND c.name = '{$col}'
                    ")->getRow();
                    if ($constraint) {
                        $db->query("ALTER TABLE payroll_schemes DROP CONSTRAINT {$constraint->name}");
                    }
                }
                $db->query("ALTER TABLE payroll_schemes DROP COLUMN {$col}");
            }
        }
    }
}
