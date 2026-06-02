<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDivDeptToEmployees extends Migration
{
    public function up()
    {
        $fields = [
            'division_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'position_id'
            ],
            'department_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'division_id'
            ],
        ];
        $this->forge->addColumn('employees', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('employees', ['division_id', 'department_id']);
    }
}
