<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CheckOvertimeCalc extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'app:check-overtime-calc';
    protected $description = 'Checks database details for all client 1 employees';

    public function run(array $params)
    {
        $db = \Config\Database::connect();

        $employees = $db->table('employees')
            ->select('employees.id, employees.nama, employees.position_id, employees.department_id, employees.division_id, positions.nama as position_name, departments.nama as department_name, divisions.nama as division_name')
            ->join('positions', 'positions.id = employees.position_id', 'left')
            ->join('departments', 'departments.id = employees.department_id', 'left')
            ->join('divisions', 'divisions.id = employees.division_id', 'left')
            ->where('employees.client_id', 1)
            ->get()
            ->getResultArray();

        CLI::write("=== CLIENT 1 EMPLOYEES ===", "yellow");
        foreach ($employees as $emp) {
            CLI::write("ID: {$emp['id']} | Name: {$emp['nama']} | Position: {$emp['position_name']} (ID: {$emp['position_id']}) | Dept: {$emp['department_name']} (ID: {$emp['department_id']}) | Div: {$emp['division_name']} (ID: {$emp['division_id']})");
        }

        $configs = $db->table('client_payroll_configs')->where('client_id', 1)->get()->getResultArray();
        CLI::write("=== CLIENT 1 PAYROLL CONFIGS ===", "yellow");
        print_r($configs);
    }
}
