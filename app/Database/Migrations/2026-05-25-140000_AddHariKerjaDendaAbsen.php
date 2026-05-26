<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddHariKerjaDendaAbsen extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        $fields = [];
        
        if (!$db->fieldExists('hari_kerja', 'employees')) {
            $fields['hari_kerja'] = [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 5,
                'null'       => true,
            ];
        }
        if (!$db->fieldExists('denda_absen', 'employees')) {
            $fields['denda_absen'] = [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
                'null'       => true,
            ];
        }

        if (!empty($fields)) {
            $this->forge->addColumn('employees', $fields);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('employees', 'hari_kerja');
        $this->forge->dropColumn('employees', 'denda_absen');
    }
}
