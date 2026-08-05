<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Baseline migration that creates ALL tables defined in Migrasi.php controller.
 * 
 * This ensures `php spark migrate` works on a completely fresh database
 * without requiring Migrasi.php to be run first.
 * 
 * Tables created:
 *  1. payroll_schemes         10. payroll_schedules       19. company_payroll_setting
 *  2. tax_schemes             11. holiday_calendar        20. early_arrival
 *  3. compensation_schemes    12. system_settings         21. dismissed_notifications
 *  4. bpjs_schemes            13. shift_schemes           22. fpk_master
 *  5. client_payroll_configs  14. employee_shifts         23. fpk_assignments
 *  6. status_logs             15. minimum_wages           24. pkwt
 *  7. attendance_logs         16. client_compensations    25. pkwt_components
 *  8. overtime_logs           17. compensation_components 26. client_absence_configs
 *  9. payroll_attendance      18. payroll_final
 */
class CreateAllBaseSchemesTables extends Migration
{
    public function up()
    {
        // =====================================================================
        // 1. payroll_schemes
        // =====================================================================
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

        // =====================================================================
        // 2. tax_schemes
        // =====================================================================
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

        // =====================================================================
        // 3. compensation_schemes
        // =====================================================================
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
            $this->forge->createTable('compensation_schemes');
        }

        // =====================================================================
        // 4. bpjs_schemes
        // =====================================================================
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

        // =====================================================================
        // 5. client_payroll_configs
        // =====================================================================
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

        // =====================================================================
        // 6. status_logs
        // =====================================================================
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

        // =====================================================================
        // 7. attendance_logs
        // =====================================================================
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

        // =====================================================================
        // 8. overtime_logs
        // =====================================================================
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

