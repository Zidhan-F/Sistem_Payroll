<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class DumpPayroll extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'app:dump-payroll';
    protected $description = 'Dumps payroll configurations, schemes, and users';

    public function run(array $params)
    {
        $db = \Config\Database::connect();

        $users = $db->table('users')->get()->getResultArray();
        CLI::write("=== USERS ===", "yellow");
        foreach ($users as $u) {
            CLI::write("Username: {$u['username']}, Password: {$u['password']}, Role: {$u['role']}");
        }

        $employees = $db->table('employees')->get()->getResultArray();
        CLI::write("=== EMPLOYEES ===", "yellow");
        foreach ($employees as $emp) {
            CLI::write("ID: {$emp['id']}, Nama: {$emp['nama']}, Client ID: {$emp['client_id']}, Join Date: {$emp['tgl_masuk']}");
        }

        $pkwts = $db->table('pkwt')->get()->getResultArray();
        CLI::write("=== PKWTS ===", "yellow");
        foreach ($pkwts as $pkwt) {
            CLI::write("ID: {$pkwt['id']}, Name: {$pkwt['employee_name']}, Client ID: {$pkwt['client_id']}");
        }

        $periods = $db->table('payroll_periods')->get()->getResultArray();
        CLI::write("=== PERIODS ===", "yellow");
        foreach ($periods as $p) {
            CLI::write("ID: {$p['id']}, Month: {$p['bulan']}, Year: {$p['tahun']}, Status Cutoff: {$p['status_cutoff']}, Client ID: {$p['client_id']}");
        }

        $payrolls = $db->table('payrolls')
            ->select('payrolls.*, employees.nama')
            ->join('employees', 'employees.id = payrolls.employee_id')
            ->get()
            ->getResultArray();
        CLI::write("=== GENERATED PAYROLLS (MONTHLY/LEGACY) ===", "yellow");
        foreach ($payrolls as $pr) {
            $thp = $pr['take_home_pay'] ?? '-';
            $gp = $pr['gaji_pokok'] ?? '-';
            $status = $pr['status_pembayaran'] ?? '-';
            CLI::write("ID: {$pr['id']}, Emp: {$pr['nama']}, Period: {$pr['bulan']}-{$pr['tahun']}, Status: {$status}, THP: {$thp}, GP: {$gp}");
        }

        $payrollFinals = $db->table('payroll_final')
            ->select('payroll_final.*, pkwt.employee_name')
            ->join('pkwt', 'pkwt.id = payroll_final.pkwt_id')
            ->get()
            ->getResultArray();
        CLI::write("=== GENERATED PAYROLL_FINAL (PKWT) ===", "yellow");
        foreach ($payrollFinals as $pf) {
            $thp = $pf['take_home_pay'] ?? '-';
            $gp = $pf['gaji_pokok'] ?? '-';
            $status = $pf['status_approval'] ?? '-';
            $lembur = $pf['lembur_pay'] ?? '-';
            $potongan = $pf['potongan_absen'] ?? '-';
            CLI::write("ID: {$pf['id']}, Emp: {$pf['employee_name']}, Period ID: {$pf['period_id']}, Status: {$status}, THP: {$thp}, GP: {$gp}, Lembur Pay: {$lembur}, Potongan Absen: {$potongan}");
        }

        $otLogs = $db->table('overtime_logs')
            ->select('overtime_logs.*, employees.nama')
            ->join('employees', 'employees.id = overtime_logs.employee_id')
            ->get()
            ->getResultArray();
        CLI::write("=== OVERTIME LOGS ===", "yellow");
        foreach ($otLogs as $ot) {
            $isHoliday = $ot['is_holiday'] ?? '-';
            $isRapel = $ot['is_rapel'] ?? '-';
            $period = $ot['payout_period'] ?? '-';
            CLI::write("Emp: {$ot['nama']}, Date: {$ot['tanggal']}, Hours: {$ot['jam_lembur']}, Status: {$ot['status']}, IsHoliday: {$isHoliday}, IsRapel: {$isRapel}, PayoutPeriod: {$period}");
        }
    }
}
