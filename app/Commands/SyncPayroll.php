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
        $periodId = !empty($params[0]) ? intval($params[0]) : 16;
        $clientId = !empty($params[1]) ? intval($params[1]) : 5;

        $api = new \App\Controllers\Api();

        CLI::write("Running syncEmployeesToPKWT for client $clientId...", 'yellow');
        $ref = new \ReflectionClass($api);

        $syncEmployees = $ref->getMethod('syncEmployeesToPKWT');
        $syncEmployees->setAccessible(true);
        $syncEmployees->invoke($api, $clientId);

        CLI::write("Running syncOvertimeToPayrollAttendance for period $periodId, client $clientId...", 'yellow');
        $syncOvertime = $ref->getMethod('syncOvertimeToPayrollAttendance');
        $syncOvertime->setAccessible(true);
        $syncOvertime->invoke($api, $periodId, $clientId);

        CLI::write("Running syncEarlyArrivalToPayrollAttendance for period $periodId, client $clientId...", 'yellow');
        $syncEarly = $ref->getMethod('syncEarlyArrivalToPayrollAttendance');
        $syncEarly->setAccessible(true);
        $syncEarly->invoke($api, $periodId, $clientId);

        CLI::write("Sync completed successfully!", 'green');
    }
}
