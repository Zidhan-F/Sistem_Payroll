<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CleanupDb extends BaseCommand
{
    protected $group       = 'App';
    protected $name        = 'app:cleanupdb';
    protected $description = 'Cleans up invalid attendance logs, early arrival logs, and overtime logs before employees join date';

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        
        CLI::write("Fetching all employees...", 'yellow');
        $employees = $db->table('employees')->get()->getResultArray();
        CLI::write("Total employees found: " . count($employees), 'green');

        $totalAttendanceDeleted = 0;
        $totalEarlyArrivalDeleted = 0;
        $totalOvertimeDeleted = 0;

        foreach ($employees as $emp) {
            $joinDate = !empty($emp['tgl_masuk']) ? $emp['tgl_masuk'] : ($emp['start_contract'] ?? null);
            if (empty($joinDate)) {
                continue;
            }

            $id = $emp['id'];
            $nama = $emp['nama'];

            // Count records before join date
            $attCount = $db->table('attendance_logs')
                          ->where('employee_id', $id)
                          ->where('log_date <', $joinDate)
                          ->countAllResults();

            $eaCount = $db->table('early_arrival')
                         ->where('employee_id', $id)
                         ->where('date <', $joinDate)
                         ->countAllResults();

            $otCount = $db->table('overtime_logs')
                         ->where('employee_id', $id)
                         ->where('tanggal <', $joinDate)
                         ->countAllResults();

            if ($attCount > 0 || $eaCount > 0 || $otCount > 0) {
                CLI::write("Employee: {$nama} (ID: {$id}) | Join Date: {$joinDate}", 'cyan');
                if ($attCount > 0) {
                    $db->table('attendance_logs')
                       ->where('employee_id', $id)
                       ->where('log_date <', $joinDate)
                       ->delete();
                    CLI::write("  - Deleted {$attCount} attendance logs", 'red');
                    $totalAttendanceDeleted += $attCount;
                }
                if ($eaCount > 0) {
                    $db->table('early_arrival')
                       ->where('employee_id', $id)
                       ->where('date <', $joinDate)
                       ->delete();
                    CLI::write("  - Deleted {$eaCount} early arrival logs", 'red');
                    $totalEarlyArrivalDeleted += $eaCount;
                }
                if ($otCount > 0) {
                    $db->table('overtime_logs')
                       ->where('employee_id', $id)
                       ->where('tanggal <', $joinDate)
                       ->delete();
                    CLI::write("  - Deleted {$otCount} overtime logs", 'red');
                    $totalOvertimeDeleted += $otCount;
                }
            }
        }

        CLI::write("--- CLEANUP SUMMARY ---", 'yellow');
        CLI::write("Total attendance logs deleted: {$totalAttendanceDeleted}", 'green');
        CLI::write("Total early arrival logs deleted: {$totalEarlyArrivalDeleted}", 'green');
        CLI::write("Total overtime logs deleted: {$totalOvertimeDeleted}", 'green');
    }
}
