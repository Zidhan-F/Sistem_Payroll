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
        $employees = $db->table('employees')->select('id, nama, client_id')->get()->getResultArray();
        foreach ($employees as $emp) {
            CLI::write(json_encode($emp));
        }
    }
}
