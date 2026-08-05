<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAllBaseSchemesTables extends Migration
{
    public function up()
    {
        // 1. payroll_schemes Table
        if (!$this->db->tableExists('payroll_schemes')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'nama' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '255',
                ],
                'gaji_pokok' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'default'    => 0,
                ],
                'deskripsi' => [
                    'type' => 'TEXT',
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
            $this->forge->createTable('payroll_schemes');
        }

        // 2. tax_schemes Table
        if (!$this->db->tableExists('tax_schemes')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'nama' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '100',
                ],
                'metode' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '50',
                    'default'    => 'Gross',
                ],
                'ptkp_status' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '10',
                    'default'    => 'TK/0',
                ],
                'tipe' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '20',
                    'default'    => 'pph21',
                ],
                'deskripsi' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('tax_schemes');
        }

        // 3. compensation_schemes Table
        if (!$this->db->tableExists('compensation_schemes')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'nama' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '100',
                ],
                'tunjangan_transport' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'default'    => 0,
                ],
                'tunjangan_makan' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'default'    => 0,
                ],
                'tunjangan_komunikasi' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'default'    => 0,
                ],
                'tunjangan_jabatan' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'default'    => 0,
                ],
                'tunjangan_kehadiran' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'default'    => 0,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('compensation_schemes');
        }

        // 4. bpjs_schemes Table
        if (!$this->db->tableExists('bpjs_schemes')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'nama' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '100',
                ],
                'bpjs_kes_karyawan' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '5,2',
                    'default'    => 1.00,
                ],
                'bpjs_kes_perusahaan' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '5,2',
                    'default'    => 4.00,
                ],
                'bpjs_jht_karyawan' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '5,2',
                    'default'    => 2.00,
                ],
                'bpjs_jht_perusahaan' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '5,2',
                    'default'    => 3.70,
                ],
                'bpjs_jp_karyawan' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '5,2',
                    'default'    => 1.00,
                ],
                'bpjs_jp_perusahaan' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '5,2',
                    'default'    => 2.00,
                ],
                'bpjs_jkk_perusahaan' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '5,2',
                    'default'    => 0.24,
                ],
                'bpjs_jkm_perusahaan' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '5,2',
                    'default'    => 0.30,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('bpjs_schemes');
        }

        // 5. client_payroll_configs Table
        if (!$this->db->tableExists('client_payroll_configs')) {
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
                    'null'       => true,
                ],
                'payroll_scheme_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'tax_scheme_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'compensation_scheme_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'bpjs_scheme_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'pay_date' => [
                    'type'       => 'INT',
                    'constraint' => 2,
                    'default'    => 25,
                ],
                'cutoff_start' => [
                    'type'       => 'INT',
                    'constraint' => 2,
                    'default'    => 21,
                ],
                'cutoff_end' => [
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
            $this->forge->createTable('client_payroll_configs');
        }

        // 6. status_logs Table
        if (!$this->db->tableExists('status_logs')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'description' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'user_action' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('status_logs');
        }

        // 7. attendance_logs Table
        if (!$this->db->tableExists('attendance_logs')) {
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
                    'null'       => true,
                ],
                'client_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'null'       => true,
                ],
                'log_date' => [
                    'type' => 'DATE',
                    'null' => true,
                ],
                'check_in' => [
                    'type' => 'TIME',
                    'null' => true,
                ],
                'check_out' => [
                    'type' => 'TIME',
                    'null' => true,
                ],
                'work_hours' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '5,2',
                    'default'    => 0,
                ],
                'overtime_hours' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '5,2',
                    'default'    => 0,
                ],
                'status' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'default'    => 'Hadir',
                ],
                'notes' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('attendance_logs');
        }

        // 8. overtime_logs Table
        if (!$this->db->tableExists('overtime_logs')) {
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
                    'null'       => true,
                ],
                'client_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'null'       => true,
                ],
                'log_date' => [
                    'type' => 'DATE',
                    'null' => true,
                ],
                'hours' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '5,2',
                    'default'    => 0,
                ],
                'status' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'default'    => 'Pending',
                ],
                'notes' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('overtime_logs');
        }
    }

    public function down()
    {
        // Safety no-op
    }
}
