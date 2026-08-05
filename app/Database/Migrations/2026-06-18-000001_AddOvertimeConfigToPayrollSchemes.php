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

        if ($db->DBDriver === 'SQLSRV') {
            foreach ($columns as $col => $def) {
                $db->query("IF OBJECT_ID('payroll_schemes') IS NOT NULL AND NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('payroll_schemes') AND name = '{$col}')
                            ALTER TABLE payroll_schemes ADD {$col} {$def}");
            }
        } else {
            $fields = [
                'overtime_type'   => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'standard', 'null' => false],
                'lumpsum_subtype' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
                'lumpsum_nominal' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0.00, 'null' => false],
            ];
            foreach ($fields as $col => $attr) {
                if (!$db->fieldExists($col, 'payroll_schemes')) {
                    $this->forge->addColumn('payroll_schemes', [$col => $attr]);
                }
            }
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();
        $columns = ['overtime_type', 'lumpsum_subtype', 'lumpsum_nominal'];
        if ($db->DBDriver === 'SQLSRV') {
            foreach ($columns as $col) {
                $result = $db->query("SELECT 1 FROM sys.columns WHERE object_id = OBJECT_ID('payroll_schemes') AND name = '{$col}'")->getRow();
                if ($result) {
                    $constraint = $db->query("
                        SELECT dc.name 
                        FROM sys.default_constraints dc
                        JOIN sys.columns c ON dc.parent_object_id = c.object_id AND dc.parent_column_id = c.column_id
                        WHERE c.object_id = OBJECT_ID('payroll_schemes') AND c.name = '{$col}'
                    ")->getRow();
                    if ($constraint) {
                        $db->query("ALTER TABLE payroll_schemes DROP CONSTRAINT {$constraint->name}");
                    }
                    $db->query("ALTER TABLE payroll_schemes DROP COLUMN {$col}");
                }
            }
        } else {
            foreach ($columns as $col) {
                if ($db->fieldExists($col, 'payroll_schemes')) {
                    $this->forge->dropColumn('payroll_schemes', $col);
                }
            }
        }
    }
}
