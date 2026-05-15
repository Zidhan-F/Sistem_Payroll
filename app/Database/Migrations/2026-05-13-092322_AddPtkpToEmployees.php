<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPtkpToEmployees extends Migration
{
    public function up()
    {
        $this->forge->addColumn('employees', [
            'ptkp' => [
                'type'       => 'VARCHAR',
                'constraint' => '10',
                'default'    => 'TK/0',
            ],
            'bank_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('employees', ['ptkp', 'bank_name']);
    }
}
