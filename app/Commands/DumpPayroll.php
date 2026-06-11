<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class DumpPayroll extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'app:dump-payroll';
    protected $description = 'Dumps payroll configurations and schemes';

    public function run(array $params)
    {
        $db = \Config\Database::connect();

        $logs = $db->table('attendance_logs')
            ->whereIn('employee_id', [2, 3, 4, 5])
            ->orderBy('log_date', 'ASC')
            ->get()
            ->getResultArray();
            
        CLI::write("=== ALL LOGS FOR EMPLOYEES [2,3,4,5] COUNT: " . count($logs) . " ===", "yellow");
        foreach ($logs as $log) {
            CLI::write("LogID: {$log['id']}, EmpID: {$log['employee_id']}, Date: {$log['log_date']}, PayoutPeriod: {$log['payout_period']}, IsRapel: {$log['is_rapel']}");
        }
    }
}
