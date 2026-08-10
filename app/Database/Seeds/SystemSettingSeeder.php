<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    public function run()
    {
        $db = $this->db;

        $settings = [
            [
                'setting_key'   => 'overtime_divisor',
                'setting_value' => '173',
                'description'   => 'Pembagi jam kerja bulanan untuk kalkulasi upah per jam lembur (default: 20 hari x 8 jam = 160)',
            ],
            [
                'setting_key'   => 'standard_work_days',
                'setting_value' => '22',
                'description'   => 'Jumlah hari kerja standar per bulan',
            ],
            [
                'setting_key'   => 'standard_work_hours',
                'setting_value' => '8',
                'description'   => 'Jumlah jam kerja standar per hari',
            ],
            [
                'setting_key'   => 'max_overtime_regular',
                'setting_value' => '3',
                'description'   => 'Batas maksimal jam lembur reguler per hari kerja',
            ],
            [
                'setting_key'   => 'overtime_multiplier_workday',
                'setting_value' => '1.5',
                'description'   => 'Pengali tarif lembur hari kerja',
            ],
            [
                'setting_key'   => 'overtime_multiplier_holiday',
                'setting_value' => '2.0',
                'description'   => 'Pengali tarif lembur hari libur/weekend',
            ],
            [
                'setting_key'   => 'minimum_overtime_minutes',
                'setting_value' => '30',
                'description'   => 'Durasi minimum lembur yang dihitung (menit)',
            ],
            [
                'setting_key'   => 'overtime_start_after_hours',
                'setting_value' => '8',
                'description'   => 'Mulai menghitung lembur setelah melewati batas jam kerja normal harian (default: 8 jam)',
            ],
        ];

        foreach ($settings as $s) {
            $exists = $db->table('system_settings')->where('setting_key', $s['setting_key'])->countAllResults();
            if ($exists === 0) {
                $db->table('system_settings')->insert($s);
            }
        }

        // Company Payroll Setting
        $compExists = $db->table('company_payroll_setting')->countAllResults();
        if ($compExists === 0) {
            $db->table('company_payroll_setting')->insert([
                'early_arrival_enabled'     => 1,
                'max_early_arrival_minutes' => 180,
            ]);
        }
    }
}
