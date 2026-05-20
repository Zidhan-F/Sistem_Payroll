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
        
        $query = $this->db->table('minimum_wages')->where('tipe', $tipe);
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
        return $this->respond($clients);
    }

    public function createClient()
    {
        $data = $this->request->getJSON(true);
        $this->db->table('clients')->insert($data);
        $this->logActivity("Membuat klien baru: " . ($data['nama'] ?? ''));
        return $this->respondCreated(['message' => 'Klien berhasil ditambahkan']);
    }

    public function updateClient($id)
    {
        $data = $this->request->getJSON(true);
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
        $data = $this->request->getJSON(true);
        $this->db->table('payroll_schemes')->insert($data);
        $this->logActivity("Membuat skema payroll baru: " . ($data['nama'] ?? ''));
        return $this->respondCreated(['message' => 'Skema berhasil ditambahkan']);
    }

    public function updatePayrollScheme($id)
    {
        $data = $this->request->getJSON(true);
        $this->db->table('payroll_schemes')->where('id', $id)->update($data);
        $this->logActivity("Mengupdate skema payroll ID: " . $id . " (" . ($data['nama'] ?? '') . ")");
        return $this->respond(['message' => 'Skema berhasil diupdate']);
    }

    public function deletePayrollScheme($id)
    {
        $scheme = $this->db->table('payroll_schemes')->where('id', $id)->get()->getRow();
        $schemeName = $scheme ? $scheme->nama : 'Unknown';
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
        return $this->respondCreated(['message' => 'Skema pajak berhasil ditambahkan']);
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
        
        $componentData = [
            'scheme_id' => $schemeId,
            'nama' => $requestData['nama'] ?? '',
            'tipe' => $requestData['tipe'] ?? 'pendapatan',
            'nilai' => $requestData['nilai'] ?? 0,
            'is_persentase' => isset($requestData['is_persentase']) ? intval($requestData['is_persentase']) : 0,
            'jenis_komponen' => ($requestData['nama'] === 'Basic Salary' ? 'basic_salary' : ($requestData['sifat_kompensasi'] === 'tidak_tetap' ? 'tidak_tetap' : 'tetap')),
            'sumber_nilai' => $requestData['sumber_nilai'] ?? 'nominal',
            'periode' => $requestData['periode'] ?? 'bulan',
            'sifat_kompensasi' => $requestData['sifat_kompensasi'] ?? 'tetap'
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
        
        $componentData = [
            'nama' => $requestData['nama'] ?? '',
            'tipe' => $requestData['tipe'] ?? 'pendapatan',
            'nilai' => $requestData['nilai'] ?? 0,
            'is_persentase' => isset($requestData['is_persentase']) ? intval($requestData['is_persentase']) : 0,
            'jenis_komponen' => ($requestData['nama'] === 'Basic Salary' ? 'basic_salary' : ($requestData['sifat_kompensasi'] === 'tidak_tetap' ? 'tidak_tetap' : 'tetap')),
            'sumber_nilai' => $requestData['sumber_nilai'] ?? 'nominal',
            'periode' => $requestData['periode'] ?? 'bulan',
            'sifat_kompensasi' => $requestData['sifat_kompensasi'] ?? 'tetap'
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
                            ->select('clients.id as client_id, clients.nama as client_name, client_payroll_configs.id as setup_id, payroll_schemes.nama as payroll_scheme_name, tax_schemes.nama as tax_scheme_name, compensation_schemes.nama as compensation_scheme_name, client_payroll_configs.pay_date, client_payroll_configs.cutoff_start, client_payroll_configs.cutoff_end, client_payroll_configs.payroll_scheme_id, client_payroll_configs.tax_scheme_id, client_payroll_configs.compensation_scheme_id, client_payroll_configs.payroll_type, client_payroll_configs.minimum_wage_id, client_payroll_configs.custom_nominal, minimum_wages.nama_daerah as minimum_wage_region, minimum_wages.nominal as minimum_wage_nominal')
                            ->join('client_payroll_configs', 'client_payroll_configs.client_id = clients.id', 'left')
                            ->join('payroll_schemes', 'payroll_schemes.id = client_payroll_configs.payroll_scheme_id', 'left')
                            ->join('tax_schemes', 'tax_schemes.id = client_payroll_configs.tax_scheme_id', 'left')
                            ->join('compensation_schemes', 'compensation_schemes.id = client_payroll_configs.compensation_scheme_id', 'left')
                            ->join('minimum_wages', 'minimum_wages.id = client_payroll_configs.minimum_wage_id', 'left')
                            ->get()
                            ->getResult();
        return $this->respond($configs);
    }

    public function saveClientConfig()
    {
        $data = $this->request->getJSON(true);
        $clientId = $data['client_id'];
        
        // Check if exists
        $existing = $this->db->table('client_payroll_configs')->where('client_id', $clientId)->get()->getRow();
        
        if ($existing) {
            $this->db->table('client_payroll_configs')->where('client_id', $clientId)->update($data);
        } else {
            $this->db->table('client_payroll_configs')->insert($data);
        }
        
        return $this->respond(['message' => 'Konfigurasi payroll klien berhasil disimpan']);
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

        // 2. Get Client Scheme Config
        $config = $this->db->table('client_payroll_configs')
                           ->where('client_id', $data['client_id'])
                           ->get()
                           ->getRow();

        if ($config && $config->payroll_scheme_id) {
            // 3. Fetch components from scheme
            $components = $this->db->table('payroll_components')
                                   ->where('scheme_id', $config->payroll_scheme_id)
                                   ->get()
                                   ->getResult();

            foreach ($components as $comp) {
                $nilai = $comp->nilai;
                // If this is a basic salary component, use the manual input
                if (stripos($comp->nama, 'Gaji Pokok') !== false) {
                    $nilai = $basicSalary;
                }

                $this->db->table('pkwt_components')->insert([
                    'pkwt_id' => $pkwtId,
                    'nama' => $comp->nama,
                    'tipe' => $comp->tipe,
                    'nilai' => $nilai,
                    'is_persentase' => $comp->is_persentase
                ]);
            }
        } else {
            // If no scheme, at least insert the basic salary as a component
            $this->db->table('pkwt_components')->insert([
                'pkwt_id' => $pkwtId,
                'nama' => 'Gaji Pokok',
                'tipe' => 'pendapatan',
                'nilai' => $basicSalary,
                'is_persentase' => false
            ]);
        }

        // 4. Fetch components from global compensation scheme (if configured)
        if ($config && $config->compensation_scheme_id) {
            $compComponents = $this->db->table('compensation_components')
                                       ->where('scheme_id', $config->compensation_scheme_id)
                                       ->get()
                                       ->getResult();
            foreach ($compComponents as $comp) {
                $this->db->table('pkwt_components')->insert([
                    'pkwt_id' => $pkwtId,
                    'nama' => $comp->nama,
                    'tipe' => $comp->tipe,
                    'nilai' => $comp->nilai,
                    'is_persentase' => $comp->is_persentase,
                    'jenis_komponen' => $comp->jenis_komponen ?? 'kompensasi',
                    'sifat_kompensasi' => $comp->sifat_kompensasi ?? 'tetap',
                    'sumber_nilai' => $comp->sumber_nilai ?? 'nominal',
                    'periode' => $comp->periode ?? 'bulan'
                ]);
            }
        }

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

    // --- PERIODS ---
    public function getPeriods()
    {
        $data = $this->db->table('payroll_periods')->orderBy('tahun', 'DESC')->orderBy('bulan', 'DESC')->get()->getResult();
        return $this->respond($data);
    }

    public function createPeriod()
    {
        $data = $this->request->getJSON(true);
        $months = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
        $data['nama'] = $months[$data['bulan'] - 1] . " " . $data['tahun'];
        
        $this->db->table('payroll_periods')->insert($data);
        return $this->respondCreated(['message' => 'Periode baru berhasil dibuka']);
    }

    // --- ATTENDANCE / CUT-OFF ---
    public function getAttendance($periodId)
    {
        $clientId = $this->request->getGet('client_id');
        // Get all PKWT and their attendance for this period
        $query = $this->db->table('pkwt')
                          ->select('pkwt.id as pkwt_id, pkwt.employee_name, payroll_attendance.hari_kerja, payroll_attendance.jam_lembur, payroll_attendance.potongan_absensi, payroll_attendance.bonus_tambahan')
                          ->join('payroll_attendance', "payroll_attendance.pkwt_id = pkwt.id AND payroll_attendance.period_id = $periodId", 'left');
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

    // --- GENERATE PAYROLL ---
    public function generatePayroll($periodId)
    {
        $clientId = $this->request->getGet('client_id');
        // 1. Get all PKWTs
        $query = $this->db->table('pkwt');
        if ($clientId) {
            $query->where('client_id', $clientId);
        }
        $pkwts = $query->get()->getResult();

        foreach ($pkwts as $pkwt) {
            // Load client absence config
            $absenceConfig = $this->db->table('client_absence_configs')->where('client_id', $pkwt->client_id)->get()->getRow();
            $isProrate = ($absenceConfig && $absenceConfig->prorate == 1);
            $isAbsenTidakPotong = ($absenceConfig && $absenceConfig->absen_tidak_potong == 1);

            // 2. Get Fixed Components from PKWT
            $components = $this->db->table('pkwt_components')->where('pkwt_id', $pkwt->id)->get()->getResult();
            
            // 3. Get Attendance Data
            $att = $this->db->table('payroll_attendance')
                            ->where('period_id', $periodId)
                            ->where('pkwt_id', $pkwt->id)
                            ->get()->getRow();
            
            // 4. Resolve Employee / Client UMR (Minimum Wage)
            $emp = $this->db->table('employees')
                            ->select('employees.*, minimum_wages.nominal as umr_nominal, minimum_wages.id as mw_id')
                            ->join('minimum_wages', 'minimum_wages.id = employees.minimum_wage_id', 'left')
                            ->where('employees.nama', $pkwt->employee_name)
                            ->get()
                            ->getRow();
            
            $minimumWage = 0;
            $mwId = null;
            if ($emp && isset($emp->umr_nominal) && $emp->umr_nominal > 0) {
                $minimumWage = floatval($emp->umr_nominal);
                $mwId = $emp->mw_id;
            } else {
                // Fallback to client config's minimum wage
                $clientConfig = $this->db->table('client_payroll_configs')
                                         ->select('client_payroll_configs.*, minimum_wages.nominal as umr_nominal, minimum_wages.id as mw_id')
                                         ->join('minimum_wages', 'minimum_wages.id = client_payroll_configs.minimum_wage_id', 'left')
                                         ->where('client_id', $pkwt->client_id)
                                         ->get()
                                         ->getRow();
                if ($clientConfig && isset($clientConfig->umr_nominal) && $clientConfig->umr_nominal > 0) {
                    $minimumWage = floatval($clientConfig->umr_nominal);
                    $mwId = $clientConfig->mw_id;
                }
            }

            // Resolve UMP and UMK values
            $resolvedWages = $this->resolveUmpUmk($mwId);
            $umpWageValue = $resolvedWages['ump'];
            $umkWageValue = $resolvedWages['umk'];

            // 5. First pass: Find and calculate Gaji Pokok (Basic Salary)
            $gajiPokok = 0;
            foreach ($components as $comp) {
                $isBasicSalary = false;
                if (isset($comp->jenis_komponen) && $comp->jenis_komponen === 'basic_salary') {
                    $isBasicSalary = true;
                } elseif (stripos($comp->nama, 'Gaji Pokok') !== false) {
                    $isBasicSalary = true;
                }

                if ($isBasicSalary) {
                    $base_nilai = floatval($comp->nilai);
                    
                    // Check source
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
                    if (isset($comp->periode)) {
                        if ($comp->periode === 'hari') {
                            $days = ($att && isset($att->hari_kerja)) ? intval($att->hari_kerja) : 0;
                            $base_nilai = $base_nilai * $days;
                        } elseif ($comp->periode === 'minggu') {
                            $base_nilai = $base_nilai * 4;
                        } elseif ($comp->periode === 'tahun') {
                            $base_nilai = $base_nilai / 12;
                        } else {
                            // bulanan
                            if ($isProrate) {
                                $days = ($att && isset($att->hari_kerja)) ? intval($att->hari_kerja) : 0;
                                $base_nilai = $base_nilai * ($days / 21);
                            }
                        }
                    }
                    
                    $gajiPokok = $base_nilai;
                    break; // Assume only one basic salary component
                }
            }

            if ($gajiPokok <= 0 && $emp && isset($emp->gaji_pokok)) {
                $gajiPokok = floatval($emp->gaji_pokok);
                if ($isProrate) {
                    $days = ($att && isset($att->hari_kerja)) ? intval($att->hari_kerja) : 0;
                    $gajiPokok = $gajiPokok * ($days / 21);
                }
            }

            // 6. Second pass: Calculate all components
            $totalPendapatan = 0;
            $totalPotongan = 0;

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
                    if (isset($comp->jenis_komponen) && !empty($comp->jenis_komponen)) {
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
                            $days = ($att && isset($att->hari_kerja)) ? intval($att->hari_kerja) : 0;
                            $nilai = $base_nilai * $days;
                        } elseif ($comp->periode === 'minggu') {
                            $nilai = $base_nilai * 4;
                        } elseif ($comp->periode === 'tahun') {
                            $nilai = $base_nilai / 12;
                        } else {
                            // bulanan
                            if ($isProrate) {
                                $days = ($att && isset($att->hari_kerja)) ? intval($att->hari_kerja) : 0;
                                $nilai = $base_nilai * ($days / 21);
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
                } else {
                    $totalPotongan += $nilai;
                }
            }

            // 7. Add Variable Data from Attendance
            if ($att) {
                if (!$isAbsenTidakPotong) {
                    $potongan_absen = floatval($att->potongan_absensi);
                    $nominalPotongan = ($absenceConfig && isset($absenceConfig->nominal_potongan)) ? floatval($absenceConfig->nominal_potongan) : 0;
                    if ($nominalPotongan > 0 && $potongan_absen == 0) {
                        $missingDays = 21 - intval($att->hari_kerja);
                        if ($missingDays > 0) {
                            $potongan_absen = $missingDays * $nominalPotongan;
                        }
                    }
                    $totalPotongan += $potongan_absen;
                }
                $totalPendapatan += $att->bonus_tambahan;
                
                // Simplified Overtime Calculation (e.g., 20.000 per hour)
                $overtimePay = $att->jam_lembur * 20000; 
                $totalPendapatan += $overtimePay;
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
                'status_approval' => 'Pending'
            ];

            if ($existingFinal) {
                $this->db->table('payroll_final')->where('id', $existingFinal->id)->update($finalData);
            } else {
                $this->db->table('payroll_final')->insert($finalData);
            }
        }

        $period = $this->db->table('payroll_periods')->where('id', $periodId)->get()->getRow();
        $periodName = $period ? $period->nama : "ID: $periodId";
        $this->logActivity("Men-generate payroll untuk periode: " . $periodName);
        return $this->respond(['message' => 'Gaji bulanan berhasil di-generate untuk periode ini']);
    }

    public function getPayrollResults($periodId)
    {
        $clientId = $this->request->getGet('client_id');
        $query = $this->db->table('payroll_final')
                         ->select('payroll_final.*, pkwt.employee_name')
                         ->join('pkwt', 'pkwt.id = payroll_final.pkwt_id')
                         ->where('period_id', $periodId);
        if ($clientId) {
            $query->where('pkwt.client_id', $clientId);
        }
        $data = $query->get()->getResult();
        return $this->respond($data);
    }

    public function approvePayroll($id)
    {
        $username = $this->request->getHeaderLine('X-User-Action') ?: 'Admin';
        $this->db->table('payroll_final')->where('id', $id)->update([
            'status_approval' => 'Approved',
            'approved_by' => $username
        ]);
        
        $final = $this->db->table('payroll_final')
                          ->select('payroll_final.*, pkwt.employee_name')
                          ->join('pkwt', 'pkwt.id = payroll_final.pkwt_id')
                          ->where('payroll_final.id', $id)
                          ->get()
                          ->getRow();
        $employeeName = $final ? $final->employee_name : 'Unknown';
        $this->logActivity("Menyetujui payroll karyawan: " . $employeeName . " (Payroll ID: " . $id . ")");
        return $this->respond(['message' => 'Gaji telah disetujui']);
    }

    public function getSlipDetails($id)
    {
        // Get Final Result
        $final = $this->db->table('payroll_final')
                          ->select('payroll_final.*, pkwt.employee_name, pkwt.position_name, pkwt.client_id, payroll_periods.nama as period_name, clients.nama as client_name')
                          ->join('pkwt', 'pkwt.id = payroll_final.pkwt_id')
                          ->join('payroll_periods', 'payroll_periods.id = payroll_final.period_id')
                          ->join('clients', 'clients.id = pkwt.client_id')
                          ->where('payroll_final.id', $id)
                          ->get()
                          ->getRowArray();
        
        if (!$final) return $this->failNotFound('Data tidak ditemukan');

        // Get absence config
        $absenceConfig = $this->db->table('client_absence_configs')->where('client_id', $final['client_id'])->get()->getRow();
        $isProrate = ($absenceConfig && $absenceConfig->prorate == 1);
        $isAbsenTidakPotong = ($absenceConfig && $absenceConfig->absen_tidak_potong == 1);

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
                        ->select('employees.*, minimum_wages.nominal as umr_nominal, minimum_wages.id as mw_id')
                        ->join('minimum_wages', 'minimum_wages.id = employees.minimum_wage_id', 'left')
                        ->where('employees.nama', $final['employee_name'])
                        ->get()
                        ->getRow();
        
        $minimumWage = 0;
        $mwId = null;
        if ($emp && isset($emp->umr_nominal) && $emp->umr_nominal > 0) {
            $minimumWage = floatval($emp->umr_nominal);
            $mwId = $emp->mw_id;
        } else {
            // Fallback to client config's minimum wage
            $clientConfig = $this->db->table('client_payroll_configs')
                                     ->select('client_payroll_configs.*, minimum_wages.nominal as umr_nominal, minimum_wages.id as mw_id')
                                     ->join('minimum_wages', 'minimum_wages.id = client_payroll_configs.minimum_wage_id', 'left')
                                     ->where('client_id', $final['client_id'])
                                     ->get()
                                     ->getRow();
            if ($clientConfig && isset($clientConfig->umr_nominal) && $clientConfig->umr_nominal > 0) {
                $minimumWage = floatval($clientConfig->umr_nominal);
                $mwId = $clientConfig->mw_id;
            }
        }

        // Resolve UMP and UMK values
        $resolvedWages = $this->resolveUmpUmk($mwId);
        $umpWageValue = $resolvedWages['ump'];
        $umkWageValue = $resolvedWages['umk'];

        // 2. First pass: Find and calculate Gaji Pokok (Basic Salary)
        $gajiPokok = 0;
        foreach ($fixed as $comp) {
            $isBasicSalary = false;
            if (isset($comp['jenis_komponen']) && $comp['jenis_komponen'] === 'basic_salary') {
                $isBasicSalary = true;
            } elseif (stripos($comp['nama'], 'Gaji Pokok') !== false) {
                $isBasicSalary = true;
            }

            if ($isBasicSalary) {
                $base_nilai = floatval($comp['nilai']);
                
                // Check source
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
                            $base_nilai = $base_nilai * ($days / 21);
                        }
                    }
                }
                
                $gajiPokok = $base_nilai;
                break; // Assume only one basic salary component
            }
        }

        if ($gajiPokok <= 0 && $emp && isset($emp->gaji_pokok)) {
            $gajiPokok = floatval($emp->gaji_pokok);
            if ($isProrate) {
                $days = ($att && isset($att['hari_kerja'])) ? intval($att['hari_kerja']) : 0;
                $gajiPokok = $gajiPokok * ($days / 21);
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
                $nilai = $gajiPokok;
            } else {
                if (isset($comp['jenis_komponen']) && !empty($comp['jenis_komponen'])) {
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
                        if ($isProrate) {
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
                        $nilai = $gajiPokok * ($nilai / 100);
                    }
                }
            }

            if ($comp['tipe'] === 'pendapatan') {
                $earnings[] = ['nama' => $comp['nama'], 'nilai' => $nilai];
            } else {
                $deductions[] = ['nama' => $comp['nama'], 'nilai' => $nilai];
            }
        }

        if ($att) {
            if ($att['jam_lembur'] > 0) $earnings[] = ['nama' => 'Lembur', 'nilai' => $att['jam_lembur'] * 20000];
            if ($att['bonus_tambahan'] > 0) $earnings[] = ['nama' => 'Bonus/Lainnya', 'nilai' => $att['bonus_tambahan']];
            
            $potongan_absen = floatval($att['potongan_absensi']);
            if (!$isAbsenTidakPotong) {
                $nominalPotongan = ($absenceConfig && isset($absenceConfig->nominal_potongan)) ? floatval($absenceConfig->nominal_potongan) : 0;
                if ($nominalPotongan > 0 && $potongan_absen == 0) {
                    $missingDays = 21 - intval($att['hari_kerja']);
                    if ($missingDays > 0) {
                        $potongan_absen = $missingDays * $nominalPotongan;
                    }
                }
                if ($potongan_absen > 0) {
                    $deductions[] = ['nama' => 'Potongan Absen', 'nilai' => $potongan_absen];
                }
            }
        }

        return $this->respond([
            'info' => $final,
            'earnings' => $earnings,
            'deductions' => $deductions
        ]);
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
            'action' => $action,
            'user_action' => $username,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    protected function resolveUmpUmk($minimumWageId, $tahun = null)
    {
        $res = ['ump' => 0, 'umk' => 0];
        if (!$minimumWageId) return $res;

        $currentWage = $this->db->table('minimum_wages')->where('id', $minimumWageId)->get()->getRow();
        if (!$currentWage) return $res;

        $year = $tahun ?: $currentWage->tahun ?: date('Y');

        if ($currentWage->tipe === 'UMP') {
            $res['ump'] = floatval($currentWage->nominal);
            // Fallback UMK to the same value
            $res['umk'] = floatval($currentWage->nominal);
        } else if ($currentWage->tipe === 'UMK') {
            $res['umk'] = floatval($currentWage->nominal);
            
            // Find corresponding UMP for the province
            $provinceName = $currentWage->provinsi ?: $currentWage->nama_daerah;
            $umpWage = $this->db->table('minimum_wages')
                                ->where('tipe', 'UMP')
                                ->where('tahun', $year)
                                ->groupStart()
                                    ->where('nama_daerah', $provinceName)
                                    ->orWhere('provinsi', $provinceName)
                                ->groupEnd()
                                ->get()
                                ->getRow();
            if ($umpWage) {
                $res['ump'] = floatval($umpWage->nominal);
            } else {
                $res['ump'] = floatval($currentWage->nominal); // fallback
            }
        }
        return $res;
    }
}