        // =====================================================================
        // 9. payroll_attendance (Cut-Off Data)
        // =====================================================================
        if (!$this->db->tableExists('payroll_attendance')) {
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
                    'null'       => true,
                ],
                'pkwt_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'null'       => true,
                ],
                'hari_kerja' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'default'    => 0,
                ],
                'jam_lembur' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '10,2',
                    'default'    => 0,
                ],
                'potongan_absensi' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'default'    => 0,
                ],
                'bonus_tambahan' => [
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
            $this->forge->createTable('payroll_attendance');
        }

        // =====================================================================
        // 10. payroll_final (Hasil Gaji Akhir)
        // =====================================================================
        if (!$this->db->tableExists('payroll_final')) {
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
                    'null'       => true,
                ],
                'pkwt_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'null'       => true,
                ],
                'total_pendapatan' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'null'       => true,
                ],
                'total_potongan' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'null'       => true,
                ],
                'take_home_pay' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'null'       => true,
                ],
                'status_approval' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '20',
                    'default'    => 'Pending',
                ],
                'approved_by' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '100',
                    'null'       => true,
                ],
                'gaji_pokok' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'default'    => 0,
                ],
                'raw_components' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('payroll_final');
        }

        // =====================================================================
        // 11. minimum_wages (UMP/UMK)
        // =====================================================================
        if (!$this->db->tableExists('minimum_wages')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'tipe' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '10',
                    'null'       => true,
                ],
                'kode_daerah' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '20',
                    'null'       => true,
                ],
                'nama_daerah' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '100',
                    'null'       => true,
                ],
                'provinsi' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '100',
                    'null'       => true,
                ],
                'nominal' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'null'       => true,
                ],
                'tahun' => [
                    'type'       => 'INT',
                    'constraint' => 4,
                    'null'       => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('minimum_wages');
        }

        // =====================================================================
        // 12. pkwt (Kontrak Kerja)
        // =====================================================================
        if (!$this->db->tableExists('pkwt')) {
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
                    'null'       => true,
                ],
                'employee_name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '255',
                ],
                'position_name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '255',
                    'null'       => true,
                ],
                'start_date' => [
                    'type' => 'DATE',
                    'null' => true,
                ],
                'end_date' => [
                    'type' => 'DATE',
                    'null' => true,
                ],
                'status' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '20',
                    'default'    => 'Active',
                ],
                'tipe_perjanjian' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '50',
                    'null'       => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('pkwt');
        }

        // =====================================================================
        // 13. pkwt_components (Rincian Gaji PKWT)
        // =====================================================================
        if (!$this->db->tableExists('pkwt_components')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'pkwt_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'null'       => true,
                ],
                'nama' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '100',
                    'null'       => true,
                ],
                'tipe' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '20',
                    'null'       => true,
                ],
                'nilai' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'default'    => 0,
                ],
                'is_persentase' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 0,
                ],
                'jenis_komponen' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '50',
                    'default'    => 'kompensasi',
                ],
                'sifat_kompensasi' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '20',
                    'default'    => 'tetap',
                ],
                'sumber_nilai' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '50',
                    'default'    => 'nominal',
                ],
                'periode' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '20',
                    'default'    => 'bulan',
                ],
                'is_bpjs' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 0,
                ],
                'is_pph21' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 1,
                ],
                'allowance_type' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '20',
                    'default'    => 'Routine',
                ],
                'payout_period' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '20',
                    'null'       => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('pkwt_components');
        }

        // =====================================================================
        // 14. client_compensations (Legacy)
        // =====================================================================
        if (!$this->db->tableExists('client_compensations')) {
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
                    'null'       => true,
                ],
                'nama' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '100',
                ],
                'tipe' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '20',
                    'default'    => 'pendapatan',
                ],
                'nominal' => [
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
            $this->forge->createTable('client_compensations');
        }

        // =====================================================================
        // 15. compensation_components
        // =====================================================================
        if (!$this->db->tableExists('compensation_components')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'scheme_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'null'       => true,
                ],
                'nama' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '100',
                ],
                'tipe' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '20',
                    'default'    => 'pendapatan',
                ],
                'nilai' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'default'    => 0,
                ],
                'is_persentase' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 0,
                ],
                'jenis_komponen' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '50',
                    'default'    => 'kompensasi',
                ],
                'sumber_nilai' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '50',
                    'default'    => 'nominal',
                ],
                'periode' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '20',
                    'default'    => 'bulan',
                ],
                'sifat_kompensasi' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '20',
                    'default'    => 'tetap',
                ],
                'is_bpjs' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 0,
                ],
                'is_pph21' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 1,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('compensation_components');
        }

        // =====================================================================
        // 16. client_absence_configs
        // =====================================================================
        if (!$this->db->tableExists('client_absence_configs')) {
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
                    'null'       => true,
                ],
                'prorate' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'default'    => 0,
                ],
                'absen_tidak_potong' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'default'    => 0,
                ],
                'nominal_potongan' => [
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
            $this->forge->createTable('client_absence_configs');
        }

        // =====================================================================
        // 17. payroll_schedules
        // =====================================================================
        if (!$this->db->tableExists('payroll_schedules')) {
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
                'deskripsi' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'tahun' => [
                    'type'       => 'INT',
                    'constraint' => 4,
                    'default'    => 2026,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('payroll_schedules');
        }

        // =====================================================================
        // 18. holiday_calendar
        // =====================================================================
        if (!$this->db->tableExists('holiday_calendar')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'tanggal' => [
                    'type' => 'DATE',
                ],
                'deskripsi' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '255',
                ],
                'tahun' => [
                    'type'       => 'INT',
                    'constraint' => 4,
                    'null'       => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('holiday_calendar');
        }

        // =====================================================================
        // 19. system_settings
        // =====================================================================
        if (!$this->db->tableExists('system_settings')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'setting_key' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '100',
                ],
                'setting_value' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '255',
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
            $this->forge->createTable('system_settings');
        }

        // =====================================================================
        // 20. shift_schemes
        // =====================================================================
        if (!$this->db->tableExists('shift_schemes')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '100',
                ],
                'start_time' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '10',
                ],
                'end_time' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '10',
                ],
                'duration' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '4,1',
                ],
                'grace_period_late' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'default'    => 0,
                ],
                'grace_period_early' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'default'    => 0,
                ],
                'break_start_time' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '10',
                    'null'       => true,
                ],
                'break_end_time' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '10',
                    'null'       => true,
                ],
                'break_duration' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '4,1',
                    'default'    => 0.0,
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
            $this->forge->createTable('shift_schemes');
        }

        // =====================================================================
        // 21. employee_shifts
        // =====================================================================
        if (!$this->db->tableExists('employee_shifts')) {
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
                ],
                'shift_scheme_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                ],
                'start_date' => [
                    'type' => 'DATE',
                ],
                'end_date' => [
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
            $this->forge->createTable('employee_shifts');
        }

        // =====================================================================
        // 22. company_payroll_setting
        // =====================================================================
        if (!$this->db->tableExists('company_payroll_setting')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'early_arrival_enabled' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 1,
                ],
                'max_early_arrival_minutes' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'default'    => 180,
                ],
                'early_arrival_min_minutes' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'default'    => 30,
                ],
                'early_arrival_calculation_unit' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'default'    => 60,
                ],
                'early_arrival_rounding_method' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '20',
                    'default'    => 'CEILING',
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
            $this->forge->createTable('company_payroll_setting');
        }

        // =====================================================================
        // 23. early_arrival
        // =====================================================================
        if (!$this->db->tableExists('early_arrival')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'attendance_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                ],
                'employee_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                ],
                'date' => [
                    'type' => 'DATE',
                ],
                'shift_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'null'       => true,
                ],
                'shift_start_time' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '10',
                    'null'       => true,
                ],
                'check_in_time' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '10',
                    'null'       => true,
                ],
                'early_minutes' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                ],
                'eligible_minutes' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                ],
                'status' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '20',
                    'default'    => 'PENDING',
                ],
                'approved_by' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '100',
                    'null'       => true,
                ],
                'approved_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'payroll_status' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '20',
                    'default'    => 'NOT_PROCESSED',
                ],
                'payroll_period' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '20',
                    'null'       => true,
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
            $this->forge->createTable('early_arrival');
        }

        // =====================================================================
        // 24. dismissed_notifications
        // =====================================================================
        if (!$this->db->tableExists('dismissed_notifications')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'notification_id' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '150',
                ],
                'dismissed_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('dismissed_notifications');
        }

        // =====================================================================
        // 25. fpk_master
        // =====================================================================
        if (!$this->db->tableExists('fpk_master')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'nomor_fpk' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '50',
                ],
                'nama_fpk' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '150',
                ],
                'provinsi' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '100',
                ],
                'city' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '100',
                ],
                'status' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '20',
                    'default'    => 'Open',
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
            $this->forge->createTable('fpk_master');
        }

        // =====================================================================
        // 26. fpk_assignments
        // =====================================================================
        if (!$this->db->tableExists('fpk_assignments')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'fpk_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                ],
                'employee_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                ],
                'nik' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '50',
                ],
                'nama_karyawan' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '150',
                ],
                'nomor_fpk' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '50',
                ],
                'nama_fpk' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '150',
                ],
                'provinsi' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '100',
                    'null'       => true,
                ],
                'city' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '100',
                    'null'       => true,
                ],
                'tanggal_penempatan' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'user_submit' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '100',
                    'null'       => true,
                ],
                'status' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '20',
                    'default'    => 'Active',
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
            $this->forge->createTable('fpk_assignments');
        }
    }

    public function down()
    {
        // Safety no-op — do not drop tables on rollback
    }
}
