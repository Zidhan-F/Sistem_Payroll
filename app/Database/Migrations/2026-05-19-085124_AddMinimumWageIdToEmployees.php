<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMinimumWageIdToEmployees extends Migration
{
    public function up()
    {
        $this->forge->addColumn('employees', [
            'minimum_wage_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('employees', 'minimum_wage_id');
    }
}
