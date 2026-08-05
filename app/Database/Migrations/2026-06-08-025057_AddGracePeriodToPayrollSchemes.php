<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddGracePeriodToPayrollSchemes extends Migration
{
    public function up()
    {
        $fields = [
            'grace_period_late' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'grace_period_early' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
        ];
        
        if ($this->db->tableExists('payroll_schemes')) {
            $fieldsToAdd = [];
            foreach ($fields as $col => $attr) {
                if (!$this->db->fieldExists($col, 'payroll_schemes')) {
                    $fieldsToAdd[$col] = $attr;
                }
            }
            if (!empty($fieldsToAdd)) {
                $this->forge->addColumn('payroll_schemes', $fieldsToAdd);
            }
        }
    }

    public function down()
    {
        $this->forge->dropColumn('payroll_schemes', ['grace_period_late', 'grace_period_early']);
    }
}
