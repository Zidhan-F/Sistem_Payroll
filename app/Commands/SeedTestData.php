<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class SeedTestData extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'seed:testdata';
    protected $description = 'Seed 50 clients, 50 locations, and 50 employees';

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        
        for ($i = 1; $i <= 50; $i++) {
            // 1. Create Client
            $db->table('clients')->insert([
                'nama' => "Test Client $i",
                'email' => "client{$i}@test.com",
                'sektor' => 'Teknologi',
                'npwp' => '12.345.678.9-00' . sprintf("%02d", $i) . '.000',
                'nib' => '123456789' . sprintf("%02d", $i),
                'tgl_gabung' => date('Y-m-d'),
                'alamat' => "Jalan Test Data No. $i",
                'status' => 'Aktif',
                'telepon' => '0812345678' . sprintf("%02d", $i),
            ]);
            $clientId = $db->insertID();

            // 2. Create Location (Initials + ID)
            $locationName = "Kantor Test $i";
            $words = explode(' ', $locationName);
            $initials = '';
            foreach ($words as $w) {
                if (!empty($w)) $initials .= strtoupper($w[0]);
            }
            
            $db->table('work_locations')->insert([
                'client_id' => $clientId,
                'lokasi_kerja' => $locationName,
                'location_code' => 'TEMP', // Temp value
                'provinsi' => 'DKI JAKARTA',
                'kota_kabupaten' => 'KOTA JAKARTA PUSAT',
            ]);
            $locationId = $db->insertID();

            $locationCode = $initials . $locationId;
            $db->table('work_locations')->where('id', $locationId)->update(['location_code' => $locationCode]);

            // 3. Create Employee
            $contractYear = date('Y');
            
            // Generate NIK
            $db->transStart();
            $seqRow = $db->table('employee_sequences')->where('year', $contractYear)->get()->getRow();
            if ($seqRow) {
                $nextSeq = $seqRow->last_sequence + 1;
                $db->table('employee_sequences')->where('year', $contractYear)->update(['last_sequence' => $nextSeq]);
            } else {
                $lastEmp = $db->table('employees')
                               ->select('employ_id')
                               ->where("employ_id LIKE '" . $contractYear . "%'")
                               ->orderBy('employ_id', 'DESC')
                               ->limit(1)
                               ->get()
                               ->getRow();
                $nextSeq = 1;
                if ($lastEmp && $lastEmp->employ_id) {
                    $lastSeq = (int) substr($lastEmp->employ_id, 4);
                    $nextSeq = $lastSeq + 1;
                }
                $db->table('employee_sequences')->insert([
                    'year' => $contractYear,
                    'last_sequence' => $nextSeq
                ]);
            }
            $db->transComplete();
            
            $employId = $contractYear . str_pad($nextSeq, 5, '0', STR_PAD_LEFT);

            $db->table('employees')->insert([
                'client_id' => $clientId,
                'employ_id' => $employId,
                'nik' => $employId, // Ensure 'nik' is also populated if required by constraints
                'nama' => "Karyawan Test $i",
                'email' => "karyawan{$i}@test.com",
                'tempat_lahir' => 'Jakarta',
                'tanggal_lahir' => '1990-01-01',
                'npwp' => '98.765.432.1-00' . sprintf("%02d", $i) . '.000',
                'work_location_id' => $locationId,
                'tipe_perjanjian' => 'PKWT',
                'start_contract' => date('Y-m-d'),
                'end_contract' => date('Y-m-d', strtotime('+1 year')),
            ]);
        }
        
        CLI::write("Successfully seeded 50 Clients, 50 Work Locations, and 50 Employees!", 'green');
    }
    
    private function getNextId($db, $table)
    {
        $query = $db->query("SELECT IDENT_CURRENT('$table') + IDENT_INCR('$table') AS next_id");
        $row = $query->getRow();
        if ($row && $row->next_id) {
            return $row->next_id;
        }
        return 1;
    }
}
