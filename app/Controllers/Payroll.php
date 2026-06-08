<?php

namespace App\Controllers;

use App\Models\PayrollModel;
use App\Models\PayrollDetailModel;
use App\Models\EmployeeModel;
use App\Models\AttendanceModel;
use App\Models\ClientModel;
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
     * Get attendance and overtime logs summary for all client employees in cutoff period
     */
    public function getAttendanceSummary()
    {
        $clientId = $this->request->getGet('client_id');
        $bulan = $this->request->getGet('bulan');
        $tahun = $this->request->getGet('tahun');

        if (is_cli()) {
            if (!$clientId) $clientId = $_GET['client_id'] ?? null;
            if (!$bulan) $bulan = $_GET['bulan'] ?? null;
            if (!$tahun) $tahun = $_GET['tahun'] ?? null;
        }

        if (!$clientId || !$bulan || !$tahun) {
            return $this->respond([]);
        }

        $db = \Config\Database::connect();
        
        $payrollConfig = $db->table('client_payroll_configs')
                            ->where('client_id', $clientId)
                            ->get()
                            ->getRow();
        
        $cutoffStartDay = $payrollConfig ? intval($payrollConfig->cutoff_start) : 21;
        $cutoffEndDay = $payrollConfig ? intval($payrollConfig->cutoff_end) : 20;

        if ($cutoffStartDay <= 1) {
            $startDateStr = sprintf('%d-%02d-01', $tahun, $bulan);
            $endDateStr = date('Y-m-t', strtotime($startDateStr));
        } else {
            $prevMonth = intval($bulan) - 1;
            $prevYear = intval($tahun);
            if ($prevMonth == 0) {
                $prevMonth = 12;
                $prevYear--;
            }
            $startDateStr = sprintf('%d-%02d-%02d', $prevYear, $prevMonth, $cutoffStartDay);
            $endDateStr = sprintf('%d-%02d-%02d', $tahun, $bulan, $cutoffEndDay);
        }

        $payoutPeriodStr = intval($bulan) . '-' . intval($tahun);

        $employees = $db->table('employees')
                        ->where('client_id', $clientId)
                        ->get()
                        ->getResultArray();

        $summary = [];
        foreach ($employees as $emp) {
            // Hadir
            $hadirStd = $db->table('attendance_logs')
                             ->where('employee_id', $emp['id'])
                             ->where('tanggal >=', $startDateStr)
                             ->where('tanggal <=', $endDateStr)
                             ->where('status', 'Hadir')
                             ->where('(is_rapel = 0 OR is_rapel IS NULL)')
                             ->countAllResults();

            $hadirRapel = $db->table('attendance_logs')
                             ->where('employee_id', $emp['id'])
                             ->where('status', 'Hadir')
                             ->where('is_rapel', 1)
                             ->where('payout_period', $payoutPeriodStr)
                             ->countAllResults();

            $hadirCount = $hadirStd + $hadirRapel;

            // Sakit/Izin/Cuti
            $sakitStd = $db->table('attendance_logs')
                             ->where('employee_id', $emp['id'])
                             ->where('tanggal >=', $startDateStr)
                             ->where('tanggal <=', $endDateStr)
                             ->groupStart()
                                 ->where('status', 'Sakit')
                                 ->orWhere('status', 'Izin')
                                 ->orWhere('status', 'Cuti')
                             ->groupEnd()
                             ->where('(is_rapel = 0 OR is_rapel IS NULL)')
                             ->countAllResults();

            $sakitRapel = $db->table('attendance_logs')
                             ->where('employee_id', $emp['id'])
                             ->groupStart()
                                 ->where('status', 'Sakit')
                                 ->orWhere('status', 'Izin')
                                 ->orWhere('status', 'Cuti')
                             ->groupEnd()
                             ->where('is_rapel', 1)
                             ->where('payout_period', $payoutPeriodStr)
                             ->countAllResults();

            $sakitCount = $sakitStd + $sakitRapel;

            // Absen/Alpa
            $alpaStd = $db->table('attendance_logs')
                             ->where('employee_id', $emp['id'])
                             ->where('tanggal >=', $startDateStr)
                             ->where('tanggal <=', $endDateStr)
                             ->where('status', 'Absen')
                             ->where('(is_rapel = 0 OR is_rapel IS NULL)')
                             ->countAllResults();

            $alpaRapel = $db->table('attendance_logs')
                             ->where('employee_id', $emp['id'])
                             ->where('status', 'Absen')
                             ->where('is_rapel', 1)
                             ->where('payout_period', $payoutPeriodStr)
                             ->countAllResults();

            $alpaCount = $alpaStd + $alpaRapel;

            // Late & Early Leave (hours sum)
            $lateSumObj = $db->table('attendance_logs')
                             ->where('employee_id', $emp['id'])
                             ->where('tanggal >=', $startDateStr)
                             ->where('tanggal <=', $endDateStr)
                             ->selectSum('late_hours')
                             ->get()->getRow();
            $lateHours = $lateSumObj ? floatval($lateSumObj->late_hours) : 0.0;

            $earlySumObj = $db->table('attendance_logs')
                              ->where('employee_id', $emp['id'])
                              ->where('tanggal >=', $startDateStr)
                              ->where('tanggal <=', $endDateStr)
                              ->selectSum('early_leave_hours')
                              ->get()->getRow();
            $earlyHours = $earlySumObj ? floatval($earlySumObj->early_leave_hours) : 0.0;

            // Lembur (hours sum) - ONLY APPROVED
            $lemburStdSum = $db->table('overtime_logs')
                             ->where('overtime_logs.employee_id', $emp['id'])
                             ->where('overtime_logs.tanggal >=', $startDateStr)
                             ->where('overtime_logs.tanggal <=', $endDateStr)
                             ->where('overtime_logs.status', 'Approved')
                             ->selectSum('overtime_logs.jam_lembur')
                             ->join('attendance_logs', 'attendance_logs.employee_id = overtime_logs.employee_id AND attendance_logs.tanggal = overtime_logs.tanggal', 'left')
                             ->where('(attendance_logs.is_rapel = 0 OR attendance_logs.is_rapel IS NULL)')
                             ->get()
                             ->getRow();
            $lemburStd = $lemburStdSum ? floatval($lemburStdSum->jam_lembur) : 0.0;

            $lemburRapelSum = $db->table('overtime_logs')
                             ->where('overtime_logs.employee_id', $emp['id'])
                             ->where('overtime_logs.status', 'Approved')
                             ->selectSum('overtime_logs.jam_lembur')
                             ->join('attendance_logs', 'attendance_logs.employee_id = overtime_logs.employee_id AND attendance_logs.tanggal = overtime_logs.tanggal', 'inner')
                             ->where('attendance_logs.is_rapel', 1)
                             ->where('attendance_logs.payout_period', $payoutPeriodStr)
                             ->get()
                             ->getRow();
            $lemburRapel = $lemburRapelSum ? floatval($lemburRapelSum->jam_lembur) : 0.0;

            $lemburHours = $lemburStd + $lemburRapel;

            $summary[] = [
                'employee_id' => $emp['id'],
                'hadir' => $hadirCount,
                'sakit' => $sakitCount,
                'alpa' => $alpaCount,
                'lembur' => $lemburHours,
                'late_hours' => $lateHours,
                'early_leave_hours' => $earlyHours
            ];
        }

        return $this->respond($summary);
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

        // Synchronize employees to PKWT first to make sure contracts/components are up-to-date
        $apiController = new \App\Controllers\Api();
        $apiController->syncEmployeesToPKWT($clientId);

        $daysInMonth = date('t', mktime(0, 0, 0, intval($bulan), 1, intval($tahun)));

        // 1. Ambil Skema Klien
        $clientModel = new ClientModel();
        $schema = $clientModel->find($clientId);
        
        $bpjsKesRate = $schema ? ($schema['bpjs_kes_percent'] / 100) : 0.01;
        $bpjsJhtRate = $schema ? ($schema['bpjs_jht_percent'] / 100) : 0.02;
        $taxMethod   = $schema ? $schema['tax_method'] : 'Gross';
        $otRateSchema = $schema ? $schema['overtime_rate_per_hour'] : 0;

        $db = \Config\Database::connect();
        $payrollConfig = $db->table('client_payroll_configs')
                            ->select('client_payroll_configs.*, minimum_wages.nominal as minimum_wage_nominal')
                            ->join('minimum_wages', 'minimum_wages.id = client_payroll_configs.minimum_wage_id', 'left')
                            ->where('client_id', $clientId)
                            ->get()
                            ->getRow();

        $taxScheme = null;
        if ($payrollConfig && !empty($payrollConfig->tax_scheme_id)) {
            $taxScheme = $db->table('tax_schemes')->where('id', $payrollConfig->tax_scheme_id)->get()->getRow();
        }

        $bpjsScheme = null;
        if ($payrollConfig && !empty($payrollConfig->bpjs_scheme_id)) {
            $bpjsScheme = $db->table('tax_schemes')->where('id', $payrollConfig->bpjs_scheme_id)->get()->getRow();
        }
        if (!$bpjsScheme) {
            $bpjsScheme = $taxScheme;
        }

        $minimumWage = 0;
        if ($payrollConfig && !empty($payrollConfig->minimum_wage_id)) {
            $mw = $db->table('minimum_wages')->where('id', $payrollConfig->minimum_wage_id)->get()->getRow();
            if ($mw) {
                $minimumWage = floatval($mw->nominal);
            }
        }

        // 2. Ambil Komponen Payroll Custom (Global Master Skema Kompensasi or Legacy)
        $components = [];
        if ($payrollConfig && !empty($payrollConfig->compensation_scheme_id)) {
            $compComponents = $db->table('compensation_components')
                                 ->where('scheme_id', $payrollConfig->compensation_scheme_id)
                                 ->get()
                                 ->getResultArray();
            foreach ($compComponents as $cc) {
                $components[] = [
                    'nama_komponen' => $cc['nama'],
                    'tipe' => ($cc['tipe'] === 'pendapatan') ? 'Tunjangan' : 'Potongan',
                    'jenis_nilai' => (intval($cc['is_persentase']) === 1) ? 'Persentase' : 'Tetap',
                    'nilai' => floatval($cc['nilai'])
                ];
            }
        } else {
            $compModel = new PayrollComponentModel();
            $components = $compModel->getByClient($clientId);
        }

        $employeeModel = new EmployeeModel();
        $detailModel = new PayrollDetailModel();

        // Fetch overtime divisor setting
        $db = \Config\Database::connect();
        $otDivisorRow = $db->table('system_settings')->where('setting_key', 'overtime_divisor')->get()->getRow();
        $overtimeDivisor = $otDivisorRow ? floatval($otDivisorRow->setting_value) : 160.0;
        if ($overtimeDivisor <= 0) {
            $overtimeDivisor = 160.0;
        }

        // Get cutoff dates
        $cutoffStartDay = $payrollConfig ? intval($payrollConfig->cutoff_start) : 21;
        $cutoffEndDay = $payrollConfig ? intval($payrollConfig->cutoff_end) : 20;

        if ($cutoffStartDay <= 1) {
            $startDateStr = sprintf('%d-%02d-01', $tahun, $bulan);
            $endDateStr = date('Y-m-t', strtotime($startDateStr));
        } else {
            $prevMonth = intval($bulan) - 1;
            $prevYear = intval($tahun);
            if ($prevMonth == 0) {
                $prevMonth = 12;
                $prevYear--;
            }
            $startDateStr = sprintf('%d-%02d-%02d', $prevYear, $prevMonth, $cutoffStartDay);
            $endDateStr = sprintf('%d-%02d-%02d', $tahun, $bulan, $cutoffEndDay);
        }

        // Check for any Pending overtime logs for this client's employees in the cutoff period
        $pendingOvertimeCount = $db->table('overtime_logs')
            ->join('employees', 'employees.id = overtime_logs.employee_id')
            ->where('employees.client_id', $clientId)
            ->where('overtime_logs.tanggal >=', $startDateStr)
            ->where('overtime_logs.tanggal <=', $endDateStr)
            ->where('overtime_logs.status', 'Pending')
            ->countAllResults();

        if ($pendingOvertimeCount > 0) {
            return $this->failValidationErrors("Terdapat $pendingOvertimeCount data lembur yang masih berstatus 'Pending' pada periode cut-off ini. Harap setujui (Approve) atau tolak (Reject) terlebih dahulu di tab Overtime.");
        }

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

        foreach ($dataKaryawan as &$dk) {
            $emp = $employeeModel->find($dk['employee_id']);
            if (!$emp) continue;

            // Get employee's active PKWT
            $pkwt = $db->table('pkwt')
                       ->where('client_id', $clientId)
                       ->where('employee_name', $emp['nama'])
                       ->where('status', 'Active')
                       ->get()
                       ->getRow();

            // Resolve employee-specific minimum wage and UMP/UMK values
            $empMinimumWage = $minimumWage;
            $empMwId = null;
            if ($payrollConfig && !empty($payrollConfig->minimum_wage_id)) {
                $empMwId = $payrollConfig->minimum_wage_id;
            }
            if (!empty($emp['minimum_wage_id'])) {
                $empMwId = $emp['minimum_wage_id'];
                $mw = $db->table('minimum_wages')->where('id', $emp['minimum_wage_id'])->get()->getRow();
                if ($mw) {
                    $empMinimumWage = floatval($mw->nominal);
                }
            }

            // Get employee's work location province for UMP lookup fallback
            $empProvince = null;
            if (!empty($emp['work_location_id'])) {
                $wl = $db->table('work_locations')->where('id', $emp['work_location_id'])->get()->getRow();
                if ($wl && !empty($wl->provinsi)) {
                    $empProvince = $wl->provinsi;
                }
            }

            $umpWageValue = $empMinimumWage;
            $umkWageValue = $empMinimumWage;
            $currentYear = date('Y');
            if ($empMwId) {
                $currentWage = $db->table('minimum_wages')->where('id', $empMwId)->get()->getRow();
                if ($currentWage) {
                    $wageYear = $currentWage->tahun ?: $currentYear;
                    if ($currentWage->tipe === 'UMP') {
                        // Try current year first
                        if ($currentWage->tahun == $currentYear) {
                            $umpWageValue = floatval($currentWage->nominal);
                        } else {
                            $searchName = $currentWage->nama_daerah ?: $currentWage->provinsi;
                            $newerUmp = $db->table('minimum_wages')
                                           ->where('tipe', 'UMP')
                                           ->where('tahun', $currentYear)
                                           ->groupStart()
                                               ->where('nama_daerah', $searchName)
                                               ->orWhere('provinsi', $searchName)
                                           ->groupEnd()
                                           ->get()
                                           ->getRow();
                            $umpWageValue = $newerUmp ? floatval($newerUmp->nominal) : floatval($currentWage->nominal);
                        }
                        $umkWageValue = $umpWageValue;
                    } else if ($currentWage->tipe === 'UMK') {
                        // For UMK, try current year
                        if ($currentWage->tahun == $currentYear) {
                            $umkWageValue = floatval($currentWage->nominal);
                        } else {
                            $newerUmk = $db->table('minimum_wages')
                                           ->where('tipe', 'UMK')
                                           ->where('tahun', $currentYear)
                                           ->where('nama_daerah', $currentWage->nama_daerah)
                                           ->get()
                                           ->getRow();
                            $umkWageValue = $newerUmk ? floatval($newerUmk->nominal) : floatval($currentWage->nominal);
                        }
                        
                        // Find corresponding UMP by kode_daerah province code first
                        $umpFound = false;
                        if (!empty($currentWage->kode_daerah)) {
                            $parts = explode(' ', $currentWage->kode_daerah);
                            if (count($parts) >= 2) {
                                $codeParts = explode('.', $parts[1]);
                                $provCode = $codeParts[0];
                                $umpByCode = $db->table('minimum_wages')
                                                ->where('tipe', 'UMP')
                                                ->where('tahun', $currentYear)
                                                ->like('kode_daerah', "ID $provCode", 'after')
                                                ->get()
                                                ->getRow();
                                if (!$umpByCode) {
                                    $umpByCode = $db->table('minimum_wages')
                                                    ->where('tipe', 'UMP')
                                                    ->where('tahun', $wageYear)
                                                    ->like('kode_daerah', "ID $provCode", 'after')
                                                    ->get()
                                                    ->getRow();
                                }
                                if ($umpByCode) {
                                    $umpWageValue = floatval($umpByCode->nominal);
                                    $umpFound = true;
                                }
                            }
                        }
                        
                        // If not found by code, try by province name
                        if (!$umpFound) {
                            $provinceSearchNames = [];
                            if (!empty($currentWage->provinsi)) $provinceSearchNames[] = $currentWage->provinsi;
                            if (!empty($empProvince)) {
                                $provinceSearchNames[] = $empProvince;
                                $provinceSearchNames[] = strtoupper($empProvince);
                            }
                            
                            $yearsToTry = array_unique([$currentYear, $wageYear]);
                            foreach ($yearsToTry as $tryYear) {
                                foreach ($provinceSearchNames as $provName) {
                                    if (empty($provName)) continue;
                                    $umpWage = $db->table('minimum_wages')
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
                                    if ($umpWage) {
                                        $umpWageValue = floatval($umpWage->nominal);
                                        $umpFound = true;
                                        break 2;
                                    }
                                }
                            }
                            
                            if (!$umpFound) {
                                $umpWageValue = $umkWageValue; // fallback UMP to UMK
                            }
                        }
                    }
                }
            }

            $baseSalary = floatval($emp['gaji_pokok'] ?? 0);
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

            $empComponents = [];
            if ($pkwt) {
                $dbComponents = $db->table('pkwt_components')
                                   ->where('pkwt_id', $pkwt->id)
                                   ->get()
                                   ->getResultArray();
                
                foreach ($dbComponents as $comp) {
                    // Check if Ad-hoc and verify period matching current month/year
                    $isAdhoc = isset($comp['allowance_type']) && $comp['allowance_type'] === 'Ad-hoc';
                    if ($isAdhoc) {
                        $payoutPeriod = trim($comp['payout_period'] ?? '');
                        $currentPeriod1 = intval($bulan) . '-' . intval($tahun);
                        $currentPeriod2 = sprintf('%02d-%d', intval($bulan), intval($tahun));
                        if ($payoutPeriod !== $currentPeriod1 && $payoutPeriod !== $currentPeriod2) {
                            continue; // Skip this component
                        }
                    }

                    $isBasic = (isset($comp['jenis_komponen']) && $comp['jenis_komponen'] === 'basic_salary') || (stripos($comp['nama'], 'Gaji Pokok') !== false);
                    if ($isBasic) {
                        $sumber_nilai = $comp['sumber_nilai'] ?? 'nominal';
                        $base_nilai = floatval($comp['nilai']);
                        if ($sumber_nilai === 'ump') {
                            $baseSalary = $umpWageValue * ($base_nilai / 100);
                        } else if ($sumber_nilai === 'umk') {
                            $baseSalary = $umkWageValue * ($base_nilai / 100);
                        } else if ($sumber_nilai === 'ump_umk') {
                            $baseSalary = $empMinimumWage * ($base_nilai / 100);
                        } else {
                            // Force base_nilai to use Employee's setup if available!
                            if ($emp && isset($emp['gaji_pokok']) && floatval($emp['gaji_pokok']) > 0) {
                                $baseSalary = floatval($emp['gaji_pokok']);
                            } else if ($empMinimumWage > 0) {
                                $baseSalary = $empMinimumWage;
                            } else {
                                $baseSalary = $base_nilai;
                            }
                        }
                    } else {
                        $empComponents[] = [
                            'nama_komponen' => $comp['nama'],
                            'tipe' => ($comp['tipe'] === 'pendapatan') ? 'Tunjangan' : 'Potongan',
                            'jenis_nilai' => (intval($comp['is_persentase']) === 1) ? 'Persentase' : 'Tetap',
                            'nilai' => floatval($comp['nilai']),
                            'is_bpjs' => intval($comp['is_bpjs'] ?? 0),
                            'is_pph21' => intval($comp['is_pph21'] ?? 1),
                            'jenis_komponen' => $comp['jenis_komponen'] ?? '',
                            'sifat_kompensasi' => $comp['sifat_kompensasi'] ?? '',
                            'sumber_nilai' => $comp['sumber_nilai'] ?? '',
                            'periode' => $comp['periode'] ?? ''
                        ];
                    }
                }
            } else {
                foreach ($components as $comp) {
                    $empComponents[] = [
                        'nama_komponen' => $comp['nama_komponen'] ?? $comp['nama'] ?? '',
                        'tipe' => $comp['tipe'],
                        'jenis_nilai' => $comp['jenis_nilai'],
                        'nilai' => $comp['nilai'],
                        'is_bpjs' => isset($comp['is_bpjs']) ? intval($comp['is_bpjs']) : 0,
                        'is_pph21' => isset($comp['is_pph21']) ? intval($comp['is_pph21']) : 1,
                        'jenis_komponen' => $comp['jenis_komponen'] ?? '',
                        'sifat_kompensasi' => $comp['sifat_kompensasi'] ?? '',
                        'sumber_nilai' => $comp['sumber_nilai'] ?? '',
                        'periode' => $comp['periode'] ?? ''
                    ];
                }
            }

            // === ENGINE PERHITUNGAN BARU ===
            // Standar hari kerja per bulan (default dari system_settings, fallback 22, or override per employee)
            $standardDaysRow = $db->table('system_settings')->where('setting_key', 'standard_work_days')->get()->getRow();
            $systemStandardDays = $standardDaysRow ? intval($standardDaysRow->setting_value) : 22;
            $standardDays = isset($emp['custom_standard_days']) && intval($emp['custom_standard_days']) > 0 
                ? intval($emp['custom_standard_days']) 
                : $systemStandardDays;
            if ($standardDays <= 0) {
                $standardDays = 22;
            }

            // Overtime divisor (default 160, or system configuration)
            $standardHours = isset($overtimeDivisor) ? $overtimeDivisor : 160.0;
            $upahPerJam = $baseSalary / $standardHours;

            // Langkah 1: Hitung Hari Kerja Aktual dari attendance_logs berdasarkan cutoff period
            $attendanceLogs = $db->table('attendance_logs')
                ->where('employee_id', $emp['id'])
                ->where('tanggal >=', $startDateStr)
                ->where('tanggal <=', $endDateStr)
                ->where('status', 'Hadir')
                ->get()->getResultArray();
            $actualDaysWorked = count($attendanceLogs);

            // Jika tidak ada attendance_logs, gunakan data dari form input (backward compatible)
            if ($actualDaysWorked === 0 && isset($dk['hadir']) && intval($dk['hadir']) > 0) {
                $actualDaysWorked = intval($dk['hadir']);
            }
            $dk['hadir'] = $actualDaysWorked;

            // Gaji Prorata
            if ($actualDaysWorked >= $standardDays) {
                $gajiProrata = $baseSalary;
            } else {
                $gajiProrata = ($actualDaysWorked / $standardDays) * $baseSalary;
            }

            // Langkah 2: Hitung Lembur dari overtime_logs berdasarkan cutoff period (hanya yang APPROVED)
            $overtimeLogs = $db->table('overtime_logs')
                ->where('employee_id', $emp['id'])
                ->where('tanggal >=', $startDateStr)
                ->where('tanggal <=', $endDateStr)
                ->where('status', 'Approved')
                ->get()->getResultArray();

            // Langkah 3A: Lembur Reguler (Hari Kerja) — Dual Bucket
            $totalEmber1 = 0; // Jam pertama tiap hari → 1.5x
            $totalEmber2 = 0; // Jam 2-3 tiap hari → 2.0x
            // Langkah 3B: Lembur Hari Libur/Weekend
            $lemburLibur = 0;

            if (count($overtimeLogs) > 0) {
                $totalJamLemburLog = 0;
                foreach ($overtimeLogs as $otLog) {
                    $jam = floatval($otLog['jam_lembur']);
                    $totalJamLemburLog += $jam;
                    
                    // Check if date is holiday or sunday
                    $isHoliday = intval($otLog['is_holiday'] ?? 0);
                    if (!$isHoliday) {
                        $dayOfWeek = date('w', strtotime($otLog['tanggal']));
                        if ($dayOfWeek == 0) {
                            $isHoliday = 1;
                        } else {
                            $holiday = $db->table('holiday_calendar')->where('tanggal', $otLog['tanggal'])->get()->getRow();
                            if ($holiday) {
                                $isHoliday = 1;
                            }
                        }
                    }

                    if ($isHoliday) {
                        // Lembur Hari Libur: tiered calculation
                        if ($jam <= 6) {
                            $lemburLibur += $jam * 2 * $upahPerJam;
                        } elseif ($jam == 7) {
                            $lemburLibur += (6 * 2 * $upahPerJam) + (1 * 3 * $upahPerJam);
                        } else {
                            $lemburLibur += (6 * 2 * $upahPerJam) + (1 * 3 * $upahPerJam) + (($jam - 7) * 4 * $upahPerJam);
                        }
                    } else {
                        // Lembur Reguler: dual bucket (max 3 jam/hari)
                        $jam = min($jam, 3);
                        $ember1 = min($jam, 1);         // Jam pertama → 1.5x
                        $ember2 = max($jam - 1, 0);     // Jam 2-3 → 2.0x
                        $totalEmber1 += $ember1;
                        $totalEmber2 += $ember2;
                    }
                }
                $dk['lembur'] = $totalJamLemburLog;
            } else {
                // Backward compatible: jika tidak ada overtime_logs, gunakan data form lama
                if (isset($dk['lembur']) && floatval($dk['lembur']) > 0) {
                    $totalJamLembur = floatval($dk['lembur']);
                    // Treat semua sebagai lembur reguler dengan dual bucket simplified
                    $totalEmber1 = min($totalJamLembur, 1);
                    $totalEmber2 = max($totalJamLembur - 1, 0);
                }
            }

            $lemburReguler = ($totalEmber1 * 1.5 * $upahPerJam) + ($totalEmber2 * 2.0 * $upahPerJam);
            $overtimePay = $lemburReguler + $lemburLibur;

            // Potongan Absen — berdasarkan attendance_logs berdasarkan cutoff period
            $absenCount = 0;
            $absenLogs = $db->table('attendance_logs')
                ->where('employee_id', $emp['id'])
                ->where('tanggal >=', $startDateStr)
                ->where('tanggal <=', $endDateStr)
                ->where('status', 'Absen')
                ->get()->getResultArray();
            $absenCount = count($absenLogs);

            // Backward compatible: gunakan data alpa dari form jika attendance_logs kosong
            if ($absenCount === 0 && isset($dk['alpa']) && intval($dk['alpa']) > 0) {
                $absenCount = intval($dk['alpa']);
            }
            $dk['alpa'] = $absenCount;

            $potonganAlpa = ($baseSalary / $standardDays) * $absenCount;

            // Potongan Keterlambatan dan Pulang Awal
            $lateHours = isset($dk['late_hours']) ? floatval($dk['late_hours']) : 0.0;
            $earlyHours = isset($dk['early_leave_hours']) ? floatval($dk['early_leave_hours']) : 0.0;
            $potonganLate = $lateHours * $upahPerJam;
            $potonganEarly = $earlyHours * $upahPerJam;

            // Langkah 5: Rapel Otomatis untuk Karyawan Baru
            if (!empty($emp['tgl_masuk'])) {
                $joinDate = strtotime($emp['tgl_masuk']);
                $periodeStart = strtotime("$tahun-$bulan-01");
                $periodeEnd = strtotime(date('Y-m-t', $periodeStart));
                
                // Jika join_date berada di pertengahan periode payroll saat ini
                if ($joinDate > $periodeStart && $joinDate <= $periodeEnd) {
                    $rapelAmount = $baseSalary - $gajiProrata;
                    if ($rapelAmount > 0) {
                        // Hitung bulan depan
                        $nextMonth = intval($bulan) + 1;
                        $nextYear = intval($tahun);
                        if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }
                        $payoutPeriod = $nextMonth . '-' . $nextYear;
                        
                        // Cek apakah rapel sudah pernah dibuat
                        $existingRapel = $db->table('pkwt_components')
                            ->where('pkwt_id', $pkwt ? $pkwt->id : 0)
                            ->where('allowance_type', 'Ad-hoc')
                            ->where('payout_period', $payoutPeriod)
                            ->like('nama', 'Rapel')
                            ->get()->getRow();
                        
                        if (!$existingRapel && $pkwt) {
                            $db->table('pkwt_components')->insert([
                                'pkwt_id' => $pkwt->id,
                                'nama' => 'Rapel Gaji (Prorata Bulan Pertama)',
                                'tipe' => 'pendapatan',
                                'nilai' => $rapelAmount,
                                'is_persentase' => 0,
                                'jenis_komponen' => 'kompensasi',
                                'sifat_kompensasi' => 'tidak_tetap',
                                'sumber_nilai' => 'nominal',
                                'periode' => 'bulan',
                                'allowance_type' => 'Ad-hoc',
                                'payout_period' => $payoutPeriod,
                            ]);
                        }
                    }
                }
            }

            // Gunakan gajiProrata sebagai basis (bukan baseSalary penuh)
            $baseSalaryForCalc = $gajiProrata;

            // Match payroll scheme template for organizational matching
            $schemeModel = new \App\Models\PayrollSchemeTemplateModel();
            $schemeTemplateObj = $schemeModel->getSchemeForEmployee(
                $clientId,
                $emp['division_id'] ?? null,
                $emp['department_id'] ?? null,
                $emp['position_id'] ?? null
            );
            $schemeTemplate = $schemeTemplateObj ? (array)$schemeTemplateObj : null;

            // Hitung komponen custom
            $customTunjangan = 0;
            $customPotongan = 0;
            $customDetails = [];

            $bpjsWageBase = $baseSalary; // Gaji Pokok is always included
            $pphWageBase = $baseSalary;  // Gaji Pokok is always included

            foreach ($empComponents as $comp) {
                $nilaiKomponen = 0;
                
                if (!empty($comp['jenis_komponen'])) {
                    // New Master Compensation component logic
                    $base_nilai = floatval($comp['nilai']);
                    
                    if (isset($comp['sumber_nilai'])) {
                        if ($comp['sumber_nilai'] === 'ump') {
                            $base_nilai = $umpWageValue * ($base_nilai / 100);
                        } else if ($comp['sumber_nilai'] === 'umk') {
                            $base_nilai = $umkWageValue * ($base_nilai / 100);
                        } else if ($comp['sumber_nilai'] === 'ump_umk') {
                            $base_nilai = $empMinimumWage * ($base_nilai / 100);
                        }
                    }
                    
                    // Scale by period
                    if (($comp['periode'] ?? '') === 'hari') {
                        $nilaiKomponen = $base_nilai * intval($dk['hadir']);
                    } elseif (($comp['periode'] ?? '') === 'minggu') {
                        $nilaiKomponen = $base_nilai * 4;
                    } elseif (($comp['periode'] ?? '') === 'tahun') {
                        $nilaiKomponen = $base_nilai / 12;
                    } else {
                        // bulanan
                        $isProrateAbs = false;
                        if ($payrollConfig && ($payrollConfig->prorate == 1)) {
                            $isProrateAbs = true;
                        }
                        if ($isProrateAbs && isset($comp['sifat_kompensasi']) && $comp['sifat_kompensasi'] === 'tidak_tetap') {
                            $nilaiKomponen = $base_nilai * (intval($dk['hadir']) / $daysInMonth);
                        } else {
                            $nilaiKomponen = $base_nilai;
                        }
                    }
                } else {
                    // Legacy components
                    if ($comp['jenis_nilai'] === 'Tetap') {
                        $nilaiKomponen = floatval($comp['nilai']);
                    } else {
                        // Persentase dari gaji pokok
                        $nilaiKomponen = $baseSalary * (floatval($comp['nilai']) / 100);
                    }
                }

                if ($comp['tipe'] === 'Tunjangan') {
                    $customTunjangan += $nilaiKomponen;

                    $isBpjsInc = ($comp['is_bpjs'] == 1);
                    $isPphInc = ($comp['is_pph21'] == 1);

                    if ($isBpjsInc) {
                        $bpjsWageBase += $nilaiKomponen;
                    }
                    if ($isPphInc) {
                        $pphWageBase += $nilaiKomponen;
                    }
                } else {
                    $customPotongan += $nilaiKomponen;
                }

                $customDetails[] = [
                    'nama_komponen' => $comp['nama_komponen'] ?? $comp['nama'] ?? '',
                    'tipe' => $comp['tipe'],
                    'jumlah' => $nilaiKomponen
                ];
            }

            // Hitung BPJS & Pajak TER 2024
            $empMinimumWage = $minimumWage;
            if (!empty($emp['minimum_wage_id'])) {
                $mw = $db->table('minimum_wages')->where('id', $emp['minimum_wage_id'])->get()->getRow();
                if ($mw) {
                    $empMinimumWage = floatval($mw->nominal);
                }
            }

            $ptkpStatus = 'TK/0';
            if (!empty($emp['ptkp'])) {
                $ptkpStatus = $emp['ptkp'];
            } elseif ($taxScheme && !empty($taxScheme->ptkp_status)) {
                $ptkpStatus = $taxScheme->ptkp_status;
            } elseif ($schemeTemplate && !empty($schemeTemplate['ptkp_status'])) {
                $ptkpStatus = $schemeTemplate['ptkp_status'];
            }

            // Adjust PPh wage base for attendance variations & overtime
            $pphWageBaseFinal = $pphWageBase + $overtimePay - $potonganAlpa;

            // Fallback for BPJS wage base to minimumWage if lower
            if ($empMinimumWage > 0 && $bpjsWageBase < $empMinimumWage) {
                $bpjsWageBase = $empMinimumWage;
            }

            $calc = $this->calculateBpjsAndTax($baseSalary, $bpjsWageBase, $pphWageBaseFinal, $schemeTemplate, $taxScheme, $empMinimumWage, $ptkpStatus, $bpjsScheme);

            $bpjsKesKaryawan = $calc['bpjs_kes_karyawan'];
            $bpjsJhtKaryawan = $calc['bpjs_jht_karyawan'];
            $bpjsJpKaryawan = $calc['bpjs_jp_karyawan'];
            $pph21 = $calc['pph21'];
            $taxAllowance = $calc['tax_allowance'];
            $curTaxMethod = $calc['metode_pajak'];

            $employeeBpjsDeductions = $bpjsKesKaryawan + $bpjsJhtKaryawan + $bpjsJpKaryawan;

            if ($curTaxMethod === 'Gross Up') {
                $totalTunjangan = $overtimePay + $taxAllowance + $customTunjangan;
                $totalPotongan = $employeeBpjsDeductions + $pph21 + $potonganAlpa + $potonganLate + $potonganEarly + $customPotongan;
            } elseif ($curTaxMethod === 'Gross') {
                $totalTunjangan = $overtimePay + $customTunjangan;
                $totalPotongan = $employeeBpjsDeductions + $pph21 + $potonganAlpa + $potonganLate + $potonganEarly + $customPotongan;
            } else { // Nett
                $totalTunjangan = $overtimePay + $customTunjangan;
                $totalPotongan = $employeeBpjsDeductions + $potonganAlpa + $potonganLate + $potonganEarly + $customPotongan;
            }

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
                'status_pembayaran' => 'Waiting Approval',
                'potongan_absen' => $potonganAlpa,
                'jam_lembur' => isset($dk['lembur']) ? $dk['lembur'] : 0,
                'lembur_pay' => $overtimePay,
                'pph21' => $pph21,
                'bpjs_kes_karyawan' => $bpjsKesKaryawan,
                'bpjs_jht_karyawan' => $bpjsJhtKaryawan,
                'bpjs_jp_karyawan' => $bpjsJpKaryawan,
                'bpjs_kes_perusahaan' => $calc['bpjs_kes_perusahaan'],
                'bpjs_jht_perusahaan' => $calc['bpjs_jht_perusahaan'],
                'bpjs_jp_perusahaan' => $calc['bpjs_jp_perusahaan'],
                'bpjs_jkk_perusahaan' => $calc['bpjs_jkk_perusahaan'],
                'bpjs_jkm_perusahaan' => $calc['bpjs_jkm_perusahaan'],
                'tax_allowance' => $taxAllowance,
                'tax_method' => $curTaxMethod
            ];
            $payrollId = $this->model->insert($payrollData);

            // Simpan Detail standar
            if ($overtimePay > 0) $detailModel->insert(['payroll_id' => $payrollId, 'nama_komponen' => 'Lembur', 'tipe' => 'Tunjangan', 'jumlah' => $overtimePay]);
            if ($taxAllowance > 0) $detailModel->insert(['payroll_id' => $payrollId, 'nama_komponen' => 'Tunjangan Pajak (Gross Up)', 'tipe' => 'Tunjangan', 'jumlah' => $taxAllowance]);
            if ($bpjsKesKaryawan > 0) $detailModel->insert(['payroll_id' => $payrollId, 'nama_komponen' => 'BPJS Kesehatan (1% Karyawan)', 'tipe' => 'Potongan', 'jumlah' => $bpjsKesKaryawan]);
            if ($bpjsJhtKaryawan > 0) $detailModel->insert(['payroll_id' => $payrollId, 'nama_komponen' => 'BPJS TK JHT (2% Karyawan)', 'tipe' => 'Potongan', 'jumlah' => $bpjsJhtKaryawan]);
            if ($bpjsJpKaryawan > 0) $detailModel->insert(['payroll_id' => $payrollId, 'nama_komponen' => 'BPJS TK JP (1% Karyawan)', 'tipe' => 'Potongan', 'jumlah' => $bpjsJpKaryawan]);
            if ($potonganAlpa > 0) $detailModel->insert(['payroll_id' => $payrollId, 'nama_komponen' => 'Potongan Alpa/Absen', 'tipe' => 'Potongan', 'jumlah' => $potonganAlpa]);
            if ($potonganLate > 0) $detailModel->insert(['payroll_id' => $payrollId, 'nama_komponen' => 'Potongan Keterlambatan', 'tipe' => 'Potongan', 'jumlah' => $potonganLate]);
            if ($potonganEarly > 0) $detailModel->insert(['payroll_id' => $payrollId, 'nama_komponen' => 'Potongan Pulang Awal', 'tipe' => 'Potongan', 'jumlah' => $potonganEarly]);
            if ($pph21 > 0 && $curTaxMethod !== 'Net') {
                $detailModel->insert(['payroll_id' => $payrollId, 'nama_komponen' => 'Pajak PPh 21', 'tipe' => 'Potongan', 'jumlah' => $pph21]);
            }

            // Simpan Detail Beban Perusahaan (Informasi)
            if ($calc['bpjs_kes_perusahaan'] > 0) $detailModel->insert(['payroll_id' => $payrollId, 'nama_komponen' => 'BPJS Kesehatan (4% Beban Perusahaan)', 'tipe' => 'Beban Perusahaan', 'jumlah' => $calc['bpjs_kes_perusahaan']]);
            if ($calc['bpjs_jht_perusahaan'] > 0) $detailModel->insert(['payroll_id' => $payrollId, 'nama_komponen' => 'BPJS TK JHT (3.7% Beban Perusahaan)', 'tipe' => 'Beban Perusahaan', 'jumlah' => $calc['bpjs_jht_perusahaan']]);
            if ($calc['bpjs_jp_perusahaan'] > 0) $detailModel->insert(['payroll_id' => $payrollId, 'nama_komponen' => 'BPJS TK JP (2% Beban Perusahaan)', 'tipe' => 'Beban Perusahaan', 'jumlah' => $calc['bpjs_jp_perusahaan']]);
            if ($calc['bpjs_jkk_perusahaan'] > 0) $detailModel->insert(['payroll_id' => $payrollId, 'nama_komponen' => 'BPJS TK JKK (Beban Perusahaan)', 'tipe' => 'Beban Perusahaan', 'jumlah' => $calc['bpjs_jkk_perusahaan']]);
            if ($calc['bpjs_jkm_perusahaan'] > 0) $detailModel->insert(['payroll_id' => $payrollId, 'nama_komponen' => 'BPJS TK JKM (Beban Perusahaan)', 'tipe' => 'Beban Perusahaan', 'jumlah' => $calc['bpjs_jkm_perusahaan']]);

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
}

