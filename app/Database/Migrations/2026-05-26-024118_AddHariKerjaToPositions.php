<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddHariKerjaToPositions extends Migration
{
    public function up()
    {
        $fields = [
            'hari_kerja' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 5,
                'null' => true,
            ],
        ];
        $this->forge->addColumn('positions', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('positions', 'hari_kerja');
    }
}
