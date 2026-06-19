<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class SyncPayroll extends BaseCommand
{
    protected $group       = 'App';
    protected $name        = 'app:syncpayroll';
    protected $description = 'Triggers payroll calculations for period 1 and client 1';

    public function run(array $params)
    {
        $api = new \App\Controllers\Api();

        CLI::write("Running syncEmployeesToPKWT...", 'yellow');
        $ref = new \ReflectionClass($api);

        $syncEmployees = $ref->getMethod('syncEmployeesToPKWT');
        $syncEmployees->setAccessible(true);
        $syncEmployees->invoke($api, 1);

        CLI::write("Running syncOvertimeToPayrollAttendance...", 'yellow');
        $syncOvertime = $ref->getMethod('syncOvertimeToPayrollAttendance');
        $syncOvertime->setAccessible(true);
        $syncOvertime->invoke($api, 1, 1);

        CLI::write("Running syncEarlyArrivalToPayrollAttendance...", 'yellow');
        $syncEarly = $ref->getMethod('syncEarlyArrivalToPayrollAttendance');
        $syncEarly->setAccessible(true);
        $syncEarly->invoke($api, 1, 1);

        CLI::write("Sync completed successfully!", 'green');
    }
}
