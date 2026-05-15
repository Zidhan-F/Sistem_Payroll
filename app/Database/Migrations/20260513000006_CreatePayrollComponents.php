<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePayrollComponents extends Migration
{
    public function up()
    {
        // 1. Payroll Components — Komponen gaji per klien
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'client_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'nama_komponen' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'tipe' => [
                'type'       => 'VARCHAR',
                'constraint' => '20', // Tunjangan / Potongan
            ],
            'jenis_nilai' => [
                'type'       => 'VARCHAR',
                'constraint' => '20', // Tetap / Persentase
                'default'    => 'Tetap',
            ],
            'nilai' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
            ],
            'is_active' => [
                'type'       => 'INT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('payroll_components');

        // 2. Payroll Period Checks — Tracking masalah saat pengecekan cut-off
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'period_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'employee_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'issue_type' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
            ],
            'issue_detail' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'is_resolved' => [
                'type'       => 'INT',
                'constraint' => 1,
                'default'    => 0,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('payroll_period_checks');

        // 3. Add status_cutoff update to payroll_periods: add 'Checking' status support
        // The existing VARCHAR(20) already supports 'Open', 'Checking', 'Closed'
    }

    public function down()
    {
        $this->forge->dropTable('payroll_period_checks');
        $this->forge->dropTable('payroll_components');
    }
}
