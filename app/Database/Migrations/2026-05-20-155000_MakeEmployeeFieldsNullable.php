<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MakeEmployeeFieldsNullable extends Migration
{
    public function up()
    {
        $fields = [
            'no_rekening' => [
                'name'       => 'no_rekening',
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
            ],
            'bank_name' => [
                'name'       => 'bank_name',
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
            'ptkp' => [
                'name'       => 'ptkp',
                'type'       => 'VARCHAR',
                'constraint' => '10',
                'null'       => true,
                'default'    => 'TK/0',
            ],
            'position_id' => [
                'name'       => 'position_id',
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'tgl_masuk' => [
                'name' => 'tgl_masuk',
                'type' => 'DATE',
                'null' => true,
            ],
            'minimum_wage_id' => [
                'name'       => 'minimum_wage_id',
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'alamat' => [
                'name' => 'alamat',
                'type' => 'TEXT',
                'null' => true,
            ],
        ];

        $this->forge->modifyColumn('employees', $fields);
    }

    public function down()
    {
    }
}
