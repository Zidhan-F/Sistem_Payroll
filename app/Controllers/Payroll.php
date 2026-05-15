<?php

namespace App\Controllers;

use App\Models\PayrollModel;
use App\Models\PayrollDetailModel;
use App\Models\EmployeeModel;
use App\Models\AttendanceModel;
use App\Models\ClientSchemaModel;
use App\Models\PayrollPeriodModel;
use App\Models\PayrollComponentModel;
use App\Models\PayrollPeriodCheckModel;
use App\Models\ContractModel;
use CodeIgniter\RESTful\ResourceController;

class Payroll extends ResourceController
{
    protected $modelName = 'App\Models\PayrollModel';
    protected $format    = 'json';

    /**
     * Get payroll status for a specific period & client
     */
    public function getStatus()
    {
        $bulan = $this->request->getGet('bulan');
        $tahun = $this->request->getGet('tahun');
        $clientId = $this->request->getGet('client_id');

        return $this->respond($this->model
                    ->join('employees', 'employees.id = payrolls.employee_id')
                    ->where('payrolls.bulan', $bulan)
                    ->where('payrolls.tahun', $tahun)
                    ->where('employees.client_id', $clientId)
                    ->select('payrolls.*')
                    ->findAll());
    }

    /**
     * Process bulk payroll — Generate Gaji Bulanan
     * Step: Input Cut Off → Generate Gaji
     */
    public function processBulk()
    {
        $json = $this->request->getJSON(true);
        $clientId = $json['client_id'];
        $bulan = $json['bulan'];
        $tahun = $json['tahun'];
        $dataKaryawan = $json['data']; // Array of {employee_id, hadir, alpa, sakit, lembur}

        // 1. Ambil Skema Klien
        $schemaModel = new ClientSchemaModel();
        $schema = $schemaModel->where('client_id', $clientId)->first();
        
        $bpjsKesRate = $schema ? ($schema['bpjs_kes_percent'] / 100) : 0.01;
        $bpjsJhtRate = $schema ? ($schema['bpjs_jht_percent'] / 100) : 0.02;
        $taxMethod   = $schema ? $schema['tax_method'] : 'Gross';
        $otRateSchema = $schema ? $schema['overtime_rate_per_hour'] : 0;

        // 2. Ambil Komponen Payroll Custom
        $compModel = new PayrollComponentModel();
        $components = $compModel->getByClient($clientId);

        $employeeModel = new EmployeeModel();
        $detailModel = new PayrollDetailModel();

        // Tandai Periode Cut Off
        $periodModel = new PayrollPeriodModel();
        $periodExist = $periodModel->where(['client_id' => $clientId, 'bulan' => $bulan, 'tahun' => $tahun])->first();
        if (!$periodExist) {
            $periodModel->insert([
                'client_id' => $clientId, 'bulan' => $bulan, 'tahun' => $tahun, 
                'status_cutoff' => 'Generated', 'pay_date' => date('Y-m-d')
            ]);
        } else {
            $periodModel->update($periodExist['id'], ['status_cutoff' => 'Generated']);
        }

        foreach ($dataKaryawan as $dk) {
            $emp = $employeeModel->find($dk['employee_id']);
            if (!$emp) continue;

            $baseSalary = $emp['gaji_pokok'];

            // Hitung Lembur (jika rate skema 0, pakai standar depnaker 1/173)
            $otRate = $otRateSchema > 0 ? $otRateSchema : ($baseSalary / 173);
            $overtimePay = $otRate * 1.5 * $dk['lembur'];

            // Potongan Absen (Gaji Pokok / 22 hari kerja * jumlah alpa)
            $potonganAlpa = ($baseSalary / 22) * $dk['alpa'];

            // Hitung BPJS berdasarkan Skema
            $bpjsKes = $baseSalary * $bpjsKesRate;
            $bpjsJht = $baseSalary * $bpjsJhtRate;

            // Hitung Pajak Sederhana (5% dari Gross)
            $gross = $baseSalary + $overtimePay;
            $taxAmount = $gross * 0.05;
            $tunjanganPajak = 0;

            if ($taxMethod === 'Gross') {
                $pajakPotongan = $taxAmount;
            } elseif ($taxMethod === 'Net') {
                $pajakPotongan = 0;
            } elseif ($taxMethod === 'Gross Up') {
                $tunjanganPajak = $taxAmount;
                $pajakPotongan = $taxAmount;
            } else {
                $pajakPotongan = $taxAmount;
            }

            // Hitung komponen custom
            $customTunjangan = 0;
            $customPotongan = 0;
            $customDetails = [];

            foreach ($components as $comp) {
                $nilaiKomponen = 0;
                if ($comp['jenis_nilai'] === 'Tetap') {
                    $nilaiKomponen = floatval($comp['nilai']);
                } else {
                    // Persentase dari gaji pokok
                    $nilaiKomponen = $baseSalary * (floatval($comp['nilai']) / 100);
                }

                if ($comp['tipe'] === 'Tunjangan') {
                    $customTunjangan += $nilaiKomponen;
                } else {
                    $customPotongan += $nilaiKomponen;
                }

                $customDetails[] = [
                    'nama_komponen' => $comp['nama_komponen'],
                    'tipe' => $comp['tipe'],
                    'jumlah' => $nilaiKomponen
                ];
            }

            $totalTunjangan = $overtimePay + $tunjanganPajak + $customTunjangan;
            $totalPotongan = $bpjsKes + $bpjsJht + $potonganAlpa + $pajakPotongan + $customPotongan;
            $thp = $baseSalary + $totalTunjangan - $totalPotongan;

            // Delete existing if re-generating
            $existingPayroll = $this->model->where(['employee_id' => $emp['id'], 'bulan' => $bulan, 'tahun' => $tahun])->first();
            if ($existingPayroll) {
                $detailModel->where('payroll_id', $existingPayroll['id'])->delete();
                $this->model->delete($existingPayroll['id']);
            }

            // Simpan Payroll Utama
            $payrollData = [
                'employee_id' => $emp['id'],
                'bulan' => $bulan,
                'tahun' => $tahun,
                'gaji_pokok' => $baseSalary,
                'total_tunjangan' => $totalTunjangan,
                'total_potongan' => $totalPotongan,
                'take_home_pay' => $thp,
                'status_pembayaran' => 'Waiting Approval'
            ];
            $payrollId = $this->model->insert($payrollData);

            // Simpan Detail standar
            if ($overtimePay > 0) $detailModel->insert(['payroll_id' => $payrollId, 'nama_komponen' => 'Lembur', 'tipe' => 'Tunjangan', 'jumlah' => $overtimePay]);
            if ($tunjanganPajak > 0) $detailModel->insert(['payroll_id' => $payrollId, 'nama_komponen' => 'Tunjangan Pajak (Gross Up)', 'tipe' => 'Tunjangan', 'jumlah' => $tunjanganPajak]);
            if ($bpjsKes > 0) $detailModel->insert(['payroll_id' => $payrollId, 'nama_komponen' => 'BPJS Kesehatan', 'tipe' => 'Potongan', 'jumlah' => $bpjsKes]);
            if ($bpjsJht > 0) $detailModel->insert(['payroll_id' => $payrollId, 'nama_komponen' => 'BPJS Ketenagakerjaan', 'tipe' => 'Potongan', 'jumlah' => $bpjsJht]);
            if ($potonganAlpa > 0) $detailModel->insert(['payroll_id' => $payrollId, 'nama_komponen' => 'Potongan Alpa/Absen', 'tipe' => 'Potongan', 'jumlah' => $potonganAlpa]);
            if ($pajakPotongan > 0) $detailModel->insert(['payroll_id' => $payrollId, 'nama_komponen' => 'Pajak PPh 21', 'tipe' => 'Potongan', 'jumlah' => $pajakPotongan]);

            // Simpan Detail custom
            foreach ($customDetails as $cd) {
                if ($cd['jumlah'] > 0) {
                    $detailModel->insert([
                        'payroll_id' => $payrollId,
                        'nama_komponen' => $cd['nama_komponen'],
                        'tipe' => $cd['tipe'],
                        'jumlah' => $cd['jumlah']
                    ]);
                }
            }
        }

        return $this->respondCreated(['message' => 'Payroll berhasil di-generate dan masuk tahap Pengecekan']);
    }

