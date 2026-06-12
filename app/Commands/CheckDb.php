<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CheckDb extends BaseCommand
{
    protected $group       = 'App';
    protected $name        = 'app:checkdb';
    protected $description = 'Checks overtime logs and employees database tables';

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        
        CLI::write("--- OVERTIME LOGS ---", 'yellow');
        $logs = $db->table('overtime_logs')->get()->getResultArray();
        foreach ($logs as $log) {
            CLI::write(json_encode($log));
        }

        CLI::write("--- EMPLOYEES ---", 'yellow');
        $employees = $db->table('employees')->select('id, nama, client_id, employ_id, nik')->get()->getResultArray();
        foreach ($employees as $emp) {
            CLI::write(json_encode($emp));
        }

        CLI::write("--- PKWT ---", 'yellow');
        $pkwts = $db->table('pkwt')->get()->getResultArray();
        foreach ($pkwts as $p) {
            CLI::write(json_encode($p));
        }

        CLI::write("--- PAYROLL ATTENDANCE ---", 'yellow');
        $att = $db->table('payroll_attendance')->get()->getResultArray();
        foreach ($att as $a) {
            CLI::write(json_encode($a));
        }

        CLI::write("--- LAST 20 ATTENDANCE LOGS ---", 'yellow');
        $logs = $db->table('attendance_logs')->orderBy('id', 'DESC')->limit(20)->get()->getResultArray();
        foreach ($logs as $log) {
            CLI::write(json_encode($log));
        }

        CLI::write("--- ATTENDANCE TEST FOR PERIOD 3 CLIENT 1 ---", 'yellow');
        $testQuery = $db->table('pkwt')
                          ->select('pkwt.id as pkwt_id, pkwt.employee_name, pkwt.tipe_perjanjian, 
                                    payroll_attendance.hari_kerja, payroll_attendance.jam_lembur, 
                                    payroll_attendance.potongan_absensi, payroll_attendance.bonus_tambahan,
                                    employees.id as employee_id, employees.employ_id, employees.nik, employees.gaji_pokok,
                                    employees.hari_kerja as employee_hari_kerja, positions.hari_kerja as position_hari_kerja')
                          ->join('payroll_attendance', "payroll_attendance.pkwt_id = pkwt.id AND payroll_attendance.period_id = 3", 'left')
                          ->join('employees', "employees.client_id = pkwt.client_id AND employees.nama = pkwt.employee_name", 'left')
                          ->join('positions', 'positions.id = employees.position_id', 'left')
                          ->where('pkwt.client_id', 1)
                          ->get()->getResultArray();
        foreach ($testQuery as $row) {
            CLI::write(json_encode($row));
        }

        CLI::write("--- SQL SERVER DATE CONVERSION TEST ---", 'yellow');
        try {
            $dateTest = $db->query("SELECT id, log_date, MONTH(log_date) as m, YEAR(log_date) as y FROM attendance_logs")->getResultArray();
            foreach (array_slice($dateTest, 0, 5) as $row) {
                CLI::write(json_encode($row));
            }
        } catch (\Exception $e) {
            CLI::write("ERROR: " . $e->getMessage(), 'red');
        }
    }
}


