<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFpkIdToEmployeesTable extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        if (!$db->fieldExists('fpk_id', 'employees')) {
            $this->forge->addColumn('employees', [
                'fpk_id' => [
                    'type' => 'INT',
                    'null' => true,
                ]
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('fpk_id', 'employees')) {
            $this->forge->dropColumn('employees', 'fpk_id');
        }
    }
}
