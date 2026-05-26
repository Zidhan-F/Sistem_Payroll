<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddOrgLevelsToClientPayrollConfigs extends Migration
{
    public function up()
    {
        $fields = [
            'division_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'department_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'position_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
        ];
        
        $this->forge->addColumn('client_payroll_configs', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('client_payroll_configs', ['division_id', 'department_id', 'position_id']);
    }
}
