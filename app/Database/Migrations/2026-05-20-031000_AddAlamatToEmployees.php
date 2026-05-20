<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAlamatToEmployees extends Migration
{
    public function up()
    {
        $this->forge->addColumn('employees', [
            'alamat' => [
                'type'       => 'VARCHAR',
                'constraint' => '1000',
                'null'       => true,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('employees', 'alamat');
    }
}
