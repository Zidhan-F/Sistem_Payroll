<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAdvancedPayrollSystem extends Migration
{
    public function up()
    {
        // 1. Client Schemas (Payroll & Tax)
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
            'bpjs_kes_percent' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 1.00, // Default 1%
            ],
            'bpjs_jht_percent' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 2.00, // Default 2%
            ],
            'overtime_rate_per_hour' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0, // 0 means use formula (Salary/173)
            ],
            'tax_method' => [
                'type'       => 'ENUM',
                'constraint' => ['Gross', 'Net', 'Gross Up'],
                'default'    => 'Gross',
            ],
            'cut_off_start' => [
                'type'       => 'INT',
                'constraint' => 2,
                'default'    => 21,
            ],
            'cut_off_end' => [
                'type'       => 'INT',
                'constraint' => 2,
                'default'    => 20,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('client_schemas');

        // 2. Contracts (PKWT)
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
            'client_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'no_kontrak' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'tgl_mulai' => [
                'type' => 'DATE',
            ],
            'tgl_berakhir' => [
                'type' => 'DATE',
            ],
            'gaji_pokok' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
            ],
            'status_pkwt' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'default'    => 'Aktif',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('contracts');

        // 3. Cut-off / Payroll Periods
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
            'bulan' => [
                'type'       => 'INT',
                'constraint' => 2,
            ],
            'tahun' => [
                'type'       => 'INT',
                'constraint' => 4,
            ],
            'status_cutoff' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'default'    => 'Open', // Open, Checking, Closed
            ],
            'pay_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('payroll_periods');
    }

    public function down()
    {
        $this->forge->dropTable('payroll_periods');
        $this->forge->dropTable('contracts');
        $this->forge->dropTable('client_schemas');
    }
}
