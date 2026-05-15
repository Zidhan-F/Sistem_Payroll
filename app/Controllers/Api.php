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
        $data = $this->db->table('minimum_wages')->where('tipe', $tipe)->get()->getResult();
        return $this->respond($data);
    }

    public function saveMinimumWages()
    {
        $data = $this->request->getJSON(true);
        // If it's a batch upload, we might handle multiple rows
        if (isset($data['items'])) {
            foreach ($data['items'] as $item) {
                $this->db->table('minimum_wages')->insert($item);
            }
        } else {
            $this->db->table('minimum_wages')->insert($data);
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
        return $this->respondCreated(['message' => 'Klien berhasil ditambahkan']);
    }

    public function updateClient($id)
    {
        $data = $this->request->getJSON(true);
        $this->db->table('clients')->where('id', $id)->update($data);
        return $this->respond(['message' => 'Klien berhasil diupdate']);
    }

    public function deleteClient($id)
    {
        $this->db->table('clients')->where('id', $id)->delete();
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
        return $this->respondCreated(['message' => 'Skema berhasil ditambahkan']);
    }

    public function updatePayrollScheme($id)
    {
        $data = $this->request->getJSON(true);
        $this->db->table('payroll_schemes')->where('id', $id)->update($data);
        return $this->respond(['message' => 'Skema berhasil diupdate']);
    }

    public function deletePayrollScheme($id)
    {
        $this->db->table('payroll_schemes')->where('id', $id)->delete();
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

    // --- CLIENT PAYROLL CONFIGS ---
    public function getClientConfigs()
    {
        $configs = $this->db->table('clients')
                            ->select('clients.id as client_id, clients.nama as client_name, client_payroll_configs.id as setup_id, payroll_schemes.nama as payroll_scheme_name, tax_schemes.nama as tax_scheme_name, client_payroll_configs.pay_date, client_payroll_configs.cutoff_start, client_payroll_configs.cutoff_end')
                            ->join('client_payroll_configs', 'client_payroll_configs.client_id = clients.id', 'left')
                            ->join('payroll_schemes', 'payroll_schemes.id = client_payroll_configs.payroll_scheme_id', 'left')
                            ->join('tax_schemes', 'tax_schemes.id = client_payroll_configs.tax_scheme_id', 'left')
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
        $data = $this->db->table('pkwt')
                         ->select('pkwt.*, clients.nama as client_name')
                         ->join('clients', 'clients.id = pkwt.client_id')
                         ->get()
                         ->getResultArray();
        
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

        return $this->respondCreated(['message' => 'PKWT berhasil dibuat dan gaji telah tergenerate']);
    }

    public function deletePKWT($id)
    {
        $this->db->table('pkwt')->where('id', $id)->delete();
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
        // Get all PKWT and their attendance for this period
        $pkwts = $this->db->table('pkwt')
                          ->select('pkwt.id as pkwt_id, pkwt.employee_name, payroll_attendance.hari_kerja, payroll_attendance.jam_lembur, payroll_attendance.potongan_absensi, payroll_attendance.bonus_tambahan')
                          ->join('payroll_attendance', "payroll_attendance.pkwt_id = pkwt.id AND payroll_attendance.period_id = $periodId", 'left')
                          ->get()
                          ->getResult();
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
        // 1. Get all PKWTs
        $pkwts = $this->db->table('pkwt')->get()->getResult();

        foreach ($pkwts as $pkwt) {
            // 2. Get Fixed Components from PKWT
            $components = $this->db->table('pkwt_components')->where('pkwt_id', $pkwt->id)->get()->getResult();
            
            // 3. Get Attendance Data
            $att = $this->db->table('payroll_attendance')
                            ->where('period_id', $periodId)
                            ->where('pkwt_id', $pkwt->id)
                            ->get()->getRow();
            
            $totalPendapatan = 0;
            $totalPotongan = 0;

            foreach ($components as $comp) {
                if ($comp->tipe === 'pendapatan') {
                    $totalPendapatan += $comp->nilai;
                } else {
                    $totalPotongan += $comp->nilai;
                }
            }

            // 4. Add Variable Data from Attendance
            if ($att) {
                $totalPotongan += $att->potongan_absensi;
                $totalPendapatan += $att->bonus_tambahan;
                
                // Simplified Overtime Calculation (e.g., 20.000 per hour)
                $overtimePay = $att->jam_lembur * 20000; 
                $totalPendapatan += $overtimePay;
            }

            $thp = $totalPendapatan - $totalPotongan;

            // 5. Save to payroll_final
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

        return $this->respond(['message' => 'Gaji bulanan berhasil di-generate untuk periode ini']);
    }

    public function getPayrollResults($periodId)
    {
        $data = $this->db->table('payroll_final')
                         ->select('payroll_final.*, pkwt.employee_name')
                         ->join('pkwt', 'pkwt.id = payroll_final.pkwt_id')
                         ->where('period_id', $periodId)
                         ->get()
                         ->getResult();
        return $this->respond($data);
    }

    public function approvePayroll($id)
    {
        $this->db->table('payroll_final')->where('id', $id)->update([
            'status_approval' => 'Approved',
            'approved_by' => 'Admin'
        ]);
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
        
        $earnings = [];
        $deductions = [];

        foreach ($fixed as $f) {
            if ($f['tipe'] === 'pendapatan') $earnings[] = ['nama' => $f['nama'], 'nilai' => $f['nilai']];
            else $deductions[] = ['nama' => $f['nama'], 'nilai' => $f['nilai']];
        }

        if ($att) {
            if ($att['jam_lembur'] > 0) $earnings[] = ['nama' => 'Lembur', 'nilai' => $att['jam_lembur'] * 20000];
            if ($att['bonus_tambahan'] > 0) $earnings[] = ['nama' => 'Bonus/Lainnya', 'nilai' => $att['bonus_tambahan']];
            if ($att['potongan_absensi'] > 0) $deductions[] = ['nama' => 'Potongan Absen', 'nilai' => $att['potongan_absensi']];
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
        $employees = $this->db->table('positions')
                              ->select('positions.*, departments.nama as department_name, divisions.nama as division_name')
                              ->join('departments', 'departments.id = positions.department_id')
                              ->join('divisions', 'divisions.id = departments.division_id')
                              ->get()
                              ->getResult();
        return $this->respond($employees);
    }
}
