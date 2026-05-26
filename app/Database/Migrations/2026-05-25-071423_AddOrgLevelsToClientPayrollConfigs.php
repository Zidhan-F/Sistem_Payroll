<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddOrgLevelsToClientPayrollConfigs extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        $fields = [];
        
        if (!$db->fieldExists('division_id', 'client_payroll_configs')) {
            $fields['division_id'] = [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ];
        }
        if (!$db->fieldExists('department_id', 'client_payroll_configs')) {
            $fields['department_id'] = [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ];
        }
        if (!$db->fieldExists('position_id', 'client_payroll_configs')) {
            $fields['position_id'] = [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ];
        }
        
        if (!empty($fields)) {
            $this->forge->addColumn('client_payroll_configs', $fields);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('client_payroll_configs', ['division_id', 'department_id', 'position_id']);
    }
}
