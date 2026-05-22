<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEmployIdToEmployees extends Migration
{
    public function up()
    {
        $this->forge->addColumn('employees', [
            'employ_id' => [
                'type'       => 'NVARCHAR',
                'constraint' => 20,
                'null'       => true,
                'after'      => 'id',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('employees', 'employ_id');
    }
}
