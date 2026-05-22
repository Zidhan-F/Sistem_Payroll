<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class SeedOrg extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'seed:org';
    protected $description = 'Seed organizational structure dummy data for all clients';

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        $clients = $db->table('clients')->get()->getResult();
        
        $countDivs = 0;
        $countDepts = 0;
        $countPos = 0;

        foreach ($clients as $client) {
            // Division 1
            $existingDiv = $db->table('divisions')->where('client_id', $client->id)->where('nama', 'Divisi 1')->get()->getRow();
            if ($existingDiv) {
                $divId = $existingDiv->id;
            } else {
                $db->table('divisions')->insert([
                    'client_id' => $client->id,
                    'nama' => 'Divisi 1'
                ]);
                $divId = $db->insertID();
                $countDivs++;
            }
            
            // Departmen 1 & 2
            for ($i = 1; $i <= 2; $i++) {
                $deptName = 'Departmen ' . $i;
                $existingDept = $db->table('departments')
                    ->where('division_id', $divId)
                    ->where('nama', $deptName)
                    ->get()->getRow();
                    
                if ($existingDept) {
                    $deptId = $existingDept->id;
                } else {
                    $db->table('departments')->insert([
                        'client_id' => $client->id,
                        'division_id' => $divId,
                        'nama' => $deptName
                    ]);
                    $deptId = $db->insertID();
                    $countDepts++;
                }
                
                // Posisi 1 to 9
                for ($j = 1; $j <= 9; $j++) {
                    $posName = 'Posisi ' . $j;
                    $existingPos = $db->table('positions')
                        ->where('department_id', $deptId)
                        ->where('nama', $posName)
                        ->get()->getRow();
                        
                    if (!$existingPos) {
                        $db->table('positions')->insert([
                            'department_id' => $deptId,
                            'nama' => $posName
                        ]);
                        $countPos++;
                    }
                }
            }
        }
        
        CLI::write("Seeding completed successfully!", 'green');
        CLI::write("Created: $countDivs Divisions, $countDepts Departments, $countPos Positions.", 'yellow');
    }
}
