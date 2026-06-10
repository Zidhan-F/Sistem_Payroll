<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class Api extends ResourceController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function getNotifications()
    {
        $notifications = [];

        // 1. Check if there are active clients
        $clients = $this->db->table('clients')->get()->getResultArray();
        
        // Get current month and year
        $currentMonth = intval(date('n'));
        $currentYear = intval(date('Y'));
        
        $monthsWord = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $monthName = $monthsWord[$currentMonth] ?? date('F');

        foreach ($clients as $client) {
            // Check if there is an active payroll period for this client, month, year
            $period = $this->db->table('payroll_periods')
                               ->where('client_id', $client['id'])
                               ->where('bulan', $currentMonth)
                               ->where('tahun', $currentYear)
                               ->get()
                               ->getRow();
            if (!$period) {
                $notifications[] = [
                    'id' => 'cutoff_' . $client['id'],
                    'type' => 'warning',
                    'title' => 'Cut-off Periode Belum Dibuat',
                    'message' => "Klien <strong>" . esc($client['nama']) . "</strong> belum memiliki tanggal cut-off / periode aktif untuk bulan " . esc($monthName) . " " . esc($currentYear) . "!",
                    'link' => 'klien',
                    'client_id' => intval($client['id']),
                    'client_name' => $client['nama'],
                    'client_sektor' => $client['sektor']
                ];
            }
        }



        return $this->respond([
            'status' => 200,
            'data' => $notifications,
            'count' => count($notifications)
        ]);
    }

    // --- AUTH ---
    public function login()
    {
        $json = $this->request->getJSON();
        $username = $json->username ?? '';
        $password = $json->password ?? '';

        $user = $this->db->table('users')
                         ->where('username', $username)
                         ->get()
                         ->getRow();

        if ($user && $password === $user->password) { // Catatan: Sebaiknya gunakan password_verify
            $this->logActivity("User login berhasil", $user->username);
            return $this->respond([
                'message' => 'Login berhasil',
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username
                ]
            ]);
        }

        return $this->failUnauthorized('Username atau password salah');
    }

    public function getMinimumWages()
    {
        $tipe = $this->request->getGet('tipe') ?: 'UMP';
        $tahun = $this->request->getGet('tahun');
        
        $query = $this->db->table('minimum_wages');
        if ($tipe !== 'all') {
            $query->where('tipe', $tipe);
        }
        if ($tahun) {
            $query->where('tahun', $tahun);
        }
        $data = $query->get()->getResult();
        return $this->respond($data);
    }

    public function saveMinimumWages()
    {
        $data = $this->request->getJSON(true);
        
        $items = isset($data['items']) ? $data['items'] : [$data];
        if (empty($items)) {
            return $this->respondCreated(['message' => 'Tidak ada data untuk disimpan']);
        }
        
        // Extract the tipe and tahun from the first item to define our upload scope
        $firstItem = $items[0];
        $tipe = $firstItem['tipe'] ?? 'UMP';
        $tahun = $firstItem['tahun'] ?? 2026;
        
        // Strictly delete ONLY the existing records of this specific tipe and tahun!
        // This guarantees that UMK is NEVER touched when uploading UMP, and vice versa!
        $this->db->table('minimum_wages')
            ->where('tipe', $tipe)
            ->where('tahun', $tahun)
            ->delete();
            
        // Insert the uploaded list for this specific type and year
        foreach ($items as $item) {
            // Force strict consistency
            $item['tipe'] = $tipe;
            $item['tahun'] = $tahun;
            $this->db->table('minimum_wages')->insert($item);
        }
        
        return $this->respondCreated(['message' => 'Data gaji minimum berhasil disimpan']);
    }

    // --- CLIENTS ---
    public function getClients()
    {
        $clients = $this->db->table('clients')->get()->getResult();
        foreach ($clients as &$client) {
            if (isset($client->npwp)) {
                $client->npwp = (string)$client->npwp;
            }
        }
        return $this->respond($clients);
    }

    public function createClient()
    {
        $data = $this->request->getJSON(true);
        if (isset($data['npwp'])) {
            $data['npwp'] = (string)$data['npwp'];
        }
        $this->db->table('clients')->insert($data);
        $this->logActivity("Membuat klien baru: " . ($data['nama'] ?? ''));
        return $this->respondCreated(['message' => 'Klien berhasil ditambahkan']);
    }

    public function updateClient($id)
    {
        $data = $this->request->getJSON(true);
        if (isset($data['npwp'])) {
            $data['npwp'] = (string)$data['npwp'];
        }
        $this->db->table('clients')->where('id', $id)->update($data);
        $this->logActivity("Mengupdate klien ID: " . $id . " (" . ($data['nama'] ?? '') . ")");
        return $this->respond(['message' => 'Klien berhasil diupdate']);
    }

    public function deleteClient($id)
    {
        $client = $this->db->table('clients')->where('id', $id)->get()->getRow();
        $clientName = $client ? $client->nama : 'Unknown';
        $this->db->table('clients')->where('id', $id)->delete();
        $this->logActivity("Menghapus klien ID: " . $id . " (" . $clientName . ")");
        return $this->respondDeleted(['message' => 'Klien berhasil dihapus']);
    }

    // --- ORGANISASI ---
    public function getOrg()
    {
        $clientId = $this->request->getGet('client_id');
        $query = $this->db->table('divisions');
        if ($clientId) $query->where('client_id', $clientId);
        
        $divisions = $query->get()->getResultArray();

        foreach ($divisions as &$div) {
            $div['departments'] = $this->db->table('departments')
                                           ->where('division_id', $div['id'])
                                           ->get()
                                           ->getResultArray();
            
            foreach ($div['departments'] as &$dept) {
                $dept['positions'] = $this->db->table('positions')
                                              ->where('department_id', $dept['id'])
                                              ->get()
                                              ->getResultArray();
            }
        }

        return $this->respond($divisions);
    }

    public function createDivision()
    {
        $data = $this->request->getJSON(true);
        $this->db->table('divisions')->insert($data);
        return $this->respondCreated(['message' => 'Divisi berhasil ditambahkan']);
    }

    public function createDepartment()
    {
        $data = $this->request->getJSON(true);
        $this->db->table('departments')->insert($data);
        return $this->respondCreated(['message' => 'Department berhasil ditambahkan']);
    }

    public function createPosition()
    {
        $data = $this->request->getJSON(true);
        $this->db->table('positions')->insert($data);
        return $this->respondCreated(['message' => 'Posisi berhasil ditambahkan']);
    }

    public function updateOrg($type, $id)
    {
        $data = $this->request->getJSON(true);
        $table = '';
        if ($type === 'divisi') $table = 'divisions';
        elseif ($type === 'department') $table = 'departments';
        elseif ($type === 'posisi') $table = 'positions';

        if ($table) {
            $this->db->table($table)->where('id', $id)->update($data);
            return $this->respond(['message' => 'Data berhasil diupdate']);
        }
        return $this->fail('Tipe organisasi tidak valid');
    }

    public function deleteOrg($type, $id)
    {
        $table = '';
        if ($type === 'divisi') $table = 'divisions';
        elseif ($type === 'department') $table = 'departments';
        elseif ($type === 'posisi') $table = 'positions';

        if ($table) {
            $this->db->table($table)->where('id', $id)->delete();
            return $this->respondDeleted(['message' => 'Data berhasil dihapus']);
        }
        return $this->fail('Tipe organisasi tidak valid');
    }

    // --- PAYROLL ---
    public function getPayrollSchemes()
    {
        $schemes = $this->db->table('payroll_schemes')->get()->getResultArray();
        foreach ($schemes as &$scheme) {
            $scheme['components'] = $this->db->table('payroll_components')
                                             ->where('scheme_id', $scheme['id'])
                                             ->get()
                                             ->getResultArray();
        }
        return $this->respond($schemes);
    }

    public function createPayrollScheme()
    {
        $requestData = $this->request->getJSON(true);
        
        $schemeData = [
            'nama' => $requestData['nama'] ?? '',
            'deskripsi' => $requestData['deskripsi'] ?? '',
            'tipe' => $requestData['tipe'] ?? 'bulanan',
            'compensation_scheme_id' => !empty($requestData['compensation_scheme_id']) ? intval($requestData['compensation_scheme_id']) : null,
            'prorate' => isset($requestData['prorate']) ? intval($requestData['prorate']) : 0,
            'absen_tidak_potong' => isset($requestData['absen_tidak_potong']) ? intval($requestData['absen_tidak_potong']) : 0,
            'nominal_potongan' => isset($requestData['nominal_potongan']) ? floatval($requestData['nominal_potongan']) : 0,
            'grace_period_late' => isset($requestData['grace_period_late']) ? intval($requestData['grace_period_late']) : 0,
            'grace_period_early' => isset($requestData['grace_period_early']) ? intval($requestData['grace_period_early']) : 0,
            'min_overtime' => isset($requestData['min_overtime']) ? intval($requestData['min_overtime']) : 30,
            'denda_terlambat_per_jam' => isset($requestData['denda_terlambat_per_jam']) ? floatval($requestData['denda_terlambat_per_jam']) : 0,
            'denda_alfa_per_hari' => isset($requestData['denda_alfa_per_hari']) ? floatval($requestData['denda_alfa_per_hari']) : 0,
            'early_leave_threshold' => isset($requestData['early_leave_threshold']) ? intval($requestData['early_leave_threshold']) : 0,
        ];
        
        $this->db->table('payroll_schemes')->insert($schemeData);
        $schemeId = $this->db->insertID();
        
        $componentData = [
            'scheme_id' => $schemeId,
            'nama' => 'Gaji Pokok',
            'tipe' => 'pendapatan',
            'nilai' => $requestData['nilai'] ?? 0,
            'is_persentase' => isset($requestData['is_persentase']) ? intval($requestData['is_persentase']) : 0,
            'jenis_komponen' => 'basic_salary',
            'sumber_nilai' => $requestData['sumber_nilai'] ?? 'nominal',
            'periode' => $requestData['periode'] ?? 'bulan',
            'sifat_kompensasi' => 'tetap',
            'is_bpjs' => 1,
            'is_pph21' => 1
        ];
        
        $this->db->table('payroll_components')->insert($componentData);

        // Save selected compensation components
        if (!empty($requestData['components'])) {
            foreach ($requestData['components'] as $comp) {
                $compData = [
                    'scheme_id' => $schemeId,
                    'nama' => $comp['nama'] ?? '',
                    'tipe' => $comp['tipe'] ?? 'pendapatan',
                    'nilai' => isset($comp['nilai']) ? floatval($comp['nilai']) : 0,
                    'is_persentase' => isset($comp['is_persentase']) ? intval($comp['is_persentase']) : 0,
                    'jenis_komponen' => 'kompensasi',
                    'sumber_nilai' => $comp['sumber_nilai'] ?? 'nominal',
                    'periode' => $comp['periode'] ?? 'bulan',
                    'sifat_kompensasi' => $comp['sifat_kompensasi'] ?? 'tetap',
                    'is_bpjs' => isset($comp['is_bpjs']) ? intval($comp['is_bpjs']) : 0,
                    'is_pph21' => isset($comp['is_pph21']) ? intval($comp['is_pph21']) : 1
                ];
                $this->db->table('payroll_components')->insert($compData);
            }
        }
        
        $this->logActivity("Membuat skema payroll baru: " . ($schemeData['nama'] ?? ''));
        return $this->respondCreated(['message' => 'Skema berhasil ditambahkan']);
    }

    public function updatePayrollScheme($id)
    {
        $requestData = $this->request->getJSON(true);
        
        $schemeData = [
            'nama' => $requestData['nama'] ?? '',
            'deskripsi' => $requestData['deskripsi'] ?? '',
            'tipe' => $requestData['tipe'] ?? 'bulanan',
            'compensation_scheme_id' => !empty($requestData['compensation_scheme_id']) ? intval($requestData['compensation_scheme_id']) : null,
            'prorate' => isset($requestData['prorate']) ? intval($requestData['prorate']) : 0,
            'absen_tidak_potong' => isset($requestData['absen_tidak_potong']) ? intval($requestData['absen_tidak_potong']) : 0,
            'nominal_potongan' => isset($requestData['nominal_potongan']) ? floatval($requestData['nominal_potongan']) : 0,
            'grace_period_late' => isset($requestData['grace_period_late']) ? intval($requestData['grace_period_late']) : 0,
            'grace_period_early' => isset($requestData['grace_period_early']) ? intval($requestData['grace_period_early']) : 0,
            'min_overtime' => isset($requestData['min_overtime']) ? intval($requestData['min_overtime']) : 30,
            'denda_terlambat_per_jam' => isset($requestData['denda_terlambat_per_jam']) ? floatval($requestData['denda_terlambat_per_jam']) : 0,
            'denda_alfa_per_hari' => isset($requestData['denda_alfa_per_hari']) ? floatval($requestData['denda_alfa_per_hari']) : 0,
            'early_leave_threshold' => isset($requestData['early_leave_threshold']) ? intval($requestData['early_leave_threshold']) : 0,
        ];
        
        $this->db->table('payroll_schemes')->where('id', $id)->update($schemeData);
        
        $componentData = [
            'nama' => 'Gaji Pokok',
            'tipe' => 'pendapatan',
            'nilai' => $requestData['nilai'] ?? 0,
            'is_persentase' => isset($requestData['is_persentase']) ? intval($requestData['is_persentase']) : 0,
            'jenis_komponen' => 'basic_salary',
            'sumber_nilai' => $requestData['sumber_nilai'] ?? 'nominal',
            'periode' => $requestData['periode'] ?? 'bulan',
            'sifat_kompensasi' => 'tetap',
            'is_bpjs' => 1,
            'is_pph21' => 1
        ];
        
        $existing = $this->db->table('payroll_components')->where('scheme_id', $id)->where('jenis_komponen', 'basic_salary')->get()->getRow();
        if ($existing) {
            $this->db->table('payroll_components')->where('id', $existing->id)->update($componentData);
        } else {
            $componentData['scheme_id'] = $id;
            $this->db->table('payroll_components')->insert($componentData);
        }

        // Delete existing non-basic components
        $this->db->table('payroll_components')
                 ->where('scheme_id', $id)
                 ->where('jenis_komponen !=', 'basic_salary')
                 ->delete();

        // Save selected compensation components
        if (!empty($requestData['components'])) {
            foreach ($requestData['components'] as $comp) {
                $compData = [
                    'scheme_id' => $id,
                    'nama' => $comp['nama'] ?? '',
                    'tipe' => $comp['tipe'] ?? 'pendapatan',
                    'nilai' => isset($comp['nilai']) ? floatval($comp['nilai']) : 0,
                    'is_persentase' => isset($comp['is_persentase']) ? intval($comp['is_persentase']) : 0,
                    'jenis_komponen' => 'kompensasi',
                    'sumber_nilai' => $comp['sumber_nilai'] ?? 'nominal',
                    'periode' => $comp['periode'] ?? 'bulan',
                    'sifat_kompensasi' => $comp['sifat_kompensasi'] ?? 'tetap',
                    'is_bpjs' => isset($comp['is_bpjs']) ? intval($comp['is_bpjs']) : 0,
                    'is_pph21' => isset($comp['is_pph21']) ? intval($comp['is_pph21']) : 1
                ];
                $this->db->table('payroll_components')->insert($compData);
            }
        }
        
        $this->logActivity("Mengupdate skema payroll ID: " . $id . " (" . ($schemeData['nama'] ?? '') . ")");
        return $this->respond(['message' => 'Skema berhasil diupdate']);
    }

    public function deletePayrollScheme($id)
    {
        $scheme = $this->db->table('payroll_schemes')->where('id', $id)->get()->getRow();
        $schemeName = $scheme ? $scheme->nama : 'Unknown';
        $this->db->table('payroll_components')->where('scheme_id', $id)->delete();
        $this->db->table('payroll_schemes')->where('id', $id)->delete();
        $this->logActivity("Menghapus skema payroll ID: " . $id . " (" . $schemeName . ")");
        return $this->respondDeleted(['message' => 'Skema berhasil dihapus']);
    }

    public function createPayrollComponent()
    {
        $data = $this->request->getJSON(true);
        $this->db->table('payroll_components')->insert($data);
        return $this->respondCreated(['message' => 'Komponen berhasil ditambahkan']);
    }

    public function updatePayrollComponent($id)
    {
        $data = $this->request->getJSON(true);
        $this->db->table('payroll_components')->where('id', $id)->update($data);
        return $this->respond(['message' => 'Komponen berhasil diupdate']);
    }

    public function deletePayrollComponent($id)
    {
        $this->db->table('payroll_components')->where('id', $id)->delete();
        return $this->respondDeleted(['message' => 'Komponen berhasil dihapus']);
    }

    // --- TAX SCHEMES ---
    public function getTaxSchemes()
    {
        $schemes = $this->db->table('tax_schemes')->get()->getResult();
        return $this->respond($schemes);
    }

    public function createTaxScheme()
    {
        $data = $this->request->getJSON(true);
        $this->db->table('tax_schemes')->insert($data);
        $id = $this->db->insertID();
        return $this->respondCreated([
            'status' => 'success',
            'message' => 'Skema pajak berhasil ditambahkan',
            'id' => $id
        ]);
    }

    public function updateTaxScheme($id)
    {
        $data = $this->request->getJSON(true);
        $this->db->table('tax_schemes')->where('id', $id)->update($data);
        return $this->respond(['message' => 'Skema pajak berhasil diupdate']);
    }

    public function deleteTaxScheme($id)
    {
        $this->db->table('tax_schemes')->where('id', $id)->delete();
        return $this->respondDeleted(['message' => 'Skema pajak berhasil dihapus']);
    }

    // --- COMPENSATION SCHEMES ---
    public function getCompensationSchemes()
    {
        $schemes = $this->db->table('compensation_schemes')->get()->getResultArray();
        foreach ($schemes as &$scheme) {
            $scheme['components'] = $this->db->table('compensation_components')
                                             ->where('scheme_id', $scheme['id'])
                                             ->get()
                                             ->getResultArray();
        }
        return $this->respond($schemes);
    }

    public function createCompensationScheme()
    {
        $requestData = $this->request->getJSON(true);
        
        $schemeData = [
            'nama' => $requestData['nama'] ?? '',
            'deskripsi' => $requestData['deskripsi'] ?? ''
        ];
        
        $this->db->table('compensation_schemes')->insert($schemeData);
        $schemeId = $this->db->insertID();
        $namaKompensasi = $requestData['nama'] ?? '';
        $sifatKompensasi = ($requestData['sifat_kompensasi'] ?? '') === 'tidak_tetap' ? 'tidak_tetap' : 'tetap';
        
        $componentData = [
            'scheme_id' => $schemeId,
            'nama' => $namaKompensasi,
            'tipe' => $requestData['tipe'] ?? 'pendapatan',
            'nilai' => $requestData['nilai'] ?? 0,
            'is_persentase' => isset($requestData['is_persentase']) ? intval($requestData['is_persentase']) : 0,
            'jenis_komponen' => 'kompensasi',
            'sumber_nilai' => $requestData['sumber_nilai'] ?? 'nominal',
            'periode' => $requestData['periode'] ?? 'bulan',
            'sifat_kompensasi' => $sifatKompensasi
        ];
        
        $this->db->table('compensation_components')->insert($componentData);
        
        $this->logActivity("Membuat skema kompensasi baru: " . ($schemeData['nama'] ?? ''));
        return $this->respondCreated(['message' => 'Skema kompensasi berhasil ditambahkan']);
    }

    public function updateCompensationScheme($id)
    {
        $requestData = $this->request->getJSON(true);
        
        $schemeData = [
            'nama' => $requestData['nama'] ?? '',
            'deskripsi' => $requestData['deskripsi'] ?? ''
        ];
        
        $this->db->table('compensation_schemes')->where('id', $id)->update($schemeData);
        
        $namaKompensasi = $requestData['nama'] ?? '';
        $sifatKompensasi = ($requestData['sifat_kompensasi'] ?? '') === 'tidak_tetap' ? 'tidak_tetap' : 'tetap';

        $componentData = [
            'nama' => $namaKompensasi,
            'tipe' => $requestData['tipe'] ?? 'pendapatan',
            'nilai' => $requestData['nilai'] ?? 0,
            'is_persentase' => isset($requestData['is_persentase']) ? intval($requestData['is_persentase']) : 0,
            'jenis_komponen' => 'kompensasi',
            'sumber_nilai' => $requestData['sumber_nilai'] ?? 'nominal',
            'periode' => $requestData['periode'] ?? 'bulan',
            'sifat_kompensasi' => $sifatKompensasi
        ];
        
        $existing = $this->db->table('compensation_components')->where('scheme_id', $id)->get()->getRow();
        if ($existing) {
            $this->db->table('compensation_components')->where('id', $existing->id)->update($componentData);
        } else {
            $componentData['scheme_id'] = $id;
            $this->db->table('compensation_components')->insert($componentData);
        }
        
        $this->logActivity("Mengupdate skema kompensasi ID: " . $id . " (" . ($schemeData['nama'] ?? '') . ")");
        return $this->respond(['message' => 'Skema kompensasi berhasil diupdate']);
    }

    public function deleteCompensationScheme($id)
    {
        $scheme = $this->db->table('compensation_schemes')->where('id', $id)->get()->getRow();
        $schemeName = $scheme ? $scheme->nama : 'Unknown';
        $this->db->table('compensation_schemes')->where('id', $id)->delete();
        $this->db->table('compensation_components')->where('scheme_id', $id)->delete(); // Cascade
        $this->logActivity("Menghapus skema kompensasi ID: " . $id . " (" . $schemeName . ")");
        return $this->respondDeleted(['message' => 'Skema kompensasi berhasil dihapus']);
    }

    // --- SCHEDULE TEMPLATES ---
    public function getScheduleTemplates()
    {
        $tahun = $this->request->getGet('tahun');
        $builder = $this->db->table('payroll_schedules');
        if ($tahun) {
            $builder->where('tahun', intval($tahun));
        }
        $schedules = $builder->get()->getResultArray();
        return $this->respond($schedules);
    }

    public function createScheduleTemplate()
    {
        $data = $this->request->getJSON(true);
        $insertData = [
            'nama' => $data['nama'] ?? '',
            'pay_date' => isset($data['pay_date']) ? intval($data['pay_date']) : 25,
            'cutoff_start' => isset($data['cutoff_start']) ? intval($data['cutoff_start']) : 21,
            'cutoff_end' => isset($data['cutoff_end']) ? intval($data['cutoff_end']) : 20,
            'deskripsi' => $data['deskripsi'] ?? null,
            'tahun' => isset($data['tahun']) ? intval($data['tahun']) : intval(date('Y'))
        ];

        $this->db->table('payroll_schedules')->insert($insertData);
        $this->logActivity("Membuat master schedule baru: " . ($insertData['nama'] ?? ''));
        return $this->respondCreated(['message' => 'Schedule template berhasil ditambahkan']);
    }

    public function updateScheduleTemplate($id)
    {
        $data = $this->request->getJSON(true);
        $updateData = [
            'nama' => $data['nama'] ?? '',
            'pay_date' => isset($data['pay_date']) ? intval($data['pay_date']) : 25,
            'cutoff_start' => isset($data['cutoff_start']) ? intval($data['cutoff_start']) : 21,
            'cutoff_end' => isset($data['cutoff_end']) ? intval($data['cutoff_end']) : 20,
            'deskripsi' => $data['deskripsi'] ?? null,
            'tahun' => isset($data['tahun']) ? intval($data['tahun']) : intval(date('Y'))
        ];

        $this->db->table('payroll_schedules')->where('id', $id)->update($updateData);
        $this->logActivity("Mengupdate master schedule ID: " . $id . " (" . ($updateData['nama'] ?? '') . ")");
        return $this->respond(['message' => 'Schedule template berhasil diupdate']);
    }

    public function deleteScheduleTemplate($id)
    {
        $schedule = $this->db->table('payroll_schedules')->where('id', $id)->get()->getRow();
        $name = $schedule ? $schedule->nama : 'Unknown';
        $this->db->table('payroll_schedules')->where('id', $id)->delete();
        $this->logActivity("Menghapus master schedule ID: " . $id . " (" . $name . ")");
        return $this->respondDeleted(['message' => 'Schedule template berhasil dihapus']);
    }

    // --- ATTENDANCE LOGS ---
    private function calculateShiftAttendance($employeeId, $tanggal, $jamMasuk, $jamKeluar, $status, $providedShiftSchemeId = null, $payoutPeriod = null)
    {
        $db = \Config\Database::connect();
        
        // 1. Determine Shift Scheme
        $shiftSchemeId = null;
        if (!empty($providedShiftSchemeId)) {
            $shiftSchemeId = intval($providedShiftSchemeId);
        } else {
            // Find active shift assignment for this employee
            $empShift = $db->table('employee_shifts')
                ->where('employee_id', intval($employeeId))
                ->where('start_date <=', $tanggal)
                ->groupStart()
                    ->where('end_date IS NULL')
                    ->orWhere('end_date >=', $tanggal)
                ->groupEnd()
                ->orderBy('start_date', 'DESC')
                ->get()->getRow();
            if ($empShift) {
                $shiftSchemeId = intval($empShift->shift_scheme_id);
            }
        }

        $result = [
            'shift_scheme_id'            => $shiftSchemeId,
            'calculated_work_hours'      => 0.0,
            'calculated_overtime_hours'  => 0.0,
            'late_hours'                 => 0.0,
            'late_minutes'               => 0,
            'late_penalty_hours'         => 0,
            'denda_terlambat'            => 0.0,
            'early_leave_hours'          => 0.0,
            'is_early_leave_alfa'        => 0,
            'denda_alfa'                 => 0.0,
            'absent_penalty'             => 0.0,
            'is_incomplete'              => 0,
            'is_rapel'                   => 0,
            'payout_period'              => null,
        ];

        // 2. Determine rapel status based on client's cut-off
        $emp = $db->table('employees')->where('id', intval($employeeId))->get()->getRow();
        $clientId = $emp ? $emp->client_id : null;

        $cutoffStart = 21;
        $cutoffEnd = 20;
        if ($clientId) {
            $clientConfig = $db->table('client_payroll_configs')->where('client_id', $clientId)->get()->getRow();
            if ($clientConfig && !empty($clientConfig->scheme_template_id)) {
                $scheme = $db->table('payroll_scheme_templates')->where('id', $clientConfig->scheme_template_id)->get()->getRow();
                if ($scheme && !empty($scheme->schedule_template_id)) {
                    $sched = $db->table('payroll_schedules')->where('id', $scheme->schedule_template_id)->get()->getRow();
                    if ($sched) {
                        $cutoffStart = intval($sched->cutoff_start);
                        $cutoffEnd = intval($sched->cutoff_end);
                    }
                }
            }
        }

        // Determine which payroll period this date naturally belongs to
        $ts = strtotime($tanggal);
        $tYear = intval(date('Y', $ts));
        $tMonth = intval(date('n', $ts));
        $tDay = intval(date('j', $ts));

        if ($tDay > $cutoffEnd) {
            $natMonth = $tMonth + 1;
            $natYear = $tYear;
            if ($natMonth > 12) {
                $natMonth = 1;
                $natYear++;
            }
        } else {
            $natMonth = $tMonth;
            $natYear = $tYear;
        }
        $naturalPeriod = $natMonth . '-' . $natYear;

        if (!empty($payoutPeriod) && $payoutPeriod !== $naturalPeriod) {
            $result['is_rapel'] = 1;
            $result['payout_period'] = $payoutPeriod;
        }

        if (!$shiftSchemeId) {
            return $result;
        }

        $shift = $db->table('shift_schemes')->where('id', $shiftSchemeId)->get()->getRow();
        if (!$shift) {
            return $result;
        }

        if ($status !== 'Hadir') {
            return $result;
        }

        if (empty($jamMasuk)) {
            $result['is_incomplete'] = 1;
            return $result;
        }

        // 3. Perform calculations
        $inTime = strtotime($tanggal . ' ' . $jamMasuk);
        $shiftIn = strtotime($tanggal . ' ' . $shift->start_time);
        $shiftOut = strtotime($tanggal . ' ' . $shift->end_time);

        $graceLate  = 0;
        $graceEarly = 0;
        $minOvertime          = 30;
        $dendaTerlambatPerJam = 0.0;

        // Skema absensi (untuk early leave / alfa) — dari payroll_scheme
        $absenTidakPotong    = 0;    // 1 = tidak potong gaji
        $absenNominalPerHari = 0.0;  // nominal potongan per hari (Attendance Deducts Nominal)
        $absenProrate        = 1;    // default prorate (gaji/hari kerja)

        if ($clientId) {
            $schemeTemplateModel = new \App\Models\PayrollSchemeTemplateModel();
            $stoScheme = $schemeTemplateModel->getSchemeForEmployee(
                $clientId,
                $emp->division_id ?? null,
                $emp->department_id ?? null,
                $emp->position_id ?? null
            );

            if ($stoScheme) {
                $graceLate            = intval($stoScheme['grace_period_late'] ?? 0);
                $graceEarly           = intval($stoScheme['grace_period_early'] ?? 0);
                $minOvertime          = intval($stoScheme['min_overtime'] ?? 30);
                $dendaTerlambatPerJam = floatval($stoScheme['denda_terlambat_per_jam'] ?? 0);
            } else {
                $clientConfig = $db->table('client_payroll_configs')
                                   ->where('client_id', $clientId)
                                   ->get()->getRow();
                if ($clientConfig && !empty($clientConfig->payroll_scheme_id)) {
                    $payrollScheme = $db->table('payroll_schemes')
                                        ->where('id', $clientConfig->payroll_scheme_id)
                                        ->get()->getRow();
                    if ($payrollScheme) {
                        $graceLate            = intval($payrollScheme->grace_period_late ?? 0);
                        $graceEarly           = intval($payrollScheme->grace_period_early ?? 0);
                        $minOvertime          = intval($payrollScheme->min_overtime ?? 30);
                        $dendaTerlambatPerJam = floatval($payrollScheme->denda_terlambat_per_jam ?? 0);

                        // Baca skema absensi dari payroll_scheme
                        $absenTidakPotong    = intval($payrollScheme->absen_tidak_potong ?? 0);
                        $absenNominalPerHari = floatval($payrollScheme->nominal_potongan ?? 0);
                        // prorate = 1 jika bukan "tidak potong" dan bukan "nominal"
                        $absenProrate = ($absenTidakPotong == 0 && $absenNominalPerHari == 0) ? 1 : 0;
                    }
                }
            }
        }

        // ── ALFA / ABSEN ──────────────────────────────────────────────────────
        if ($status !== 'Hadir') {
            // Tidak hitung denda di sini — potongan alfa dihitung di Payroll.php
            // berdasarkan skema absensi (prorate / nominal / tidak potong)
            return $result;
        }

        // ── HADIR: hitung keterlambatan ───────────────────────────────────────
        if (empty($jamMasuk)) {
            $result['is_incomplete'] = 1;
            return $result;
        }

        $inTime   = strtotime($tanggal . ' ' . $jamMasuk);
        $shiftIn  = strtotime($tanggal . ' ' . $shift->start_time);
        $shiftOut = strtotime($tanggal . ' ' . $shift->end_time);

        // Keterlambatan
        $lateMinutes      = 0;
        $latePenaltyHours = 0;
        $dendaTerlambat   = 0.0;

        if ($inTime > ($shiftIn + ($graceLate * 60))) {
            $lateMinutes      = intval(ceil(($inTime - $shiftIn) / 60));
            $latePenaltyHours = intval(ceil($lateMinutes / 60.0));
            $dendaTerlambat   = $latePenaltyHours * $dendaTerlambatPerJam;
        }

        $result['late_hours']         = round($lateMinutes / 60.0, 2);
        $result['late_minutes']       = $lateMinutes;
        $result['late_penalty_hours'] = $latePenaltyHours;
        $result['denda_terlambat']    = $dendaTerlambat;

        // ── Jam keluar ────────────────────────────────────────────────────────
        if (empty($jamKeluar)) {
            $result['is_incomplete'] = 1;
            return $result;
        }

        $outTime = strtotime($tanggal . ' ' . $jamKeluar);
        if ($outTime <= $inTime) {
            $result['is_incomplete'] = 1;
            return $result;
        }

        // ── Early Leave ───────────────────────────────────────────────────────
        $earlyLeaveMinutes = 0;
        $isEarlyLeaveAlfa  = 0;
        $earlyLeaveHours   = 0.0;

        if ($outTime < $shiftOut) {
            $earlyLeaveMinutes = intval(ceil(($shiftOut - $outTime) / 60));
            $earlyLeaveHours   = round($earlyLeaveMinutes / 60.0, 2);

            // Jika early leave melebihi toleransi grace → dihitung sebagai Alfa
            if ($earlyLeaveMinutes > $graceEarly) {
                $result['is_incomplete'] = 1;
                $isEarlyLeaveAlfa = 1;
                // Potongan untuk early leave dihitung di Payroll.php (ikut skema absensi)
            }
        }

        $result['early_leave_hours']   = $earlyLeaveHours;
        $result['is_early_leave_alfa'] = $isEarlyLeaveAlfa;
        $result['denda_alfa']          = 0.0;   // Tidak dipakai, dihitung di Payroll.php
        $result['absent_penalty']      = 0.0;   // Tidak dipakai, dihitung di Payroll.php

        // ── Jam Kerja Aktual ──────────────────────────────────────────────────
        $breakDuration       = isset($shift->break_duration) ? floatval($shift->break_duration) : 0.0;
        $actualDurationHours = max(0, (($outTime - $inTime) / 3600) - $breakDuration);
        $standardDuration    = floatval($shift->duration);

        $workHours = round(min($standardDuration, $actualDurationHours), 2);
        $result['calculated_work_hours'] = min(9999.99, max(0, $workHours));

        // ── Overtime ──────────────────────────────────────────────────────────
        $otHours   = 0.0;
        $otMinutes = 0;

        if ($outTime > $shiftOut) {
            $otMinutes = ($outTime - $shiftOut) / 60;
            $otHours   = $otMinutes / 60.0;
        }

        if ($otMinutes >= $minOvertime) {
            $result['calculated_overtime_hours'] = min(9999.99, round(max(0, $otHours), 2));
        } else {
            $result['calculated_overtime_hours'] = 0.0;
        }

        // ── Sync ke overtime_logs ─────────────────────────────────────────────
        if ($result['calculated_overtime_hours'] > 0) {
            $existingOt = $db->table('overtime_logs')
                ->where('employee_id', intval($employeeId))
                ->where('tanggal', $tanggal)
                ->get()->getRow();

            $isHoliday = 0;
            $dayOfWeek = date('w', strtotime($tanggal));
            if ($dayOfWeek == 0) {
                $isHoliday = 1;
            } else {
                $holiday = $db->table('holiday_calendar')->where('tanggal', $tanggal)->get()->getRow();
                if ($holiday) {
                    $isHoliday = 1;
                }
            }

            $otData = [
                'jam_lembur'  => $result['calculated_overtime_hours'],
                'is_holiday'  => $isHoliday,
                'keterangan'  => 'Auto: shift ' . $shift->name,
                'status'      => 'Pending',
                'approved_by' => null,
                'approved_at' => null,
            ];

            if ($existingOt) {
                $db->table('overtime_logs')->where('id', $existingOt->id)->update($otData);
            } else {
                $otData['employee_id'] = intval($employeeId);
                $otData['tanggal']     = $tanggal;
                $db->table('overtime_logs')->insert($otData);
            }
        } else {
            $db->table('overtime_logs')
                ->where('employee_id', intval($employeeId))
                ->where('tanggal', $tanggal)
                ->delete();
        }

        return $result;
    }

    public function getAttendanceLogs()
    {
        $employeeId = $this->request->getGet('employee_id');
        $bulan = $this->request->getGet('bulan');
        $tahun = $this->request->getGet('tahun');
        $clientId = $this->request->getGet('client_id');

        $builder = $this->db->table('attendance_logs');
        $builder->select('attendance_logs.id, attendance_logs.employee_id, attendance_logs.log_date as tanggal, 
                          attendance_logs.status, attendance_logs.check_in as jam_masuk, 
                          attendance_logs.check_out as jam_keluar, attendance_logs.notes as keterangan, 
                          attendance_logs.created_at, attendance_logs.shift_scheme_id, attendance_logs.is_rapel, 
                          attendance_logs.payout_period, attendance_logs.calculated_work_hours, 
                          attendance_logs.calculated_overtime_hours, attendance_logs.is_incomplete,
                          employees.nama as employee_name, shift_schemes.name as shift_name');
        $builder->join('employees', 'employees.id = attendance_logs.employee_id', 'left');
        $builder->join('shift_schemes', 'shift_schemes.id = attendance_logs.shift_scheme_id', 'left');

        if ($employeeId) {
            $builder->where('attendance_logs.employee_id', intval($employeeId));
        }
        if ($clientId) {
            $builder->where('employees.client_id', intval($clientId));
        }
        if ($bulan && $tahun) {
            $builder->where('MONTH(attendance_logs.log_date)', intval($bulan));
            $builder->where('YEAR(attendance_logs.log_date)', intval($tahun));
        }

        $builder->orderBy('attendance_logs.log_date', 'ASC');
        $logs = $builder->get()->getResultArray();
        return $this->respond($logs);
    }

    public function createAttendanceLog()
    {
        $data = $this->request->getJSON(true);

        if (empty($data['employee_id']) || empty($data['tanggal'])) {
            return $this->failValidationErrors('employee_id dan tanggal wajib diisi');
        }

        $calc = $this->calculateShiftAttendance(
            $data['employee_id'],
            $data['tanggal'],
            $data['jam_masuk'] ?? null,
            $data['jam_keluar'] ?? null,
            $data['status'] ?? 'Hadir',
            $data['shift_scheme_id'] ?? null,
            $data['payout_period'] ?? null
        );

        $builder = $this->db->table('attendance_logs')
            ->where('employee_id', intval($data['employee_id']))
            ->where('log_date', $data['tanggal']);
        if (!empty($calc['shift_scheme_id'])) {
            $builder->where('shift_scheme_id', $calc['shift_scheme_id']);
        } else {
            $builder->where('shift_scheme_id IS NULL');
        }
        $existing = $builder->get()->getRow();

        if ($existing) {
            $this->db->table('attendance_logs')->where('id', $existing->id)->update([
                'check_in' => $data['jam_masuk'] ?? null,
                'check_out' => $data['jam_keluar'] ?? null,
                'notes' => $data['keterangan'] ?? null,
                'shift_scheme_id'            => $calc['shift_scheme_id'],
                'is_rapel'                   => $calc['is_rapel'],
                'payout_period'              => $calc['payout_period'],
                'calculated_work_hours'      => $calc['calculated_work_hours'],
                'calculated_overtime_hours'  => $calc['calculated_overtime_hours'],
                'late_hours'                 => $calc['late_hours'],
                'late_minutes'               => $calc['late_minutes'],
                'late_penalty_hours'         => $calc['late_penalty_hours'],
                'denda_terlambat'            => $calc['denda_terlambat'],
                'early_leave_hours'          => $calc['early_leave_hours'],
                'early_leave_minutes'        => $calc['early_leave_minutes'] ?? 0,
                'is_early_leave_alfa'        => $calc['is_early_leave_alfa'],
                'denda_alfa'                 => $calc['denda_alfa'],
                'absent_penalty'             => $calc['absent_penalty'],
                'is_incomplete'              => $calc['is_incomplete'],
            ]);
            return $this->respond(['message' => 'Attendance log berhasil diupdate']);
        }

        $insertData = [
            'employee_id'                => intval($data['employee_id']),
            'log_date'                   => $data['tanggal'],
            'check_in'                   => $data['jam_masuk'] ?? null,
            'check_out'                  => $data['jam_keluar'] ?? null,
            'notes'                      => $data['keterangan'] ?? null,
            'shift_scheme_id'            => $calc['shift_scheme_id'],
            'is_rapel'                   => $calc['is_rapel'],
            'payout_period'              => $calc['payout_period'],
            'calculated_work_hours'      => $calc['calculated_work_hours'],
            'calculated_overtime_hours'  => $calc['calculated_overtime_hours'],
            'late_hours'                 => $calc['late_hours'],
            'late_minutes'               => $calc['late_minutes'],
            'late_penalty_hours'         => $calc['late_penalty_hours'],
            'denda_terlambat'            => $calc['denda_terlambat'],
            'early_leave_hours'          => $calc['early_leave_hours'],
            'early_leave_minutes'        => $calc['early_leave_minutes'] ?? 0,
            'is_early_leave_alfa'        => $calc['is_early_leave_alfa'],
            'denda_alfa'                 => $calc['denda_alfa'],
            'absent_penalty'             => $calc['absent_penalty'],
            'is_incomplete'              => $calc['is_incomplete'],
        ];

        $this->db->table('attendance_logs')->insert($insertData);
        return $this->respondCreated(['message' => 'Attendance log berhasil ditambahkan']);
    }

    public function createAttendanceBulk()
    {
        $data = $this->request->getJSON(true);
        $logs = $data['logs'] ?? [];
        $count = 0;

        foreach ($logs as $log) {
            if (empty($log['employee_id']) || empty($log['tanggal'])) continue;

            $shiftSchemeId = $log['shift_scheme_id'] ?? null;

            // Jika shift_scheme_id kosong, coba resolve dari shift_name
            if (empty($shiftSchemeId) && !empty($log['shift_name'])) {
                $matchedShift = $this->db->table('shift_schemes')
                    ->where('LOWER(name)', strtolower(trim($log['shift_name'])))
                    ->get()->getRow();
                if (!$matchedShift) {
                    // Coba partial match
                    $matchedShift = $this->db->table('shift_schemes')
                        ->like('name', trim($log['shift_name']), 'none')
                        ->get()->getRow();
                }
                if ($matchedShift) {
                    $shiftSchemeId = $matchedShift->id;
                }
            }

            // Jika masih kosong, coba auto-detect shift dari jam masuk
            if (empty($shiftSchemeId) && !empty($log['jam_masuk'])) {
                $jamMasukInt = intval(explode(':', $log['jam_masuk'])[0]);
                // Shift pagi: masuk sebelum jam 12
                // Shift siang: masuk jam 12 ke atas
                $namaShiftGuess = $jamMasukInt < 12 ? 'Pagi' : 'Siang';
                $matchedShift = $this->db->table('shift_schemes')
                    ->where('name', $namaShiftGuess)
                    ->get()->getRow();
                if ($matchedShift) {
                    $shiftSchemeId = $matchedShift->id;
                }
            }

            $calc = $this->calculateShiftAttendance(
                $log['employee_id'],
                $log['tanggal'],
                $log['jam_masuk'] ?? null,
                $log['jam_keluar'] ?? null,
                $log['status'] ?? 'Hadir',
                $shiftSchemeId,
                $log['payout_period'] ?? null
            );

            $builder = $this->db->table('attendance_logs')
                ->where('employee_id', intval($log['employee_id']))
                ->where('log_date', $log['tanggal']);
            if (!empty($calc['shift_scheme_id'])) {
                $builder->where('shift_scheme_id', $calc['shift_scheme_id']);
            } else {
                $builder->where('shift_scheme_id IS NULL');
            }
            $existing = $builder->get()->getRow();

            $logData = [
                'check_in'                   => $log['jam_masuk'] ?? null,
                'check_out'                  => $log['jam_keluar'] ?? null,
                'notes'                      => $log['keterangan'] ?? null,
                'shift_scheme_id'            => $calc['shift_scheme_id'],
                'is_rapel'                   => $calc['is_rapel'],
                'payout_period'              => $calc['payout_period'],
                'calculated_work_hours'      => min(9999.99, max(0, floatval($calc['calculated_work_hours']))),
                'calculated_overtime_hours'  => min(9999.99, max(0, floatval($calc['calculated_overtime_hours']))),
                'late_hours'                 => min(9999.99, max(0, floatval($calc['late_hours']))),
                'late_minutes'               => intval($calc['late_minutes']),
                'late_penalty_hours'         => intval($calc['late_penalty_hours']),
                'denda_terlambat'            => floatval($calc['denda_terlambat']),
                'early_leave_hours'          => min(9999.99, max(0, floatval($calc['early_leave_hours']))),
                'early_leave_minutes'        => intval($calc['early_leave_minutes'] ?? 0),
                'is_early_leave_alfa'        => intval($calc['is_early_leave_alfa']),
                'denda_alfa'                 => floatval($calc['denda_alfa']),
                'absent_penalty'             => floatval($calc['absent_penalty']),
                'is_incomplete'              => $calc['is_incomplete'],
            ];

            if ($existing) {
                $this->db->table('attendance_logs')->where('id', $existing->id)->update($logData);
            } else {
                $logData['employee_id'] = intval($log['employee_id']);
                $logData['log_date'] = $log['tanggal'];
                $this->db->table('attendance_logs')->insert($logData);
            }
            $count++;
        }

        return $this->respondCreated(['message' => "Berhasil menyimpan {$count} attendance logs"]);
    }

    public function updateAttendanceLog($id)
    {
        $data = $this->request->getJSON(true);
        $old = $this->db->table('attendance_logs')->where('id', $id)->get()->getRow();
        if (!$old) {
            return $this->fail('Attendance log tidak ditemukan');
        }

        $calc = $this->calculateShiftAttendance(
            $old->employee_id,
            $old->log_date,
            $data['jam_masuk'] ?? $old->check_in,
            $data['jam_keluar'] ?? $old->check_out,
            'Hadir', // Default status since column doesn't exist
            $data['shift_scheme_id'] ?? $old->shift_scheme_id,
            $data['payout_period'] ?? $old->payout_period
        );

        $updateData = [
            'check_in' => $data['jam_masuk'] ?? $old->check_in,
            'check_out' => $data['jam_keluar'] ?? $old->check_out,
            'notes' => $data['keterangan'] ?? $old->notes,
            'shift_scheme_id' => $calc['shift_scheme_id'],
            'is_rapel' => $calc['is_rapel'],
            'payout_period' => $calc['payout_period'],
            'calculated_work_hours' => $calc['calculated_work_hours'],
            'calculated_overtime_hours' => $calc['calculated_overtime_hours'],
            'late_hours' => $calc['late_hours'],
            'late_minutes' => $calc['late_minutes'] ?? 0,
            'late_penalty_hours' => $calc['late_penalty_hours'] ?? 0,
            'denda_terlambat' => $calc['denda_terlambat'] ?? 0,
            'early_leave_hours' => $calc['early_leave_hours'],
            'early_leave_minutes' => $calc['early_leave_minutes'] ?? 0,
            'is_early_leave_alfa' => $calc['is_early_leave_alfa'] ?? 0,
            'denda_alfa' => $calc['denda_alfa'] ?? 0,
            'absent_penalty' => $calc['absent_penalty'] ?? 0,
            'is_incomplete' => $calc['is_incomplete']
        ];

        $this->db->table('attendance_logs')->where('id', $id)->update($updateData);
        return $this->respond(['message' => 'Attendance log berhasil diupdate']);
    }

    public function deleteAttendanceLog($id)
    {
        $old = $this->db->table('attendance_logs')->where('id', $id)->get()->getRow();
        if ($old) {
            // Also clean up automatic overtime log
            $this->db->table('overtime_logs')
                ->where('employee_id', $old->employee_id)
                ->where('tanggal', $old->log_date)
                ->delete();
        }
        $this->db->table('attendance_logs')->where('id', $id)->delete();
        return $this->respondDeleted(['message' => 'Attendance log berhasil dihapus']);
    }

    // --- OVERTIME LOGS ---
    public function getOvertimeLogs()
    {
        $employeeId = $this->request->getGet('employee_id');
        $bulan = $this->request->getGet('bulan');
        $tahun = $this->request->getGet('tahun');
        $clientId = $this->request->getGet('client_id');

        $builder = $this->db->table('overtime_logs');
        $builder->select('overtime_logs.*, employees.nama as employee_name');
        $builder->join('employees', 'employees.id = overtime_logs.employee_id', 'left');

        if ($employeeId) {
            $builder->where('overtime_logs.employee_id', intval($employeeId));
        }
        if ($clientId) {
            $builder->where('employees.client_id', intval($clientId));
        }
        if ($bulan && $tahun) {
            $builder->where('MONTH(overtime_logs.tanggal)', intval($bulan));
            $builder->where('YEAR(overtime_logs.tanggal)', intval($tahun));
        }

        $builder->orderBy('overtime_logs.tanggal', 'ASC');
        $logs = $builder->get()->getResultArray();
        return $this->respond($logs);
    }

    public function createOvertimeLog()
    {
        $data = $this->request->getJSON(true);

        if (empty($data['employee_id']) || empty($data['tanggal']) || !isset($data['jam_lembur'])) {
            return $this->failValidationErrors('employee_id, tanggal, dan jam_lembur wajib diisi');
        }

        $jamLembur = floatval($data['jam_lembur']);
        $isHoliday = intval($data['is_holiday'] ?? 0);

        // Validasi: Lembur hari kerja maksimal 3 jam
        if (!$isHoliday && $jamLembur > 3) {
            return $this->failValidationErrors('Lembur hari kerja maksimal 3 jam per hari!');
        }
        if ($jamLembur <= 0) {
            return $this->failValidationErrors('Jam lembur harus lebih dari 0!');
        }

        // Auto-detect holiday from holiday_calendar
        if (!$isHoliday) {
            $holiday = $this->db->table('holiday_calendar')
                ->where('tanggal', $data['tanggal'])
                ->get()->getRow();
            if ($holiday) {
                $isHoliday = 1;
            } else {
                // Check if weekend (Saturday=6, Sunday=0)
                $dayOfWeek = date('w', strtotime($data['tanggal']));
                if ($dayOfWeek == 0 || $dayOfWeek == 6) {
                    $isHoliday = 1;
                }
            }
        }

        // Check duplicate
        $existing = $this->db->table('overtime_logs')
            ->where('employee_id', intval($data['employee_id']))
            ->where('tanggal', $data['tanggal'])
            ->get()->getRow();
        if ($existing) {
            $this->db->table('overtime_logs')->where('id', $existing->id)->update([
                'jam_lembur' => $jamLembur,
                'is_holiday' => $isHoliday,
                'keterangan' => $data['keterangan'] ?? null,
                'status' => 'Pending',
                'approved_by' => null,
                'approved_at' => null
            ]);
            return $this->respond(['message' => 'Overtime log berhasil diupdate']);
        }

        $insertData = [
            'employee_id' => intval($data['employee_id']),
            'tanggal' => $data['tanggal'],
            'jam_lembur' => $jamLembur,
            'is_holiday' => $isHoliday,
            'keterangan' => $data['keterangan'] ?? null,
            'status' => 'Pending',
            'approved_by' => null,
            'approved_at' => null
        ];

        $this->db->table('overtime_logs')->insert($insertData);
        return $this->respondCreated(['message' => 'Overtime log berhasil ditambahkan']);
    }

    public function importOvertimeLogs()
    {
        $db = \Config\Database::connect();
        $json = $this->request->getJSON(true);
        $logs = $json['logs'] ?? [];
        $payoutPeriodStr = $json['payout_period'] ?? null;
        
        if (empty($logs)) {
            return $this->failValidationErrors('Tidak ada data lembur yang diunggah.');
        }

        $successCount = 0;
        $errorLogs = [];

        foreach ($logs as $index => $row) {
            $nik = trim($row['nik'] ?? '');
            $nama = trim($row['nama'] ?? '');
            $tanggal = trim($row['tanggal'] ?? '');
            $nominal = floatval($row['nominal'] ?? 0);

            if (empty($tanggal) || $nominal <= 0 || (empty($nik) && empty($nama))) {
                $errorLogs[] = "Baris " . ($index + 1) . ": Data tidak lengkap atau nominal <= 0.";
                continue;
            }

            // 1. Lookup Employee
            $employee = null;
            if (!empty($nik)) {
                $employee = $db->table('employees')->where('nik', $nik)->get()->getRowArray();
            }
            if (!$employee && !empty($nama)) {
                $employee = $db->table('employees')->where('LOWER(nama)', strtolower($nama))->get()->getRowArray();
            }
            if (!$employee && !empty($nama)) {
                $employee = $db->table('employees')->like('nama', $nama)->get()->getRowArray();
            }

            if (!$employee) {
                $errorLogs[] = "Baris " . ($index + 1) . ": Karyawan '" . ($nik ?: $nama) . "' tidak ditemukan.";
                continue;
            }

            $empId = $employee['id'];
            $clientId = $employee['client_id'];

            // 2. Resolve Base Salary
            $baseSalary = floatval($employee['gaji_pokok'] ?? 0);
            $activeContract = $db->table('contracts')
                ->where('employee_id', $empId)
                ->where('status_pkwt', 'Aktif')
                ->orderBy('tgl_mulai', 'DESC')
                ->get()->getRow();
            if ($activeContract && floatval($activeContract->gaji_pokok) > 0) {
                $baseSalary = floatval($activeContract->gaji_pokok);
            } else {
                $payrollConfig = $db->table('client_payroll_configs')->where('client_id', $clientId)->get()->getRow();
                if ($payrollConfig) {
                    if ($payrollConfig->payroll_type === 'UMP' || $payrollConfig->payroll_type === 'UMK') {
                        if ($payrollConfig->minimum_wage_nominal > 0) {
                            $baseSalary = floatval($payrollConfig->minimum_wage_nominal);
                        }
                    } elseif ($payrollConfig->payroll_type === 'Nominal') {
                        if ($payrollConfig->custom_nominal > 0) {
                            $baseSalary = floatval($payrollConfig->custom_nominal);
                        }
                    }
                }
            }

            // 3. Resolve PKWT components
            $pkwt = $db->table('pkwt')
                ->where('client_id', $clientId)
                ->where('employee_name', $employee['nama'])
                ->where('status', 'Active')
                ->get()->getRow();

            $empComponents = [];
            if ($pkwt) {
                $dbComponents = $db->table('pkwt_components')
                    ->where('pkwt_id', $pkwt->id)
                    ->get()->getResultArray();
                foreach ($dbComponents as $comp) {
                    $isBasic = (isset($comp['jenis_komponen']) && $comp['jenis_komponen'] === 'basic_salary') || (stripos($comp['nama'], 'Gaji Pokok') !== false);
                    if (!$isBasic) {
                        $empComponents[] = [
                            'nama_komponen' => $comp['nama'],
                            'nilai' => floatval($comp['nilai']),
                            'sumber_nilai' => $comp['sumber_nilai'] ?? '',
                        ];
                    }
                }
            }

            // Resolve UMP/UMK values for UMP/UMK fallback in components
            $empMinimumWage = 0.0;
            if ($employee['minimum_wage_id']) {
                $mw = $db->table('minimum_wages')->where('id', $employee['minimum_wage_id'])->get()->getRow();
                if ($mw) {
                    $empMinimumWage = floatval($mw->nominal);
                }
            }
            $umpWageValue = $empMinimumWage;
            $umkWageValue = $empMinimumWage;

            // 4. Resolve Nominal Lembur Bulanan
            $nominalLemburBulanan = 0.0;
            foreach ($empComponents as $comp) {
                $compName = $comp['nama_komponen'] ?? '';
                if (stripos($compName, 'Lembur') !== false || stripos($compName, 'Overtime') !== false) {
                    $baseVal = floatval($comp['nilai']);
                    $sumberVal = $comp['sumber_nilai'] ?? 'nominal';
                    if ($sumberVal === 'ump') {
                        $baseVal = $umpWageValue * ($baseVal / 100);
                    } else if ($sumberVal === 'umk') {
                        $baseVal = $umkWageValue * ($baseVal / 100);
                    } else if ($sumberVal === 'ump_umk') {
                        $baseVal = $empMinimumWage * ($baseVal / 100);
                    }
                    $nominalLemburBulanan = $baseVal;
                    break;
                }
            }
            if ($nominalLemburBulanan <= 0.0) {
                $nominalLemburBulanan = $baseSalary;
            }

            // 5. Resolve hourly rate
            $workDaysConfig = isset($employee['hari_kerja']) ? intval($employee['hari_kerja']) : 5;
            $standardHours = ($workDaysConfig === 6) ? 48.0 : 40.0;
            $upahPerJam = $nominalLemburBulanan / $standardHours;

            if ($upahPerJam <= 0) {
                $errorLogs[] = "Baris " . ($index + 1) . ": Tarif lembur per jam untuk '" . $employee['nama'] . "' bernilai 0.";
                continue;
            }

            $jamLembur = round($nominal / $upahPerJam, 1);

            // 6. Detect holiday
            $isHoliday = 0;
            $holiday = $db->table('holiday_calendar')->where('tanggal', $tanggal)->get()->getRow();
            if ($holiday) {
                $isHoliday = 1;
            } else {
                $dayOfWeek = date('w', strtotime($tanggal));
                if ($dayOfWeek == 0 || $dayOfWeek == 6) {
                    $isHoliday = 1;
                }
            }

            // 7. Detect rapel status based on client's cut-off
            $cutoffStart = 21;
            $cutoffEnd = 20;
            $clientConfig = $db->table('client_payroll_configs')->where('client_id', $clientId)->get()->getRow();
            if ($clientConfig && !empty($clientConfig->scheme_template_id)) {
                $scheme = $db->table('payroll_scheme_templates')->where('id', $clientConfig->scheme_template_id)->get()->getRow();
                if ($scheme && !empty($scheme->schedule_template_id)) {
                    $sched = $db->table('payroll_schedules')->where('id', $scheme->schedule_template_id)->get()->getRow();
                    if ($sched) {
                        $cutoffStart = intval($sched->cutoff_start);
                        $cutoffEnd = intval($sched->cutoff_end);
                    }
                }
            }

            $ts = strtotime($tanggal);
            $tYear = intval(date('Y', $ts));
            $tMonth = intval(date('n', $ts));
            $tDay = intval(date('j', $ts));

            if ($tDay > $cutoffEnd) {
                $natMonth = $tMonth + 1;
                $natYear = $tYear;
                if ($natMonth > 12) {
                    $natMonth = 1;
                    $natYear++;
                }
            } else {
                $natMonth = $tMonth;
                $natYear = $tYear;
            }
            $naturalPeriod = $natMonth . '-' . $natYear;

            $isRapel = 0;
            if (!empty($payoutPeriodStr) && $payoutPeriodStr !== $naturalPeriod) {
                $isRapel = 1;
            }

            // 8. Insert or Update Log
            $existing = $db->table('overtime_logs')
                ->where('employee_id', $empId)
                ->where('tanggal', $tanggal)
                ->get()->getRow();

            $approvedBy = session()->get('username') ?: 'Admin';

            $logData = [
                'employee_id'   => $empId,
                'tanggal'       => $tanggal,
                'jam_lembur'    => $jamLembur,
                'is_holiday'    => $isHoliday,
                'keterangan'    => $row['keterangan'] ?? 'Imported from Excel',
                'status'        => 'Approved',
                'approved_by'   => $approvedBy,
                'approved_at'   => date('Y-m-d H:i:s'),
                'is_rapel'      => $isRapel,
                'payout_period' => $payoutPeriodStr
            ];

            if ($existing) {
                $db->table('overtime_logs')->where('id', $existing->id)->update($logData);
            } else {
                $db->table('overtime_logs')->insert($logData);
            }

            $successCount++;
        }

        return $this->respond([
            'success' => true,
            'imported_count' => $successCount,
            'errors' => $errorLogs
        ]);
    }

    public function updateOvertimeLog($id)
    {
        $data = $this->request->getJSON(true);
        $jamLembur = floatval($data['jam_lembur'] ?? 0);
        $isHoliday = intval($data['is_holiday'] ?? 0);

        if (!$isHoliday && $jamLembur > 3) {
            return $this->failValidationErrors('Lembur hari kerja maksimal 3 jam per hari!');
        }

        $updateData = [
            'jam_lembur' => $jamLembur,
            'is_holiday' => $isHoliday,
            'keterangan' => $data['keterangan'] ?? null,
            'status' => 'Pending',
            'approved_by' => null,
            'approved_at' => null
        ];

        $this->db->table('overtime_logs')->where('id', $id)->update($updateData);
        return $this->respond(['message' => 'Overtime log berhasil diupdate']);
    }

    public function deleteOvertimeLog($id)
    {
        $this->db->table('overtime_logs')->where('id', $id)->delete();
        return $this->respondDeleted(['message' => 'Overtime log berhasil dihapus']);
    }

    public function approveOvertimeLog($id)
    {
        $approvedBy = session()->get('username') ?: 'Admin';
        $this->db->table('overtime_logs')->where('id', $id)->update([
            'status' => 'Approved',
            'approved_by' => $approvedBy,
            'approved_at' => date('Y-m-d H:i:s')
        ]);
        return $this->respond(['message' => 'Overtime log berhasil disetujui.']);
    }

    public function rejectOvertimeLog($id)
    {
        $approvedBy = session()->get('username') ?: 'Admin';
        $this->db->table('overtime_logs')->where('id', $id)->update([
            'status' => 'Rejected',
            'approved_by' => $approvedBy,
            'approved_at' => date('Y-m-d H:i:s')
        ]);
        return $this->respond(['message' => 'Overtime log berhasil ditolak.']);
    }

    public function bulkApproveOvertimeLogs()
    {
        $json = $this->request->getJSON(true);
        $ids = $json['ids'] ?? [];
        if (empty($ids)) {
            return $this->failValidationErrors('Tidak ada ID lembur yang dipilih.');
        }
        $approvedBy = session()->get('username') ?: 'Admin';
        $this->db->table('overtime_logs')->whereIn('id', $ids)->update([
            'status' => 'Approved',
            'approved_by' => $approvedBy,
            'approved_at' => date('Y-m-d H:i:s')
        ]);
        return $this->respond(['message' => count($ids) . ' data lembur berhasil disetujui.']);
    }

    public function bulkRejectOvertimeLogs()
    {
        $json = $this->request->getJSON(true);
        $ids = $json['ids'] ?? [];
        if (empty($ids)) {
            return $this->failValidationErrors('Tidak ada ID lembur yang dipilih.');
        }
        $approvedBy = session()->get('username') ?: 'Admin';
        $this->db->table('overtime_logs')->whereIn('id', $ids)->update([
            'status' => 'Rejected',
            'approved_by' => $approvedBy,
            'approved_at' => date('Y-m-d H:i:s')
        ]);
        return $this->respond(['message' => count($ids) . ' data lembur berhasil ditolak.']);
    }

    // --- HOLIDAY CALENDAR ---
    public function getHolidays()
    {
        $tahun = $this->request->getGet('tahun');
        $builder = $this->db->table('holiday_calendar');
        if ($tahun) {
            $builder->where('tahun', intval($tahun));
        }
        $builder->orderBy('tanggal', 'ASC');
        $holidays = $builder->get()->getResultArray();
        return $this->respond($holidays);
    }

    public function createHoliday()
    {
        $data = $this->request->getJSON(true);

        if (empty($data['tanggal']) || empty($data['deskripsi'])) {
            return $this->failValidationErrors('tanggal dan deskripsi wajib diisi');
        }

        // Check duplicate
        $existing = $this->db->table('holiday_calendar')
            ->where('tanggal', $data['tanggal'])
            ->get()->getRow();
        if ($existing) {
            return $this->failValidationErrors('Tanggal libur sudah terdaftar!');
        }

        $tahun = intval(date('Y', strtotime($data['tanggal'])));
        $insertData = [
            'tanggal' => $data['tanggal'],
            'deskripsi' => $data['deskripsi'],
            'tahun' => $tahun,
        ];

        $this->db->table('holiday_calendar')->insert($insertData);
        $this->logActivity("Menambahkan hari libur: " . $data['deskripsi'] . " (" . $data['tanggal'] . ")");
        return $this->respondCreated(['message' => 'Hari libur berhasil ditambahkan']);
    }

    public function updateHoliday($id)
    {
        $data = $this->request->getJSON(true);
        $updateData = [
            'tanggal' => $data['tanggal'] ?? null,
            'deskripsi' => $data['deskripsi'] ?? null,
        ];
        if (!empty($updateData['tanggal'])) {
            $updateData['tahun'] = intval(date('Y', strtotime($updateData['tanggal'])));
        }

        $this->db->table('holiday_calendar')->where('id', $id)->update($updateData);
        return $this->respond(['message' => 'Hari libur berhasil diupdate']);
    }

    public function deleteHoliday($id)
    {
        $holiday = $this->db->table('holiday_calendar')->where('id', $id)->get()->getRow();
        $desc = $holiday ? $holiday->deskripsi : 'Unknown';
        $this->db->table('holiday_calendar')->where('id', $id)->delete();
        $this->logActivity("Menghapus hari libur: " . $desc);
        return $this->respondDeleted(['message' => 'Hari libur berhasil dihapus']);
    }

    public function createCompensationComponent()
    {
        $data = $this->request->getJSON(true);
        $this->db->table('compensation_components')->insert($data);
        return $this->respondCreated(['message' => 'Komponen kompensasi berhasil ditambahkan']);
    }

    public function updateCompensationComponent($id)
    {
        $data = $this->request->getJSON(true);
        $this->db->table('compensation_components')->where('id', $id)->update($data);
        return $this->respond(['message' => 'Komponen kompensasi berhasil diupdate']);
    }

    public function deleteCompensationComponent($id)
    {
        $this->db->table('compensation_components')->where('id', $id)->delete();
        return $this->respondDeleted(['message' => 'Komponen kompensasi berhasil dihapus']);
    }

    // --- CLIENT PAYROLL CONFIGS ---
    public function getClientConfigs()
    {
        $configs = $this->db->table('clients')
                            ->select('clients.id as client_id, clients.nama as client_name, client_payroll_configs.id as setup_id, payroll_schemes.nama as payroll_scheme_name, tax_schemes.nama as tax_scheme_name, bpjs_schemes.nama as bpjs_scheme_name, compensation_schemes.nama as compensation_scheme_name, client_payroll_configs.pay_date, client_payroll_configs.cutoff_start, client_payroll_configs.cutoff_end, client_payroll_configs.payroll_scheme_id, client_payroll_configs.tax_scheme_id, client_payroll_configs.bpjs_scheme_id, client_payroll_configs.compensation_scheme_id, client_payroll_configs.payroll_type, client_payroll_configs.minimum_wage_id, client_payroll_configs.custom_nominal, minimum_wages.nama_daerah as minimum_wage_region, minimum_wages.nominal as minimum_wage_nominal, client_payroll_configs.division_id, client_payroll_configs.department_id, client_payroll_configs.position_id, divisions.nama as division_name, departments.nama as department_name, positions.nama as position_name')
                            ->join('client_payroll_configs', 'client_payroll_configs.client_id = clients.id', 'left')
                            ->join('payroll_schemes', 'payroll_schemes.id = client_payroll_configs.payroll_scheme_id', 'left')
                            ->join('tax_schemes', 'tax_schemes.id = client_payroll_configs.tax_scheme_id', 'left')
                            ->join('tax_schemes as bpjs_schemes', 'bpjs_schemes.id = client_payroll_configs.bpjs_scheme_id', 'left')
                            ->join('compensation_schemes', 'compensation_schemes.id = client_payroll_configs.compensation_scheme_id', 'left')
                            ->join('minimum_wages', 'minimum_wages.id = client_payroll_configs.minimum_wage_id', 'left')
                            ->join('divisions', 'divisions.id = client_payroll_configs.division_id', 'left')
                            ->join('departments', 'departments.id = client_payroll_configs.department_id', 'left')
                            ->join('positions', 'positions.id = client_payroll_configs.position_id', 'left')
                            ->get()
                            ->getResult();
        return $this->respond($configs);
    }

    public function saveClientConfig()
    {
        $data = $this->request->getJSON(true);
        $clientId = $data['client_id'];
        
        $divId = $data['division_id'] ?? null;
        $deptId = $data['department_id'] ?? null;
        $posId = $data['position_id'] ?? null;
        
        $divisionId = !empty($data['division_id']) ? intval($data['division_id']) : null;
        $departmentId = !empty($data['department_id']) ? intval($data['department_id']) : null;
        $positionId = !empty($data['position_id']) ? intval($data['position_id']) : null;

        $data['division_id'] = $divisionId;
        $data['department_id'] = $departmentId;
        $data['position_id'] = $positionId;
        
        // Handle hari_kerja for positions
        if ($positionId && isset($data['hari_kerja']) && $data['hari_kerja'] !== '') {
            $this->db->table('positions')->where('id', $positionId)->update(['hari_kerja' => intval($data['hari_kerja'])]);
        }
        unset($data['hari_kerja']);
        
        // Cek jika sudah ada skema untuk level organisasi ini yang SPESIFIK
        $query = $this->db->table('client_payroll_configs')->where('client_id', $clientId);
        if ($divisionId) $query->where('division_id', $divisionId); else $query->where('division_id IS NULL');
        if ($departmentId) $query->where('department_id', $departmentId); else $query->where('department_id IS NULL');
        if ($positionId) $query->where('position_id', $positionId); else $query->where('position_id IS NULL');
        
        $existing = $query->get()->getRow();
        
        // JIKA SUDAH ADA, LANGSUNG UPDATE (UPSERT)
        $isUpdateAction = isset($data['id']) && $data['id'] > 0;
        
        if ($existing && !$isUpdateAction) {
            $data['id'] = $existing->id;
        }
        
        if (isset($data['id'])) {
            $idToUpdate = $data['id'];
            unset($data['id']); // Mencegah update kolom identity
            $this->db->table('client_payroll_configs')->where('id', $idToUpdate)->update($data);
        } else {
            $this->db->table('client_payroll_configs')->insert($data);
        }

        return $this->respond(['status' => 'success', 'message' => 'Pilihan skema berhasil disimpan']);
    }

    public function getClientConfigMappings($clientId)
    {
        $configs = $this->db->table('client_payroll_configs')
            ->select('
                client_payroll_configs.*,
                divisions.nama as division_name,
                departments.nama as department_name,
                positions.nama as position_name,
                payroll_schemes.nama as payroll_scheme_name,
                tax_schemes.nama as tax_scheme_name,
                bpjs_schemes.nama as bpjs_scheme_name,
                compensation_schemes.nama as compensation_scheme_name,
                minimum_wages.nama_daerah as minimum_wage_region,
                minimum_wages.nominal as minimum_wage_nominal
            ')
            ->join('divisions', 'divisions.id = client_payroll_configs.division_id', 'left')
            ->join('departments', 'departments.id = client_payroll_configs.department_id', 'left')
            ->join('positions', 'positions.id = client_payroll_configs.position_id', 'left')
            ->join('payroll_schemes', 'payroll_schemes.id = client_payroll_configs.payroll_scheme_id', 'left')
            ->join('tax_schemes', 'tax_schemes.id = client_payroll_configs.tax_scheme_id', 'left')
            ->join('tax_schemes as bpjs_schemes', 'bpjs_schemes.id = client_payroll_configs.bpjs_scheme_id', 'left')
            ->join('compensation_schemes', 'compensation_schemes.id = client_payroll_configs.compensation_scheme_id', 'left')
            ->join('minimum_wages', 'minimum_wages.id = client_payroll_configs.minimum_wage_id', 'left')
            ->where('client_payroll_configs.client_id', $clientId)
            ->get()->getResultArray();
            
        return $this->respond($configs);
    }
    
    public function deleteClientConfig($id)
    {
        $this->db->table('client_payroll_configs')->where('id', $id)->delete();
        return $this->respondDeleted(['id' => $id, 'message' => 'Mapping skema berhasil dihapus']);
    }

    // --- PKWT ---
    public function getPKWT()
    {
        $clientId = $this->request->getGet('client_id');
        $query = $this->db->table('pkwt')
                         ->select('pkwt.*, clients.nama as client_name')
                         ->join('clients', 'clients.id = pkwt.client_id');
        if ($clientId) {
            $query->where('pkwt.client_id', $clientId);
        }
        $data = $query->get()->getResultArray();
        
        foreach ($data as &$row) {
            $row['components'] = $this->db->table('pkwt_components')
                                          ->where('pkwt_id', $row['id'])
                                          ->get()
                                          ->getResultArray();

            // Try to find the employee to resolve UMP/UMK nominal
            $emp = $this->db->table('employees')
                            ->select('employees.*, minimum_wages.nominal as wage_nominal, minimum_wages.tipe as wage_tipe')
                            ->join('minimum_wages', 'minimum_wages.id = employees.minimum_wage_id', 'left')
                            ->where('employees.client_id', $row['client_id'])
                            ->where('employees.nama', $row['employee_name'])
                            ->get()
                            ->getRow();

            foreach ($row['components'] as &$comp) {
                $comp['nilai_nominal'] = floatval($comp['nilai']);
                $isBasic = (stripos($comp['nama'], 'Gaji Pokok') !== false || ($comp['jenis_komponen'] ?? '') === 'basic_salary');
                if ($isBasic) {
                    if (isset($comp['sumber_nilai']) && ($comp['sumber_nilai'] === 'ump' || $comp['sumber_nilai'] === 'umk')) {
                        // Resolve proper UMP/UMK values using the enhanced resolver
                        $mwId = ($emp && isset($emp->minimum_wage_id)) ? $emp->minimum_wage_id : null;
                        $empProvince = null;
                        if ($emp && !empty($emp->work_location_id)) {
                            $wl = $this->db->table('work_locations')->where('id', $emp->work_location_id)->get()->getRow();
                            if ($wl && !empty($wl->provinsi)) {
                                $empProvince = $wl->provinsi;
                            }
                        }
                        $resolved = $this->resolveUmpUmk($mwId, null, $empProvince);
                        $wageValue = ($comp['sumber_nilai'] === 'ump') ? $resolved['ump'] : $resolved['umk'];
                        if ($wageValue > 0) {
                            $comp['nilai_nominal'] = $wageValue * (floatval($comp['nilai']) / 100);
                        }
                    }
                }
            }
        }
        
        return $this->respond($data);
    }

    public function createPKWT()
    {
        $data = $this->request->getJSON(true);
        $basicSalary = $data['basic_salary'] ?? 0;
        unset($data['basic_salary']);

        // 1. Insert PKWT
        $this->db->table('pkwt')->insert($data);
        $pkwtId = $this->db->insertID();

        $this->syncPKWTComponents($pkwtId, $basicSalary);

        $this->logActivity("Membuat PKWT baru untuk karyawan: " . ($data['employee_name'] ?? ''));
        return $this->respondCreated(['message' => 'PKWT berhasil dibuat dan gaji telah tergenerate']);
    }

    public function deletePKWT($id)
    {
        $pkwt = $this->db->table('pkwt')->where('id', $id)->get()->getRow();
        $employeeName = $pkwt ? $pkwt->employee_name : 'Unknown';
        $this->db->table('pkwt')->where('id', $id)->delete();
        $this->logActivity("Menghapus PKWT karyawan: " . $employeeName . " (ID: " . $id . ")");
        return $this->respondDeleted(['message' => 'PKWT berhasil dihapus']);
    }

    public function syncEmployeesPKWTEndpoint()
    {
        $clientId = $this->request->getGet('client_id');
        if (empty($clientId)) {
            return $this->fail('Client ID required');
        }
        
        $this->syncEmployeesToPKWT($clientId);
        return $this->respond(['message' => 'Employees synchronized to PKWT successfully']);
    }

    // --- PERIODS ---
    public function getPeriods()
    {
        $clientId = $this->request->getGet('client_id');
        $query = $this->db->table('payroll_periods')->orderBy('tahun', 'DESC')->orderBy('bulan', 'DESC');
        if ($clientId) {
            $query->where('client_id', $clientId);
        }
        $data = $query->get()->getResult();
        foreach ($data as &$row) {
            $row = $this->formatPeriodRow($row);
        }
        return $this->respond($data);
    }

    public function createPeriod()
    {
        $data = $this->request->getJSON(true);
        
        $insertData = [
            'client_id' => $data['client_id'] ?? null,
            'bulan' => intval($data['bulan']),
            'tahun' => intval($data['tahun']),
            'status_cutoff' => 'Open',
            'pay_date' => null
        ];
        
        $this->db->table('payroll_periods')->insert($insertData);
        return $this->respondCreated(['message' => 'Periode baru berhasil dibuka']);
    }

    // --- ATTENDANCE / CUT-OFF ---
    public function getAttendance($periodId)
    {
        $clientId = $this->request->getGet('client_id');
        $this->syncEmployeesToPKWT($clientId);
        // Get all PKWT and their attendance for this period
        $query = $this->db->table('pkwt')
                          ->select('pkwt.id as pkwt_id, pkwt.employee_name, pkwt.tipe_perjanjian, 
                                    payroll_attendance.hari_kerja, payroll_attendance.jam_lembur, 
                                    payroll_attendance.potongan_absensi, payroll_attendance.bonus_tambahan,
                                    employees.id as employee_id, employees.employ_id, employees.nik, employees.gaji_pokok,
                                    employees.hari_kerja as employee_hari_kerja, positions.hari_kerja as position_hari_kerja')
                          ->join('payroll_attendance', "payroll_attendance.pkwt_id = pkwt.id AND payroll_attendance.period_id = $periodId", 'left')
                          ->join('employees', "employees.client_id = pkwt.client_id AND employees.nama = pkwt.employee_name", 'left')
                          ->join('positions', 'positions.id = employees.position_id', 'left');
        if ($clientId) {
            $query->where('pkwt.client_id', $clientId);
        }
        $pkwts = $query->get()->getResult();
        return $this->respond($pkwts);
    }

    public function saveAttendance()
    {
        $data = $this->request->getJSON(true);
        $periodId = $data['period_id'];
        $pkwtId = $data['pkwt_id'];

        $existing = $this->db->table('payroll_attendance')
                             ->where('period_id', $periodId)
                             ->where('pkwt_id', $pkwtId)
                             ->get()->getRow();
        
        if ($existing) {
            $this->db->table('payroll_attendance')->where('id', $existing->id)->update($data);
        } else {
            $this->db->table('payroll_attendance')->insert($data);
        }
        return $this->respond(['message' => 'Data cut-off berhasil disimpan']);
    }

    public function saveAttendanceBulk()
    {
        $records = $this->request->getJSON(true);
        if (empty($records) || !is_array($records)) {
            return $this->failValidationErrors('Data absensi kosong.');
        }

        $count = 0;
        foreach ($records as $record) {
            $periodId = $record['period_id'] ?? null;
            $pkwtId = $record['pkwt_id'] ?? null;
            if (!$periodId || !$pkwtId) {
                continue;
            }

            $existing = $this->db->table('payroll_attendance')
                                 ->where('period_id', $periodId)
                                 ->where('pkwt_id', $pkwtId)
                                 ->get()->getRow();

            $saveData = [
                'period_id' => $periodId,
                'pkwt_id' => $pkwtId,
                'hari_kerja' => $record['hari_kerja'] ?? 0,
                'jam_lembur' => $record['jam_lembur'] ?? 0,
                'potongan_absensi' => $record['potongan_absensi'] ?? 0,
                'bonus_tambahan' => $record['bonus_tambahan'] ?? 0,
            ];

            if ($existing) {
                $this->db->table('payroll_attendance')->where('id', $existing->id)->update($saveData);
            } else {
                $this->db->table('payroll_attendance')->insert($saveData);
            }
            $count++;
        }

        return $this->respond(['message' => "Berhasil menyimpan {$count} data cut-off"]);
    }

    // --- GENERATE PAYROLL ---
    public function generatePayroll($periodId)
    {
        $clientId = $this->request->getGet('client_id');
        $this->syncEmployeesToPKWT($clientId);
        $period = $this->db->table('payroll_periods')->where('id', $periodId)->get()->getRow();
        if (!$period) {
            return $this->failNotFound('Periode tidak ditemukan');
        }
        $daysInMonth = date('t', mktime(0, 0, 0, intval($period->bulan), 1, intval($period->tahun)));

        // 1. Get all PKWTs
        $query = $this->db->table('pkwt');
        if ($clientId) {
            $query->where('client_id', $clientId);
        }
        $pkwts = $query->get()->getResult();

        foreach ($pkwts as $pkwt) {
            // Get client config to find payroll scheme ID
            $clientConfig = $this->resolveClientConfig($pkwt->client_id, $pkwt->position_name);
            
            $isProrate = false;
            $isAbsenTidakPotong = false;
            $nominalPotonganAbsen = 0;
            
            // Try resolving from payroll scheme first
            if ($clientConfig && $clientConfig->payroll_scheme_id) {
                $payrollScheme = $this->db->table('payroll_schemes')->where('id', $clientConfig->payroll_scheme_id)->get()->getRow();
                if ($payrollScheme) {
                    $isProrate = ($payrollScheme->prorate == 1);
                    $isAbsenTidakPotong = ($payrollScheme->absen_tidak_potong == 1);
                    $nominalPotonganAbsen = floatval($payrollScheme->nominal_potongan);
                }
            }
            
            // Fallback to client_absence_configs
            $absenceConfig = $this->db->table('client_absence_configs')->where('client_id', $pkwt->client_id)->get()->getRow();
            if ($clientConfig && !$isProrate && !$isAbsenTidakPotong && $nominalPotonganAbsen == 0) {
                if ($absenceConfig) {
                    $isProrate = ($absenceConfig->prorate == 1);
                    $isAbsenTidakPotong = ($absenceConfig->absen_tidak_potong == 1);
                    $nominalPotonganAbsen = floatval($absenceConfig->nominal_potongan);
                }
            }

            // Get Client's overtime_rate_per_hour
            $client = $this->db->table('clients')->where('id', $pkwt->client_id)->get()->getRow();
            $otRate = ($client && isset($client->overtime_rate_per_hour) && floatval($client->overtime_rate_per_hour) > 0)
                      ? floatval($client->overtime_rate_per_hour)
                      : 20000;

            // 2. Get Fixed Components from PKWT
            $components = $this->db->table('pkwt_components')->where('pkwt_id', $pkwt->id)->get()->getResult();
            
            // 3. Get Attendance Data
            $att = $this->db->table('payroll_attendance')
                            ->where('period_id', $periodId)
                            ->where('pkwt_id', $pkwt->id)
                            ->get()->getRow();
            
            // 4. Resolve Employee / Client UMR (Minimum Wage)
            $emp = $this->db->table('employees')
                            ->select('employees.*, minimum_wages.nominal as umr_nominal, minimum_wages.id as mw_id, positions.hari_kerja as position_hari_kerja')
                            ->join('minimum_wages', 'minimum_wages.id = employees.minimum_wage_id', 'left')
                            ->join('positions', 'positions.id = employees.position_id', 'left')
                            ->where('employees.nama', $pkwt->employee_name)
                            ->get()
                            ->getRow();

            $stdWorkingDays = 21;
            if ($emp && isset($emp->position_hari_kerja)) {
                $posHk = intval($emp->position_hari_kerja);
                if ($posHk === 6) {
                    $stdWorkingDays = 25;
                } elseif ($posHk === 7) {
                    $stdWorkingDays = 30;
                }
            }
            
            $minimumWage = 0;
            $mwId = null;
            if ($emp && isset($emp->umr_nominal) && $emp->umr_nominal > 0) {
                $minimumWage = floatval($emp->umr_nominal);
                $mwId = $emp->mw_id;
            } else {
                // Fallback to client config's minimum wage
                if ($clientConfig && isset($clientConfig->minimum_wage_id)) {
                    $mw = $this->db->table('minimum_wages')->where('id', $clientConfig->minimum_wage_id)->get()->getRow();
                    if ($mw) {
                        $minimumWage = floatval($mw->nominal);
                        $mwId = $mw->id;
                    }
                }
            }

            // Resolve UMP and UMK values - pass employee's work location province for UMP lookup
            $empProvince = null;
            if ($emp && !empty($emp->work_location_id)) {
                $wl = $this->db->table('work_locations')->where('id', $emp->work_location_id)->get()->getRow();
                if ($wl && !empty($wl->provinsi)) {
                    $empProvince = $wl->provinsi;
                }
            }
            $resolvedWages = $this->resolveUmpUmk($mwId, null, $empProvince);
            $umpWageValue = $resolvedWages['ump'];
            $umkWageValue = $resolvedWages['umk'];

            // Check if we have a basic salary component with source = kompensasi
            $hasKompensasiSource = false;
            foreach ($components as $comp) {
                $isBasicSalary = (isset($comp->jenis_komponen) && $comp->jenis_komponen === 'basic_salary') || (stripos($comp->nama, 'Gaji Pokok') !== false);
                if ($isBasicSalary && isset($comp->sumber_nilai) && $comp->sumber_nilai === 'kompensasi') {
                    $hasKompensasiSource = true;
                    break;
                }
            }

            // 5. First pass: Find and calculate Gaji Pokok (Basic Salary)
            $gajiPokok = 0;
            $unproratedGajiPokok = 0;
            foreach ($components as $comp) {
                $isBasicSalary = false;
                if (isset($comp->jenis_komponen) && $comp->jenis_komponen === 'basic_salary') {
                    $isBasicSalary = true;
                } elseif (stripos($comp->nama, 'Gaji Pokok') !== false) {
                    $isBasicSalary = true;
                }

                if ($isBasicSalary) {
                    $base_nilai = floatval($comp->nilai);
                    $sumber_nilai = $comp->sumber_nilai ?? 'nominal';
                    
                    if ($sumber_nilai === 'ump') {
                        $base_nilai = $umpWageValue * ($base_nilai / 100);
                    } else if ($sumber_nilai === 'umk') {
                        $base_nilai = $umkWageValue * ($base_nilai / 100);
                    } else if ($sumber_nilai === 'ump_umk') {
                        $base_nilai = $minimumWage * ($base_nilai / 100);
                    } else if ($sumber_nilai === 'kompensasi') {
                        $kompTetapValue = 0;
                        foreach ($components as $c) {
                            if (($c->jenis_komponen ?? '') === 'kompensasi' && ($c->sifat_kompensasi ?? '') === 'tetap') {
                                $kompTetapValue += floatval($c->nilai);
                            }
                        }
                        $base_nilai = $kompTetapValue * ($base_nilai / 100);
                    } else {
                        // Force base_nilai to use Employee's setup if available!
                        if ($emp && isset($emp->gaji_pokok) && floatval($emp->gaji_pokok) > 0) {
                            $base_nilai = floatval($emp->gaji_pokok);
                        } else if ($minimumWage > 0) {
                            $base_nilai = $minimumWage;
                        }
                    }
                    
                    $unproratedGajiPokok = $base_nilai;
                    
                    // Scale by period
                    if (isset($comp->periode)) {
                        if ($comp->periode === 'hari') {
                            $days = ($att && isset($att->hari_kerja) && $att->hari_kerja !== null) ? intval($att->hari_kerja) : $stdWorkingDays;
                            $base_nilai = $base_nilai * $days;
                        } elseif ($comp->periode === 'minggu') {
                            $base_nilai = $base_nilai * 4;
                        } elseif ($comp->periode === 'tahun') {
                            $base_nilai = $base_nilai / 12;
                        } else {
                            // bulanan
                            if ($isProrate) {
                                $days = ($att && isset($att->hari_kerja) && $att->hari_kerja !== null) ? intval($att->hari_kerja) : $stdWorkingDays;
                                $base_nilai = $base_nilai * ($days / $daysInMonth);
                            }
                        }
                    }
                    
                    $gajiPokok = $base_nilai;
                    break; // Assume only one basic salary component
                }
            }

            if ($gajiPokok <= 0 && $emp && isset($emp->gaji_pokok)) {
                $unproratedGajiPokok = floatval($emp->gaji_pokok);
                $gajiPokok = $unproratedGajiPokok;
                if ($isProrate) {
                    $days = ($att && isset($att->hari_kerja) && $att->hari_kerja !== null) ? intval($att->hari_kerja) : $stdWorkingDays;
                    $gajiPokok = $gajiPokok * ($days / $daysInMonth);
                }
            }
            // 6. Second pass: Calculate all components
            $totalPendapatan = 0;
            $totalPotongan = 0;
            $tunjanganTetap = 0;

            // Match payroll scheme template for organizational matching
            $schemeModel = new \App\Models\PayrollSchemeTemplateModel();
            $schemeTemplateObj = $schemeModel->getSchemeForEmployee(
                $pkwt->client_id,
                $emp->division_id ?? null,
                $emp->department_id ?? null,
                $emp->position_id ?? null
            );
            $schemeTemplate = $schemeTemplateObj ? (array)$schemeTemplateObj : null;

            $bpjsWageBase = 0;
            $pphWageBase = 0;

            // Match payroll scheme template for organizational matching
            $schemeModel = new \App\Models\PayrollSchemeTemplateModel();
            $schemeTemplateObj = $schemeModel->getSchemeForEmployee(
                $pkwt->client_id,
                $emp->division_id ?? null,
                $emp->department_id ?? null,
                $emp->position_id ?? null
            );
            $schemeTemplate = $schemeTemplateObj ? (array)$schemeTemplateObj : null;

            $bpjsWageBase = 0;
            $pphWageBase = 0;

            foreach ($components as $comp) {
                $isBasic = false;
                if (isset($comp->jenis_komponen) && $comp->jenis_komponen === 'basic_salary') {
                    $isBasic = true;
                } elseif (stripos($comp->nama, 'Gaji Pokok') !== false) {
                    $isBasic = true;
                }

                $nilai = 0;

                if ($isBasic) {
                    $nilai = $gajiPokok;
                } else {
                    if ($hasKompensasiSource && ($comp->jenis_komponen ?? '') === 'kompensasi' && ($comp->sifat_kompensasi ?? '') === 'tetap') {
                        $nilai = 0; // Prevent double-counting by setting it to 0
                    } else if (isset($comp->jenis_komponen) && !empty($comp->jenis_komponen)) {
                        // New Master Compensation component logic
                        $base_nilai = floatval($comp->nilai);
                        
                        if (isset($comp->sumber_nilai)) {
                            if ($comp->sumber_nilai === 'ump') {
                                $base_nilai = $umpWageValue * ($base_nilai / 100);
                            } else if ($comp->sumber_nilai === 'umk') {
                                $base_nilai = $umkWageValue * ($base_nilai / 100);
                            } else if ($comp->sumber_nilai === 'ump_umk') {
                                $base_nilai = $minimumWage * ($base_nilai / 100);
                            }
                        }
                        
                        // Scale by period
                        if ($comp->periode === 'hari') {
                            $days = ($att && isset($att->hari_kerja) && $att->hari_kerja !== null) ? intval($att->hari_kerja) : $stdWorkingDays;
                            $nilai = $base_nilai * $days;
                        } elseif ($comp->periode === 'minggu') {
                            $nilai = $base_nilai * 4;
                        } elseif ($comp->periode === 'tahun') {
                            $nilai = $base_nilai / 12;
                        } else {
                            // bulanan
                            // Tunjangan tetap: Nilai tunjangan tetap bersifat konstan setiap periode (TIDAK terprorate)
                            if ($isProrate && isset($comp->sifat_kompensasi) && $comp->sifat_kompensasi === 'tidak_tetap') {
                                $days = ($att && isset($att->hari_kerja) && $att->hari_kerja !== null) ? intval($att->hari_kerja) : $stdWorkingDays;
                                $nilai = $base_nilai * ($days / $stdWorkingDays);
                            } else {
                                $nilai = $base_nilai;
                            }
                        }
                    } else {
                        // Legacy component logic
                        $nilai = floatval($comp->nilai);
                        if (intval($comp->is_persentase) === 1 || $comp->is_persentase === true) {
                            $nilai = $gajiPokok * ($nilai / 100);
                        }
                    }
                }

                if ($comp->tipe === 'pendapatan') {
                    $totalPendapatan += $nilai;

                    // Resolve inclusion flags
                    $nameClean = strtolower(trim($comp->nama));
                    $isBpjsInc = false;
                    $isPphInc = true;

                    if ($isBasic) {
                        $isBpjsInc = true;
                        $isPphInc = true;
                    } else {
                        $isBpjsInc = (isset($comp->is_bpjs) && $comp->is_bpjs == 1);
                        $isPphInc = (!isset($comp->is_pph21) || $comp->is_pph21 == 1);
                    }

                    if ($isBpjsInc) {
                        $bpjsWageBase += $nilai;
                    }
                    if ($isPphInc) {
                        $pphWageBase += $nilai;
                    }
                } else {
                    $totalPotongan += $nilai;
                }
            }

            // 7. Add Variable Data from Attendance
            $potonganAbsenVal = 0;
            if ($att) {
                if (!$isAbsenTidakPotong) {
                    $potongan_absen = floatval($att->potongan_absensi);
                    if ($potongan_absen == 0) {
                        $missingDays = max(0, $daysInMonth - intval($att->hari_kerja));
                        if ($missingDays > 0) {
                            if ($isProrate) {
                                // Pro-rate: potongan = Base Salary * (Hari Tidak Masuk / Hari dalam Bulan)
                                $potongan_absen = $unproratedGajiPokok * ($missingDays / $daysInMonth);
                            } else {
                                // Deduction tetap: potongan = nominal yang ditetapkan per hari absen
                                $nominalPotongan = ($nominalPotonganAbsen > 0) ? $nominalPotonganAbsen : (($absenceConfig && isset($absenceConfig->nominal_potongan)) ? floatval($absenceConfig->nominal_potongan) : 0);
                                $potongan_absen = $missingDays * $nominalPotongan;
                            }
                        }
                    }
                    $potonganAbsenVal = $potongan_absen;
                    $totalPotongan += $potongan_absen;
                }
                
                // Potongan Late dan Early Leave based on attendance_logs
                $cutoffStart = $clientConfig ? intval($clientConfig->cutoff_start) : 1;
                $cutoffEnd = $clientConfig ? intval($clientConfig->cutoff_end) : $daysInMonth;
                if ($cutoffStart === 0) $cutoffStart = 1;
                if ($cutoffEnd === 0) $cutoffEnd = $daysInMonth;

                $bulan_start = intval($period->bulan);
                $tahun_start = intval($period->tahun);
                $bulan_end = $bulan_start;
                $tahun_end = $tahun_start;
                if ($cutoffStart > 1) {
                    $bulan_start -= 1;
                    if ($bulan_start < 1) {
                        $bulan_start = 12;
                        $tahun_start -= 1;
                    }
                }
                $startDateStr = sprintf('%04d-%02d-%02d', $tahun_start, $bulan_start, $cutoffStart);
                $endDateStr = sprintf('%04d-%02d-%02d', $tahun_end, $bulan_end, $cutoffEnd);

                $lateEarlySum = null;
                if ($emp && isset($emp->id)) {
                    $lateEarlySum = $this->db->table('attendance_logs')
                        ->selectSum('late_hours')
                        ->selectSum('early_leave_hours')
                        ->where('employee_id', $emp->id)
                        ->where('log_date >=', $startDateStr)
                        ->where('log_date <=', $endDateStr)
                        ->get()->getRow();
                }
                
                $lateHours = $lateEarlySum ? floatval($lateEarlySum->late_hours) : 0;
                $earlyHours = $lateEarlySum ? floatval($lateEarlySum->early_leave_hours) : 0;
                
                $standardHours = 160.0;
                $upahPerJam = $gajiPokok / $standardHours;
                
                $potonganLate = $lateHours * $upahPerJam;
                $potonganEarly = $earlyHours * $upahPerJam;
                
                $totalPotongan += $potonganLate + $potonganEarly;
                
                $totalPendapatan += $att->bonus_tambahan;
                
                // Overtime Calculation based on Client's overtime_rate_per_hour
                $overtimePay = $att->jam_lembur * $otRate; 
                $totalPendapatan += $overtimePay;
            }

            // Adjust PPh wage base for attendance variations
            $pphWageBaseFinal = $pphWageBase;
            if ($att) {
                $pphWageBaseFinal += ($att->jam_lembur * $otRate) + floatval($att->bonus_tambahan) - $potonganAbsenVal;
            }

            // Fallback for BPJS wage base to minimumWage if lower
            if ($minimumWage > 0 && $bpjsWageBase < $minimumWage) {
                $bpjsWageBase = $minimumWage;
            }

            // Calculate BPJS & Tax
            $taxScheme = null;
            if ($clientConfig && $clientConfig->tax_scheme_id) {
                $taxScheme = $this->db->table('tax_schemes')->where('id', $clientConfig->tax_scheme_id)->get()->getRow();
            }

            $bpjsScheme = null;
            if ($clientConfig && !empty($clientConfig->bpjs_scheme_id)) {
                $bpjsScheme = $this->db->table('tax_schemes')->where('id', $clientConfig->bpjs_scheme_id)->get()->getRow();
            }
            if (!$bpjsScheme) {
                $bpjsScheme = $taxScheme;
            }

            $ptkpStatus = 'TK/0';
            if ($emp && !empty($emp->ptkp)) {
                $ptkpStatus = $emp->ptkp;
            } elseif ($taxScheme && !empty($taxScheme->ptkp_status)) {
                $ptkpStatus = $taxScheme->ptkp_status;
            } elseif ($schemeTemplate && !empty($schemeTemplate['ptkp_status'])) {
                $ptkpStatus = $schemeTemplate['ptkp_status'];
            }

            // Resolve BPJS & Tax calculations
            $calc = $this->calculateBpjsAndTax($gajiPokok, $bpjsWageBase, $pphWageBaseFinal, $schemeTemplate, $taxScheme, $minimumWage, $ptkpStatus, $bpjsScheme);

            // Deductions from Employee: BPJS Kes Karyawan, JHT Karyawan, JP Karyawan
            $employeeBpjsDeductions = $calc['bpjs_kes_karyawan'] + $calc['bpjs_jht_karyawan'] + $calc['bpjs_jp_karyawan'];

            if ($calc['metode_pajak'] === 'Gross Up') {
                $totalPendapatan += $calc['tax_allowance'];
                $totalPotongan += $employeeBpjsDeductions + $calc['pph21'];
            } elseif ($calc['metode_pajak'] === 'Gross') {
                $totalPotongan += $employeeBpjsDeductions + $calc['pph21'];
            } else { // Nett
                $totalPotongan += $employeeBpjsDeductions;
            }

            $thp = $totalPendapatan - $totalPotongan;

            // 8. Save to payroll_final
            $existingFinal = $this->db->table('payroll_final')
                                      ->where('period_id', $periodId)
                                      ->where('pkwt_id', $pkwt->id)
                                      ->get()->getRow();
            
            $finalData = [
                'period_id' => $periodId,
                'pkwt_id' => $pkwt->id,
                'total_pendapatan' => $totalPendapatan,
                'total_potongan' => $totalPotongan,
                'take_home_pay' => $thp,
                'status_approval' => 'Pending',
                'bpjs_kes_karyawan' => $calc['bpjs_kes_karyawan'],
                'bpjs_kes_perusahaan' => $calc['bpjs_kes_perusahaan'],
                'bpjs_jht_karyawan' => $calc['bpjs_jht_karyawan'],
                'bpjs_jht_perusahaan' => $calc['bpjs_jht_perusahaan'],
                'bpjs_jp_karyawan' => $calc['bpjs_jp_karyawan'],
                'bpjs_jp_perusahaan' => $calc['bpjs_jp_perusahaan'],
                'bpjs_jkk_perusahaan' => $calc['bpjs_jkk_perusahaan'],
                'bpjs_jkm_perusahaan' => $calc['bpjs_jkm_perusahaan'],
                'pph21' => $calc['pph21'],
                'tax_allowance' => $calc['tax_allowance'],
                'tax_method' => $calc['metode_pajak'],
                'ptkp_status' => $ptkpStatus,
                'gaji_pokok' => $gajiPokok,
                'potongan_absen' => $potonganAbsenVal,
                'jam_lembur' => ($att) ? floatval($att->jam_lembur) : 0,
                'lembur_pay' => ($att) ? (floatval($att->jam_lembur) * $otRate) : 0,
                'bonus_tambahan' => ($att) ? floatval($att->bonus_tambahan) : 0,
                'raw_components' => json_encode($components)
            ];

            if ($existingFinal) {
                $this->db->table('payroll_final')->where('id', $existingFinal->id)->update($finalData);
            } else {
                $this->db->table('payroll_final')->insert($finalData);
            }

        }

        $period = $this->db->table('payroll_periods')->where('id', $periodId)->get()->getRow();
        $period = $this->formatPeriodRow($period);
        $periodName = $period ? $period->nama : "ID: $periodId";
        $this->logActivity("Men-generate payroll untuk periode: " . $periodName);
        return $this->respond(['message' => 'Gaji bulanan berhasil di-generate untuk periode ini']);
    }

    public function getPayrollResults($periodId)
    {
        $clientId = $this->request->getGet('client_id');
        $query = $this->db->table('payroll_final')
                         ->select('
                             payroll_final.*, 
                             pkwt.employee_name, 
                             pkwt.tipe_perjanjian, 
                             pkwt.position_name as pkwt_position_name,
                             pkwt.client_id,
                             divisions.nama as division_name,
                             departments.nama as department_name,
                             positions.nama as position_name
                         ')
                         ->join('pkwt', 'pkwt.id = payroll_final.pkwt_id')
                         ->join('employees', 'employees.nama = pkwt.employee_name AND employees.client_id = pkwt.client_id', 'left')
                         ->join('positions', 'positions.id = employees.position_id', 'left')
                         ->join('departments', 'departments.id = positions.department_id', 'left')
                         ->join('divisions', 'divisions.id = departments.division_id', 'left')
                         ->where('payroll_final.period_id', $periodId);
        if ($clientId) {
            $query->where('pkwt.client_id', $clientId);
        }
        $data = $query->get()->getResult();

        foreach ($data as &$row) {
            $row->division_name = $row->division_name ?? '-';
            $row->department_name = $row->department_name ?? '-';
            $row->position_name = $row->position_name ?? $row->pkwt_position_name ?? '-';

            // Resolve Scheme Name
            $schemeName = '-';
            $clientConfig = $this->resolveClientConfig($row->client_id, $row->position_name);
            if ($clientConfig) {
                if ($clientConfig->payroll_type === 'Nominal') {
                    $schemeName = 'Nominal (Rp ' . number_format($clientConfig->custom_nominal, 0, ',', '.') . ')';
                } elseif ($clientConfig->payroll_type === 'UMP/UMK' || $clientConfig->payroll_type === 'UMP' || $clientConfig->payroll_type === 'UMK') {
                    $schemeName = $clientConfig->payroll_type;
                } elseif ($clientConfig->payroll_type === 'Template' && $clientConfig->payroll_scheme_id) {
                    $payrollScheme = $this->db->table('payroll_schemes')
                                              ->where('id', $clientConfig->payroll_scheme_id)
                                              ->get()
                                              ->getRow();
                    if ($payrollScheme) {
                        $schemeName = $payrollScheme->nama;
                    } else {
                        $schemeName = 'Template';
                    }
                }
            }
            $row->scheme_name = $schemeName;
        }

        return $this->respond($data);
    }

    public function approvePayroll($id)
    {
        $row = $this->db->table('payroll_final')
                        ->select('payroll_final.*, pkwt.employee_name, pkwt.position_name, pkwt.client_id')
                        ->join('pkwt', 'pkwt.id = payroll_final.pkwt_id')
                        ->where('payroll_final.id', $id)
                        ->get()
                        ->getRow();
        if (!$row) {
            return $this->failNotFound('Data gaji tidak ditemukan');
        }
        
        // Resolve Scheme
        $clientConfig = $this->resolveClientConfig($row->client_id, $row->position_name);
        if (!$clientConfig) {
            return $this->fail('Gaji untuk karyawan ' . $row->employee_name . ' tidak dapat disetujui karena belum memiliki skema payroll.');
        }
        
        // Check Net Salary (take_home_pay)
        if (floatval($row->take_home_pay) <= 0) {
            return $this->fail('Gaji untuk karyawan ' . $row->employee_name . ' tidak dapat disetujui karena Net Salary bernilai Rp 0 atau kurang.');
        }

        $username = $this->request->getHeaderLine('X-User-Action') ?: 'Admin';
        $this->db->table('payroll_final')->where('id', $id)->update([
            'status_approval' => 'Approved',
            'approved_by' => $username
        ]);
        
        $employeeName = $row->employee_name;
        $this->logActivity("Menyetujui payroll karyawan: " . $employeeName . " (Payroll ID: " . $id . ")");
        return $this->respond(['message' => 'Gaji telah disetujui']);
    }

    public function approvePayrollBulk()
    {
        $json = $this->request->getJSON(true);
        $ids = $json['ids'] ?? [];
        if (empty($ids)) {
            return $this->fail('Tidak ada data gaji yang dipilih.');
        }

        // Validation check for all selected IDs first
        foreach ($ids as $id) {
            $row = $this->db->table('payroll_final')
                            ->select('payroll_final.*, pkwt.employee_name, pkwt.position_name, pkwt.client_id')
                            ->join('pkwt', 'pkwt.id = payroll_final.pkwt_id')
                            ->where('payroll_final.id', $id)
                            ->get()
                            ->getRow();
            if (!$row) {
                return $this->failNotFound('Data gaji tidak ditemukan (ID: ' . $id . ')');
            }
            
            // Resolve Scheme
            $clientConfig = $this->resolveClientConfig($row->client_id, $row->position_name);
            if (!$clientConfig) {
                return $this->fail('Gaji untuk karyawan ' . $row->employee_name . ' tidak dapat disetujui karena belum memiliki skema payroll.');
            }
            
            // Check Net Salary (take_home_pay)
            if (floatval($row->take_home_pay) <= 0) {
                return $this->fail('Gaji untuk karyawan ' . $row->employee_name . ' tidak dapat disetujui karena Net Salary bernilai Rp 0 atau kurang.');
            }
        }

        $username = $this->request->getHeaderLine('X-User-Action') ?: 'Admin';
        $this->db->table('payroll_final')
                 ->whereIn('id', $ids)
                 ->update([
                     'status_approval' => 'Approved',
                     'approved_by' => $username
                 ]);
                 
        // Log activity for each approved employee
        $finals = $this->db->table('payroll_final')
                          ->select('payroll_final.id, pkwt.employee_name')
                          ->join('pkwt', 'pkwt.id = payroll_final.pkwt_id')
                          ->whereIn('payroll_final.id', $ids)
                          ->get()
                          ->getResult();
                          
        foreach ($finals as $final) {
            $this->logActivity("Menyetujui payroll karyawan: " . $final->employee_name . " (Payroll ID: " . $final->id . ")");
        }
        
        return $this->respond(['status' => 'success', 'message' => 'Semua data gaji terpilih berhasil disetujui']);
    }

    public function getSlipDetails($id)
    {
        // Get Final Result
        $final = $this->db->table('payroll_final')
                          ->select('payroll_final.*, pkwt.employee_name, pkwt.position_name, pkwt.client_id, payroll_periods.bulan, payroll_periods.tahun, clients.nama as client_name, employees.employ_id')
                          ->join('pkwt', 'pkwt.id = payroll_final.pkwt_id')
                          ->join('payroll_periods', 'payroll_periods.id = payroll_final.period_id')
                          ->join('clients', 'clients.id = pkwt.client_id')
                          ->join('employees', 'employees.nama = pkwt.employee_name AND employees.client_id = pkwt.client_id', 'left')
                          ->where('payroll_final.id', $id)
                          ->get()
                          ->getRowArray();
        
        if (!$final) return $this->failNotFound('Data tidak ditemukan');

        $months = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
        $final['period_name'] = ($months[intval($final['bulan'] ?? 1) - 1] ?? '') . " " . ($final['tahun'] ?? '');

        // Load active period to calculate calendar days
        $period = $this->db->table('payroll_periods')->where('id', $final['period_id'])->get()->getRow();
        $period = $this->formatPeriodRow($period);
        $daysInMonth = $period ? date('t', mktime(0, 0, 0, intval($period->bulan), 1, intval($period->tahun))) : 30;

        // Get client config to find payroll scheme ID
        $clientConfig = $this->resolveClientConfig($final['client_id'], $final['position_name'] ?? null);
        
        $isProrate = false;
        $isAbsenTidakPotong = false;
        $nominalPotonganAbsen = 0;
        
        // Try resolving from payroll scheme first
        if ($clientConfig && $clientConfig->payroll_scheme_id) {
            $payrollScheme = $this->db->table('payroll_schemes')->where('id', $clientConfig->payroll_scheme_id)->get()->getRow();
            if ($payrollScheme) {
                $isProrate = ($payrollScheme->prorate == 1);
                $isAbsenTidakPotong = ($payrollScheme->absen_tidak_potong == 1);
                $nominalPotonganAbsen = floatval($payrollScheme->nominal_potongan);
            }
        }
        
        // Fallback to client_absence_configs
        $absenceConfig = $this->db->table('client_absence_configs')->where('client_id', $final['client_id'])->get()->getRow();
        if ($clientConfig && !$isProrate && !$isAbsenTidakPotong && $nominalPotonganAbsen == 0) {
            if ($absenceConfig) {
                $isProrate = ($absenceConfig->prorate == 1);
                $isAbsenTidakPotong = ($absenceConfig->absen_tidak_potong == 1);
                $nominalPotonganAbsen = floatval($absenceConfig->nominal_potongan);
            }
        }

        // Get Client's overtime_rate_per_hour
        $client = $this->db->table('clients')->where('id', $final['client_id'])->get()->getRow();
        $otRate = ($client && isset($client->overtime_rate_per_hour) && floatval($client->overtime_rate_per_hour) > 0)
                  ? floatval($client->overtime_rate_per_hour)
                  : 20000;

        // Get Fixed Components
        $fixed = $this->db->table('pkwt_components')
                          ->where('pkwt_id', $final['pkwt_id'])
                          ->get()
                          ->getResultArray();
        
        // Get Variable Components (Attendance)
        $att = $this->db->table('payroll_attendance')
                        ->where('period_id', $final['period_id'])
                        ->where('pkwt_id', $final['pkwt_id'])
                        ->get()
                        ->getRowArray();

        // 1. Resolve Employee / Client UMR (Minimum Wage)
        $emp = $this->db->table('employees')
                        ->select('employees.*, minimum_wages.nominal as umr_nominal, minimum_wages.id as mw_id, positions.hari_kerja as position_hari_kerja')
                        ->join('minimum_wages', 'minimum_wages.id = employees.minimum_wage_id', 'left')
                        ->join('positions', 'positions.id = employees.position_id', 'left')
                        ->where('employees.nama', $final['employee_name'])
                        ->get()
                        ->getRow();

        $stdWorkingDays = 21;
        if ($emp && isset($emp->position_hari_kerja)) {
            $posHk = intval($emp->position_hari_kerja);
            if ($posHk === 6) {
                $stdWorkingDays = 25;
            } elseif ($posHk === 7) {
                $stdWorkingDays = 30;
            }
        }
        
        $minimumWage = 0;
        $mwId = null;
        if ($emp && isset($emp->umr_nominal) && $emp->umr_nominal > 0) {
            $minimumWage = floatval($emp->umr_nominal);
            $mwId = $emp->mw_id;
        } else {
            // Fallback to client config's minimum wage
            if ($clientConfig && isset($clientConfig->minimum_wage_id)) {
                $mw = $this->db->table('minimum_wages')->where('id', $clientConfig->minimum_wage_id)->get()->getRow();
                if ($mw) {
                    $minimumWage = floatval($mw->nominal);
                    $mwId = $mw->id;
                }
            }
        }

        // Resolve UMP and UMK values - pass employee's work location province for UMP lookup
        $empProvince = null;
        if ($emp && !empty($emp->work_location_id)) {
            $wl = $this->db->table('work_locations')->where('id', $emp->work_location_id)->get()->getRow();
            if ($wl && !empty($wl->provinsi)) {
                $empProvince = $wl->provinsi;
            }
        }
        $resolvedWages = $this->resolveUmpUmk($mwId, null, $empProvince);
        $umpWageValue = $resolvedWages['ump'];
        $umkWageValue = $resolvedWages['umk'];

        // Check if we have a basic salary component with source = kompensasi
        $hasKompensasiSource = false;
        foreach ($fixed as $comp) {
            $isBasic = (isset($comp['jenis_komponen']) && $comp['jenis_komponen'] === 'basic_salary') || (stripos($comp['nama'], 'Gaji Pokok') !== false);
            if ($isBasic && isset($comp['sumber_nilai']) && $comp['sumber_nilai'] === 'kompensasi') {
                $hasKompensasiSource = true;
                break;
            }
        }

        // 2. First pass: Find and calculate Gaji Pokok (Basic Salary)
        $gajiPokok = 0;
        $unproratedGajiPokok = 0;
        foreach ($fixed as $comp) {
            $isBasicSalary = false;
            if (isset($comp['jenis_komponen']) && $comp['jenis_komponen'] === 'basic_salary') {
                $isBasicSalary = true;
            } elseif (stripos($comp['nama'], 'Gaji Pokok') !== false) {
                $isBasicSalary = true;
            }

            if ($isBasicSalary) {
                $base_nilai = floatval($comp['nilai']);
                $sumber_nilai = $comp['sumber_nilai'] ?? 'nominal';
                
                // Force check against actual client scheme if available
                if (isset($clientConfig) && $clientConfig->payroll_scheme_id) {
                    $schemeComp = $this->db->table('payroll_components')
                        ->where('scheme_id', $clientConfig->payroll_scheme_id)
                        ->where('jenis_komponen', 'basic_salary')
                        ->get()->getRow();
                    if ($schemeComp) {
                        $sumber_nilai = $schemeComp->sumber_nilai ?? 'nominal';
                        $base_nilai = floatval($schemeComp->nilai);
                    }
                }
                
                if ($sumber_nilai === 'ump') {
                    $base_nilai = $umpWageValue * ($base_nilai / 100);
                } else if ($sumber_nilai === 'umk') {
                    $base_nilai = $umkWageValue * ($base_nilai / 100);
                } else if ($sumber_nilai === 'ump_umk') {
                    $base_nilai = $minimumWage * ($base_nilai / 100);
                } else if ($sumber_nilai === 'kompensasi') {
                    $kompTetapValue = 0;
                    foreach ($fixed as $c) {
                        if (($c['jenis_komponen'] ?? '') === 'kompensasi' && ($c['sifat_kompensasi'] ?? '') === 'tetap') {
                            $kompTetapValue += floatval($c['nilai']);
                        }
                    }
                    $base_nilai = $kompTetapValue * ($base_nilai / 100);
                } else {
                    // Force base_nilai to use Employee's setup if available!
                    if ($emp && isset($emp->gaji_pokok) && floatval($emp->gaji_pokok) > 0) {
                        $base_nilai = floatval($emp->gaji_pokok);
                    } else if ($minimumWage > 0) {
                        $base_nilai = $minimumWage;
                    }
                }

                $unproratedGajiPokok = $base_nilai;

                // Scale by period
                if (isset($comp['periode'])) {
                    if ($comp['periode'] === 'hari') {
                        $days = ($att && isset($att['hari_kerja'])) ? intval($att['hari_kerja']) : 0;
                        $base_nilai = $base_nilai * $days;
                    } elseif ($comp['periode'] === 'minggu') {
                        $base_nilai = $base_nilai * 4;
                    } elseif ($comp['periode'] === 'tahun') {
                        $base_nilai = $base_nilai / 12;
                    } else {
                        // bulanan
                        if ($isProrate) {
                            $days = ($att && isset($att['hari_kerja'])) ? intval($att['hari_kerja']) : 0;
                            $base_nilai = $base_nilai * ($days / $daysInMonth);
                        }
                    }
                }
                
                $gajiPokok = $base_nilai;
                break; // Assume only one basic salary component
            }
        }

        if ($gajiPokok <= 0 && $emp && isset($emp->gaji_pokok)) {
            $unproratedGajiPokok = floatval($emp->gaji_pokok);
            $gajiPokok = $unproratedGajiPokok;
            if ($isProrate) {
                $days = ($att && isset($att['hari_kerja'])) ? intval($att['hari_kerja']) : 0;
                $gajiPokok = $gajiPokok * ($days / $daysInMonth);
            }
        }

        $earnings = [];
        $deductions = [];

        // 3. Calculate components
        foreach ($fixed as $comp) {
            $isBasic = false;
            if (isset($comp['jenis_komponen']) && $comp['jenis_komponen'] === 'basic_salary') {
                $isBasic = true;
            } elseif (stripos($comp['nama'], 'Gaji Pokok') !== false) {
                $isBasic = true;
            }

            $nilai = 0;

            if ($isBasic) {
                $nilai = isset($final['gaji_pokok']) ? floatval($final['gaji_pokok']) : $gajiPokok;
            } else {
                if ($hasKompensasiSource && ($comp['jenis_komponen'] ?? '') === 'kompensasi' && ($comp['sifat_kompensasi'] ?? '') === 'tetap') {
                    $nilai = 0; // Prevent double-counting by setting it to 0
                } else if (isset($comp['jenis_komponen']) && !empty($comp['jenis_komponen'])) {
                    // New Master Compensation component logic
                    $base_nilai = floatval($comp['nilai']);
                    
                    if (isset($comp['sumber_nilai'])) {
                        if ($comp['sumber_nilai'] === 'ump') {
                            $base_nilai = $umpWageValue * ($base_nilai / 100);
                        } else if ($comp['sumber_nilai'] === 'umk') {
                            $base_nilai = $umkWageValue * ($base_nilai / 100);
                        } else if ($comp['sumber_nilai'] === 'ump_umk') {
                            $base_nilai = $minimumWage * ($base_nilai / 100);
                        }
                    }
                    
                    // Scale by period
                    if ($comp['periode'] === 'hari') {
                        $days = ($att && isset($att['hari_kerja'])) ? intval($att['hari_kerja']) : 0;
                        $nilai = $base_nilai * $days;
                    } elseif ($comp['periode'] === 'minggu') {
                        $nilai = $base_nilai * 4;
                    } elseif ($comp['periode'] === 'tahun') {
                        $nilai = $base_nilai / 12;
                    } else {
                        // bulanan
                        // Tunjangan tetap: Nilai tunjangan tetap bersifat konstan setiap periode (TIDAK terprorate)
                        if ($isProrate && isset($comp['sifat_kompensasi']) && $comp['sifat_kompensasi'] === 'tidak_tetap') {
                            $days = ($att && isset($att['hari_kerja'])) ? intval($att['hari_kerja']) : 0;
                            $nilai = $base_nilai * ($days / 21);
                        } else {
                            $nilai = $base_nilai;
                        }
                    }
                } else {
                    // Legacy component logic
                    $nilai = floatval($comp['nilai']);
                    if (intval($comp['is_persentase']) === 1 || $comp['is_persentase'] === true) {
                        $nilai = (isset($final['gaji_pokok']) ? floatval($final['gaji_pokok']) : $gajiPokok) * ($nilai / 100);
                    }
                }
            }

            if ($nilai > 0) {
                if ($comp['tipe'] === 'pendapatan') {
                    $earnings[] = ['nama' => $comp['nama'], 'nilai' => $nilai];
                } else {
                    $deductions[] = ['nama' => $comp['nama'], 'nilai' => $nilai];
                }
            }
        }

        if ($att) {
            if (isset($final['jam_lembur']) && floatval($final['lembur_pay']) > 0) {
                $earnings[] = ['nama' => 'Lembur', 'nilai' => floatval($final['lembur_pay'])];
            } else if ($att['jam_lembur'] > 0) {
                $earnings[] = ['nama' => 'Lembur', 'nilai' => $att['jam_lembur'] * $otRate];
            }

            if (isset($final['bonus_tambahan']) && floatval($final['bonus_tambahan']) > 0) {
                $earnings[] = ['nama' => 'Bonus/Lainnya', 'nilai' => floatval($final['bonus_tambahan'])];
            } else if ($att['bonus_tambahan'] > 0) {
                $earnings[] = ['nama' => 'Bonus/Lainnya', 'nilai' => $att['bonus_tambahan']];
            }
            
            if (isset($final['potongan_absen']) && floatval($final['potongan_absen']) > 0) {
                $deductions[] = ['nama' => 'Potongan Absen', 'nilai' => floatval($final['potongan_absen'])];
            } else if (!$isAbsenTidakPotong) {
                $potongan_absen = floatval($att['potongan_absensi']);
                if ($potongan_absen == 0) {
                    $missingDays = max(0, $daysInMonth - intval($att['hari_kerja']));
                    if ($missingDays > 0) {
                        if ($isProrate) {
                            $potongan_absen = $unproratedGajiPokok * ($missingDays / $daysInMonth);
                        } else {
                            $nominalPotongan = ($nominalPotonganAbsen > 0) ? $nominalPotonganAbsen : (($absenceConfig && isset($absenceConfig->nominal_potongan)) ? floatval($absenceConfig->nominal_potongan) : 0);
                            $potongan_absen = $missingDays * $nominalPotongan;
                        }
                    }
                }
                if ($potongan_absen > 0) {
                    $deductions[] = ['nama' => 'Potongan Absen', 'nilai' => $potongan_absen];
                }
            }
        }

        // Add BPJS and PPh 21 detailed lines
        if (isset($final['bpjs_kes_karyawan'])) {
            if (floatval($final['bpjs_kes_karyawan']) > 0) {
                $deductions[] = ['nama' => 'BPJS Kesehatan (1% Karyawan)', 'nilai' => floatval($final['bpjs_kes_karyawan'])];
            }
            if (floatval($final['bpjs_jht_karyawan']) > 0) {
                $deductions[] = ['nama' => 'BPJS TK JHT (2% Karyawan)', 'nilai' => floatval($final['bpjs_jht_karyawan'])];
            }
            if (floatval($final['bpjs_jp_karyawan']) > 0) {
                $deductions[] = ['nama' => 'BPJS TK JP (1% Karyawan)', 'nilai' => floatval($final['bpjs_jp_karyawan'])];
            }

            if (floatval($final['pph21']) > 0) {
                if ($final['tax_method'] === 'Gross Up') {
                    $earnings[] = ['nama' => 'Tunjangan Pajak (Gross Up)', 'nilai' => floatval($final['tax_allowance'])];
                    $deductions[] = ['nama' => 'Potongan Pajak PPh 21', 'nilai' => floatval($final['pph21'])];
                } elseif ($final['tax_method'] === 'Gross') {
                    $deductions[] = ['nama' => 'Potongan Pajak PPh 21', 'nilai' => floatval($final['pph21'])];
                }
            }
        }

        return $this->respond([
            'info' => $final,
            'earnings' => $earnings,
            'deductions' => $deductions
        ]);
    }

    public function exportPayrollCsv($periodId)
    {
        $clientId = $this->request->getGet('client_id');
        
        $query = $this->db->table('payroll_final')
                          ->select('
                              payroll_final.*, 
                              pkwt.employee_name, 
                              pkwt.position_name, 
                              pkwt.tipe_perjanjian, 
                              pkwt.client_id, 
                              clients.nama as client_name,
                              employees.employ_id,
                              employees.tempat_lahir,
                              employees.tanggal_lahir,
                              employees.npwp,
                              divisions.nama as division_name,
                              departments.nama as department_name,
                              work_locations.lokasi_kerja as location_name,
                              minimum_wages.nominal as min_wage
                          ')
                          ->join('pkwt', 'pkwt.id = payroll_final.pkwt_id')
                          ->join('clients', 'clients.id = pkwt.client_id')
                          ->join('employees', 'employees.nama = pkwt.employee_name AND employees.client_id = pkwt.client_id', 'left')
                          ->join('positions', 'positions.id = employees.position_id', 'left')
                          ->join('departments', 'departments.id = positions.department_id', 'left')
                          ->join('divisions', 'divisions.id = departments.division_id', 'left')
                          ->join('work_locations', 'work_locations.id = employees.work_location_id', 'left')
                          ->join('minimum_wages', 'minimum_wages.id = employees.minimum_wage_id', 'left')
                          ->where('payroll_final.period_id', $periodId);
                          
        if ($clientId) {
            $query->where('pkwt.client_id', $clientId);
        }
        
        $results = $query->get()->getResultArray();
        
        if ($this->request->getGet('format') === 'json') {
            return $this->respond($results);
        }
        
        // Define file name
        $periodInfo = $this->db->table('payroll_periods')->where('id', $periodId)->get()->getRow();
        $periodInfo = $this->formatPeriodRow($periodInfo);
        $periodName = $periodInfo ? $periodInfo->nama : 'Unknown_Period';
        $filename = "Payroll_Report_" . str_replace(' ', '_', $periodName) . ".csv";
        
        // Set headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        
        // Open output stream
        $output = fopen('php://output', 'w');
        
        // Add BOM to fix UTF-8 in Excel
        fputs($output, $bom =(chr(0xEF) . chr(0xBB) . chr(0xBF)));

        // Write Header Row
        fputcsv($output, [
            'No', 
            'Company / Client',
            'Employee ID (NIK)',
            'Employee Name', 
            'Place & Date of Birth',
            'NPWP',
            'Division',
            'Department',
            'Position / Role', 
            'Work Location',
            'Min. Wage (UMP/UMK)',
            'Basic Salary', 
            'Overtime Pay',
            'Bonus/Lainnya',
            'Total Income (Pendapatan)', 
            'Absence Deduction',
            'BPJS Ketenagakerjaan (Karyawan)',
            'BPJS Kesehatan (Karyawan)',
            'Tax (PPh21)',
            'Total Deductions (Potongan)', 
            'Take Home Pay', 
            'Status'
        ], ';');
        
        // Write Data Rows
        $no = 1;
        foreach ($results as $row) {
            $bpjsTK = (float)($row['bpjs_jht_karyawan'] ?? 0) + (float)($row['bpjs_jp_karyawan'] ?? 0);
            $bpjsKes = (float)($row['bpjs_kes_karyawan'] ?? 0);
            
            $placeDob = '';
            if (!empty($row['tempat_lahir']) && !empty($row['tanggal_lahir'])) {
                $placeDob = $row['tempat_lahir'] . ', ' . $row['tanggal_lahir'];
            } elseif (!empty($row['tempat_lahir'])) {
                $placeDob = $row['tempat_lahir'];
            } elseif (!empty($row['tanggal_lahir'])) {
                $placeDob = $row['tanggal_lahir'];
            }
            
            fputcsv($output, [
                $no++,
                $row['client_name'] ?? '-',
                $row['employ_id'] ?? '-',
                $row['employee_name'] ?? '-',
                $placeDob ?: '-',
                $row['npwp'] ?? '-',
                $row['division_name'] ?? '-',
                $row['department_name'] ?? '-',
                $row['position_name'] ?? '-',
                $row['location_name'] ?? '-',
                isset($row['min_wage']) ? number_format((float)$row['min_wage'], 0, '', '') : '-',
                number_format((float)($row['gaji_pokok'] ?? 0), 0, '', ''),
                number_format((float)($row['lembur_pay'] ?? 0), 0, '', ''),
                number_format((float)($row['bonus_tambahan'] ?? 0), 0, '', ''),
                number_format((float)($row['total_pendapatan'] ?? 0), 0, '', ''),
                number_format((float)($row['potongan_absen'] ?? 0), 0, '', ''),
                number_format($bpjsTK, 0, '', ''),
                number_format($bpjsKes, 0, '', ''),
                number_format((float)($row['pph21'] ?? 0), 0, '', ''),
                number_format((float)($row['total_potongan'] ?? 0), 0, '', ''),
                number_format((float)($row['take_home_pay'] ?? 0), 0, '', ''),
                $row['status_approval'] ?? 'Pending'
            ], ';');
        }
        
        fclose($output);
        exit;
    }


    // --- EMPLOYEES ---
    public function getEmployees()
    {
        $clientId = $this->request->getGet('client_id');
        $query = $this->db->table('positions')
                             ->select('positions.*, departments.nama as department_name, divisions.nama as division_name')
                             ->join('departments', 'departments.id = positions.department_id')
                             ->join('divisions', 'divisions.id = departments.division_id');
        if ($clientId) {
            $query->where('divisions.client_id', $clientId);
        }
        $employees = $query->get()->getResult();
        return $this->respond($employees);
    }

    // --- CLIENT COMPENSATIONS ---
    public function getCompensations($clientId)
    {
        $data = $this->db->table('client_compensations')
                         ->where('client_id', $clientId)
                         ->orderBy('id', 'ASC')
                         ->get()
                         ->getResultArray();
        return $this->respond($data);
    }

    public function createCompensation()
    {
        $data = $this->request->getJSON(true);
        $this->db->table('client_compensations')->insert($data);
        return $this->respondCreated(['message' => 'Komponen kompensasi berhasil ditambahkan']);
    }

    public function updateCompensation($id)
    {
        $data = $this->request->getJSON(true);
        unset($data['id']);
        $this->db->table('client_compensations')->where('id', $id)->update($data);
        return $this->respond(['message' => 'Komponen kompensasi berhasil diupdate']);
    }

    public function deleteCompensation($id)
    {
        $this->db->table('client_compensations')->where('id', $id)->delete();
        return $this->respondDeleted(['message' => 'Komponen kompensasi berhasil dihapus']);
    }

    // --- CLIENT ABSENCE CONFIG ---
    public function getAbsenceConfig($clientId)
    {
        $data = $this->db->table('client_absence_configs')
                         ->where('client_id', $clientId)
                         ->get()
                         ->getRowArray();
        return $this->respond($data ?: []);
    }

    public function saveAbsenceConfig()
    {
        $data = $this->request->getJSON(true);
        $clientId = $data['client_id'];

        $existing = $this->db->table('client_absence_configs')
                             ->where('client_id', $clientId)
                             ->get()
                             ->getRow();

        if ($existing) {
            $this->db->table('client_absence_configs')->where('client_id', $clientId)->update($data);
        } else {
            $this->db->table('client_absence_configs')->insert($data);
        }

        $this->logActivity("Menyimpan konfigurasi absensi klien ID: " . $clientId);
        return $this->respond(['message' => 'Konfigurasi absensi berhasil disimpan']);
    }

    // --- LOGS ---
    public function getLogs()
    {
        $logs = $this->db->table('status_logs')
                         ->orderBy('created_at', 'DESC')
                         ->get()
                         ->getResultArray();
        return $this->respond($logs ?: []);
    }

    protected function logActivity($action, $username = null)
    {
        if ($username === null) {
            $username = $this->request->getHeaderLine('X-User-Action') ?: 'System';
        }
        
        $this->db->table('status_logs')->insert([
            'description' => $action,
            'user_action' => $username,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    protected function resolveUmpUmk($minimumWageId, $tahun = null, $employeeProvince = null)
    {
        $res = ['ump' => 0, 'umk' => 0];
        if (!$minimumWageId) return $res;

        $currentWage = $this->db->table('minimum_wages')->where('id', $minimumWageId)->get()->getRow();
        if (!$currentWage) return $res;

        // Try current year first, then wage record's year
        $currentYear = date('Y');
        $wageYear = $currentWage->tahun ?: $currentYear;
        $year = $tahun ?: $currentYear;

        if ($currentWage->tipe === 'UMP') {
            // If the stored UMP is for the current year, use it directly
            if ($currentWage->tahun == $currentYear) {
                $res['ump'] = floatval($currentWage->nominal);
            } else {
                // Try to find a more recent UMP for the same region
                $searchName = $currentWage->nama_daerah ?: $currentWage->provinsi;
                $newerUmp = $this->db->table('minimum_wages')
                                     ->where('tipe', 'UMP')
                                     ->where('tahun', $currentYear)
                                     ->groupStart()
                                         ->where('nama_daerah', $searchName)
                                         ->orWhere('provinsi', $searchName)
                                     ->groupEnd()
                                     ->get()
                                     ->getRow();
                $res['ump'] = $newerUmp ? floatval($newerUmp->nominal) : floatval($currentWage->nominal);
            }
            // Fallback UMK to the same value
            $res['umk'] = $res['ump'];
        } else if ($currentWage->tipe === 'UMK') {
            // For UMK, also try to find the current year's UMK
            if ($currentWage->tahun == $currentYear) {
                $res['umk'] = floatval($currentWage->nominal);
            } else {
                $searchName = $currentWage->nama_daerah;
                $newerUmk = $this->db->table('minimum_wages')
                                     ->where('tipe', 'UMK')
                                     ->where('tahun', $currentYear)
                                     ->where('nama_daerah', $searchName)
                                     ->get()
                                     ->getRow();
                $res['umk'] = $newerUmk ? floatval($newerUmk->nominal) : floatval($currentWage->nominal);
            }
            
            // Find corresponding UMP for the province
            // Build list of province names to search for
            $provinceSearchNames = [];
            if (!empty($currentWage->provinsi)) {
                $provinceSearchNames[] = $currentWage->provinsi;
            }
            if (!empty($employeeProvince)) {
                $provinceSearchNames[] = $employeeProvince;
                // Also try uppercase variant
                $provinceSearchNames[] = strtoupper($employeeProvince);
            }
            // Also try to extract province from kode_daerah (e.g. "ID 31.73" -> province code 31)
            if (!empty($currentWage->kode_daerah)) {
                $parts = explode(' ', $currentWage->kode_daerah);
                if (count($parts) >= 2) {
                    $codeParts = explode('.', $parts[1]);
                    $provCode = $codeParts[0]; // e.g. "31"
                    // Look for UMP with matching province code prefix
                    $umpByCode = $this->db->table('minimum_wages')
                                         ->where('tipe', 'UMP')
                                         ->where('tahun', $currentYear)
                                         ->like('kode_daerah', "ID $provCode", 'after')
                                         ->get()
                                         ->getRow();
                    if (!$umpByCode) {
                        // Fallback to wage record's year
                        $umpByCode = $this->db->table('minimum_wages')
                                             ->where('tipe', 'UMP')
                                             ->where('tahun', $wageYear)
                                             ->like('kode_daerah', "ID $provCode", 'after')
                                             ->get()
                                             ->getRow();
                    }
                    if ($umpByCode) {
                        $res['ump'] = floatval($umpByCode->nominal);
                        return $res;
                    }
                }
            }
            
            // Search UMP by province name
            $umpWage = null;
            $yearsToTry = array_unique([$currentYear, $wageYear]);
            foreach ($yearsToTry as $tryYear) {
                foreach ($provinceSearchNames as $provName) {
                    if (empty($provName)) continue;
                    $umpWage = $this->db->table('minimum_wages')
                                        ->where('tipe', 'UMP')
                                        ->where('tahun', $tryYear)
                                        ->groupStart()
                                            ->where('nama_daerah', $provName)
                                            ->orWhere('provinsi', $provName)
                                            ->orWhere('nama_daerah', strtoupper($provName))
                                            ->orWhere('provinsi', strtoupper($provName))
                                        ->groupEnd()
                                        ->get()
                                        ->getRow();
                    if ($umpWage) break 2;
                }
            }
            
            if ($umpWage) {
                $res['ump'] = floatval($umpWage->nominal);
            } else {
                $res['ump'] = $res['umk']; // fallback UMP to UMK value
            }
        }
        return $res;
    }

    public function checkSchema()
    {
        $clientId = $this->request->getGet('client_id');
        $divId = $this->request->getGet('division_id');
        $deptId = $this->request->getGet('department_id');
        $posId = $this->request->getGet('position_id');

        if (!$clientId) return $this->fail('Client ID required');

        // Cek Posisi
        if ($posId) {
            $config = $this->db->table('client_payroll_configs')->where(['client_id' => $clientId, 'position_id' => $posId])->get()->getRow();
            if ($config) return $this->respond(['level' => 'Posisi', 'config' => $config]);
        }
        
        // Cek Departemen
        if ($deptId) {
            $config = $this->db->table('client_payroll_configs')->where(['client_id' => $clientId, 'department_id' => $deptId, 'position_id' => null])->get()->getRow();
            if ($config) return $this->respond(['level' => 'Departemen', 'config' => $config]);
        }

        // Cek Divisi
        if ($divId) {
            $config = $this->db->table('client_payroll_configs')->where(['client_id' => $clientId, 'division_id' => $divId, 'department_id' => null, 'position_id' => null])->get()->getRow();
            if ($config) return $this->respond(['level' => 'Divisi', 'config' => $config]);
        }

        // Cek General Client
        $config = $this->db->table('client_payroll_configs')->where(['client_id' => $clientId, 'division_id' => null, 'department_id' => null, 'position_id' => null])->get()->getRow();
        if ($config) return $this->respond(['level' => 'General Client', 'config' => $config]);

    }

    public function previewPayroll()
    {
        $clientId = $this->request->getGet('client_id');
        $divId = $this->request->getGet('division_id');
        $deptId = $this->request->getGet('department_id');
        $posId = $this->request->getGet('position_id');
        $workLocId = $this->request->getGet('work_location_id');

        if (!$clientId) return $this->fail('Client ID required');

        // Resolve Config
        $config = null;
        $level = 'Tidak Ada';
        
        if ($posId) {
            $config = $this->db->table('client_payroll_configs')
                ->where('client_id', $clientId)
                ->where('position_id', $posId)
                ->get()->getRow();
            if ($config) $level = 'Posisi';
        }
        if (!$config && $deptId) {
            $config = $this->db->table('client_payroll_configs')
                ->where('client_id', $clientId)
                ->where('department_id', $deptId)
                ->where('position_id IS NULL')
                ->get()->getRow();
            if ($config) $level = 'Departemen';
        }
        if (!$config && $divId) {
            $config = $this->db->table('client_payroll_configs')
                ->where('client_id', $clientId)
                ->where('division_id', $divId)
                ->where('department_id IS NULL')
                ->where('position_id IS NULL')
                ->get()->getRow();
            if ($config) $level = 'Divisi';
        }
        if (!$config) {
            $config = $this->db->table('client_payroll_configs')
                ->where('client_id', $clientId)
                ->where('division_id IS NULL')
                ->where('department_id IS NULL')
                ->where('position_id IS NULL')
                ->get()->getRow();
            if ($config) $level = 'General Client';
        }

        if (!$config) {
            return $this->respond(['status' => 'error', 'message' => 'Skema tidak ditemukan']);
        }

        $effectivePayrollType = $config->payroll_type;
        $resolvedPayrollType = $config->payroll_type;
        if ($config->payroll_type === 'Template' && $config->payroll_scheme_id) {
            $basicComp = $this->db->table('payroll_components')
                ->where('scheme_id', $config->payroll_scheme_id)
                ->groupStart()
                    ->where('jenis_komponen', 'basic_salary')
                    ->orLike('nama', 'Gaji Pokok')
                ->groupEnd()
                ->get()
                ->getRow();
            if ($basicComp && !empty($basicComp->sumber_nilai)) {
                if ($basicComp->sumber_nilai === 'ump') {
                    $resolvedPayrollType = 'UMP';
                    $effectivePayrollType = 'UMP';
                } else if ($basicComp->sumber_nilai === 'umk') {
                    $resolvedPayrollType = 'UMK';
                    $effectivePayrollType = 'UMK';
                } else if ($basicComp->sumber_nilai === 'ump_umk') {
                    $resolvedPayrollType = 'UMP/UMK';
                    $effectivePayrollType = 'UMP/UMK';
                }
            }
        }

        $gajiPokok = 0;
        $desc = "Tipe Skema: {$config->payroll_type}. ";

        if ($config->payroll_type === 'Nominal') {
            $gajiPokok = floatval($config->custom_nominal);
            $desc .= "Menggunakan nominal kustom Rp " . number_format($gajiPokok, 0, ',', '.');
        } else if ($effectivePayrollType === 'UMP/UMK' || $effectivePayrollType === 'UMP' || $effectivePayrollType === 'UMK') {
            $mw = null;
            if ($workLocId) {
                $loc = $this->db->table('work_locations')->where('id', $workLocId)->get()->getRow();
                if ($loc) {
                    $searchCity = !empty($loc->kota_kabupaten) ? trim(strtolower($loc->kota_kabupaten)) : null;
                    $searchProv = !empty($loc->provinsi) ? trim(strtolower($loc->provinsi)) : null;
                    
                    if ($effectivePayrollType === 'UMK') {
                        if ($searchCity) {
                            $mw = $this->db->table('minimum_wages')->where(['tipe' => 'UMK', 'nama_daerah' => $loc->kota_kabupaten])->orderBy('tahun', 'DESC')->get()->getRow();
                            if (!$mw) {
                                $mw = $this->db->table('minimum_wages')->where('tipe', 'UMK')->where('LOWER(nama_daerah)', $searchCity)->orderBy('tahun', 'DESC')->get()->getRow();
                            }
                        }
                    } else if ($effectivePayrollType === 'UMP') {
                        if ($searchProv) {
                            $mw = $this->db->table('minimum_wages')->where(['tipe' => 'UMP', 'nama_daerah' => $loc->provinsi])->orderBy('tahun', 'DESC')->get()->getRow();
                            if (!$mw) {
                                $mw = $this->db->table('minimum_wages')->where('tipe', 'UMP')->where('LOWER(nama_daerah)', $searchProv)->orderBy('tahun', 'DESC')->get()->getRow();
                            }
                        }
                    } else { // UMP/UMK
                        if ($searchCity) {
                            $mw = $this->db->table('minimum_wages')->where(['tipe' => 'UMK', 'nama_daerah' => $loc->kota_kabupaten])->orderBy('tahun', 'DESC')->get()->getRow();
                            if (!$mw) {
                                $mw = $this->db->table('minimum_wages')->where('tipe', 'UMK')->where('LOWER(nama_daerah)', $searchCity)->orderBy('tahun', 'DESC')->get()->getRow();
                            }
                        }
                        if (!$mw && $searchProv) {
                            $mw = $this->db->table('minimum_wages')->where(['tipe' => 'UMP', 'nama_daerah' => $loc->provinsi])->orderBy('tahun', 'DESC')->get()->getRow();
                            if (!$mw) {
                                $mw = $this->db->table('minimum_wages')->where('tipe', 'UMP')->where('LOWER(nama_daerah)', $searchProv)->orderBy('tahun', 'DESC')->get()->getRow();
                            }
                        }
                    }
                    
                    if ($mw) {
                        $desc .= "Mendeteksi lokasi {$mw->nama_daerah}. ";
                    }
                }
            }
            if (!$mw && $config->minimum_wage_id) {
                $mw = $this->db->table('minimum_wages')->where('id', $config->minimum_wage_id)->get()->getRow();
            }
            
            if ($mw) {
                $gajiPokok = floatval($mw->nominal);
                if ($config->payroll_type === 'Template' && isset($basicComp) && in_array($basicComp->sumber_nilai, ['ump', 'umk', 'ump_umk'])) {
                    $multiplier = floatval($basicComp->nilai);
                    $gajiPokok = $gajiPokok * ($multiplier / 100);
                    $desc .= "Menggunakan UMR {$mw->nama_daerah} (Rp " . number_format($mw->nominal, 0, ',', '.') . ") dengan multiplier {$multiplier}% menjadi Rp " . number_format($gajiPokok, 0, ',', '.');
                } else {
                    $desc .= "Menggunakan UMR {$mw->nama_daerah} (Rp " . number_format($gajiPokok, 0, ',', '.') . ")";
                }
            } else {
                $desc .= "UMR tidak ditemukan untuk lokasi ini.";
            }
        } else if ($config->payroll_type === 'Template') {
            // Find template components if possible. For now, zero as fallback if no fixed income found.
            $desc .= "Menggunakan Template. Silakan sesuaikan komponen gaji pokok jika diperlukan.";
            $gajiPokok = 0;
        }

        $hariKerja = 5;
        if ($posId) {
            $pos = $this->db->table('positions')->where('id', $posId)->get()->getRow();
            if ($pos && isset($pos->hari_kerja)) {
                $hariKerja = intval($pos->hari_kerja);
            }
        }

        $hariKerjaBulanan = 22;
        if ($hariKerja == 6) $hariKerjaBulanan = 26;
        if ($hariKerja == 7) $hariKerjaBulanan = 30;

        $gajiHarian = $gajiPokok / $hariKerjaBulanan;
        $dendaAbsen = $gajiHarian;

        return $this->respond([
            'status' => 'success',
            'level' => $level,
            'payroll_type' => $resolvedPayrollType,
            'gaji_pokok' => $gajiPokok,
            'hari_kerja' => $hariKerja,
            'gaji_harian' => round($gajiHarian),
            'denda_absen' => round($dendaAbsen),
            'description' => $desc
        ]);
    }

    private function calculateBpjsAndTax($gajiPokok, $bpjsWageBase, $pphWageBase, $schemeTemplate, $taxScheme, $minimumWage, $ptkpStatus, $bpjsScheme = null)
    {
        $result = [
            'bpjs_kes_karyawan' => 0,
            'bpjs_kes_perusahaan' => 0,
            'bpjs_jht_karyawan' => 0,
            'bpjs_jht_perusahaan' => 0,
            'bpjs_jp_karyawan' => 0,
            'bpjs_jp_perusahaan' => 0,
            'bpjs_jkk_perusahaan' => 0,
            'bpjs_jkm_perusahaan' => 0,
            'pph21' => 0,
            'tax_allowance' => 0,
            'ter_rate' => 0,
            'metode_pajak' => 'Gross'
        ];

        // Resolve tax method
        if ($schemeTemplate) {
            $result['metode_pajak'] = $schemeTemplate['metode_pajak'] ?? ($taxScheme->metode ?? 'Gross');
        } elseif ($taxScheme) {
            $result['metode_pajak'] = $taxScheme->metode ?? 'Gross';
        }

        if ($gajiPokok <= 0) {
            return $result;
        }

        // Determine Rates source
        $bpjsSrc = $bpjsScheme ?: ($schemeTemplate ?: $taxScheme);
        $taxSrc = $schemeTemplate ?: $taxScheme;

        if (!$bpjsSrc || !$taxSrc) {
            return $result;
        }

        $isBpjsTemplate = is_array($bpjsSrc);
        $isTaxTemplate = is_array($taxSrc);

        $result['metode_pajak'] = $isTaxTemplate ? ($taxSrc['metode_pajak'] ?? 'Gross') : ($taxSrc->metode ?? 'Gross');

        $kesRateEmp = floatval($isBpjsTemplate ? ($bpjsSrc['bpjs_kes_karyawan'] ?? 1.0) : ($bpjsSrc->bpjs_kes_karyawan ?? 1.0)) / 100;
        $kesRateCo = floatval($isBpjsTemplate ? ($bpjsSrc['bpjs_kes_perusahaan'] ?? 4.0) : ($bpjsSrc->bpjs_kes_perusahaan ?? 4.0)) / 100;
        $kesMaxSal = floatval($isBpjsTemplate ? ($bpjsSrc['bpjs_kes_max_salary'] ?? 12000000) : ($bpjsSrc->bpjs_kes_max_salary ?? 12000000));

        $jhtRateEmp = floatval($isBpjsTemplate ? ($bpjsSrc['bpjs_jht_karyawan'] ?? 2.0) : ($bpjsSrc->bpjs_jht_karyawan ?? 2.0)) / 100;
        $jhtRateCo = floatval($isBpjsTemplate ? ($bpjsSrc['bpjs_jht_perusahaan'] ?? 3.7) : ($bpjsSrc->bpjs_jht_perusahaan ?? 3.7)) / 100;

        $jpRateEmp = floatval($isBpjsTemplate ? ($bpjsSrc['bpjs_jp_karyawan'] ?? 1.0) : ($bpjsSrc->bpjs_jp_karyawan ?? 1.0)) / 100;
        $jpRateCo = floatval($isBpjsTemplate ? ($bpjsSrc['bpjs_jp_perusahaan'] ?? 2.0) : ($bpjsSrc->bpjs_jp_perusahaan ?? 2.0)) / 100;
        $jpMaxSal = floatval($isBpjsTemplate ? ($bpjsSrc['bpjs_jp_max_salary'] ?? 10024600) : ($bpjsSrc->bpjs_jp_max_salary ?? 10024600));

        $jkkRateCo = floatval($isBpjsTemplate ? ($bpjsSrc['bpjs_jkk_perusahaan'] ?? 0.24) : ($bpjsSrc->bpjs_jkk_perusahaan ?? 0.24)) / 100;
        $jkmRateCo = floatval($isBpjsTemplate ? ($bpjsSrc['bpjs_jkm_perusahaan'] ?? 0.30) : ($bpjsSrc->bpjs_jkm_perusahaan ?? 0.30)) / 100;

        // Apply caps
        $kesWageBase = min($bpjsWageBase, $kesMaxSal);
        $jpWageBase = min($bpjsWageBase, $jpMaxSal);

        // Calculations
        $result['bpjs_kes_karyawan'] = $kesWageBase * $kesRateEmp;
        $result['bpjs_kes_perusahaan'] = $kesWageBase * $kesRateCo;

        $result['bpjs_jht_karyawan'] = $bpjsWageBase * $jhtRateEmp;
        $result['bpjs_jht_perusahaan'] = $bpjsWageBase * $jhtRateCo;

        $result['bpjs_jp_karyawan'] = $jpWageBase * $jpRateEmp;
        $result['bpjs_jp_perusahaan'] = $jpWageBase * $jpRateCo;

        $result['bpjs_jkk_perusahaan'] = $bpjsWageBase * $jkkRateCo;
        $result['bpjs_jkm_perusahaan'] = $bpjsWageBase * $jkmRateCo;

        // PPh 21 TER 2024 Calculation
        $bpjsCoPremiums = $result['bpjs_kes_perusahaan'] + $result['bpjs_jkk_perusahaan'] + $result['bpjs_jkm_perusahaan'];
        
        $ptkpCategory = $this->determineTerCategory($ptkpStatus);

        if ($result['metode_pajak'] === 'Gross Up') {
            // Iteration loop for Gross Up
            $allowance = 0;
            for ($i = 0; $i < 5; $i++) {
                $brutoPajak = $pphWageBase + $bpjsCoPremiums + $allowance;
                $terRate = $this->getTerRate($ptkpCategory, $brutoPajak);
                $allowance = $brutoPajak * ($terRate / 100);
            }
            $result['tax_allowance'] = $allowance;
            $result['pph21'] = $allowance;
            $result['ter_rate'] = $terRate;
        } else {
            $brutoPajak = $pphWageBase + $bpjsCoPremiums;
            $terRate = $this->getTerRate($ptkpCategory, $brutoPajak);
            $result['pph21'] = $brutoPajak * ($terRate / 100);
            $result['ter_rate'] = $terRate;
        }

        return $result;
    }

    private function determineTerCategory($ptkpStatus)
    {
        $ptkpStatus = strtoupper(trim($ptkpStatus ?? 'TK/0'));
        if (in_array($ptkpStatus, ['TK/0', 'TK/1', 'K/0'])) {
            return 'A';
        }
        if (in_array($ptkpStatus, ['TK/2', 'TK/3', 'K/1', 'K/2'])) {
            return 'B';
        }
        if ($ptkpStatus === 'K/3') {
            return 'C';
        }
        return 'A';
    }

    private function getTerRate($category, $bruto)
    {
        if ($category === 'A') {
            if ($bruto <= 5400000) return 0.0;
            if ($bruto <= 5650000) return 0.25;
            if ($bruto <= 5950000) return 0.5;
            if ($bruto <= 6300000) return 0.75;
            if ($bruto <= 6750000) return 1.0;
            if ($bruto <= 7500000) return 1.25;
            if ($bruto <= 8550000) return 1.5;
            if ($bruto <= 9650000) return 1.75;
            if ($bruto <= 10950000) return 2.0;
            if ($bruto <= 12950000) return 3.0;
            if ($bruto <= 15000000) return 4.0;
            if ($bruto <= 17850000) return 5.0;
            if ($bruto <= 21050000) return 6.0;
            if ($bruto <= 24450000) return 7.0;
            if ($bruto <= 29350000) return 8.0;
            if ($bruto <= 35900000) return 9.0;
            if ($bruto <= 43850000) return 10.0;
            if ($bruto <= 54200000) return 11.0;
            if ($bruto <= 68600000) return 12.0;
            if ($bruto <= 83700000) return 13.0;
            if ($bruto <= 99600000) return 14.0;
            if ($bruto <= 165600000) return 15.0;
            if ($bruto <= 219200000) return 19.0;
            if ($bruto <= 276000000) return 20.0;
            if ($bruto <= 346500000) return 21.0;
            if ($bruto <= 439700000) return 22.0;
            if ($bruto <= 563800000) return 23.0;
            if ($bruto <= 775200000) return 24.0;
            if ($bruto <= 1121200000) return 25.0;
            if ($bruto <= 1512200000) return 26.0;
            if ($bruto <= 2000000000) return 30.0;
            return 34.0;
        } elseif ($category === 'B') {
            if ($bruto <= 6200000) return 0.0;
            if ($bruto <= 6500000) return 0.25;
            if ($bruto <= 6850000) return 0.5;
            if ($bruto <= 7300000) return 0.75;
            if ($bruto <= 7800000) return 1.0;
            if ($bruto <= 8850000) return 1.25;
            if ($bruto <= 9800000) return 1.5;
            if ($bruto <= 10950000) return 1.75;
            if ($bruto <= 12300000) return 2.0;
            if ($bruto <= 14850000) return 3.0;
            if ($bruto <= 17200000) return 4.0;
            if ($bruto <= 19550000) return 5.0;
            if ($bruto <= 22700000) return 6.0;
            if ($bruto <= 26600000) return 7.0;
            if ($bruto <= 31850000) return 8.0;
            if ($bruto <= 39400000) return 9.0;
            if ($bruto <= 48250000) return 10.0;
            if ($bruto <= 58750000) return 11.0;
            if ($bruto <= 72050000) return 12.0;
            if ($bruto <= 88750000) return 13.0;
            if ($bruto <= 107800000) return 14.0;
            if ($bruto <= 168600000) return 15.0;
            if ($bruto <= 219900000) return 19.0;
            if ($bruto <= 276300000) return 20.0;
            if ($bruto <= 346800000) return 21.0;
            if ($bruto <= 439900000) return 22.0;
            if ($bruto <= 564000000) return 23.0;
            if ($bruto <= 775400000) return 24.0;
            if ($bruto <= 1121500000) return 25.0;
            if ($bruto <= 1512500000) return 26.0;
            if ($bruto <= 2000000000) return 30.0;
            return 34.0;
        } else { // Category C
            if ($bruto <= 6600000) return 0.0;
            if ($bruto <= 6950000) return 0.25;
            if ($bruto <= 7350000) return 0.5;
            if ($bruto <= 7800000) return 0.75;
            if ($bruto <= 8300000) return 1.0;
            if ($bruto <= 9550000) return 1.25;
            if ($bruto <= 10650000) return 1.5;
            if ($bruto <= 11850000) return 1.75;
            if ($bruto <= 13600000) return 2.0;
            if ($bruto <= 16000000) return 3.0;
            if ($bruto <= 18550000) return 4.0;
            if ($bruto <= 20850000) return 5.0;
            if ($bruto <= 24550000) return 6.0;
            if ($bruto <= 28600000) return 7.0;
            if ($bruto <= 34600000) return 8.0;
            if ($bruto <= 42300000) return 9.0;
            if ($bruto <= 51600000) return 10.0;
            if ($bruto <= 62900000) return 11.0;
            if ($bruto <= 77250000) return 12.0;
            if ($bruto <= 95100000) return 13.0;
            if ($bruto <= 115400000) return 14.0;
            if ($bruto <= 179800000) return 15.0;
            if ($bruto <= 233700000) return 19.0;
            if ($bruto <= 293700000) return 20.0;
            if ($bruto <= 368500000) return 21.0;
            if ($bruto <= 467400000) return 22.0;
            if ($bruto <= 599500000) return 23.0;
            if ($bruto <= 824200000) return 24.0;
            if ($bruto <= 1192200000) return 25.0;
            if ($bruto <= 1607700000) return 26.0;
            if ($bruto <= 2126000000) return 30.0;
            return 34.0;
        }
    }

    private function resolveClientConfig($clientId, $positionName = null)
    {
        $positions = [];
        if (!empty($positionName)) {
            // Find the client-specific position and its org hierarchy
            $positions = $this->db->table('positions')
                      ->select('positions.id as position_id, departments.id as department_id, divisions.id as division_id')
                      ->join('departments', 'departments.id = positions.department_id', 'left')
                      ->join('divisions', 'divisions.id = departments.division_id', 'left')
                      ->where('positions.nama', $positionName)
                      ->where('divisions.client_id', $clientId)
                      ->get()
                      ->getResult();
        }
        
        if (!empty($positions)) {
            // 2. Try to search by position_id
            foreach ($positions as $p) {
                if ($p->position_id) {
                    $config = $this->db->table('client_payroll_configs')
                                 ->where('client_id', $clientId)
                                 ->where('position_id', $p->position_id)
                                 ->get()
                                 ->getRow();
                    if ($config && ($config->payroll_scheme_id || $config->tax_scheme_id || $config->compensation_scheme_id)) {
                        return $config;
                    }
                }
            }
            
            // 3. Fallback to department_id
            foreach ($positions as $p) {
                if ($p->department_id) {
                    $config = $this->db->table('client_payroll_configs')
                                 ->where('client_id', $clientId)
                                 ->where('department_id', $p->department_id)
                                 ->where('position_id IS NULL')
                                 ->get()
                                 ->getRow();
                    if ($config && ($config->payroll_scheme_id || $config->tax_scheme_id || $config->compensation_scheme_id)) {
                        return $config;
                    }
                }
            }
            
            // 4. Fallback to division_id
            foreach ($positions as $p) {
                if ($p->division_id) {
                    $config = $this->db->table('client_payroll_configs')
                                 ->where('client_id', $clientId)
                                 ->where('division_id', $p->division_id)
                                 ->where('department_id IS NULL')
                                 ->where('position_id IS NULL')
                                 ->get()
                                 ->getRow();
                    if ($config && ($config->payroll_scheme_id || $config->tax_scheme_id || $config->compensation_scheme_id)) {
                        return $config;
                    }
                }
            }
        }
        
        // 5. Fallback to global config (where all org columns are NULL)
        return $this->db->table('client_payroll_configs')
                  ->where('client_id', $clientId)
                  ->where('division_id IS NULL')
                  ->where('department_id IS NULL')
                  ->where('position_id IS NULL')
                  ->get()
                  ->getRow();
    }

    public function syncPKWTComponents($pkwtId, $defaultBasicSalary = null)
    {
        $pkwt = $this->db->table('pkwt')->where('id', $pkwtId)->get()->getRow();
        if (!$pkwt) {
            return;
        }

        $basicSalary = 0;
        if ($defaultBasicSalary !== null && floatval($defaultBasicSalary) > 0) {
            $basicSalary = floatval($defaultBasicSalary);
        } else {
            // Fallback to employee's current gaji_pokok in database
            $emp = $this->db->table('employees')
                            ->where('client_id', $pkwt->client_id)
                            ->where('nama', $pkwt->employee_name)
                            ->get()
                            ->getRow();
            if ($emp) {
                $basicSalary = floatval($emp->gaji_pokok);
            } else {
                // Last fallback: existing basic component
                $existingBasic = $this->db->table('pkwt_components')
                                          ->where('pkwt_id', $pkwtId)
                                          ->groupStart()
                                              ->like('nama', 'Gaji Pokok')
                                              ->orWhere('jenis_komponen', 'basic_salary')
                                          ->groupEnd()
                                          ->get()
                                          ->getRow();
                if ($existingBasic) {
                    $basicSalary = floatval($existingBasic->nilai);
                }
            }
        }

        // Clear existing pkwt components
        $this->db->table('pkwt_components')->where('pkwt_id', $pkwtId)->delete();

        // Get current active Client Scheme Config
        $config = $this->resolveClientConfig($pkwt->client_id, $pkwt->position_name);

        $hasComponents = false;
        if ($config && $config->payroll_scheme_id) {
            // Fetch components from scheme
            $components = $this->db->table('payroll_components')
                                   ->where('scheme_id', $config->payroll_scheme_id)
                                   ->get()
                                   ->getResult();

            if (!empty($components)) {
                $hasComponents = true;
                foreach ($components as $comp) {
                    $nilai = $comp->nilai;
                    if (stripos($comp->nama, 'Gaji Pokok') !== false || ($comp->jenis_komponen ?? '') === 'basic_salary') {
                        if (isset($comp->sumber_nilai) && ($comp->sumber_nilai === 'ump' || $comp->sumber_nilai === 'umk' || $comp->sumber_nilai === 'kompensasi')) {
                            $nilai = $comp->nilai;
                        } else {
                            $nilai = $basicSalary;
                        }
                    }

                    $this->db->table('pkwt_components')->insert([
                        'pkwt_id' => $pkwtId,
                        'nama' => $comp->nama,
                        'tipe' => $comp->tipe,
                        'nilai' => $nilai,
                        'is_persentase' => $comp->is_persentase,
                        'jenis_komponen' => $comp->jenis_komponen ?? 'basic_salary',
                        'sifat_kompensasi' => $comp->sifat_kompensasi ?? 'tetap',
                        'sumber_nilai' => $comp->sumber_nilai ?? 'nominal',
                        'periode' => $comp->periode ?? 'bulan',
                        'is_bpjs' => $comp->is_bpjs ?? 0,
                        'is_pph21' => $comp->is_pph21 ?? 1
                    ]);
                }
            }
        }

        if (!$hasComponents) {
            // Default Gaji Pokok component
            $this->db->table('pkwt_components')->insert([
                'pkwt_id' => $pkwtId,
                'nama' => 'Gaji Pokok',
                'tipe' => 'pendapatan',
                'nilai' => $basicSalary,
                'is_persentase' => false,
                'is_bpjs' => 1,
                'is_pph21' => 1
            ]);
        }

        // Add global compensation scheme components if configured
        $compensationSchemeId = null;
        if ($config && $config->payroll_scheme_id) {
            $payrollScheme = $this->db->table('payroll_schemes')->where('id', $config->payroll_scheme_id)->get()->getRow();
            if ($payrollScheme && !empty($payrollScheme->compensation_scheme_id)) {
                $compensationSchemeId = $payrollScheme->compensation_scheme_id;
            }
        }
        if (!$compensationSchemeId && $config && $config->compensation_scheme_id) {
            $compensationSchemeId = $config->compensation_scheme_id;
        }

        if ($compensationSchemeId) {
            $compComponents = $this->db->table('compensation_components')
                                       ->where('scheme_id', $compensationSchemeId)
                                       ->get()
                                       ->getResult();
            foreach ($compComponents as $comp) {
                // Check if this component name is already added as part of payroll scheme (to avoid duplicate)
                $dup = $this->db->table('pkwt_components')
                                ->where('pkwt_id', $pkwtId)
                                ->where('nama', $comp->nama)
                                ->get()
                                ->getRow();
                if (!$dup) {
                    $this->db->table('pkwt_components')->insert([
                        'pkwt_id' => $pkwtId,
                        'nama' => $comp->nama,
                        'tipe' => $comp->tipe,
                        'nilai' => $comp->nilai,
                        'is_persentase' => $comp->is_persentase,
                        'jenis_komponen' => $comp->jenis_komponen ?? 'kompensasi',
                        'sifat_kompensasi' => $comp->sifat_kompensasi ?? 'tetap',
                        'sumber_nilai' => $comp->sumber_nilai ?? 'nominal',
                        'periode' => $comp->periode ?? 'bulan',
                        'is_bpjs' => $comp->is_bpjs ?? 0,
                        'is_pph21' => $comp->is_pph21 ?? 1
                    ]);
                }
            }
        }
    }

    public function syncEmployeesToPKWT($clientId = null)
    {
        if (empty($clientId)) {
            return;
        }

        $activeEmployees = $this->db->table('employees')
                                     ->select('employees.nama, employees.gaji_pokok, employees.tgl_masuk, employees.tipe_perjanjian, positions.nama as position_name')
                                     ->join('positions', 'positions.id = employees.position_id', 'left')
                                     ->where('employees.client_id', $clientId)
                                     ->where('employees.status', 'Aktif')
                                     ->get()
                                     ->getResult();

        foreach ($activeEmployees as $emp) {
            // Check if PKWT record already exists for this employee
            $exists = $this->db->table('pkwt')
                               ->where('client_id', $clientId)
                               ->where('employee_name', $emp->nama)
                               ->get()
                               ->getRow();

            if (!$exists) {
                // Create PKWT record
                $tglMulai = !empty($emp->tgl_masuk) ? $emp->tgl_masuk : date('Y-m-d');
                $tglBerakhir = date('Y-m-d', strtotime('+1 year', strtotime($tglMulai)));
                
                $pkwtData = [
                    'client_id' => $clientId,
                    'employee_name' => $emp->nama,
                    'position_name' => $emp->position_name ?? 'Staff',
                    'tipe_perjanjian' => $emp->tipe_perjanjian ?? 'PKWT',
                    'start_date' => $tglMulai,
                    'end_date' => $tglBerakhir,
                    'status' => 'Active',
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                $this->db->table('pkwt')->insert($pkwtData);
                $pkwtId = $this->db->insertID();

                $this->syncPKWTComponents($pkwtId, $emp->gaji_pokok);
            } else {
                if (empty($exists->tipe_perjanjian) && !empty($emp->tipe_perjanjian)) {
                    $this->db->table('pkwt')
                             ->where('id', $exists->id)
                             ->update(['tipe_perjanjian' => $emp->tipe_perjanjian]);
                }
                // ALWAYS synchronize the PKWT components to keep it up to date!
                $this->syncPKWTComponents($exists->id, $emp->gaji_pokok);
            }
        }
    }

    public function exportExcel($periodId)
    {
        $clientId = $this->request->getGet('client_id');
        if (!$clientId) {
            return $this->fail('Client ID required');
        }

        // Fetch client name for filename
        $clientName = 'All_Clients';
        $client = $this->db->table('clients')->where('id', $clientId)->get()->getRow();
        if ($client) {
            $clientName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $client->nama);
        }

        // Fetch period name
        $periodName = 'Unknown_Period';
        $period = $this->db->table('payroll_periods')->where('id', $periodId)->get()->getRow();
        $period = $this->formatPeriodRow($period);
        if ($period) {
            $periodName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $period->nama);
        }

        $filename = "Payroll_{$clientName}_{$periodName}.csv";

        // Query payroll results joining employees to get NIK & bank details
        $query = $this->db->table('payroll_final')
                         ->select('payroll_final.*, pkwt.employee_name, pkwt.position_name, pkwt.tipe_perjanjian, employees.nik, employees.bank_name, employees.no_rekening')
                         ->join('pkwt', 'pkwt.id = payroll_final.pkwt_id')
                         ->join('employees', 'employees.nama = pkwt.employee_name', 'left')
                         ->where('payroll_final.period_id', $periodId)
                         ->where('pkwt.client_id', $clientId);

        $data = $query->get()->getResultArray();

        // Stream the CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // UTF-8 BOM for proper Excel encoding
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Define Headers
        $headers = [
            'No', 'NIK', 'Nama Karyawan', 'Tipe Perjanjian', 'Jabatan', 'Metode Pajak', 'Status PTKP',
            'Gaji Pokok', 'Tunjangan Lembur', 'Bonus Tambahan', 'Tunjangan Lainnya',
            'Total Pendapatan', 'Potongan Absensi', 'BPJS Kes (Karyawan)', 'BPJS JHT (Karyawan)',
            'BPJS JP (Karyawan)', 'PPh 21', 'Potongan Lainnya', 'Total Potongan',
            'Take Home Pay (THP)', 'Nama Bank', 'No Rekening'
        ];
        fputcsv($output, $headers);
        
        $no = 1;
        foreach ($data as $row) {
            // Compute Tunjangan Lainnya
            $lemburPay = floatval($row['lembur_pay'] ?? 0);
            $bonus = floatval($row['bonus_tambahan'] ?? 0);
            $taxAllowance = floatval($row['tax_allowance'] ?? 0);
            $totalPendapatan = floatval($row['total_pendapatan'] ?? 0);
            $gajiPokok = floatval($row['gaji_pokok'] ?? 0);
            
            $tunjanganLainnya = $totalPendapatan - ($gajiPokok + $lemburPay + $bonus + $taxAllowance);
            if ($tunjanganLainnya < 0) $tunjanganLainnya = 0;
            
            // Compute Potongan Lainnya
            $bpjsKes = floatval($row['bpjs_kes_karyawan'] ?? 0);
            $bpjsJht = floatval($row['bpjs_jht_karyawan'] ?? 0);
            $bpjsJp = floatval($row['bpjs_jp_karyawan'] ?? 0);
            $pph21 = floatval($row['pph21'] ?? 0);
            $potonganAbsen = floatval($row['potongan_absen'] ?? 0);
            $totalPotongan = floatval($row['total_potongan'] ?? 0);
            
            $taxMethod = $row['tax_method'] ?? 'Gross';
            $pajakDikurangi = ($taxMethod === 'Net') ? 0 : $pph21;
            
            $potonganLainnya = $totalPotongan - ($potonganAbsen + $bpjsKes + $bpjsJht + $bpjsJp + $pajakDikurangi);
            if ($potonganLainnya < 0) $potonganLainnya = 0;
            
            fputcsv($output, [
                $no++,
                $row['nik'] ?? '-',
                $row['employee_name'],
                $row['tipe_perjanjian'] ?? 'PKWT',
                $row['position_name'] ?? '-',
                $taxMethod,
                $row['ptkp_status'] ?? '-',
                $gajiPokok,
                $lemburPay,
                $bonus,
                $tunjanganLainnya,
                $totalPendapatan,
                $potonganAbsen,
                $bpjsKes,
                $bpjsJht,
                $bpjsJp,
                $pph21,
                $potonganLainnya,
                $totalPotongan,
                floatval($row['take_home_pay'] ?? 0),
                $row['bank_name'] ?? '-',
                $row['no_rekening'] ?? '-'
            ]);
        }
        
        fclose($output);
        exit();
    }

    private function formatPeriodRow($row)
    {
        if (!$row) return $row;
        $months = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
        
        if (is_array($row)) {
            if (!isset($row['nama']) && isset($row['bulan'], $row['tahun'])) {
                $row['nama'] = ($months[intval($row['bulan']) - 1] ?? '') . " " . $row['tahun'];
            }
            if (!isset($row['status']) && isset($row['status_cutoff'])) {
                $row['status'] = $row['status_cutoff'];
            }
            return $row;
        } else if (is_object($row)) {
            if (!isset($row->nama) && isset($row->bulan, $row->tahun)) {
                $row->nama = ($months[intval($row->bulan) - 1] ?? '') . " " . $row->tahun;
            }
            if (!isset($row->status) && isset($row->status_cutoff)) {
                $row->status = $row->status_cutoff;
            }
            return $row;
        }
        return $row;
    }

    public function getSettings()
    {
        $settings = $this->db->table('system_settings')->get()->getResultArray();
        return $this->respond($settings);
    }

    public function saveSettings()
    {
        $data = $this->request->getJSON(true);
        if (!is_array($data)) {
            return $this->fail('Invalid JSON data format');
        }

        foreach ($data as $key => $val) {
            $exists = $this->db->table('system_settings')->where('setting_key', $key)->get()->getRow();
            if ($exists) {
                $this->db->table('system_settings')->where('setting_key', $key)->update([
                    'setting_value' => strval($val),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            } else {
                $this->db->table('system_settings')->insert([
                    'setting_key' => $key,
                    'setting_value' => strval($val)
                ]);
            }
        }

        $this->logActivity("Memperbarui konfigurasi sistem");
        return $this->respond(['message' => 'Settings saved successfully']);
    }

    // --- SHIFT SCHEMES ---
    public function getShiftSchemes()
    {
        $schemes = $this->db->table('shift_schemes')->get()->getResultArray();
        return $this->respond($schemes);
    }

    public function createShiftScheme()
    {
        $data = $this->request->getJSON(true);
        if (empty($data['name']) || empty($data['start_time']) || empty($data['end_time'])) {
            return $this->failValidationErrors('Name, start time, dan end time wajib diisi');
        }

        $insertData = [
            'name' => $data['name'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'duration' => isset($data['duration']) ? floatval($data['duration']) : 8.0,
            'break_start_time' => isset($data['break_start_time']) ? $data['break_start_time'] : null,
            'break_end_time' => isset($data['break_end_time']) ? $data['break_end_time'] : null,
            'break_duration' => isset($data['break_duration']) ? floatval($data['break_duration']) : 0.0,

            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->table('shift_schemes')->insert($insertData);
        $this->logActivity("Membuat skema shift baru: " . $data['name']);
        return $this->respondCreated(['message' => 'Skema shift berhasil dibuat']);
    }

    public function updateShiftScheme($id)
    {
        $data = $this->request->getJSON(true);
        if (empty($data['name']) || empty($data['start_time']) || empty($data['end_time'])) {
            return $this->failValidationErrors('Name, start time, dan end time wajib diisi');
        }

        $updateData = [
            'name' => $data['name'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'duration' => isset($data['duration']) ? floatval($data['duration']) : 8.0,
            'break_start_time' => isset($data['break_start_time']) ? $data['break_start_time'] : null,
            'break_end_time' => isset($data['break_end_time']) ? $data['break_end_time'] : null,
            'break_duration' => isset($data['break_duration']) ? floatval($data['break_duration']) : 0.0,

            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->table('shift_schemes')->where('id', $id)->update($updateData);
        $this->logActivity("Mengubah skema shift ID: " . $id);
        return $this->respond(['message' => 'Skema shift berhasil diupdate']);
    }

    public function deleteShiftScheme($id)
    {
        $referenced = $this->db->table('employee_shifts')->where('shift_scheme_id', $id)->get()->getRow();
        if ($referenced) {
            return $this->fail('Skema shift ini sedang digunakan oleh karyawan dan tidak dapat dihapus');
        }

        $this->db->table('shift_schemes')->where('id', $id)->delete();
        $this->logActivity("Menghapus skema shift ID: " . $id);
        return $this->respondDeleted(['message' => 'Skema shift berhasil dihapus']);
    }

    // --- EMPLOYEE SHIFTS ---
    public function getEmployeeShifts()
    {
        $employeeId = $this->request->getGet('employee_id');
        $builder = $this->db->table('employee_shifts')
            ->select('employee_shifts.*, shift_schemes.name as shift_name, shift_schemes.start_time, shift_schemes.end_time, employees.nama as employee_name')
            ->join('shift_schemes', 'shift_schemes.id = employee_shifts.shift_scheme_id', 'inner')
            ->join('employees', 'employees.id = employee_shifts.employee_id', 'inner');

        if (!empty($employeeId)) {
            $builder->where('employee_shifts.employee_id', intval($employeeId));
        }

        $shifts = $builder->orderBy('employee_shifts.start_date', 'DESC')->get()->getResultArray();
        return $this->respond($shifts);
    }

    public function assignEmployeeShift()
    {
        $data = $this->request->getJSON(true);
        if (empty($data['employee_id']) || empty($data['shift_scheme_id']) || empty($data['start_date'])) {
            return $this->failValidationErrors('Employee ID, Shift Scheme ID, dan Start Date wajib diisi');
        }

        $empId = intval($data['employee_id']);
        $shiftId = intval($data['shift_scheme_id']);
        $startDate = $data['start_date'];
        $endDate = !empty($data['end_date']) ? $data['end_date'] : null;

        $yesterday = date('Y-m-d', strtotime($startDate . ' -1 day'));

        $activeShifts = $this->db->table('employee_shifts')
            ->where('employee_id', $empId)
            ->where("(end_date IS NULL OR end_date >= '{$startDate}')")
            ->get()->getResultArray();

        foreach ($activeShifts as $active) {
            if ($active['start_date'] < $startDate) {
                $this->db->table('employee_shifts')
                    ->where('id', $active['id'])
                    ->update([
                        'end_date' => $yesterday,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
            } else {
                $this->db->table('employee_shifts')
                    ->where('id', $active['id'])
                    ->delete();
            }
        }

        $insertData = [
            'employee_id' => $empId,
            'shift_scheme_id' => $shiftId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->table('employee_shifts')->insert($insertData);
        $this->logActivity("Menugaskan skema shift ke karyawan ID: " . $empId);
        return $this->respondCreated(['message' => 'Shift berhasil ditugaskan ke karyawan']);
    }

    public function deleteEmployeeShift($id)
    {
        $this->db->table('employee_shifts')->where('id', $id)->delete();
        $this->logActivity("Menghapus alokasi shift karyawan ID: " . $id);
        return $this->respondDeleted(['message' => 'Alokasi shift berhasil dihapus']);
    }
}