    /**
     * Check Cut-Off — Pengecekan data sebelum approval
     * Validates: data tidak masuk, gaji kosong, kontrak expired
     */
    public function checkCutOff()
    {
        $bulan = $this->request->getGet('bulan');
        $tahun = $this->request->getGet('tahun');
        $clientId = $this->request->getGet('client_id');

        $employeeModel = new EmployeeModel();
        $contractModel = new ContractModel();
        $periodModel = new PayrollPeriodModel();

        $employees = $employeeModel->where('client_id', $clientId)->where('status', 'Aktif')->findAll();
        $payrolls = $this->model
            ->join('employees', 'employees.id = payrolls.employee_id')
            ->where('payrolls.bulan', $bulan)
            ->where('payrolls.tahun', $tahun)
            ->where('employees.client_id', $clientId)
            ->select('payrolls.*')
            ->findAll();

        $payrollEmpIds = array_column($payrolls, 'employee_id');
        $issues = [];

        foreach ($employees as $emp) {
            // Cek apakah data gaji sudah ada
            if (!in_array($emp['id'], $payrollEmpIds)) {
                $issues[] = [
                    'employee_id' => $emp['id'],
                    'nama' => $emp['nama'],
                    'issue_type' => 'Data Tidak Masuk',
                    'issue_detail' => 'Karyawan tidak memiliki data gaji di periode ini'
                ];
            }

            // Cek gaji pokok kosong
            if (empty($emp['gaji_pokok']) || $emp['gaji_pokok'] <= 0) {
                $issues[] = [
                    'employee_id' => $emp['id'],
                    'nama' => $emp['nama'],
                    'issue_type' => 'Gaji Kosong',
                    'issue_detail' => 'Gaji pokok belum diisi'
                ];
            }

            // Cek kontrak expired
            $activeContract = $contractModel
                ->where('employee_id', $emp['id'])
                ->where('status_pkwt', 'Aktif')
                ->first();
            
            if (!$activeContract) {
                $issues[] = [
                    'employee_id' => $emp['id'],
                    'nama' => $emp['nama'],
                    'issue_type' => 'Kontrak Expired',
                    'issue_detail' => 'Tidak ada PKWT aktif'
                ];
            } elseif (strtotime($activeContract['tgl_berakhir']) < time()) {
                $issues[] = [
                    'employee_id' => $emp['id'],
                    'nama' => $emp['nama'],
                    'issue_type' => 'Kontrak Expired',
                    'issue_detail' => 'PKWT berakhir pada ' . $activeContract['tgl_berakhir']
                ];
            }

            // Cek rekening kosong
            if (empty($emp['no_rekening']) || $emp['no_rekening'] === '-') {
                $issues[] = [
                    'employee_id' => $emp['id'],
                    'nama' => $emp['nama'],
                    'issue_type' => 'Data Tidak Lengkap',
                    'issue_detail' => 'Nomor rekening belum diisi'
                ];
            }
        }

        // Update period status to Checking
        $period = $periodModel->where(['client_id' => $clientId, 'bulan' => $bulan, 'tahun' => $tahun])->first();
        if ($period) {
            $periodModel->update($period['id'], ['status_cutoff' => 'Checking']);
        }

        return $this->respond([
            'issues' => $issues,
            'total_employees' => count($employees),
            'total_payrolls' => count($payrolls),
            'total_issues' => count($issues)
        ]);
    }

