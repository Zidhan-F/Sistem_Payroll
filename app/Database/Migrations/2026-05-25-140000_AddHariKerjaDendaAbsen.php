<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddHariKerjaDendaAbsen extends Migration
{
    public function up()
    {
        $fields = [
            'hari_kerja' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 5,
                'null'       => true,
            ],
            'denda_absen' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
                'null'       => true,
            ],
        ];

        $this->forge->addColumn('employees', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('employees', 'hari_kerja');
        $this->forge->dropColumn('employees', 'denda_absen');
    }
}
