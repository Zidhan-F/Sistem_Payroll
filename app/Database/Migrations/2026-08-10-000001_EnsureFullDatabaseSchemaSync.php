<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Comprehensive migration to guarantee 100% table, column, and initial master data integrity.
 * Ensures all tables, all columns, and baseline master data (UMK/UMP, admin user, system settings) exist.
 */
class EnsureFullDatabaseSchemaSync extends Migration
{
    public function up()
    {
        $this->db->resetDataCache();

        // 1. password_resets
        if (!$this->db->tableExists('password_resets')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'email' => ['type' => 'VARCHAR', 'constraint' => '100'],
                'token' => ['type' => 'VARCHAR', 'constraint' => '10'],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'expires_at' => ['type' => 'DATETIME'],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('password_resets', true);
        }

        // 2. payroll_rapel_adjustments
        if (!$this->db->tableExists('payroll_rapel_adjustments')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'employee_id' => ['type' => 'INT', 'constraint' => 11],
                'reference_period' => ['type' => 'VARCHAR', 'constraint' => '20'],
                'payment_period' => ['type' => 'VARCHAR', 'constraint' => '20'],
                'adjustment_type' => ['type' => 'VARCHAR', 'constraint' => '50'],
                'component_name' => ['type' => 'VARCHAR', 'constraint' => '100'],
                'previous_amount' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'null' => true, 'default' => 0.0],
                'correct_amount' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'null' => true, 'default' => 0.0],
                'difference_amount' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0.0],
                'reason' => ['type' => 'TEXT', 'null' => true],
                'status' => ['type' => 'VARCHAR', 'constraint' => '50', 'null' => true, 'default' => 'Pending Approval'],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('payroll_rapel_adjustments', true);
        }

        // 3. shift_master
        if (!$this->db->tableExists('shift_master')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'shift_code' => ['type' => 'VARCHAR', 'constraint' => '20'],
                'shift_name' => ['type' => 'VARCHAR', 'constraint' => '100'],
                'start_time' => ['type' => 'TIME'],
                'end_time' => ['type' => 'TIME'],
                'standard_hours' => ['type' => 'DECIMAL', 'constraint' => '4,2', 'default' => 8.0],
                'grace_in_minutes' => ['type' => 'INT', 'constraint' => 11, 'default' => 15],
                'grace_out_minutes' => ['type' => 'INT', 'constraint' => 11, 'default' => 15],
                'allow_overtime' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
                'is_overnight' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('shift_master', true);
        }

        // 4. employee_shift_assignments
        if (!$this->db->tableExists('employee_shift_assignments')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'employee_id' => ['type' => 'INT', 'constraint' => 11],
                'shift_id' => ['type' => 'INT', 'constraint' => 11],
                'effective_from' => ['type' => 'DATE'],
                'effective_to' => ['type' => 'DATE', 'null' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('employee_shift_assignments', true);
        }

        // Table: tax_schemes
        $this->db->resetDataCache();
        if ($this->db->tableExists('tax_schemes')) {
            $existingCols = $this->db->getFieldNames('tax_schemes');
            $colsToAdd = [];
            if (!in_array('tipe', $existingCols, true)) {
                $colsToAdd['tipe'] = array (
                  'type' => 'VARCHAR',
                  'constraint' => '20',
                  'default' => 'pph21',
                );
            }
            if (!in_array('bpjs_kes_karyawan', $existingCols, true)) {
                $colsToAdd['bpjs_kes_karyawan'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '5,2',
                  'default' => 1.0,
                );
            }
            if (!in_array('bpjs_kes_perusahaan', $existingCols, true)) {
                $colsToAdd['bpjs_kes_perusahaan'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '5,2',
                  'default' => 4.0,
                );
            }
            if (!in_array('bpjs_kes_max_salary', $existingCols, true)) {
                $colsToAdd['bpjs_kes_max_salary'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '15,2',
                  'default' => 12000000.0,
                );
            }
            if (!in_array('bpjs_jht_karyawan', $existingCols, true)) {
                $colsToAdd['bpjs_jht_karyawan'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '5,2',
                  'default' => 2.0,
                );
            }
            if (!in_array('bpjs_jht_perusahaan', $existingCols, true)) {
                $colsToAdd['bpjs_jht_perusahaan'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '5,2',
                  'default' => 3.7,
                );
            }
            if (!in_array('bpjs_jp_karyawan', $existingCols, true)) {
                $colsToAdd['bpjs_jp_karyawan'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '5,2',
                  'default' => 1.0,
                );
            }
            if (!in_array('bpjs_jp_perusahaan', $existingCols, true)) {
                $colsToAdd['bpjs_jp_perusahaan'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '5,2',
                  'default' => 2.0,
                );
            }
            if (!in_array('bpjs_jp_max_salary', $existingCols, true)) {
                $colsToAdd['bpjs_jp_max_salary'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '15,2',
                  'default' => 10024600.0,
                );
            }
            if (!in_array('bpjs_jkk_perusahaan', $existingCols, true)) {
                $colsToAdd['bpjs_jkk_perusahaan'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '5,2',
                  'default' => 0.24,
                );
            }
            if (!in_array('bpjs_jkm_perusahaan', $existingCols, true)) {
                $colsToAdd['bpjs_jkm_perusahaan'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '5,2',
                  'default' => 0.3,
                );
            }
            if (!empty($colsToAdd)) {
                $this->forge->addColumn('tax_schemes', $colsToAdd);
                $this->db->resetDataCache();
            }
        }

        // Table: client_payroll_configs
        $this->db->resetDataCache();
        if ($this->db->tableExists('client_payroll_configs')) {
            $existingCols = $this->db->getFieldNames('client_payroll_configs');
            $colsToAdd = [];
            if (!in_array('compensation_scheme_id', $existingCols, true)) {
                $colsToAdd['compensation_scheme_id'] = array (
                  'type' => 'INT',
                  'constraint' => 11,
                  'null' => true,
                );
            }
            if (!in_array('bpjs_scheme_id', $existingCols, true)) {
                $colsToAdd['bpjs_scheme_id'] = array (
                  'type' => 'INT',
                  'constraint' => 11,
                  'null' => true,
                );
            }
            if (!in_array('payroll_type', $existingCols, true)) {
                $colsToAdd['payroll_type'] = array (
                  'type' => 'VARCHAR',
                  'constraint' => '20',
                  'null' => true,
                );
            }
            if (!in_array('minimum_wage_id', $existingCols, true)) {
                $colsToAdd['minimum_wage_id'] = array (
                  'type' => 'INT',
                  'constraint' => 11,
                  'null' => true,
                );
            }
            if (!in_array('custom_nominal', $existingCols, true)) {
                $colsToAdd['custom_nominal'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '15,2',
                  'null' => true,
                );
            }
            if (!in_array('cutoff_gaji_pokok_start', $existingCols, true)) {
                $colsToAdd['cutoff_gaji_pokok_start'] = array (
                  'type' => 'INT',
                  'constraint' => 11,
                  'default' => 21,
                  'null' => true,
                );
            }
            if (!in_array('cutoff_gaji_pokok_end', $existingCols, true)) {
                $colsToAdd['cutoff_gaji_pokok_end'] = array (
                  'type' => 'INT',
                  'constraint' => 11,
                  'default' => 20,
                  'null' => true,
                );
            }
            if (!in_array('cutoff_gaji_pokok_val', $existingCols, true)) {
                $colsToAdd['cutoff_gaji_pokok_val'] = array (
                  'type' => 'VARCHAR',
                  'constraint' => '255',
                  'null' => true,
                );
            }
            if (!in_array('cutoff_gaji_pokok_schedule_ref', $existingCols, true)) {
                $colsToAdd['cutoff_gaji_pokok_schedule_ref'] = array (
                  'type' => 'INT',
                  'constraint' => 11,
                  'null' => true,
                );
            }
            if (!in_array('cutoff_lembur_start', $existingCols, true)) {
                $colsToAdd['cutoff_lembur_start'] = array (
                  'type' => 'INT',
                  'constraint' => 11,
                  'default' => 21,
                  'null' => true,
                );
            }
            if (!in_array('cutoff_lembur_end', $existingCols, true)) {
                $colsToAdd['cutoff_lembur_end'] = array (
                  'type' => 'INT',
                  'constraint' => 11,
                  'default' => 20,
                  'null' => true,
                );
            }
            if (!in_array('cutoff_lembur_val', $existingCols, true)) {
                $colsToAdd['cutoff_lembur_val'] = array (
                  'type' => 'VARCHAR',
                  'constraint' => '255',
                  'null' => true,
                );
            }
            if (!in_array('cutoff_lembur_schedule_ref', $existingCols, true)) {
                $colsToAdd['cutoff_lembur_schedule_ref'] = array (
                  'type' => 'INT',
                  'constraint' => 11,
                  'null' => true,
                );
            }
            if (!in_array('cutoff_insentif_start', $existingCols, true)) {
                $colsToAdd['cutoff_insentif_start'] = array (
                  'type' => 'INT',
                  'constraint' => 11,
                  'default' => 21,
                  'null' => true,
                );
            }
            if (!in_array('cutoff_insentif_end', $existingCols, true)) {
                $colsToAdd['cutoff_insentif_end'] = array (
                  'type' => 'INT',
                  'constraint' => 11,
                  'default' => 20,
                  'null' => true,
                );
            }
            if (!in_array('cutoff_insentif_val', $existingCols, true)) {
                $colsToAdd['cutoff_insentif_val'] = array (
                  'type' => 'VARCHAR',
                  'constraint' => '255',
                  'null' => true,
                );
            }
            if (!in_array('cutoff_insentif_schedule_ref', $existingCols, true)) {
                $colsToAdd['cutoff_insentif_schedule_ref'] = array (
                  'type' => 'INT',
                  'constraint' => 11,
                  'null' => true,
                );
            }
            if (!in_array('is_rapel_gaji_pokok', $existingCols, true)) {
                $colsToAdd['is_rapel_gaji_pokok'] = array (
                  'type' => 'TINYINT',
                  'constraint' => 1,
                  'default' => 1,
                );
            }
            if (!in_array('is_rapel_lembur', $existingCols, true)) {
                $colsToAdd['is_rapel_lembur'] = array (
                  'type' => 'TINYINT',
                  'constraint' => 1,
                  'default' => 1,
                );
            }
            if (!in_array('is_rapel_insentif', $existingCols, true)) {
                $colsToAdd['is_rapel_insentif'] = array (
                  'type' => 'TINYINT',
                  'constraint' => 1,
                  'default' => 1,
                );
            }
            if (!in_array('overtime_divisor', $existingCols, true)) {
                $colsToAdd['overtime_divisor'] = array (
                  'type' => 'INT',
                  'constraint' => 11,
                  'default' => 173,
                  'null' => true,
                );
            }
            if (!in_array('standard_work_days', $existingCols, true)) {
                $colsToAdd['standard_work_days'] = array (
                  'type' => 'INT',
                  'constraint' => 11,
                  'default' => 22,
                  'null' => true,
                );
            }
            if (!in_array('standard_work_hours', $existingCols, true)) {
                $colsToAdd['standard_work_hours'] = array (
                  'type' => 'INT',
                  'constraint' => 11,
                  'default' => 8,
                  'null' => true,
                );
            }
            if (!in_array('max_overtime_regular', $existingCols, true)) {
                $colsToAdd['max_overtime_regular'] = array (
                  'type' => 'INT',
                  'constraint' => 11,
                  'default' => 3,
                  'null' => true,
                );
            }
            if (!in_array('overtime_multiplier_workday', $existingCols, true)) {
                $colsToAdd['overtime_multiplier_workday'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '5,2',
                  'default' => 1.5,
                  'null' => true,
                );
            }
            if (!in_array('overtime_multiplier_holiday', $existingCols, true)) {
                $colsToAdd['overtime_multiplier_holiday'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '5,2',
                  'default' => 2.0,
                  'null' => true,
                );
            }
            if (!in_array('minimum_overtime_minutes', $existingCols, true)) {
                $colsToAdd['minimum_overtime_minutes'] = array (
                  'type' => 'INT',
                  'constraint' => 11,
                  'default' => 30,
                  'null' => true,
                );
            }
            if (!in_array('early_arrival_enabled', $existingCols, true)) {
                $colsToAdd['early_arrival_enabled'] = array (
                  'type' => 'TINYINT',
                  'constraint' => 1,
                  'default' => 1,
                  'null' => true,
                );
            }
            if (!in_array('max_early_arrival_minutes', $existingCols, true)) {
                $colsToAdd['max_early_arrival_minutes'] = array (
                  'type' => 'INT',
                  'constraint' => 11,
                  'default' => 180,
                  'null' => true,
                );
            }
            if (!in_array('division_id', $existingCols, true)) {
                $colsToAdd['division_id'] = array (
                  'type' => 'INT',
                  'constraint' => 11,
                  'null' => true,
                );
            }
            if (!in_array('department_id', $existingCols, true)) {
                $colsToAdd['department_id'] = array (
                  'type' => 'INT',
                  'constraint' => 11,
                  'null' => true,
                );
            }
            if (!in_array('position_id', $existingCols, true)) {
                $colsToAdd['position_id'] = array (
                  'type' => 'INT',
                  'constraint' => 11,
                  'null' => true,
                );
            }
            if (!empty($colsToAdd)) {
                $this->forge->addColumn('client_payroll_configs', $colsToAdd);
                $this->db->resetDataCache();
            }
        }

        // Table: payroll_schemes
        $this->db->resetDataCache();
        if ($this->db->tableExists('payroll_schemes')) {
            $existingCols = $this->db->getFieldNames('payroll_schemes');
            $colsToAdd = [];
            if (!in_array('tipe', $existingCols, true)) {
                $colsToAdd['tipe'] = array (
                  'type' => 'VARCHAR',
                  'constraint' => '50',
                  'default' => 'bulanan',
                  'null' => true,
                );
            }
            if (!in_array('max_early_arrival_minutes', $existingCols, true)) {
                $colsToAdd['max_early_arrival_minutes'] = array (
                  'type' => 'INT',
                  'constraint' => 11,
                  'default' => 180,
                  'null' => true,
                );
            }
            if (!in_array('compensation_scheme_id', $existingCols, true)) {
                $colsToAdd['compensation_scheme_id'] = array (
                  'type' => 'INT',
                  'constraint' => 11,
                  'null' => true,
                );
            }
            if (!in_array('prorate', $existingCols, true)) {
                $colsToAdd['prorate'] = array (
                  'type' => 'TINYINT',
                  'constraint' => 1,
                  'default' => 1,
                );
            }
            if (!in_array('absen_tidak_potong', $existingCols, true)) {
                $colsToAdd['absen_tidak_potong'] = array (
                  'type' => 'TINYINT',
                  'constraint' => 1,
                  'default' => 0,
                );
            }
            if (!in_array('nominal_potongan', $existingCols, true)) {
                $colsToAdd['nominal_potongan'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '15,2',
                  'default' => 0,
                );
            }
            if (!in_array('grace_period_late', $existingCols, true)) {
                $colsToAdd['grace_period_late'] = array (
                  'type' => 'INT',
                  'constraint' => 11,
                  'default' => 15,
                  'null' => true,
                );
            }
            if (!in_array('grace_period_early', $existingCols, true)) {
                $colsToAdd['grace_period_early'] = array (
                  'type' => 'INT',
                  'constraint' => 11,
                  'default' => 15,
                  'null' => true,
                );
            }
            if (!in_array('min_overtime', $existingCols, true)) {
                $colsToAdd['min_overtime'] = array (
                  'type' => 'INT',
                  'constraint' => 11,
                  'default' => 30,
                  'null' => true,
                );
            }
            if (!in_array('denda_terlambat_per_jam', $existingCols, true)) {
                $colsToAdd['denda_terlambat_per_jam'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '15,2',
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('denda_alfa_per_hari', $existingCols, true)) {
                $colsToAdd['denda_alfa_per_hari'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '15,2',
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('early_leave_threshold', $existingCols, true)) {
                $colsToAdd['early_leave_threshold'] = array (
                  'type' => 'INT',
                  'constraint' => 11,
                  'default' => 120,
                  'null' => true,
                );
            }
            if (!in_array('overtime_type', $existingCols, true)) {
                $colsToAdd['overtime_type'] = array (
                  'type' => 'VARCHAR',
                  'constraint' => '50',
                  'default' => 'depnaker',
                  'null' => true,
                );
            }
            if (!in_array('lumpsum_subtype', $existingCols, true)) {
                $colsToAdd['lumpsum_subtype'] = array (
                  'type' => 'VARCHAR',
                  'constraint' => '50',
                  'null' => true,
                );
            }
            if (!in_array('lumpsum_nominal', $existingCols, true)) {
                $colsToAdd['lumpsum_nominal'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '15,2',
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!empty($colsToAdd)) {
                $this->forge->addColumn('payroll_schemes', $colsToAdd);
                $this->db->resetDataCache();
            }
        }

        // Table: payroll_scheme_templates
        $this->db->resetDataCache();
        if ($this->db->tableExists('payroll_scheme_templates')) {
            $existingCols = $this->db->getFieldNames('payroll_scheme_templates');
            $colsToAdd = [];
            if (!in_array('denda_terlambat_per_jam', $existingCols, true)) {
                $colsToAdd['denda_terlambat_per_jam'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '15,2',
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('denda_alfa_per_hari', $existingCols, true)) {
                $colsToAdd['denda_alfa_per_hari'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '15,2',
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('early_leave_threshold', $existingCols, true)) {
                $colsToAdd['early_leave_threshold'] = array (
                  'type' => 'INT',
                  'constraint' => 11,
                  'default' => 120,
                  'null' => true,
                );
            }
            if (!in_array('bpjs_inc_transport', $existingCols, true)) {
                $colsToAdd['bpjs_inc_transport'] = array (
                  'type' => 'TINYINT',
                  'constraint' => 1,
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('pph_inc_transport', $existingCols, true)) {
                $colsToAdd['pph_inc_transport'] = array (
                  'type' => 'TINYINT',
                  'constraint' => 1,
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('bpjs_inc_makan', $existingCols, true)) {
                $colsToAdd['bpjs_inc_makan'] = array (
                  'type' => 'TINYINT',
                  'constraint' => 1,
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('pph_inc_makan', $existingCols, true)) {
                $colsToAdd['pph_inc_makan'] = array (
                  'type' => 'TINYINT',
                  'constraint' => 1,
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('bpjs_inc_komunikasi', $existingCols, true)) {
                $colsToAdd['bpjs_inc_komunikasi'] = array (
                  'type' => 'TINYINT',
                  'constraint' => 1,
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('pph_inc_komunikasi', $existingCols, true)) {
                $colsToAdd['pph_inc_komunikasi'] = array (
                  'type' => 'TINYINT',
                  'constraint' => 1,
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('bpjs_inc_jabatan', $existingCols, true)) {
                $colsToAdd['bpjs_inc_jabatan'] = array (
                  'type' => 'TINYINT',
                  'constraint' => 1,
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('pph_inc_jabatan', $existingCols, true)) {
                $colsToAdd['pph_inc_jabatan'] = array (
                  'type' => 'TINYINT',
                  'constraint' => 1,
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('bpjs_inc_kehadiran', $existingCols, true)) {
                $colsToAdd['bpjs_inc_kehadiran'] = array (
                  'type' => 'TINYINT',
                  'constraint' => 1,
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('pph_inc_kehadiran', $existingCols, true)) {
                $colsToAdd['pph_inc_kehadiran'] = array (
                  'type' => 'TINYINT',
                  'constraint' => 1,
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('bpjs_inc_kinerja', $existingCols, true)) {
                $colsToAdd['bpjs_inc_kinerja'] = array (
                  'type' => 'TINYINT',
                  'constraint' => 1,
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('pph_inc_kinerja', $existingCols, true)) {
                $colsToAdd['pph_inc_kinerja'] = array (
                  'type' => 'TINYINT',
                  'constraint' => 1,
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('grace_period_late', $existingCols, true)) {
                $colsToAdd['grace_period_late'] = array (
                  'type' => 'INT',
                  'constraint' => 11,
                  'default' => 15,
                  'null' => true,
                );
            }
            if (!in_array('grace_period_early', $existingCols, true)) {
                $colsToAdd['grace_period_early'] = array (
                  'type' => 'INT',
                  'constraint' => 11,
                  'default' => 15,
                  'null' => true,
                );
            }
            if (!in_array('min_overtime', $existingCols, true)) {
                $colsToAdd['min_overtime'] = array (
                  'type' => 'INT',
                  'constraint' => 11,
                  'default' => 30,
                  'null' => true,
                );
            }
            if (!empty($colsToAdd)) {
                $this->forge->addColumn('payroll_scheme_templates', $colsToAdd);
                $this->db->resetDataCache();
            }
        }

        // Table: pkwt
        $this->db->resetDataCache();
        if ($this->db->tableExists('pkwt')) {
            $existingCols = $this->db->getFieldNames('pkwt');
            $colsToAdd = [];
            if (!in_array('tipe_perjanjian', $existingCols, true)) {
                $colsToAdd['tipe_perjanjian'] = array (
                  'type' => 'VARCHAR',
                  'constraint' => '50',
                  'null' => true,
                );
            }
            if (!empty($colsToAdd)) {
                $this->forge->addColumn('pkwt', $colsToAdd);
                $this->db->resetDataCache();
            }
        }

        // Table: pkwt_components
        $this->db->resetDataCache();
        if ($this->db->tableExists('pkwt_components')) {
            $existingCols = $this->db->getFieldNames('pkwt_components');
            $colsToAdd = [];
            if (!in_array('jenis_komponen', $existingCols, true)) {
                $colsToAdd['jenis_komponen'] = array (
                  'type' => 'VARCHAR',
                  'constraint' => '50',
                  'default' => 'kompensasi',
                  'null' => true,
                );
            }
            if (!in_array('sifat_kompensasi', $existingCols, true)) {
                $colsToAdd['sifat_kompensasi'] = array (
                  'type' => 'VARCHAR',
                  'constraint' => '20',
                  'default' => 'tetap',
                  'null' => true,
                );
            }
            if (!in_array('sumber_nilai', $existingCols, true)) {
                $colsToAdd['sumber_nilai'] = array (
                  'type' => 'VARCHAR',
                  'constraint' => '50',
                  'default' => 'nominal',
                  'null' => true,
                );
            }
            if (!in_array('periode', $existingCols, true)) {
                $colsToAdd['periode'] = array (
                  'type' => 'VARCHAR',
                  'constraint' => '20',
                  'default' => 'bulan',
                  'null' => true,
                );
            }
            if (!in_array('allowance_type', $existingCols, true)) {
                $colsToAdd['allowance_type'] = array (
                  'type' => 'VARCHAR',
                  'constraint' => '50',
                  'null' => true,
                );
            }
            if (!in_array('payout_period', $existingCols, true)) {
                $colsToAdd['payout_period'] = array (
                  'type' => 'VARCHAR',
                  'constraint' => '20',
                  'null' => true,
                );
            }
            if (!empty($colsToAdd)) {
                $this->forge->addColumn('pkwt_components', $colsToAdd);
                $this->db->resetDataCache();
            }
        }

        // Table: payroll_components
        $this->db->resetDataCache();
        if ($this->db->tableExists('payroll_components')) {
            $existingCols = $this->db->getFieldNames('payroll_components');
            $colsToAdd = [];
            if (!in_array('is_bpjs', $existingCols, true)) {
                $colsToAdd['is_bpjs'] = array (
                  'type' => 'TINYINT',
                  'constraint' => 1,
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('is_pph21', $existingCols, true)) {
                $colsToAdd['is_pph21'] = array (
                  'type' => 'TINYINT',
                  'constraint' => 1,
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('scheme_id', $existingCols, true)) {
                $colsToAdd['scheme_id'] = array (
                  'type' => 'INT',
                  'constraint' => 11,
                  'null' => true,
                );
            }
            if (!in_array('jenis_komponen', $existingCols, true)) {
                $colsToAdd['jenis_komponen'] = array (
                  'type' => 'VARCHAR',
                  'constraint' => '50',
                  'default' => 'kompensasi',
                  'null' => true,
                );
            }
            if (!in_array('sumber_nilai', $existingCols, true)) {
                $colsToAdd['sumber_nilai'] = array (
                  'type' => 'VARCHAR',
                  'constraint' => '50',
                  'default' => 'nominal',
                  'null' => true,
                );
            }
            if (!in_array('periode', $existingCols, true)) {
                $colsToAdd['periode'] = array (
                  'type' => 'VARCHAR',
                  'constraint' => '20',
                  'default' => 'bulan',
                  'null' => true,
                );
            }
            if (!in_array('sifat_kompensasi', $existingCols, true)) {
                $colsToAdd['sifat_kompensasi'] = array (
                  'type' => 'VARCHAR',
                  'constraint' => '20',
                  'default' => 'tetap',
                  'null' => true,
                );
            }
            if (!empty($colsToAdd)) {
                $this->forge->addColumn('payroll_components', $colsToAdd);
                $this->db->resetDataCache();
            }
        }

        // Table: client_compensations
        $this->db->resetDataCache();
        if ($this->db->tableExists('client_compensations')) {
            $existingCols = $this->db->getFieldNames('client_compensations');
            $colsToAdd = [];
            if (!in_array('employee_id', $existingCols, true)) {
                $colsToAdd['employee_id'] = array (
                  'type' => 'INT',
                  'constraint' => 11,
                  'null' => true,
                );
            }
            if (!empty($colsToAdd)) {
                $this->forge->addColumn('client_compensations', $colsToAdd);
                $this->db->resetDataCache();
            }
        }

        // Table: compensation_components
        $this->db->resetDataCache();
        if ($this->db->tableExists('compensation_components')) {
            $existingCols = $this->db->getFieldNames('compensation_components');
            $colsToAdd = [];
            if (!in_array('tipe', $existingCols, true)) {
                $colsToAdd['tipe'] = array (
                  'type' => 'VARCHAR',
                  'constraint' => '50',
                  'null' => true,
                );
            }
            if (!in_array('nilai', $existingCols, true)) {
                $colsToAdd['nilai'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '15,2',
                  'default' => 0,
                );
            }
            if (!in_array('is_persentase', $existingCols, true)) {
                $colsToAdd['is_persentase'] = array (
                  'type' => 'TINYINT',
                  'constraint' => 1,
                  'default' => 0,
                );
            }
            if (!in_array('jenis_komponen', $existingCols, true)) {
                $colsToAdd['jenis_komponen'] = array (
                  'type' => 'VARCHAR',
                  'constraint' => '50',
                  'default' => 'kompensasi',
                  'null' => true,
                );
            }
            if (!in_array('sumber_nilai', $existingCols, true)) {
                $colsToAdd['sumber_nilai'] = array (
                  'type' => 'VARCHAR',
                  'constraint' => '50',
                  'default' => 'nominal',
                  'null' => true,
                );
            }
            if (!in_array('periode', $existingCols, true)) {
                $colsToAdd['periode'] = array (
                  'type' => 'VARCHAR',
                  'constraint' => '20',
                  'default' => 'bulan',
                  'null' => true,
                );
            }
            if (!in_array('sifat_kompensasi', $existingCols, true)) {
                $colsToAdd['sifat_kompensasi'] = array (
                  'type' => 'VARCHAR',
                  'constraint' => '20',
                  'default' => 'tetap',
                  'null' => true,
                );
            }
            if (!empty($colsToAdd)) {
                $this->forge->addColumn('compensation_components', $colsToAdd);
                $this->db->resetDataCache();
            }
        }

        // Table: client_absence_configs
        $this->db->resetDataCache();
        if ($this->db->tableExists('client_absence_configs')) {
            $existingCols = $this->db->getFieldNames('client_absence_configs');
            $colsToAdd = [];
            if (!in_array('nominal_potongan', $existingCols, true)) {
                $colsToAdd['nominal_potongan'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '15,2',
                  'default' => 0,
                );
            }
            if (!empty($colsToAdd)) {
                $this->forge->addColumn('client_absence_configs', $colsToAdd);
                $this->db->resetDataCache();
            }
        }

        // Table: positions
        $this->db->resetDataCache();
        if ($this->db->tableExists('positions')) {
            $existingCols = $this->db->getFieldNames('positions');
            $colsToAdd = [];
            if (!in_array('level', $existingCols, true)) {
                $colsToAdd['level'] = array (
                  'type' => 'INT',
                  'constraint' => 11,
                  'default' => 1,
                  'null' => true,
                );
            }
            if (!in_array('hari_kerja', $existingCols, true)) {
                $colsToAdd['hari_kerja'] = array (
                  'type' => 'INT',
                  'constraint' => 11,
                  'default' => 22,
                  'null' => true,
                );
            }
            if (!empty($colsToAdd)) {
                $this->forge->addColumn('positions', $colsToAdd);
                $this->db->resetDataCache();
            }
        }

        // Table: employees
        $this->db->resetDataCache();
        if ($this->db->tableExists('employees')) {
            $existingCols = $this->db->getFieldNames('employees');
            $colsToAdd = [];
            if (!in_array('alamat', $existingCols, true)) {
                $colsToAdd['alamat'] = array (
                  'type' => 'TEXT',
                  'null' => true,
                );
            }
            if (!in_array('hari_kerja', $existingCols, true)) {
                $colsToAdd['hari_kerja'] = array (
                  'type' => 'INT',
                  'constraint' => 11,
                  'default' => 22,
                  'null' => true,
                );
            }
            if (!in_array('denda_absen', $existingCols, true)) {
                $colsToAdd['denda_absen'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '15,2',
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('custom_standard_days', $existingCols, true)) {
                $colsToAdd['custom_standard_days'] = array (
                  'type' => 'INT',
                  'constraint' => 11,
                  'null' => true,
                );
            }
            if (!in_array('fpk_id', $existingCols, true)) {
                $colsToAdd['fpk_id'] = array (
                  'type' => 'INT',
                  'constraint' => 11,
                  'null' => true,
                );
            }
            if (!empty($colsToAdd)) {
                $this->forge->addColumn('employees', $colsToAdd);
                $this->db->resetDataCache();
            }
        }

        // Table: payroll_schedules
        $this->db->resetDataCache();
        if ($this->db->tableExists('payroll_schedules')) {
            $existingCols = $this->db->getFieldNames('payroll_schedules');
            $colsToAdd = [];
            if (!in_array('tahun', $existingCols, true)) {
                $colsToAdd['tahun'] = array (
                  'type' => 'INT',
                  'constraint' => 11,
                  'null' => true,
                );
            }
            if (!empty($colsToAdd)) {
                $this->forge->addColumn('payroll_schedules', $colsToAdd);
                $this->db->resetDataCache();
            }
        }

        // Table: overtime_logs
        $this->db->resetDataCache();
        if ($this->db->tableExists('overtime_logs')) {
            $existingCols = $this->db->getFieldNames('overtime_logs');
            $colsToAdd = [];
            if (!in_array('tanggal', $existingCols, true)) {
                $colsToAdd['tanggal'] = array (
                  'type' => 'DATE',
                  'null' => true,
                );
            }
            if (!in_array('jam_lembur', $existingCols, true)) {
                $colsToAdd['jam_lembur'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '10,2',
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('is_holiday', $existingCols, true)) {
                $colsToAdd['is_holiday'] = array (
                  'type' => 'TINYINT',
                  'constraint' => 1,
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('keterangan', $existingCols, true)) {
                $colsToAdd['keterangan'] = array (
                  'type' => 'TEXT',
                  'null' => true,
                );
            }
            if (!in_array('status', $existingCols, true)) {
                $colsToAdd['status'] = array (
                  'type' => 'VARCHAR',
                  'constraint' => '20',
                  'default' => 'Approved',
                  'null' => true,
                );
            }
            if (!in_array('approved_by', $existingCols, true)) {
                $colsToAdd['approved_by'] = array (
                  'type' => 'VARCHAR',
                  'constraint' => '100',
                  'null' => true,
                );
            }
            if (!in_array('approved_at', $existingCols, true)) {
                $colsToAdd['approved_at'] = array (
                  'type' => 'DATETIME',
                  'null' => true,
                );
            }
            if (!in_array('is_rapel', $existingCols, true)) {
                $colsToAdd['is_rapel'] = array (
                  'type' => 'TINYINT',
                  'constraint' => 1,
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('payout_period', $existingCols, true)) {
                $colsToAdd['payout_period'] = array (
                  'type' => 'VARCHAR',
                  'constraint' => '20',
                  'null' => true,
                );
            }
            if (!empty($colsToAdd)) {
                $this->forge->addColumn('overtime_logs', $colsToAdd);
                $this->db->resetDataCache();
            }
        }

        // Table: system_settings
        $this->db->resetDataCache();
        if ($this->db->tableExists('system_settings')) {
            $existingCols = $this->db->getFieldNames('system_settings');
            $colsToAdd = [];
            if (!in_array('description', $existingCols, true)) {
                $colsToAdd['description'] = array (
                  'type' => 'TEXT',
                  'null' => true,
                );
            }
            if (!in_array('updated_at', $existingCols, true)) {
                $colsToAdd['updated_at'] = array (
                  'type' => 'DATETIME',
                  'null' => true,
                );
            }
            if (!empty($colsToAdd)) {
                $this->forge->addColumn('system_settings', $colsToAdd);
                $this->db->resetDataCache();
            }
        }

        // Table: attendance_logs
        $this->db->resetDataCache();
        if ($this->db->tableExists('attendance_logs')) {
            $existingCols = $this->db->getFieldNames('attendance_logs');
            $colsToAdd = [];
            if (!in_array('shift_scheme_id', $existingCols, true)) {
                $colsToAdd['shift_scheme_id'] = array (
                  'type' => 'INT',
                  'constraint' => 11,
                  'null' => true,
                );
            }
            if (!in_array('is_rapel', $existingCols, true)) {
                $colsToAdd['is_rapel'] = array (
                  'type' => 'TINYINT',
                  'constraint' => 1,
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('payout_period', $existingCols, true)) {
                $colsToAdd['payout_period'] = array (
                  'type' => 'VARCHAR',
                  'constraint' => '20',
                  'null' => true,
                );
            }
            if (!in_array('calculated_work_hours', $existingCols, true)) {
                $colsToAdd['calculated_work_hours'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '10,2',
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('calculated_overtime_hours', $existingCols, true)) {
                $colsToAdd['calculated_overtime_hours'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '10,2',
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('is_incomplete', $existingCols, true)) {
                $colsToAdd['is_incomplete'] = array (
                  'type' => 'TINYINT',
                  'constraint' => 1,
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('late_hours', $existingCols, true)) {
                $colsToAdd['late_hours'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '10,2',
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('early_leave_hours', $existingCols, true)) {
                $colsToAdd['early_leave_hours'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '10,2',
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('late_minutes', $existingCols, true)) {
                $colsToAdd['late_minutes'] = array (
                  'type' => 'INT',
                  'constraint' => 11,
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('late_penalty_hours', $existingCols, true)) {
                $colsToAdd['late_penalty_hours'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '10,2',
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('denda_terlambat', $existingCols, true)) {
                $colsToAdd['denda_terlambat'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '15,2',
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('is_early_leave_alfa', $existingCols, true)) {
                $colsToAdd['is_early_leave_alfa'] = array (
                  'type' => 'TINYINT',
                  'constraint' => 1,
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('denda_alfa', $existingCols, true)) {
                $colsToAdd['denda_alfa'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '15,2',
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('absent_penalty', $existingCols, true)) {
                $colsToAdd['absent_penalty'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '15,2',
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('early_leave_minutes', $existingCols, true)) {
                $colsToAdd['early_leave_minutes'] = array (
                  'type' => 'INT',
                  'constraint' => 11,
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!empty($colsToAdd)) {
                $this->forge->addColumn('attendance_logs', $colsToAdd);
                $this->db->resetDataCache();
            }
        }

        // Table: shift_schemes
        $this->db->resetDataCache();
        if ($this->db->tableExists('shift_schemes')) {
            $existingCols = $this->db->getFieldNames('shift_schemes');
            $colsToAdd = [];
            if (!in_array('break_duration', $existingCols, true)) {
                $colsToAdd['break_duration'] = array (
                  'type' => 'INT',
                  'constraint' => 11,
                  'default' => 60,
                  'null' => true,
                );
            }
            if (!in_array('break_start_time', $existingCols, true)) {
                $colsToAdd['break_start_time'] = array (
                  'type' => 'TIME',
                  'null' => true,
                );
            }
            if (!in_array('break_end_time', $existingCols, true)) {
                $colsToAdd['break_end_time'] = array (
                  'type' => 'TIME',
                  'null' => true,
                );
            }
            if (!empty($colsToAdd)) {
                $this->forge->addColumn('shift_schemes', $colsToAdd);
                $this->db->resetDataCache();
            }
        }

        // Table: company_payroll_setting
        $this->db->resetDataCache();
        if ($this->db->tableExists('company_payroll_setting')) {
            $existingCols = $this->db->getFieldNames('company_payroll_setting');
            $colsToAdd = [];
            if (!in_array('early_arrival_min_minutes', $existingCols, true)) {
                $colsToAdd['early_arrival_min_minutes'] = array (
                  'type' => 'INT',
                  'constraint' => 11,
                  'default' => 15,
                  'null' => true,
                );
            }
            if (!in_array('early_arrival_calculation_unit', $existingCols, true)) {
                $colsToAdd['early_arrival_calculation_unit'] = array (
                  'type' => 'VARCHAR',
                  'constraint' => '20',
                  'default' => 'hour',
                  'null' => true,
                );
            }
            if (!in_array('early_arrival_rounding_method', $existingCols, true)) {
                $colsToAdd['early_arrival_rounding_method'] = array (
                  'type' => 'VARCHAR',
                  'constraint' => '20',
                  'default' => 'floor',
                  'null' => true,
                );
            }
            if (!empty($colsToAdd)) {
                $this->forge->addColumn('company_payroll_setting', $colsToAdd);
                $this->db->resetDataCache();
            }
        }

        // Table: payroll_attendance
        $this->db->resetDataCache();
        if ($this->db->tableExists('payroll_attendance')) {
            $existingCols = $this->db->getFieldNames('payroll_attendance');
            $colsToAdd = [];
            if (!in_array('early_arrival_minutes', $existingCols, true)) {
                $colsToAdd['early_arrival_minutes'] = array (
                  'type' => 'INT',
                  'constraint' => 11,
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('is_manual', $existingCols, true)) {
                $colsToAdd['is_manual'] = array (
                  'type' => 'TINYINT',
                  'constraint' => 1,
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('jam_lembur_hari_biasa', $existingCols, true)) {
                $colsToAdd['jam_lembur_hari_biasa'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '10,2',
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('jam_lembur_hari_libur', $existingCols, true)) {
                $colsToAdd['jam_lembur_hari_libur'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '10,2',
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!empty($colsToAdd)) {
                $this->forge->addColumn('payroll_attendance', $colsToAdd);
                $this->db->resetDataCache();
            }
        }

        // Table: payroll_final
        $this->db->resetDataCache();
        if ($this->db->tableExists('payroll_final')) {
            $existingCols = $this->db->getFieldNames('payroll_final');
            $colsToAdd = [];
            if (!in_array('bpjs_kes_karyawan', $existingCols, true)) {
                $colsToAdd['bpjs_kes_karyawan'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '15,2',
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('bpjs_kes_perusahaan', $existingCols, true)) {
                $colsToAdd['bpjs_kes_perusahaan'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '15,2',
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('bpjs_jht_karyawan', $existingCols, true)) {
                $colsToAdd['bpjs_jht_karyawan'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '15,2',
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('bpjs_jht_perusahaan', $existingCols, true)) {
                $colsToAdd['bpjs_jht_perusahaan'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '15,2',
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('bpjs_jp_karyawan', $existingCols, true)) {
                $colsToAdd['bpjs_jp_karyawan'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '15,2',
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('bpjs_jp_perusahaan', $existingCols, true)) {
                $colsToAdd['bpjs_jp_perusahaan'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '15,2',
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('bpjs_jkk_perusahaan', $existingCols, true)) {
                $colsToAdd['bpjs_jkk_perusahaan'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '15,2',
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('bpjs_jkm_perusahaan', $existingCols, true)) {
                $colsToAdd['bpjs_jkm_perusahaan'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '15,2',
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('pph21', $existingCols, true)) {
                $colsToAdd['pph21'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '15,2',
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('tax_allowance', $existingCols, true)) {
                $colsToAdd['tax_allowance'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '15,2',
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('tax_method', $existingCols, true)) {
                $colsToAdd['tax_method'] = array (
                  'type' => 'VARCHAR',
                  'constraint' => '50',
                  'default' => 'Gross',
                  'null' => true,
                );
            }
            if (!in_array('ptkp_status', $existingCols, true)) {
                $colsToAdd['ptkp_status'] = array (
                  'type' => 'VARCHAR',
                  'constraint' => '10',
                  'default' => 'TK/0',
                  'null' => true,
                );
            }
            if (!in_array('potongan_absen', $existingCols, true)) {
                $colsToAdd['potongan_absen'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '15,2',
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('jam_lembur', $existingCols, true)) {
                $colsToAdd['jam_lembur'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '10,2',
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('lembur_pay', $existingCols, true)) {
                $colsToAdd['lembur_pay'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '15,2',
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('bonus_tambahan', $existingCols, true)) {
                $colsToAdd['bonus_tambahan'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '15,2',
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('potongan_terlambat', $existingCols, true)) {
                $colsToAdd['potongan_terlambat'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '15,2',
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('potongan_pulang_cepat', $existingCols, true)) {
                $colsToAdd['potongan_pulang_cepat'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '15,2',
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('late_hours', $existingCols, true)) {
                $colsToAdd['late_hours'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '10,2',
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('early_leave_hours', $existingCols, true)) {
                $colsToAdd['early_leave_hours'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '10,2',
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('early_arrival_minutes', $existingCols, true)) {
                $colsToAdd['early_arrival_minutes'] = array (
                  'type' => 'INT',
                  'constraint' => 11,
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('early_arrival_pay', $existingCols, true)) {
                $colsToAdd['early_arrival_pay'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '15,2',
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('jam_lembur_biasa', $existingCols, true)) {
                $colsToAdd['jam_lembur_biasa'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '10,2',
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('jam_lembur_libur', $existingCols, true)) {
                $colsToAdd['jam_lembur_libur'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '10,2',
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!empty($colsToAdd)) {
                $this->forge->addColumn('payroll_final', $colsToAdd);
                $this->db->resetDataCache();
            }
        }

        // Table: payrolls
        $this->db->resetDataCache();
        if ($this->db->tableExists('payrolls')) {
            $existingCols = $this->db->getFieldNames('payrolls');
            $colsToAdd = [];
            if (!in_array('bpjs_kes_karyawan', $existingCols, true)) {
                $colsToAdd['bpjs_kes_karyawan'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '15,2',
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('bpjs_kes_perusahaan', $existingCols, true)) {
                $colsToAdd['bpjs_kes_perusahaan'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '15,2',
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('bpjs_jht_karyawan', $existingCols, true)) {
                $colsToAdd['bpjs_jht_karyawan'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '15,2',
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('bpjs_jht_perusahaan', $existingCols, true)) {
                $colsToAdd['bpjs_jht_perusahaan'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '15,2',
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('bpjs_jp_karyawan', $existingCols, true)) {
                $colsToAdd['bpjs_jp_karyawan'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '15,2',
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('bpjs_jp_perusahaan', $existingCols, true)) {
                $colsToAdd['bpjs_jp_perusahaan'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '15,2',
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('bpjs_jkk_perusahaan', $existingCols, true)) {
                $colsToAdd['bpjs_jkk_perusahaan'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '15,2',
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('bpjs_jkm_perusahaan', $existingCols, true)) {
                $colsToAdd['bpjs_jkm_perusahaan'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '15,2',
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('pph21', $existingCols, true)) {
                $colsToAdd['pph21'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '15,2',
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('tax_allowance', $existingCols, true)) {
                $colsToAdd['tax_allowance'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '15,2',
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('tax_method', $existingCols, true)) {
                $colsToAdd['tax_method'] = array (
                  'type' => 'VARCHAR',
                  'constraint' => '50',
                  'default' => 'Gross',
                  'null' => true,
                );
            }
            if (!in_array('ptkp_status', $existingCols, true)) {
                $colsToAdd['ptkp_status'] = array (
                  'type' => 'VARCHAR',
                  'constraint' => '10',
                  'default' => 'TK/0',
                  'null' => true,
                );
            }
            if (!in_array('potongan_absen', $existingCols, true)) {
                $colsToAdd['potongan_absen'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '15,2',
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('lembur_pay', $existingCols, true)) {
                $colsToAdd['lembur_pay'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '15,2',
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!in_array('bonus_tambahan', $existingCols, true)) {
                $colsToAdd['bonus_tambahan'] = array (
                  'type' => 'DECIMAL',
                  'constraint' => '15,2',
                  'default' => 0,
                  'null' => true,
                );
            }
            if (!empty($colsToAdd)) {
                $this->forge->addColumn('payrolls', $colsToAdd);
                $this->db->resetDataCache();
            }
        }

        // =====================================================================
        // MASTER DATA SEEDING (Auto-runs if tables are empty)
        // =====================================================================

        // Seed default admin user if users table is empty or admin doesn't exist
        if ($this->db->tableExists('users')) {
            $adminExists = $this->db->table('users')->where('username', 'admin')->countAllResults();
            if ($adminExists === 0) {
                $this->db->table('users')->insert([
                    'username' => 'admin',
                    'email'    => 'admin@example.com',
                    'password' => 'admin123',
                    'role'     => 'admin',
                    'full_name'=> 'Administrator',
                    'is_active'=> 1,
                    'created_at'=> date('Y-m-d H:i:s'),
                ]);
            }
        }

        // Seed system_settings and company_payroll_setting
        if ($this->db->tableExists('system_settings')) {
            $sysCount = $this->db->table('system_settings')->countAllResults();
            if ($sysCount === 0) {
                $defaultSettings = [
                    ['setting_key' => 'overtime_divisor', 'setting_value' => '173', 'description' => 'Pembagi jam kerja bulanan untuk kalkulasi upah per jam lembur'],
                    ['setting_key' => 'standard_work_days', 'setting_value' => '22', 'description' => 'Jumlah hari kerja standar per bulan'],
                    ['setting_key' => 'standard_work_hours', 'setting_value' => '8', 'description' => 'Jumlah jam kerja standar per hari'],
                    ['setting_key' => 'max_overtime_regular', 'setting_value' => '3', 'description' => 'Batas maksimal jam lembur reguler per hari kerja'],
                    ['setting_key' => 'overtime_multiplier_workday', 'setting_value' => '1.5', 'description' => 'Pengali tarif lembur hari kerja'],
                    ['setting_key' => 'overtime_multiplier_holiday', 'setting_value' => '2.0', 'description' => 'Pengali tarif lembur hari libur/weekend'],
                    ['setting_key' => 'minimum_overtime_minutes', 'setting_value' => '30', 'description' => 'Durasi minimum lembur yang dihitung (menit)'],
                    ['setting_key' => 'overtime_start_after_hours', 'setting_value' => '8', 'description' => 'Mulai menghitung lembur setelah melewati batas jam kerja normal harian'],
                ];
                $this->db->table('system_settings')->insertBatch($defaultSettings);
            }
        }

        if ($this->db->tableExists('company_payroll_setting')) {
            $compCount = $this->db->table('company_payroll_setting')->countAllResults();
            if ($compCount === 0) {
                $this->db->table('company_payroll_setting')->insert([
                    'early_arrival_enabled'     => 1,
                    'max_early_arrival_minutes' => 180,
                    'created_at'                => date('Y-m-d H:i:s'),
                ]);
            }
        }

        // Seed minimum_wages (all 296 UMK + 34 UMP for 2024 & 2026)
        if ($this->db->tableExists('minimum_wages')) {
            $mwCount = $this->db->table('minimum_wages')->countAllResults();
            if ($mwCount === 0) {
                $mwData = array (
  0 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => '1300000',
    'nama_daerah' => 'Jakarta',
    'provinsi' => NULL,
    'nominal' => 7000000.0,
    'tahun' => 2024,
  ),
  1 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 11.16',
    'nama_daerah' => 'KAB. ACEH TAMIANG',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  2 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 11.04',
    'nama_daerah' => 'KAB. ACEH TENGAH',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  3 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 11.03',
    'nama_daerah' => 'KAB. ACEH TIMUR',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  4 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 12.09',
    'nama_daerah' => 'KAB. ASAHAN',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  5 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 51.03',
    'nama_daerah' => 'KAB. BADUNG',
    'provinsi' => NULL,
    'nominal' => 5000000.0,
    'tahun' => 2024,
  ),
  6 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 32.04',
    'nama_daerah' => 'KAB. BANDUNG',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  7 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 32.17',
    'nama_daerah' => 'KAB. BANDUNG BARAT',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  8 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 72.01',
    'nama_daerah' => 'KAB. BANGGAI',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  9 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.26',
    'nama_daerah' => 'KAB. BANGKALAN',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  10 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.04',
    'nama_daerah' => 'KAB. BANJARNEGARA',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  11 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 73.03',
    'nama_daerah' => 'KAB. BANTAENG',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  12 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 34.02',
    'nama_daerah' => 'KAB. BANTUL',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  13 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 16.07',
    'nama_daerah' => 'KAB. BANYUASIN',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  14 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.02',
    'nama_daerah' => 'KAB. BANYUMAS',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  15 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.10',
    'nama_daerah' => 'KAB. BANYUWANGI',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  16 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 73.11',
    'nama_daerah' => 'KAB. BARRU',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  17 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.25',
    'nama_daerah' => 'KAB. BATANG',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  18 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 12.19',
    'nama_daerah' => 'KAB. BATU BARA',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  19 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 32.16',
    'nama_daerah' => 'KAB. BEKASI',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  20 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 19.02',
    'nama_daerah' => 'KAB. BELITUNG',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  21 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 14.03',
    'nama_daerah' => 'KAB. BENGKALIS',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  22 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 17.01',
    'nama_daerah' => 'KAB. BENGKULU SELATAN',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  23 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 64.03',
    'nama_daerah' => 'KAB. BERAU',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  24 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 11.11',
    'nama_daerah' => 'KAB. BIREUEN',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  25 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.16',
    'nama_daerah' => 'KAB. BLORA',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  26 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 32.01',
    'nama_daerah' => 'KAB. BOGOR',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  27 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.22',
    'nama_daerah' => 'KAB. BOJONEGORO',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  28 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.11',
    'nama_daerah' => 'KAB. BONDOWOSO',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  29 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 73.08',
    'nama_daerah' => 'KAB. BONE',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  30 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.09',
    'nama_daerah' => 'KAB. BOYOLALI',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  31 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.29',
    'nama_daerah' => 'KAB. BREBES',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  32 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 51.08',
    'nama_daerah' => 'KAB. BULELENG',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  33 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 73.02',
    'nama_daerah' => 'KAB. BULUKUMBA',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  34 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 65.01',
    'nama_daerah' => 'KAB. BULUNGAN',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  35 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 15.08',
    'nama_daerah' => 'KAB. BUNGO',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  36 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 32.07',
    'nama_daerah' => 'KAB. CIAMIS',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  37 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 32.03',
    'nama_daerah' => 'KAB. CIANJUR',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  38 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.01',
    'nama_daerah' => 'KAB. CILACAP',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  39 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 32.09',
    'nama_daerah' => 'KAB. CIREBON',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  40 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 12.07',
    'nama_daerah' => 'KAB. DELI SERDANG',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  41 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.21',
    'nama_daerah' => 'KAB. DEMAK',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  42 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 13.10',
    'nama_daerah' => 'KAB. DHARMASRAYA',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  43 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 53.08',
    'nama_daerah' => 'KAB. ENDE',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  44 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 73.16',
    'nama_daerah' => 'KAB. ENREKANG',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  45 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 32.05',
    'nama_daerah' => 'KAB. GARUT',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  46 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 51.04',
    'nama_daerah' => 'KAB. GIANYAR',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  47 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 75.01',
    'nama_daerah' => 'KAB. GORONTALO',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  48 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 73.06',
    'nama_daerah' => 'KAB. GOWA',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  49 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.25',
    'nama_daerah' => 'KAB. GRESIK',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  50 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.15',
    'nama_daerah' => 'KAB. GROBOGAN',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  51 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 63.07',
    'nama_daerah' => 'KAB. HULU SUNGAI TENGAH',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  52 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 63.08',
    'nama_daerah' => 'KAB. HULU SUNGAI UTARA',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  53 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 14.04',
    'nama_daerah' => 'KAB. INDRAGIRI HILIR',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  54 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 14.02',
    'nama_daerah' => 'KAB. INDRAGIRI HULU',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  55 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 32.12',
    'nama_daerah' => 'KAB. INDRAMAYU',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  56 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 91.03',
    'nama_daerah' => 'KAB. JAYAPURA',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  57 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.09',
    'nama_daerah' => 'KAB. JEMBER',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  58 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 51.01',
    'nama_daerah' => 'KAB. JEMBRANA',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  59 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 73.04',
    'nama_daerah' => 'KAB. JENEPONTO',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  60 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.20',
    'nama_daerah' => 'KAB. JEPARA',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  61 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.17',
    'nama_daerah' => 'KAB. JOMBANG',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  62 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 14.01',
    'nama_daerah' => 'KAB. KAMPAR',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  63 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 62.03',
    'nama_daerah' => 'KAB. KAPUAS',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  64 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 61.06',
    'nama_daerah' => 'KAB. KAPUAS HULU',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  65 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.13',
    'nama_daerah' => 'KAB. KARANGANYAR',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  66 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 32.15',
    'nama_daerah' => 'KAB. KARAWANG',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  67 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 12.06',
    'nama_daerah' => 'KAB. KARO',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  68 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.05',
    'nama_daerah' => 'KAB. KEBUMEN',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  69 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.24',
    'nama_daerah' => 'KAB. KENDAL',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  70 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 17.08',
    'nama_daerah' => 'KAB. KEPAHIANG',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  71 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 61.04',
    'nama_daerah' => 'KAB. KETAPANG',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  72 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.10',
    'nama_daerah' => 'KAB. KLATEN',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  73 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 74.01',
    'nama_daerah' => 'KAB. KOLAKA',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  74 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 63.02',
    'nama_daerah' => 'KAB. KOTABARU',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  75 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 62.01',
    'nama_daerah' => 'KAB. KOTAWARINGIN BARAT',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  76 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 62.02',
    'nama_daerah' => 'KAB. KOTAWARINGIN TIMUR',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  77 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 61.12',
    'nama_daerah' => 'KAB. KUBU RAYA',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  78 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.19',
    'nama_daerah' => 'KAB. KUDUS',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  79 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 34.01',
    'nama_daerah' => 'KAB. KULON PROGO',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  80 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 32.08',
    'nama_daerah' => 'KAB. KUNINGAN',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  81 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 64.07',
    'nama_daerah' => 'KAB. KUTAI BARAT',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  82 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 64.02',
    'nama_daerah' => 'KAB. KUTAI KARTANEGARA',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  83 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 64.08',
    'nama_daerah' => 'KAB. KUTAI TIMUR',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  84 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 12.10',
    'nama_daerah' => 'KAB. LABUHANBATU',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  85 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 12.23',
    'nama_daerah' => 'KAB. LABUHANBATU UTARA',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  86 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 16.04',
    'nama_daerah' => 'KAB. LAHAT',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  87 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.24',
    'nama_daerah' => 'KAB. LAMONGAN',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  88 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 18.04',
    'nama_daerah' => 'KAB. LAMPUNG BARAT',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  89 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 18.01',
    'nama_daerah' => 'KAB. LAMPUNG SELATAN',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  90 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 18.02',
    'nama_daerah' => 'KAB. LAMPUNG TENGAH',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  91 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 18.07',
    'nama_daerah' => 'KAB. LAMPUNG TIMUR',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  92 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 18.03',
    'nama_daerah' => 'KAB. LAMPUNG UTARA',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  93 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 61.08',
    'nama_daerah' => 'KAB. LANDAK',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  94 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 12.05',
    'nama_daerah' => 'KAB. LANGKAT',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  95 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 36.02',
    'nama_daerah' => 'KAB. LEBAK',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  96 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 52.01',
    'nama_daerah' => 'KAB. LOMBOK BARAT',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  97 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 52.02',
    'nama_daerah' => 'KAB. LOMBOK TENGAH',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  98 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 52.03',
    'nama_daerah' => 'KAB. LOMBOK TIMUR',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  99 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.08',
    'nama_daerah' => 'KAB. LUMAJANG',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  100 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 73.17',
    'nama_daerah' => 'KAB. LUWU',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  101 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 73.24',
    'nama_daerah' => 'KAB. LUWU TIMUR',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  102 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 73.22',
    'nama_daerah' => 'KAB. LUWU UTARA',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  103 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.19',
    'nama_daerah' => 'KAB. MADIUN',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  104 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.08',
    'nama_daerah' => 'KAB. MAGELANG',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  105 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.20',
    'nama_daerah' => 'KAB. MAGETAN',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  106 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 32.10',
    'nama_daerah' => 'KAB. MAJALENGKA',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  107 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 76.05',
    'nama_daerah' => 'KAB. MAJENE',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  108 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.07',
    'nama_daerah' => 'KAB. MALANG',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  109 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 76.02',
    'nama_daerah' => 'KAB. MAMUJU',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  110 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 12.13',
    'nama_daerah' => 'KAB. MANDAILING NATAL',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  111 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 53.15',
    'nama_daerah' => 'KAB. MANGGARAI BARAT',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  112 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 92.02',
    'nama_daerah' => 'KAB. MANOKWARI',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  113 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 73.09',
    'nama_daerah' => 'KAB. MAROS',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  114 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 61.10',
    'nama_daerah' => 'KAB. MELAWI',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  115 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 15.02',
    'nama_daerah' => 'KAB. MERANGIN',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  116 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 91.01',
    'nama_daerah' => 'KAB. MERAUKE',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  117 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 91.09',
    'nama_daerah' => 'KAB. MIMIKA',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  118 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 71.05',
    'nama_daerah' => 'KAB. MINAHASA SELATAN',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  119 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 71.06',
    'nama_daerah' => 'KAB. MINAHASA UTARA',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  120 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.16',
    'nama_daerah' => 'KAB. MOJOKERTO',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  121 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 72.06',
    'nama_daerah' => 'KAB. MOROWALI',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  122 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 16.03',
    'nama_daerah' => 'KAB. MUARA ENIM',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  123 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 16.06',
    'nama_daerah' => 'KAB. MUSI BANYUASIN',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  124 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 91.04',
    'nama_daerah' => 'KAB. NABIRE',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  125 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.18',
    'nama_daerah' => 'KAB. NGANJUK',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  126 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.21',
    'nama_daerah' => 'KAB. NGAWI',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  127 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 16.10',
    'nama_daerah' => 'KAB. OGAN ILIR',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  128 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 16.02',
    'nama_daerah' => 'KAB. OGAN KOMERING ILIR',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  129 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 16.01',
    'nama_daerah' => 'KAB. OGAN KOMERING ULU',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  130 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 16.09',
    'nama_daerah' => 'KAB. OGAN KOMERING ULU SELATAN',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  131 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 16.08',
    'nama_daerah' => 'KAB. OGAN KOMERING ULU TIMUR',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  132 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.01',
    'nama_daerah' => 'KAB. PACITAN',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  133 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.28',
    'nama_daerah' => 'KAB. PAMEKASAN',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  134 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 36.01',
    'nama_daerah' => 'KAB. PANDEGLANG',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  135 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 32.18',
    'nama_daerah' => 'KAB. PANGANDARAN',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  136 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 73.10',
    'nama_daerah' => 'KAB. PANGKAJENE KEPULAUAN',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  137 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 13.12',
    'nama_daerah' => 'KAB. PASAMAN BARAT',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  138 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.14',
    'nama_daerah' => 'KAB. PASURUAN',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  139 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.18',
    'nama_daerah' => 'KAB. PATI',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  140 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.26',
    'nama_daerah' => 'KAB. PEKALONGAN',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  141 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 14.05',
    'nama_daerah' => 'KAB. PELALAWAN',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  142 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.27',
    'nama_daerah' => 'KAB. PEMALANG',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  143 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 64.09',
    'nama_daerah' => 'KAB. PENAJAM PASER UTARA',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  144 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 18.09',
    'nama_daerah' => 'KAB. PESAWARAN',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  145 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 13.01',
    'nama_daerah' => 'KAB. PESISIR SELATAN',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  146 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 11.07',
    'nama_daerah' => 'KAB. PIDIE',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  147 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 73.15',
    'nama_daerah' => 'KAB. PINRANG',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  148 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 76.04',
    'nama_daerah' => 'KAB. POLEWALI MANDAR',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  149 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.02',
    'nama_daerah' => 'KAB. PONOROGO',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  150 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 18.10',
    'nama_daerah' => 'KAB. PRINGSEWU',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  151 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.13',
    'nama_daerah' => 'KAB. PROBOLINGGO',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  152 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.03',
    'nama_daerah' => 'KAB. PURBALINGGA',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  153 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 32.14',
    'nama_daerah' => 'KAB. PURWAKARTA',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  154 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.06',
    'nama_daerah' => 'KAB. PURWOREJO',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  155 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 17.02',
    'nama_daerah' => 'KAB. REJANG LEBONG',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  156 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.17',
    'nama_daerah' => 'KAB. REMBANG',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  157 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 14.07',
    'nama_daerah' => 'KAB. ROKAN HILIR',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  158 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 14.06',
    'nama_daerah' => 'KAB. ROKAN HULU',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  159 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 61.03',
    'nama_daerah' => 'KAB. SANGGAU',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  160 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 15.03',
    'nama_daerah' => 'KAB. SAROLANGUN',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  161 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.22',
    'nama_daerah' => 'KAB. SEMARANG',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  162 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 36.04',
    'nama_daerah' => 'KAB. SERANG',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  163 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 12.18',
    'nama_daerah' => 'KAB. SERDANG BEDAGAI',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  164 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 14.08',
    'nama_daerah' => 'KAB. SIAK',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  165 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 73.14',
    'nama_daerah' => 'KAB. SIDENRENG RAPPANG',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  166 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.15',
    'nama_daerah' => 'KAB. SIDOARJO',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  167 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 72.10',
    'nama_daerah' => 'KAB. SIGI',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  168 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 73.07',
    'nama_daerah' => 'KAB. SINJAI',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  169 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 61.05',
    'nama_daerah' => 'KAB. SINTANG',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  170 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.12',
    'nama_daerah' => 'KAB. SITUBONDO',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  171 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 34.04',
    'nama_daerah' => 'KAB. SLEMAN',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  172 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 73.12',
    'nama_daerah' => 'KAB. SOPPENG',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  173 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 92.01',
    'nama_daerah' => 'KAB. SORONG',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  174 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.14',
    'nama_daerah' => 'KAB. SRAGEN',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  175 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 32.13',
    'nama_daerah' => 'KAB. SUBANG',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  176 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 32.02',
    'nama_daerah' => 'KAB. SUKABUMI',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  177 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.11',
    'nama_daerah' => 'KAB. SUKOHARJO',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  178 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 52.04',
    'nama_daerah' => 'KAB. SUMBAWA',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  179 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 32.11',
    'nama_daerah' => 'KAB. SUMEDANG',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  180 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.29',
    'nama_daerah' => 'KAB. SUMENEP',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  181 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 63.09',
    'nama_daerah' => 'KAB. TABALONG',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  182 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 51.02',
    'nama_daerah' => 'KAB. TABANAN',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  183 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 73.05',
    'nama_daerah' => 'KAB. TAKALAR',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  184 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 73.18',
    'nama_daerah' => 'KAB. TANA TORAJA',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  185 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 63.10',
    'nama_daerah' => 'KAB. TANAH BUMBU',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  186 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 63.01',
    'nama_daerah' => 'KAB. TANAH LAUT',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  187 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 36.03',
    'nama_daerah' => 'KAB. TANGERANG',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  188 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 18.06',
    'nama_daerah' => 'KAB. TANGGAMUS',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  189 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 12.02',
    'nama_daerah' => 'KAB. TAPANULI UTARA',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  190 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 63.05',
    'nama_daerah' => 'KAB. TAPIN',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  191 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 32.06',
    'nama_daerah' => 'KAB. TASIKMALAYA',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  192 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 15.09',
    'nama_daerah' => 'KAB. TEBO',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  193 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.28',
    'nama_daerah' => 'KAB. TEGAL',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  194 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.23',
    'nama_daerah' => 'KAB. TEMANGGUNG',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  195 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 12.12',
    'nama_daerah' => 'KAB. TOBA SAMOSIR',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  196 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 73.26',
    'nama_daerah' => 'KAB. TORAJA UTARA',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  197 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.23',
    'nama_daerah' => 'KAB. TUBAN',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  198 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 18.05',
    'nama_daerah' => 'KAB. TULANG BAWANG',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  199 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.04',
    'nama_daerah' => 'KAB. TULUNGAGUNG',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  200 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 73.13',
    'nama_daerah' => 'KAB. WAJO',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  201 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.12',
    'nama_daerah' => 'KAB. WONOGIRI',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  202 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.07',
    'nama_daerah' => 'KAB. WONOSOBO',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  203 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 31.73',
    'nama_daerah' => 'KOTA ADM. JAKARTA BARAT',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  204 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 31.71',
    'nama_daerah' => 'KOTA ADM. JAKARTA PUSAT',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  205 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 31.74',
    'nama_daerah' => 'KOTA ADM. JAKARTA SELATAN',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  206 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 31.75',
    'nama_daerah' => 'KOTA ADM. JAKARTA TIMUR',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  207 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 31.72',
    'nama_daerah' => 'KOTA ADM. JAKARTA UTARA',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  208 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 81.71',
    'nama_daerah' => 'KOTA AMBON',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  209 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 64.71',
    'nama_daerah' => 'KOTA BALIKPAPAN',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  210 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 11.71',
    'nama_daerah' => 'KOTA BANDA ACEH',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  211 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 18.71',
    'nama_daerah' => 'KOTA BANDAR LAMPUNG',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  212 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 32.73',
    'nama_daerah' => 'KOTA BANDUNG',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  213 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 32.79',
    'nama_daerah' => 'KOTA BANJAR',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  214 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 63.72',
    'nama_daerah' => 'KOTA BANJARBARU',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  215 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 63.71',
    'nama_daerah' => 'KOTA BANJARMASIN',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  216 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 21.71',
    'nama_daerah' => 'KOTA BATAM',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  217 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.79',
    'nama_daerah' => 'KOTA BATU',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  218 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 74.72',
    'nama_daerah' => 'KOTA BAU BAU',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  219 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 32.75',
    'nama_daerah' => 'KOTA BEKASI',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  220 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 17.71',
    'nama_daerah' => 'KOTA BENGKULU',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  221 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 12.75',
    'nama_daerah' => 'KOTA BINJAI',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  222 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 71.72',
    'nama_daerah' => 'KOTA BITUNG',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  223 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.72',
    'nama_daerah' => 'KOTA BLITAR',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  224 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 32.71',
    'nama_daerah' => 'KOTA BOGOR',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  225 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 64.74',
    'nama_daerah' => 'KOTA BONTANG',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  226 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 13.75',
    'nama_daerah' => 'KOTA BUKITTINGGI',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  227 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 36.72',
    'nama_daerah' => 'KOTA CILEGON',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  228 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 32.77',
    'nama_daerah' => 'KOTA CIMAHI',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  229 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 32.74',
    'nama_daerah' => 'KOTA CIREBON',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  230 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 51.71',
    'nama_daerah' => 'KOTA DENPASAR',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  231 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 32.76',
    'nama_daerah' => 'KOTA DEPOK',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  232 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 14.72',
    'nama_daerah' => 'KOTA DUMAI',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  233 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 75.71',
    'nama_daerah' => 'KOTA GORONTALO',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  234 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 15.71',
    'nama_daerah' => 'KOTA JAMBI',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  235 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 91.71',
    'nama_daerah' => 'KOTA JAYAPURA',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  236 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.71',
    'nama_daerah' => 'KOTA KEDIRI',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  237 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 74.71',
    'nama_daerah' => 'KOTA KENDARI',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  238 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 71.74',
    'nama_daerah' => 'KOTA KOTAMOBAGU',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  239 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 53.71',
    'nama_daerah' => 'KOTA KUPANG',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  240 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 11.74',
    'nama_daerah' => 'KOTA LANGSA',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  241 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 11.73',
    'nama_daerah' => 'KOTA LHOKSEUMAWE',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  242 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 16.73',
    'nama_daerah' => 'KOTA LUBUK LINGGAU',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  243 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.77',
    'nama_daerah' => 'KOTA MADIUN',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  244 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.71',
    'nama_daerah' => 'KOTA MAGELANG',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  245 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 73.71',
    'nama_daerah' => 'KOTA MAKASSAR',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  246 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.73',
    'nama_daerah' => 'KOTA MALANG',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  247 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 71.71',
    'nama_daerah' => 'KOTA MANADO',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  248 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 52.71',
    'nama_daerah' => 'KOTA MATARAM',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  249 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 12.71',
    'nama_daerah' => 'KOTA MEDAN',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  250 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 18.72',
    'nama_daerah' => 'KOTA METRO',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  251 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.76',
    'nama_daerah' => 'KOTA MOJOKERTO',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  252 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 13.71',
    'nama_daerah' => 'KOTA PADANG',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  253 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 13.74',
    'nama_daerah' => 'KOTA PADANG PANJANG',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  254 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 12.77',
    'nama_daerah' => 'KOTA PADANG SIDEMPUAN',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  255 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 16.72',
    'nama_daerah' => 'KOTA PAGAR ALAM',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  256 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 62.71',
    'nama_daerah' => 'KOTA PALANGKARAYA',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  257 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 16.71',
    'nama_daerah' => 'KOTA PALEMBANG',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  258 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 73.73',
    'nama_daerah' => 'KOTA PALOPO',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  259 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 72.71',
    'nama_daerah' => 'KOTA PALU',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  260 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 19.71',
    'nama_daerah' => 'KOTA PANGKAL PINANG',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  261 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 73.72',
    'nama_daerah' => 'KOTA PARE PARE',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  262 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 13.77',
    'nama_daerah' => 'KOTA PARIAMAN',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  263 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.75',
    'nama_daerah' => 'KOTA PASURUAN',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  264 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 13.76',
    'nama_daerah' => 'KOTA PAYAKUMBUH',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  265 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.75',
    'nama_daerah' => 'KOTA PEKALONGAN',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  266 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 14.71',
    'nama_daerah' => 'KOTA PEKANBARU',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  267 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 12.72',
    'nama_daerah' => 'KOTA PEMATANGSIANTAR',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  268 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 61.71',
    'nama_daerah' => 'KOTA PONTIANAK',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  269 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 16.74',
    'nama_daerah' => 'KOTA PRABUMULIH',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  270 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.74',
    'nama_daerah' => 'KOTA PROBOLINGGO',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  271 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.73',
    'nama_daerah' => 'KOTA SALATIGA',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  272 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 64.72',
    'nama_daerah' => 'KOTA SAMARINDA',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  273 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.74',
    'nama_daerah' => 'KOTA SEMARANG',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  274 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 36.73',
    'nama_daerah' => 'KOTA SERANG',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  275 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 12.73',
    'nama_daerah' => 'KOTA SIBOLGA',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  276 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 61.72',
    'nama_daerah' => 'KOTA SINGKAWANG',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  277 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 13.72',
    'nama_daerah' => 'KOTA SOLOK',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  278 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 92.71',
    'nama_daerah' => 'KOTA SORONG',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  279 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 32.72',
    'nama_daerah' => 'KOTA SUKABUMI',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  280 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 15.72',
    'nama_daerah' => 'KOTA SUNGAI PENUH',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  281 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.78',
    'nama_daerah' => 'KOTA SURABAYA',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  282 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.72',
    'nama_daerah' => 'KOTA SURAKARTA',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  283 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 36.71',
    'nama_daerah' => 'KOTA TANGERANG',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  284 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 36.74',
    'nama_daerah' => 'KOTA TANGERANG SELATAN',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  285 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 12.74',
    'nama_daerah' => 'KOTA TANJUNG BALAI',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  286 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 21.72',
    'nama_daerah' => 'KOTA TANJUNG PINANG',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  287 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 65.71',
    'nama_daerah' => 'KOTA TARAKAN',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  288 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 32.78',
    'nama_daerah' => 'KOTA TASIKMALAYA',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  289 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 12.76',
    'nama_daerah' => 'KOTA TEBING TINGGI',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  290 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.76',
    'nama_daerah' => 'KOTA TEGAL',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  291 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 82.71',
    'nama_daerah' => 'KOTA TERNATE',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  292 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 71.73',
    'nama_daerah' => 'KOTA TOMOHON',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  293 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 34.71',
    'nama_daerah' => 'KOTA YOGYAKARTA',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  294 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'MYS-00001',
    'nama_daerah' => 'Malaysia',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  295 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'SIN-01001',
    'nama_daerah' => 'Singapore',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  296 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 11',
    'nama_daerah' => 'ACEH',
    'provinsi' => NULL,
    'nominal' => 3000000.0,
    'tahun' => 2024,
  ),
  297 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 12',
    'nama_daerah' => 'SUMATERA UTARA',
    'provinsi' => NULL,
    'nominal' => 4000000.0,
    'tahun' => 2024,
  ),
  298 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 17',
    'nama_daerah' => 'BENGKULU',
    'provinsi' => NULL,
    'nominal' => 2000000.0,
    'tahun' => 2024,
  ),
  299 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 15',
    'nama_daerah' => 'JAMBI',
    'provinsi' => NULL,
    'nominal' => 2000000.0,
    'tahun' => 2024,
  ),
  300 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 14',
    'nama_daerah' => 'RIAU',
    'provinsi' => NULL,
    'nominal' => 2000000.0,
    'tahun' => 2024,
  ),
  301 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 13',
    'nama_daerah' => 'SUMATERA BARAT',
    'provinsi' => NULL,
    'nominal' => 2000000.0,
    'tahun' => 2024,
  ),
  302 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 16',
    'nama_daerah' => 'SUMATERA SELATAN',
    'provinsi' => NULL,
    'nominal' => 2000000.0,
    'tahun' => 2024,
  ),
  303 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 18',
    'nama_daerah' => 'LAMPUNG',
    'provinsi' => NULL,
    'nominal' => 2000000.0,
    'tahun' => 2024,
  ),
  304 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 19',
    'nama_daerah' => 'KEP. BANGKA BELITUNG',
    'provinsi' => NULL,
    'nominal' => 2000000.0,
    'tahun' => 2024,
  ),
  305 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 21',
    'nama_daerah' => 'KEP. RIAU',
    'provinsi' => NULL,
    'nominal' => 2000000.0,
    'tahun' => 2024,
  ),
  306 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 36',
    'nama_daerah' => 'BANTEN',
    'provinsi' => NULL,
    'nominal' => 2000000.0,
    'tahun' => 2024,
  ),
  307 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 32',
    'nama_daerah' => 'JAWA BARAT',
    'provinsi' => NULL,
    'nominal' => 2000000.0,
    'tahun' => 2024,
  ),
  308 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 31',
    'nama_daerah' => 'DKI JAKARTA',
    'provinsi' => NULL,
    'nominal' => 2000000.0,
    'tahun' => 2024,
  ),
  309 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 33',
    'nama_daerah' => 'JAWA TENGAH',
    'provinsi' => NULL,
    'nominal' => 2000000.0,
    'tahun' => 2024,
  ),
  310 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 35',
    'nama_daerah' => 'JAWA TIMUR',
    'provinsi' => NULL,
    'nominal' => 2000000.0,
    'tahun' => 2024,
  ),
  311 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 34',
    'nama_daerah' => 'DI YOGYAKARTA',
    'provinsi' => NULL,
    'nominal' => 2000000.0,
    'tahun' => 2024,
  ),
  312 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 51',
    'nama_daerah' => 'BALI',
    'provinsi' => NULL,
    'nominal' => 2000000.0,
    'tahun' => 2024,
  ),
  313 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 52',
    'nama_daerah' => 'NUSA TENGGARA BARAT',
    'provinsi' => NULL,
    'nominal' => 2000000.0,
    'tahun' => 2024,
  ),
  314 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 53',
    'nama_daerah' => 'NUSA TENGGARA TIMUR',
    'provinsi' => NULL,
    'nominal' => 2000000.0,
    'tahun' => 2024,
  ),
  315 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 61',
    'nama_daerah' => 'KALIMANTAN BARAT',
    'provinsi' => NULL,
    'nominal' => 2000000.0,
    'tahun' => 2024,
  ),
  316 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 63',
    'nama_daerah' => 'KALIMANTAN SELATAN',
    'provinsi' => NULL,
    'nominal' => 2000000.0,
    'tahun' => 2024,
  ),
  317 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 62',
    'nama_daerah' => 'KALIMANTAN TENGAH',
    'provinsi' => NULL,
    'nominal' => 2000000.0,
    'tahun' => 2024,
  ),
  318 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 64',
    'nama_daerah' => 'KALIMANTAN TIMUR',
    'provinsi' => NULL,
    'nominal' => 2000000.0,
    'tahun' => 2024,
  ),
  319 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 75',
    'nama_daerah' => 'GORONTALO',
    'provinsi' => NULL,
    'nominal' => 2000000.0,
    'tahun' => 2024,
  ),
  320 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 73',
    'nama_daerah' => 'SULAWESI SELATAN',
    'provinsi' => NULL,
    'nominal' => 2000000.0,
    'tahun' => 2024,
  ),
  321 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 74',
    'nama_daerah' => 'SULAWESI TENGGARA',
    'provinsi' => NULL,
    'nominal' => 2000000.0,
    'tahun' => 2024,
  ),
  322 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 72',
    'nama_daerah' => 'SULAWESI TENGAH',
    'provinsi' => NULL,
    'nominal' => 2000000.0,
    'tahun' => 2024,
  ),
  323 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 71',
    'nama_daerah' => 'SULAWESI UTARA',
    'provinsi' => NULL,
    'nominal' => 2000000.0,
    'tahun' => 2024,
  ),
  324 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 76',
    'nama_daerah' => 'SULAWESI BARAT',
    'provinsi' => NULL,
    'nominal' => 2000000.0,
    'tahun' => 2024,
  ),
  325 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 81',
    'nama_daerah' => 'MALUKU',
    'provinsi' => NULL,
    'nominal' => 2000000.0,
    'tahun' => 2024,
  ),
  326 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 82',
    'nama_daerah' => 'MALUKU UTARA',
    'provinsi' => NULL,
    'nominal' => 2000000.0,
    'tahun' => 2024,
  ),
  327 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 91',
    'nama_daerah' => 'PAPUA',
    'provinsi' => NULL,
    'nominal' => 1000000.0,
    'tahun' => 2024,
  ),
  328 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 92',
    'nama_daerah' => 'PAPUA BARAT',
    'provinsi' => NULL,
    'nominal' => 2000000.0,
    'tahun' => 2024,
  ),
  329 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 65',
    'nama_daerah' => 'KALIMANTAN UTARA',
    'provinsi' => NULL,
    'nominal' => 2000000.0,
    'tahun' => 2024,
  ),
  330 => 
  array (
    'tipe' => 'NOMINAL',
    'kode_daerah' => 'NOM-MANUAL',
    'nama_daerah' => 'Gaji Disepakati (Rp 11.000.000)',
    'provinsi' => NULL,
    'nominal' => 11000000.0,
    'tahun' => 2026,
  ),
  331 => 
  array (
    'tipe' => 'NOMINAL',
    'kode_daerah' => 'NOM-MANUAL',
    'nama_daerah' => 'Gaji Disepakati (Rp 9.000.000)',
    'provinsi' => NULL,
    'nominal' => 9000000.0,
    'tahun' => 2026,
  ),
  332 => 
  array (
    'tipe' => 'NOMINAL',
    'kode_daerah' => 'NOMINAL',
    'nama_daerah' => 'Nominal Kesepakatan',
    'provinsi' => '',
    'nominal' => 4250000.0,
    'tahun' => 2026,
  ),
  333 => 
  array (
    'tipe' => 'NOMINAL',
    'kode_daerah' => 'NOMINAL',
    'nama_daerah' => 'Nominal Kesepakatan',
    'provinsi' => '',
    'nominal' => 4500000.0,
    'tahun' => 2026,
  ),
  334 => 
  array (
    'tipe' => 'NOMINAL',
    'kode_daerah' => 'NOMINAL',
    'nama_daerah' => 'Nominal Kesepakatan',
    'provinsi' => '',
    'nominal' => 12.0,
    'tahun' => 2026,
  ),
  335 => 
  array (
    'tipe' => 'NOMINAL',
    'kode_daerah' => 'NOMINAL',
    'nama_daerah' => 'Nominal Kesepakatan',
    'provinsi' => '',
    'nominal' => 12000000.0,
    'tahun' => 2026,
  ),
  336 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => '1300000',
    'nama_daerah' => 'Jakarta',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  337 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 11.16',
    'nama_daerah' => 'KAB. ACEH TAMIANG',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  338 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 11.04',
    'nama_daerah' => 'KAB. ACEH TENGAH',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  339 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 11.03',
    'nama_daerah' => 'KAB. ACEH TIMUR',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  340 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 12.09',
    'nama_daerah' => 'KAB. ASAHAN',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  341 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 51.03',
    'nama_daerah' => 'KAB. BADUNG',
    'provinsi' => '',
    'nominal' => 5000000.0,
    'tahun' => 2026,
  ),
  342 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 32.04',
    'nama_daerah' => 'KAB. BANDUNG',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  343 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 32.17',
    'nama_daerah' => 'KAB. BANDUNG BARAT',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  344 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 72.01',
    'nama_daerah' => 'KAB. BANGGAI',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  345 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.26',
    'nama_daerah' => 'KAB. BANGKALAN',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  346 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.04',
    'nama_daerah' => 'KAB. BANJARNEGARA',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  347 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 73.03',
    'nama_daerah' => 'KAB. BANTAENG',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  348 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 34.02',
    'nama_daerah' => 'KAB. BANTUL',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  349 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 16.07',
    'nama_daerah' => 'KAB. BANYUASIN',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  350 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.02',
    'nama_daerah' => 'KAB. BANYUMAS',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  351 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.10',
    'nama_daerah' => 'KAB. BANYUWANGI',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  352 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 73.11',
    'nama_daerah' => 'KAB. BARRU',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  353 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.25',
    'nama_daerah' => 'KAB. BATANG',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  354 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 12.19',
    'nama_daerah' => 'KAB. BATU BARA',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  355 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 32.16',
    'nama_daerah' => 'KAB. BEKASI',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  356 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 19.02',
    'nama_daerah' => 'KAB. BELITUNG',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  357 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 14.03',
    'nama_daerah' => 'KAB. BENGKALIS',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  358 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 17.01',
    'nama_daerah' => 'KAB. BENGKULU SELATAN',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  359 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 64.03',
    'nama_daerah' => 'KAB. BERAU',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  360 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 11.11',
    'nama_daerah' => 'KAB. BIREUEN',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  361 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.16',
    'nama_daerah' => 'KAB. BLORA',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  362 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 32.01',
    'nama_daerah' => 'KAB. BOGOR',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  363 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.22',
    'nama_daerah' => 'KAB. BOJONEGORO',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  364 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.11',
    'nama_daerah' => 'KAB. BONDOWOSO',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  365 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 73.08',
    'nama_daerah' => 'KAB. BONE',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  366 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.09',
    'nama_daerah' => 'KAB. BOYOLALI',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  367 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.29',
    'nama_daerah' => 'KAB. BREBES',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  368 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 51.08',
    'nama_daerah' => 'KAB. BULELENG',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  369 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 73.02',
    'nama_daerah' => 'KAB. BULUKUMBA',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  370 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 65.01',
    'nama_daerah' => 'KAB. BULUNGAN',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  371 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 15.08',
    'nama_daerah' => 'KAB. BUNGO',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  372 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 32.07',
    'nama_daerah' => 'KAB. CIAMIS',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  373 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 32.03',
    'nama_daerah' => 'KAB. CIANJUR',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  374 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.01',
    'nama_daerah' => 'KAB. CILACAP',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  375 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 32.09',
    'nama_daerah' => 'KAB. CIREBON',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  376 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 12.07',
    'nama_daerah' => 'KAB. DELI SERDANG',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  377 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.21',
    'nama_daerah' => 'KAB. DEMAK',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  378 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 13.10',
    'nama_daerah' => 'KAB. DHARMASRAYA',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  379 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 53.08',
    'nama_daerah' => 'KAB. ENDE',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  380 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 73.16',
    'nama_daerah' => 'KAB. ENREKANG',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  381 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 32.05',
    'nama_daerah' => 'KAB. GARUT',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  382 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 51.04',
    'nama_daerah' => 'KAB. GIANYAR',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  383 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 75.01',
    'nama_daerah' => 'KAB. GORONTALO',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  384 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 73.06',
    'nama_daerah' => 'KAB. GOWA',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  385 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.25',
    'nama_daerah' => 'KAB. GRESIK',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  386 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.15',
    'nama_daerah' => 'KAB. GROBOGAN',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  387 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 63.07',
    'nama_daerah' => 'KAB. HULU SUNGAI TENGAH',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  388 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 63.08',
    'nama_daerah' => 'KAB. HULU SUNGAI UTARA',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  389 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 14.04',
    'nama_daerah' => 'KAB. INDRAGIRI HILIR',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  390 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 14.02',
    'nama_daerah' => 'KAB. INDRAGIRI HULU',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  391 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 32.12',
    'nama_daerah' => 'KAB. INDRAMAYU',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  392 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 91.03',
    'nama_daerah' => 'KAB. JAYAPURA',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  393 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.09',
    'nama_daerah' => 'KAB. JEMBER',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  394 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 51.01',
    'nama_daerah' => 'KAB. JEMBRANA',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  395 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 73.04',
    'nama_daerah' => 'KAB. JENEPONTO',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  396 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.20',
    'nama_daerah' => 'KAB. JEPARA',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  397 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.17',
    'nama_daerah' => 'KAB. JOMBANG',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  398 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 14.01',
    'nama_daerah' => 'KAB. KAMPAR',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  399 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 62.03',
    'nama_daerah' => 'KAB. KAPUAS',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  400 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 61.06',
    'nama_daerah' => 'KAB. KAPUAS HULU',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  401 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.13',
    'nama_daerah' => 'KAB. KARANGANYAR',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  402 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 32.15',
    'nama_daerah' => 'KAB. KARAWANG',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  403 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 12.06',
    'nama_daerah' => 'KAB. KARO',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  404 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.05',
    'nama_daerah' => 'KAB. KEBUMEN',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  405 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.24',
    'nama_daerah' => 'KAB. KENDAL',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  406 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 17.08',
    'nama_daerah' => 'KAB. KEPAHIANG',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  407 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 61.04',
    'nama_daerah' => 'KAB. KETAPANG',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  408 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.10',
    'nama_daerah' => 'KAB. KLATEN',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  409 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 74.01',
    'nama_daerah' => 'KAB. KOLAKA',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  410 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 63.02',
    'nama_daerah' => 'KAB. KOTABARU',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  411 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 62.01',
    'nama_daerah' => 'KAB. KOTAWARINGIN BARAT',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  412 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 62.02',
    'nama_daerah' => 'KAB. KOTAWARINGIN TIMUR',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  413 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 61.12',
    'nama_daerah' => 'KAB. KUBU RAYA',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  414 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.19',
    'nama_daerah' => 'KAB. KUDUS',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  415 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 34.01',
    'nama_daerah' => 'KAB. KULON PROGO',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  416 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 32.08',
    'nama_daerah' => 'KAB. KUNINGAN',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  417 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 64.07',
    'nama_daerah' => 'KAB. KUTAI BARAT',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  418 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 64.02',
    'nama_daerah' => 'KAB. KUTAI KARTANEGARA',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  419 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 64.08',
    'nama_daerah' => 'KAB. KUTAI TIMUR',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  420 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 12.10',
    'nama_daerah' => 'KAB. LABUHANBATU',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  421 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 12.23',
    'nama_daerah' => 'KAB. LABUHANBATU UTARA',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  422 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 16.04',
    'nama_daerah' => 'KAB. LAHAT',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  423 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.24',
    'nama_daerah' => 'KAB. LAMONGAN',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  424 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 18.04',
    'nama_daerah' => 'KAB. LAMPUNG BARAT',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  425 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 18.01',
    'nama_daerah' => 'KAB. LAMPUNG SELATAN',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  426 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 18.02',
    'nama_daerah' => 'KAB. LAMPUNG TENGAH',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  427 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 18.07',
    'nama_daerah' => 'KAB. LAMPUNG TIMUR',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  428 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 18.03',
    'nama_daerah' => 'KAB. LAMPUNG UTARA',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  429 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 61.08',
    'nama_daerah' => 'KAB. LANDAK',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  430 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 12.05',
    'nama_daerah' => 'KAB. LANGKAT',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  431 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 36.02',
    'nama_daerah' => 'KAB. LEBAK',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  432 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 52.01',
    'nama_daerah' => 'KAB. LOMBOK BARAT',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  433 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 52.02',
    'nama_daerah' => 'KAB. LOMBOK TENGAH',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  434 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 52.03',
    'nama_daerah' => 'KAB. LOMBOK TIMUR',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  435 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.08',
    'nama_daerah' => 'KAB. LUMAJANG',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  436 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 73.17',
    'nama_daerah' => 'KAB. LUWU',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  437 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 73.24',
    'nama_daerah' => 'KAB. LUWU TIMUR',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  438 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 73.22',
    'nama_daerah' => 'KAB. LUWU UTARA',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  439 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.19',
    'nama_daerah' => 'KAB. MADIUN',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  440 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.08',
    'nama_daerah' => 'KAB. MAGELANG',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  441 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.20',
    'nama_daerah' => 'KAB. MAGETAN',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  442 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 32.10',
    'nama_daerah' => 'KAB. MAJALENGKA',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  443 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 76.05',
    'nama_daerah' => 'KAB. MAJENE',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  444 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.07',
    'nama_daerah' => 'KAB. MALANG',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  445 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 76.02',
    'nama_daerah' => 'KAB. MAMUJU',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  446 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 12.13',
    'nama_daerah' => 'KAB. MANDAILING NATAL',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  447 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 53.15',
    'nama_daerah' => 'KAB. MANGGARAI BARAT',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  448 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 92.02',
    'nama_daerah' => 'KAB. MANOKWARI',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  449 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 73.09',
    'nama_daerah' => 'KAB. MAROS',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  450 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 61.10',
    'nama_daerah' => 'KAB. MELAWI',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  451 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 15.02',
    'nama_daerah' => 'KAB. MERANGIN',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  452 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 91.01',
    'nama_daerah' => 'KAB. MERAUKE',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  453 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 91.09',
    'nama_daerah' => 'KAB. MIMIKA',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  454 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 71.05',
    'nama_daerah' => 'KAB. MINAHASA SELATAN',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  455 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 71.06',
    'nama_daerah' => 'KAB. MINAHASA UTARA',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  456 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.16',
    'nama_daerah' => 'KAB. MOJOKERTO',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  457 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 72.06',
    'nama_daerah' => 'KAB. MOROWALI',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  458 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 16.03',
    'nama_daerah' => 'KAB. MUARA ENIM',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  459 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 16.06',
    'nama_daerah' => 'KAB. MUSI BANYUASIN',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  460 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 91.04',
    'nama_daerah' => 'KAB. NABIRE',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  461 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.18',
    'nama_daerah' => 'KAB. NGANJUK',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  462 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.21',
    'nama_daerah' => 'KAB. NGAWI',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  463 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 16.10',
    'nama_daerah' => 'KAB. OGAN ILIR',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  464 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 16.02',
    'nama_daerah' => 'KAB. OGAN KOMERING ILIR',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  465 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 16.01',
    'nama_daerah' => 'KAB. OGAN KOMERING ULU',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  466 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 16.09',
    'nama_daerah' => 'KAB. OGAN KOMERING ULU SELATAN',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  467 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 16.08',
    'nama_daerah' => 'KAB. OGAN KOMERING ULU TIMUR',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  468 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.01',
    'nama_daerah' => 'KAB. PACITAN',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  469 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.28',
    'nama_daerah' => 'KAB. PAMEKASAN',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  470 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 36.01',
    'nama_daerah' => 'KAB. PANDEGLANG',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  471 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 32.18',
    'nama_daerah' => 'KAB. PANGANDARAN',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  472 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 73.10',
    'nama_daerah' => 'KAB. PANGKAJENE KEPULAUAN',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  473 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 13.12',
    'nama_daerah' => 'KAB. PASAMAN BARAT',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  474 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.14',
    'nama_daerah' => 'KAB. PASURUAN',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  475 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.18',
    'nama_daerah' => 'KAB. PATI',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  476 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.26',
    'nama_daerah' => 'KAB. PEKALONGAN',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  477 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 14.05',
    'nama_daerah' => 'KAB. PELALAWAN',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  478 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.27',
    'nama_daerah' => 'KAB. PEMALANG',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  479 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 64.09',
    'nama_daerah' => 'KAB. PENAJAM PASER UTARA',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  480 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 18.09',
    'nama_daerah' => 'KAB. PESAWARAN',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  481 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 13.01',
    'nama_daerah' => 'KAB. PESISIR SELATAN',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  482 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 11.07',
    'nama_daerah' => 'KAB. PIDIE',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  483 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 73.15',
    'nama_daerah' => 'KAB. PINRANG',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  484 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 76.04',
    'nama_daerah' => 'KAB. POLEWALI MANDAR',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  485 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.02',
    'nama_daerah' => 'KAB. PONOROGO',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  486 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 18.10',
    'nama_daerah' => 'KAB. PRINGSEWU',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  487 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.13',
    'nama_daerah' => 'KAB. PROBOLINGGO',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  488 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.03',
    'nama_daerah' => 'KAB. PURBALINGGA',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  489 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 32.14',
    'nama_daerah' => 'KAB. PURWAKARTA',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  490 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.06',
    'nama_daerah' => 'KAB. PURWOREJO',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  491 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 17.02',
    'nama_daerah' => 'KAB. REJANG LEBONG',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  492 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.17',
    'nama_daerah' => 'KAB. REMBANG',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  493 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 14.07',
    'nama_daerah' => 'KAB. ROKAN HILIR',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  494 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 14.06',
    'nama_daerah' => 'KAB. ROKAN HULU',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  495 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 61.03',
    'nama_daerah' => 'KAB. SANGGAU',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  496 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 15.03',
    'nama_daerah' => 'KAB. SAROLANGUN',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  497 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.22',
    'nama_daerah' => 'KAB. SEMARANG',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  498 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 36.04',
    'nama_daerah' => 'KAB. SERANG',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  499 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 12.18',
    'nama_daerah' => 'KAB. SERDANG BEDAGAI',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  500 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 14.08',
    'nama_daerah' => 'KAB. SIAK',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  501 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 73.14',
    'nama_daerah' => 'KAB. SIDENRENG RAPPANG',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  502 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.15',
    'nama_daerah' => 'KAB. SIDOARJO',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  503 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 72.10',
    'nama_daerah' => 'KAB. SIGI',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  504 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 73.07',
    'nama_daerah' => 'KAB. SINJAI',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  505 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 61.05',
    'nama_daerah' => 'KAB. SINTANG',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  506 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.12',
    'nama_daerah' => 'KAB. SITUBONDO',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  507 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 34.04',
    'nama_daerah' => 'KAB. SLEMAN',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  508 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 73.12',
    'nama_daerah' => 'KAB. SOPPENG',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  509 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 92.01',
    'nama_daerah' => 'KAB. SORONG',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  510 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.14',
    'nama_daerah' => 'KAB. SRAGEN',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  511 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 32.13',
    'nama_daerah' => 'KAB. SUBANG',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  512 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 32.02',
    'nama_daerah' => 'KAB. SUKABUMI',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  513 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.11',
    'nama_daerah' => 'KAB. SUKOHARJO',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  514 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 52.04',
    'nama_daerah' => 'KAB. SUMBAWA',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  515 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 32.11',
    'nama_daerah' => 'KAB. SUMEDANG',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  516 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.29',
    'nama_daerah' => 'KAB. SUMENEP',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  517 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 63.09',
    'nama_daerah' => 'KAB. TABALONG',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  518 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 51.02',
    'nama_daerah' => 'KAB. TABANAN',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  519 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 73.05',
    'nama_daerah' => 'KAB. TAKALAR',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  520 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 73.18',
    'nama_daerah' => 'KAB. TANA TORAJA',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  521 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 63.10',
    'nama_daerah' => 'KAB. TANAH BUMBU',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  522 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 63.01',
    'nama_daerah' => 'KAB. TANAH LAUT',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  523 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 36.03',
    'nama_daerah' => 'KAB. TANGERANG',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  524 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 18.06',
    'nama_daerah' => 'KAB. TANGGAMUS',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  525 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 12.02',
    'nama_daerah' => 'KAB. TAPANULI UTARA',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  526 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 63.05',
    'nama_daerah' => 'KAB. TAPIN',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  527 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 32.06',
    'nama_daerah' => 'KAB. TASIKMALAYA',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  528 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 15.09',
    'nama_daerah' => 'KAB. TEBO',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  529 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.28',
    'nama_daerah' => 'KAB. TEGAL',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  530 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.23',
    'nama_daerah' => 'KAB. TEMANGGUNG',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  531 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 12.12',
    'nama_daerah' => 'KAB. TOBA SAMOSIR',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  532 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 73.26',
    'nama_daerah' => 'KAB. TORAJA UTARA',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  533 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.23',
    'nama_daerah' => 'KAB. TUBAN',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  534 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 18.05',
    'nama_daerah' => 'KAB. TULANG BAWANG',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  535 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.04',
    'nama_daerah' => 'KAB. TULUNGAGUNG',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  536 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 73.13',
    'nama_daerah' => 'KAB. WAJO',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  537 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.12',
    'nama_daerah' => 'KAB. WONOGIRI',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  538 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.07',
    'nama_daerah' => 'KAB. WONOSOBO',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  539 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 31.73',
    'nama_daerah' => 'KOTA ADM. JAKARTA BARAT',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  540 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 31.71',
    'nama_daerah' => 'KOTA ADM. JAKARTA PUSAT',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  541 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 31.74',
    'nama_daerah' => 'KOTA ADM. JAKARTA SELATAN',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  542 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 31.75',
    'nama_daerah' => 'KOTA ADM. JAKARTA TIMUR',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  543 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 31.72',
    'nama_daerah' => 'KOTA ADM. JAKARTA UTARA',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  544 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 81.71',
    'nama_daerah' => 'KOTA AMBON',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  545 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 64.71',
    'nama_daerah' => 'KOTA BALIKPAPAN',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  546 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 11.71',
    'nama_daerah' => 'KOTA BANDA ACEH',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  547 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 18.71',
    'nama_daerah' => 'KOTA BANDAR LAMPUNG',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  548 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 32.73',
    'nama_daerah' => 'KOTA BANDUNG',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  549 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 32.79',
    'nama_daerah' => 'KOTA BANJAR',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  550 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 63.72',
    'nama_daerah' => 'KOTA BANJARBARU',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  551 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 63.71',
    'nama_daerah' => 'KOTA BANJARMASIN',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  552 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 21.71',
    'nama_daerah' => 'KOTA BATAM',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  553 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.79',
    'nama_daerah' => 'KOTA BATU',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  554 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 74.72',
    'nama_daerah' => 'KOTA BAU BAU',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  555 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 32.75',
    'nama_daerah' => 'KOTA BEKASI',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  556 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 17.71',
    'nama_daerah' => 'KOTA BENGKULU',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  557 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 12.75',
    'nama_daerah' => 'KOTA BINJAI',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  558 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 71.72',
    'nama_daerah' => 'KOTA BITUNG',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  559 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.72',
    'nama_daerah' => 'KOTA BLITAR',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  560 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 32.71',
    'nama_daerah' => 'KOTA BOGOR',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  561 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 64.74',
    'nama_daerah' => 'KOTA BONTANG',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  562 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 13.75',
    'nama_daerah' => 'KOTA BUKITTINGGI',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  563 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 36.72',
    'nama_daerah' => 'KOTA CILEGON',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  564 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 32.77',
    'nama_daerah' => 'KOTA CIMAHI',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  565 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 32.74',
    'nama_daerah' => 'KOTA CIREBON',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  566 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 51.71',
    'nama_daerah' => 'KOTA DENPASAR',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  567 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 32.76',
    'nama_daerah' => 'KOTA DEPOK',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  568 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 14.72',
    'nama_daerah' => 'KOTA DUMAI',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  569 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 75.71',
    'nama_daerah' => 'KOTA GORONTALO',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  570 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 15.71',
    'nama_daerah' => 'KOTA JAMBI',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  571 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 91.71',
    'nama_daerah' => 'KOTA JAYAPURA',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  572 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.71',
    'nama_daerah' => 'KOTA KEDIRI',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  573 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 74.71',
    'nama_daerah' => 'KOTA KENDARI',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  574 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 71.74',
    'nama_daerah' => 'KOTA KOTAMOBAGU',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  575 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 53.71',
    'nama_daerah' => 'KOTA KUPANG',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  576 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 11.74',
    'nama_daerah' => 'KOTA LANGSA',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  577 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 11.73',
    'nama_daerah' => 'KOTA LHOKSEUMAWE',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  578 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 16.73',
    'nama_daerah' => 'KOTA LUBUK LINGGAU',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  579 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.77',
    'nama_daerah' => 'KOTA MADIUN',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  580 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.71',
    'nama_daerah' => 'KOTA MAGELANG',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  581 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 73.71',
    'nama_daerah' => 'KOTA MAKASSAR',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  582 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.73',
    'nama_daerah' => 'KOTA MALANG',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  583 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 71.71',
    'nama_daerah' => 'KOTA MANADO',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  584 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 52.71',
    'nama_daerah' => 'KOTA MATARAM',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  585 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 12.71',
    'nama_daerah' => 'KOTA MEDAN',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  586 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 18.72',
    'nama_daerah' => 'KOTA METRO',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  587 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.76',
    'nama_daerah' => 'KOTA MOJOKERTO',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  588 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 13.71',
    'nama_daerah' => 'KOTA PADANG',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  589 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 13.74',
    'nama_daerah' => 'KOTA PADANG PANJANG',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  590 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 12.77',
    'nama_daerah' => 'KOTA PADANG SIDEMPUAN',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  591 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 16.72',
    'nama_daerah' => 'KOTA PAGAR ALAM',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  592 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 62.71',
    'nama_daerah' => 'KOTA PALANGKARAYA',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  593 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 16.71',
    'nama_daerah' => 'KOTA PALEMBANG',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  594 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 73.73',
    'nama_daerah' => 'KOTA PALOPO',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  595 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 72.71',
    'nama_daerah' => 'KOTA PALU',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  596 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 19.71',
    'nama_daerah' => 'KOTA PANGKAL PINANG',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  597 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 73.72',
    'nama_daerah' => 'KOTA PARE PARE',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  598 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 13.77',
    'nama_daerah' => 'KOTA PARIAMAN',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  599 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.75',
    'nama_daerah' => 'KOTA PASURUAN',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  600 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 13.76',
    'nama_daerah' => 'KOTA PAYAKUMBUH',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  601 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.75',
    'nama_daerah' => 'KOTA PEKALONGAN',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  602 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 14.71',
    'nama_daerah' => 'KOTA PEKANBARU',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  603 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 12.72',
    'nama_daerah' => 'KOTA PEMATANGSIANTAR',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  604 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 61.71',
    'nama_daerah' => 'KOTA PONTIANAK',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  605 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 16.74',
    'nama_daerah' => 'KOTA PRABUMULIH',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  606 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.74',
    'nama_daerah' => 'KOTA PROBOLINGGO',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  607 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.73',
    'nama_daerah' => 'KOTA SALATIGA',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  608 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 64.72',
    'nama_daerah' => 'KOTA SAMARINDA',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  609 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.74',
    'nama_daerah' => 'KOTA SEMARANG',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  610 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 36.73',
    'nama_daerah' => 'KOTA SERANG',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  611 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 12.73',
    'nama_daerah' => 'KOTA SIBOLGA',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  612 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 61.72',
    'nama_daerah' => 'KOTA SINGKAWANG',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  613 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 13.72',
    'nama_daerah' => 'KOTA SOLOK',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  614 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 92.71',
    'nama_daerah' => 'KOTA SORONG',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  615 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 32.72',
    'nama_daerah' => 'KOTA SUKABUMI',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  616 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 15.72',
    'nama_daerah' => 'KOTA SUNGAI PENUH',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  617 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 35.78',
    'nama_daerah' => 'KOTA SURABAYA',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  618 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.72',
    'nama_daerah' => 'KOTA SURAKARTA',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  619 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 36.71',
    'nama_daerah' => 'KOTA TANGERANG',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  620 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 36.74',
    'nama_daerah' => 'KOTA TANGERANG SELATAN',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  621 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 12.74',
    'nama_daerah' => 'KOTA TANJUNG BALAI',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  622 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 21.72',
    'nama_daerah' => 'KOTA TANJUNG PINANG',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  623 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 65.71',
    'nama_daerah' => 'KOTA TARAKAN',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  624 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 32.78',
    'nama_daerah' => 'KOTA TASIKMALAYA',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  625 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 12.76',
    'nama_daerah' => 'KOTA TEBING TINGGI',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  626 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 33.76',
    'nama_daerah' => 'KOTA TEGAL',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  627 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 82.71',
    'nama_daerah' => 'KOTA TERNATE',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  628 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 71.73',
    'nama_daerah' => 'KOTA TOMOHON',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  629 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'ID 34.71',
    'nama_daerah' => 'KOTA YOGYAKARTA',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  630 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'MYS-00001',
    'nama_daerah' => 'Malaysia',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  631 => 
  array (
    'tipe' => 'UMK',
    'kode_daerah' => 'SIN-01001',
    'nama_daerah' => 'Singapore',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  632 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 11',
    'nama_daerah' => 'ACEH',
    'provinsi' => '',
    'nominal' => 3000000.0,
    'tahun' => 2026,
  ),
  633 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 12',
    'nama_daerah' => 'SUMATERA UTARA',
    'provinsi' => '',
    'nominal' => 4000000.0,
    'tahun' => 2026,
  ),
  634 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 17',
    'nama_daerah' => 'BENGKULU',
    'provinsi' => '',
    'nominal' => 2000000.0,
    'tahun' => 2026,
  ),
  635 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 15',
    'nama_daerah' => 'JAMBI',
    'provinsi' => '',
    'nominal' => 2000000.0,
    'tahun' => 2026,
  ),
  636 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 14',
    'nama_daerah' => 'RIAU',
    'provinsi' => '',
    'nominal' => 2000000.0,
    'tahun' => 2026,
  ),
  637 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 13',
    'nama_daerah' => 'SUMATERA BARAT',
    'provinsi' => '',
    'nominal' => 2000000.0,
    'tahun' => 2026,
  ),
  638 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 16',
    'nama_daerah' => 'SUMATERA SELATAN',
    'provinsi' => '',
    'nominal' => 2000000.0,
    'tahun' => 2026,
  ),
  639 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 18',
    'nama_daerah' => 'LAMPUNG',
    'provinsi' => '',
    'nominal' => 2000000.0,
    'tahun' => 2026,
  ),
  640 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 19',
    'nama_daerah' => 'KEP. BANGKA BELITUNG',
    'provinsi' => '',
    'nominal' => 2000000.0,
    'tahun' => 2026,
  ),
  641 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 21',
    'nama_daerah' => 'KEP. RIAU',
    'provinsi' => '',
    'nominal' => 2000000.0,
    'tahun' => 2026,
  ),
  642 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 36',
    'nama_daerah' => 'BANTEN',
    'provinsi' => '',
    'nominal' => 2000000.0,
    'tahun' => 2026,
  ),
  643 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 32',
    'nama_daerah' => 'JAWA BARAT',
    'provinsi' => '',
    'nominal' => 2000000.0,
    'tahun' => 2026,
  ),
  644 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 31',
    'nama_daerah' => 'DKI JAKARTA',
    'provinsi' => '',
    'nominal' => 2000000.0,
    'tahun' => 2026,
  ),
  645 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 33',
    'nama_daerah' => 'JAWA TENGAH',
    'provinsi' => '',
    'nominal' => 2000000.0,
    'tahun' => 2026,
  ),
  646 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 35',
    'nama_daerah' => 'JAWA TIMUR',
    'provinsi' => '',
    'nominal' => 2000000.0,
    'tahun' => 2026,
  ),
  647 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 34',
    'nama_daerah' => 'DI YOGYAKARTA',
    'provinsi' => '',
    'nominal' => 2000000.0,
    'tahun' => 2026,
  ),
  648 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 51',
    'nama_daerah' => 'BALI',
    'provinsi' => '',
    'nominal' => 2000000.0,
    'tahun' => 2026,
  ),
  649 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 52',
    'nama_daerah' => 'NUSA TENGGARA BARAT',
    'provinsi' => '',
    'nominal' => 2000000.0,
    'tahun' => 2026,
  ),
  650 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 53',
    'nama_daerah' => 'NUSA TENGGARA TIMUR',
    'provinsi' => '',
    'nominal' => 2000000.0,
    'tahun' => 2026,
  ),
  651 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 61',
    'nama_daerah' => 'KALIMANTAN BARAT',
    'provinsi' => '',
    'nominal' => 2000000.0,
    'tahun' => 2026,
  ),
  652 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 63',
    'nama_daerah' => 'KALIMANTAN SELATAN',
    'provinsi' => '',
    'nominal' => 2000000.0,
    'tahun' => 2026,
  ),
  653 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 62',
    'nama_daerah' => 'KALIMANTAN TENGAH',
    'provinsi' => '',
    'nominal' => 2000000.0,
    'tahun' => 2026,
  ),
  654 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 64',
    'nama_daerah' => 'KALIMANTAN TIMUR',
    'provinsi' => '',
    'nominal' => 2000000.0,
    'tahun' => 2026,
  ),
  655 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 75',
    'nama_daerah' => 'GORONTALO',
    'provinsi' => '',
    'nominal' => 2000000.0,
    'tahun' => 2026,
  ),
  656 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 73',
    'nama_daerah' => 'SULAWESI SELATAN',
    'provinsi' => '',
    'nominal' => 2000000.0,
    'tahun' => 2026,
  ),
  657 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 74',
    'nama_daerah' => 'SULAWESI TENGGARA',
    'provinsi' => '',
    'nominal' => 2000000.0,
    'tahun' => 2026,
  ),
  658 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 72',
    'nama_daerah' => 'SULAWESI TENGAH',
    'provinsi' => '',
    'nominal' => 2000000.0,
    'tahun' => 2026,
  ),
  659 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 71',
    'nama_daerah' => 'SULAWESI UTARA',
    'provinsi' => '',
    'nominal' => 2000000.0,
    'tahun' => 2026,
  ),
  660 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 76',
    'nama_daerah' => 'SULAWESI BARAT',
    'provinsi' => '',
    'nominal' => 2000000.0,
    'tahun' => 2026,
  ),
  661 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 81',
    'nama_daerah' => 'MALUKU',
    'provinsi' => '',
    'nominal' => 2000000.0,
    'tahun' => 2026,
  ),
  662 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 82',
    'nama_daerah' => 'MALUKU UTARA',
    'provinsi' => '',
    'nominal' => 2000000.0,
    'tahun' => 2026,
  ),
  663 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 91',
    'nama_daerah' => 'PAPUA',
    'provinsi' => '',
    'nominal' => 1000000.0,
    'tahun' => 2026,
  ),
  664 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 92',
    'nama_daerah' => 'PAPUA BARAT',
    'provinsi' => '',
    'nominal' => 2000000.0,
    'tahun' => 2026,
  ),
  665 => 
  array (
    'tipe' => 'UMP',
    'kode_daerah' => 'ID 65',
    'nama_daerah' => 'KALIMANTAN UTARA',
    'provinsi' => '',
    'nominal' => 2000000.0,
    'tahun' => 2026,
  ),
);
                $chunks = array_chunk($mwData, 100);
                foreach ($chunks as $chunk) {
                    $this->db->table('minimum_wages')->insertBatch($chunk);
                }
            }
        }

    }

    public function down()
    {
        // Idempotent migration - nothing to drop destructively
    }
}
