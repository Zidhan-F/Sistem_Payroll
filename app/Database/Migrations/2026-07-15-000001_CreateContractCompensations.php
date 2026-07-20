<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateContractCompensations extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'employee_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'contract_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'client_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'basic_salary' => [
                'type'       => 'DECIMAL',
                'constraint' => '18,2',
                'default'    => 0.00,
            ],
            'masa_kerja_tahun' => [
                'type'       => 'INT',
                'constraint' => 5,
                'default'    => 0,
            ],
            'masa_kerja_bulan' => [
                'type'       => 'INT',
                'constraint' => 5,
                'default'    => 0,
            ],
            'masa_kerja_hari' => [
                'type'       => 'INT',
                'constraint' => 5,
                'default'    => 0,
            ],
            'multiplier' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,4',
                'default'    => 0.0000,
            ],
            'nilai_kompensasi' => [
                'type'       => 'DECIMAL',
                'constraint' => '18,2',
                'default'    => 0.00,
            ],
            'nilai_kompensasi_final' => [
                'type'       => 'DECIMAL',
                'constraint' => '18,2',
                'null'       => true,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'default'    => 'Draft', // Draft, Ditetapkan, Disetujui, Ditolak, Dibayar
            ],
            'ditetapkan_oleh' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
            'ditetapkan_pada' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'disetujui_oleh' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
            'disetujui_pada' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'catatan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'tgl_mulai_kerja' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'tgl_akhir_kontrak' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        
        $this->forge->addKey('id', true);
        $this->forge->addKey(['employee_id', 'client_id']);
        $this->forge->createTable('contract_compensations', true);
    }

    public function down()
    {
        $this->forge->dropTable('contract_compensations');
    }
}
