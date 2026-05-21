<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddJumlahAnakToEmployees extends Migration
{
    public function up()
    {
        $fields = [
            'jumlah_anak' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'null'       => true,
                'after'      => 'status_pernikahan'
            ]
        ];
        if (!$this->db->fieldExists('jumlah_anak', 'employees')) {
            $this->forge->addColumn('employees', $fields);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('employees', 'jumlah_anak');
    }
}
