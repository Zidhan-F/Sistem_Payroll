<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class GeneratePayrollCLI extends BaseCommand
{
    protected $group       = 'App';
    protected $name        = 'app:generatepayroll';
    protected $description = 'Runs Api::generatePayroll for a given period ID';

    public function run(array $params)
    {
        $periodId = $params[0] ?? null;
        if (!$periodId) {
            CLI::write("Please specify a period ID, e.g. php spark app:generatepayroll 11", 'red');
            return;
        }

        CLI::write("Instantiating Api controller...", 'yellow');
        $api = new \App\Controllers\Api();

        CLI::write("Running generatePayroll($periodId)...", 'yellow');
        try {
            $result = $api->generatePayroll($periodId);
            CLI::write("Completed successfully!", 'green');
            CLI::write(print_r($result, true));
        } catch (\Exception $e) {
            CLI::write("Error: " . $e->getMessage(), 'red');
            CLI::write($e->getTraceAsString(), 'red');
        }
    }
}
