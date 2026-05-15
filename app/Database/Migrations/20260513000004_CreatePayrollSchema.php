<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePayrollSchema extends Migration
{
    public function up()
    {
        // Employees Table
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nik' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'unique'     => true,
            ],
            'nama' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
            'no_rekening' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
            ],
            'gaji_pokok' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
            ],
            'position_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'client_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'tgl_masuk' => [
                'type' => 'DATE',
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'default'    => 'Aktif',
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
        $this->forge->createTable('employees');

        // Attendances Table
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
            'tanggal' => [
                'type' => 'DATE',
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => '20', // Hadir, Izin, Sakit, Alpa
            ],
            'jam_lembur' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('attendances');

        // Payrolls Table
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
            'bulan' => [
                'type'       => 'INT',
                'constraint' => 2,
            ],
            'tahun' => [
                'type'       => 'INT',
                'constraint' => 4,
            ],
            'gaji_pokok' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
            ],
            'total_tunjangan' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
            ],
            'total_potongan' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
            ],
            'take_home_pay' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
            ],
            'status_pembayaran' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'default'    => 'Pending',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('payrolls');

        // Payroll Details (Breakdown)
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'payroll_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'nama_komponen' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'tipe' => [
                'type'       => 'ENUM',
                'constraint' => ['Tunjangan', 'Potongan'],
            ],
            'jumlah' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('payroll_details');
    }

    public function down()
    {
        $this->forge->dropTable('payroll_details');
        $this->forge->dropTable('payrolls');
        $this->forge->dropTable('attendances');
        $this->forge->dropTable('employees');
    }
}