    /**
     * Approve single payroll
     */
    public function approve($id)
    {
        if ($this->model->update($id, ['status_pembayaran' => 'Approved'])) {
            return $this->respond(['message' => 'Payroll Approved']);
        }
        return $this->fail('Gagal approve');
    }

    /**
     * Approve All — Bulk approve semua payroll di periode ini
     */
    public function approveAll()
    {
        $json = $this->request->getJSON(true);
        $clientId = $json['client_id'];
        $bulan = $json['bulan'];
        $tahun = $json['tahun'];

        $payrolls = $this->model
            ->join('employees', 'employees.id = payrolls.employee_id')
            ->where('payrolls.bulan', $bulan)
            ->where('payrolls.tahun', $tahun)
            ->where('employees.client_id', $clientId)
            ->where('payrolls.status_pembayaran', 'Waiting Approval')
            ->select('payrolls.id')
            ->findAll();

        $count = 0;
        foreach ($payrolls as $p) {
            $this->model->update($p['id'], ['status_pembayaran' => 'Approved']);
            $count++;
        }

        // Update period status to Closed
        $periodModel = new PayrollPeriodModel();
        $period = $periodModel->where(['client_id' => $clientId, 'bulan' => $bulan, 'tahun' => $tahun])->first();
        if ($period) {
            $periodModel->update($period['id'], [
                'status_cutoff' => 'Closed',
                'pay_date' => date('Y-m-d')
            ]);
        }

        return $this->respond([
            'message' => "Berhasil approve {$count} payroll. Slip gaji siap dikirim.",
            'count' => $count
        ]);
    }
    
    /**
     * Reject single payroll
     */
    public function reject($id)
    {
        $detailModel = new PayrollDetailModel();
        $detailModel->where('payroll_id', $id)->delete();
        if ($this->model->delete($id)) {
            return $this->respondDeleted(['message' => 'Payroll Rejected dan Dihapus']);
        }
        return $this->fail('Gagal reject');
    }

    /**
     * Get Slip Gaji
     */
    public function getSlip($id)
    {
        $payroll = $this->model->find($id);
        if (!$payroll) return $this->failNotFound();

        $detailModel = new PayrollDetailModel();
        $details = $detailModel->where('payroll_id', $id)->findAll();

        $employeeModel = new EmployeeModel();
        $emp = $employeeModel->find($payroll['employee_id']);

        // Get period info for payDate
        $periodModel = new PayrollPeriodModel();
        $period = $periodModel->where([
            'bulan' => $payroll['bulan'],
            'tahun' => $payroll['tahun']
        ])->first();

        return $this->respond([
            'payroll' => $payroll,
            'details' => $details,
            'employee' => $emp,
            'period' => $period
        ]);
    }
}
