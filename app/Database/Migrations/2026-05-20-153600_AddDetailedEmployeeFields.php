<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDetailedEmployeeFields extends Migration
{
    public function up()
    {
        $newFields = [
            'tempat_lahir' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
            'tanggal_lahir' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'npwp' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
            ],
            'start_contract' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'end_contract' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'tipe_perjanjian' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
            ],
            'status_pernikahan' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
            ],
            'work_location_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
        ];

        $fieldsToAdd = [];
        foreach ($newFields as $name => $definition) {
            if (!$this->db->fieldExists($name, 'employees')) {
                $fieldsToAdd[$name] = $definition;
            }
        }

        if (!empty($fieldsToAdd)) {
            $this->forge->addColumn('employees', $fieldsToAdd);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('employees', 'tempat_lahir');
        $this->forge->dropColumn('employees', 'tanggal_lahir');
        $this->forge->dropColumn('employees', 'npwp');
        $this->forge->dropColumn('employees', 'start_contract');
        $this->forge->dropColumn('employees', 'end_contract');
        $this->forge->dropColumn('employees', 'tipe_perjanjian');
        $this->forge->dropColumn('employees', 'status_pernikahan');
        $this->forge->dropColumn('employees', 'work_location_id');
    }
}
