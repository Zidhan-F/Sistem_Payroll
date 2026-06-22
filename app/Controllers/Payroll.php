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
            $bulan_start = intval($bulan);
            $tahun_start = intval($tahun);
            if ($cutoffStartDay > $cutoffEndDay) {
                $bulan_start--;
                if ($bulan_start == 0) {
                    $bulan_start = 12;
                    $tahun_start--;
                }
            }
            $startDateStr = sprintf('%d-%02d-%02d', $tahun_start, $bulan_start, $cutoffStartDay);
            $endDateStr = sprintf('%d-%02d-%02d', $tahun, $bulan, $cutoffEndDay);
        }

        $payoutPeriodStr = intval($bulan) . '-' . intval($tahun);

        $period = $db->table('payroll_periods')
                     ->where('client_id', $clientId)
                     ->where('bulan', $bulan)
                     ->where('tahun', $tahun)
                     ->get()
                     ->getRow();
        $periodId = $period ? $period->id : null;

        $employees = $db->table('employees')
                        ->where('client_id', $clientId)
                        ->get()
                        ->getResultArray();

        $summary = [];
        foreach ($employees as $emp) {
            $pkwt = $db->table('pkwt')
                       ->where('client_id', $clientId)
                       ->where('employee_name', $emp['nama'])
                       ->where('status', 'Active')
                       ->get()->getRow();

            $empConfig = null;
            if (!empty($emp['position_id'])) {
                $empConfig = $db->table('client_payroll_configs')
                    ->where('client_id', $clientId)
                    ->where('position_id', $emp['position_id'])
                    ->get()->getRow();
            }
            if (!$empConfig && !empty($emp['department_id'])) {
                $empConfig = $db->table('client_payroll_configs')
                    ->where('client_id', $clientId)
                    ->where('department_id', $emp['department_id'])
                    ->where('position_id IS NULL')
                    ->get()->getRow();
            }
            if (!$empConfig && !empty($emp['division_id'])) {
                $empConfig = $db->table('client_payroll_configs')
                    ->where('client_id', $clientId)
                    ->where('division_id', $emp['division_id'])
                    ->where('department_id IS NULL')
                    ->where('position_id IS NULL')
                    ->get()->getRow();
            }
            if (!$empConfig) {
                $empConfig = $payrollConfig;
            }

            $daysInMonth = date('t', mktime(0, 0, 0, intval($bulan), 1, intval($tahun)));
            
            $resolveComponentDates = function($config, $component) use ($db, $bulan, $tahun, $daysInMonth) {
                $startField = "cutoff_{$component}_start";
                $endField = "cutoff_{$component}_end";
                $refField = "cutoff_{$component}_schedule_ref";
                
                $start = ($config && isset($config->$startField)) ? intval($config->$startField) : null;
                $end = ($config && isset($config->$endField)) ? intval($config->$endField) : null;
                $refId = ($config && isset($config->$refField)) ? intval($config->$refField) : null;
                
                if ($refId) {
                    $sched = $db->table('payroll_schedules')->where('id', $refId)->get()->getRow();
                    if ($sched) {
                        $start = intval($sched->cutoff_start);
                        $end = intval($sched->cutoff_end);
                    }
                }
                
                if ($start === null) {
                    if ($component === 'gaji_pokok' && $config && isset($config->cutoff_start)) {
                        $start = intval($config->cutoff_start);
                    } else {
                        $start = 21;
                    }
                }
                if ($end === null) {
                    if ($component === 'gaji_pokok' && $config && isset($config->cutoff_end)) {
                        $end = intval($config->cutoff_end);
                    } else {
                        $end = $start - 1;
                        if ($end < 1) $end = 31;
                    }
                }
                
                if ($start <= 1) {
                    $startDateStr = sprintf('%d-%02d-01', $tahun, $bulan);
                    $endDateStr = date('Y-m-t', strtotime($startDateStr));
                } else {
                    $bulan_start = intval($bulan);
                    $tahun_start = intval($tahun);
                    if ($start > $end) {
                        $bulan_start--;
                        if ($bulan_start == 0) {
                            $bulan_start = 12;
                            $tahun_start--;
                        }
                    }
                    $startDateStr = sprintf('%d-%02d-%02d', $tahun_start, $bulan_start, $start);
                    $endDateStr = sprintf('%d-%02d-%02d', $tahun, $bulan, $end);
                }
                return [$startDateStr, $endDateStr];
            };

            list($empStartDateStr, $empEndDateStr) = $resolveComponentDates($empConfig, 'gaji_pokok');
            list($empLemburStartDateStr, $empLemburEndDateStr) = $resolveComponentDates($empConfig, 'lembur');

            $hasAttendanceRecord = false;
            $hariKerjaVal = 0;
            $jamLemburVal = 0.0;

            if ($pkwt && $periodId) {
                $att = $db->table('payroll_attendance')
                          ->where('period_id', $periodId)
                          ->where('pkwt_id', $pkwt->id)
                          ->get()->getRow();
                if ($att) {
                    $hasAttendanceRecord = true;
                    $hariKerjaVal = floatval($att->hari_kerja);
                    $jamLemburVal = floatval($att->jam_lembur);
                }
            }

            $effectiveStartDateStr = !empty($emp['tgl_masuk']) ? max($empStartDateStr, date('Y-m-d', strtotime($emp['tgl_masuk']))) : $empStartDateStr;
            $effectiveLemburStartDateStr = !empty($emp['tgl_masuk']) ? max($empLemburStartDateStr, date('Y-m-d', strtotime($emp['tgl_masuk']))) : $empLemburStartDateStr;

            // Hadir
            $hadirStd = $db->table('attendance_logs')
                             ->where('employee_id', $emp['id'])
                             ->where('log_date >=', $effectiveStartDateStr)
                             ->where('log_date <=', $empEndDateStr)
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
                             ->where('log_date >=', $effectiveStartDateStr)
                             ->where('log_date <=', $empEndDateStr)
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
                             ->where('log_date >=', $effectiveStartDateStr)
                             ->where('log_date <=', $empEndDateStr)
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
                             ->where('log_date >=', $effectiveStartDateStr)
                             ->where('log_date <=', $empEndDateStr)
                             ->selectSum('late_hours')
                             ->get()->getRow();
            $lateHours = $lateSumObj ? floatval($lateSumObj->late_hours) : 0.0;

            $earlySumObj = $db->table('attendance_logs')
                              ->where('employee_id', $emp['id'])
                              ->where('log_date >=', $effectiveStartDateStr)
                              ->where('log_date <=', $empEndDateStr)
                              ->selectSum('early_leave_hours')
                              ->get()->getRow();
            $earlyHours = $earlySumObj ? floatval($earlySumObj->early_leave_hours) : 0.0;

            // Lembur (hours sum) - ONLY APPROVED
            $lemburStdSum = $db->table('overtime_logs')
                             ->where('overtime_logs.employee_id', $emp['id'])
                             ->where('overtime_logs.tanggal >=', $effectiveLemburStartDateStr)
                             ->where('overtime_logs.tanggal <=', $empLemburEndDateStr)
                             ->where('overtime_logs.status', 'Approved')
                             ->selectSum('overtime_logs.jam_lembur')
                             ->join('attendance_logs', 'attendance_logs.employee_id = overtime_logs.employee_id AND attendance_logs.log_date = overtime_logs.tanggal', 'left')
                             ->where('(attendance_logs.is_rapel = 0 OR attendance_logs.is_rapel IS NULL)')
                             ->get()
                             ->getRow();
            $lemburStd = $lemburStdSum ? floatval($lemburStdSum->jam_lembur) : 0.0;

            $lemburRapelSum = $db->table('overtime_logs')
                             ->where('overtime_logs.employee_id', $emp['id'])
                             ->where('overtime_logs.status', 'Approved')
                             ->selectSum('overtime_logs.jam_lembur')
                             ->join('attendance_logs', 'attendance_logs.employee_id = overtime_logs.employee_id AND attendance_logs.log_date = overtime_logs.tanggal', 'inner')
                             ->where('attendance_logs.is_rapel', 1)
                             ->where('attendance_logs.payout_period', $payoutPeriodStr)
                             ->get()
                             ->getRow();
            $lemburRapel = $lemburRapelSum ? floatval($lemburRapelSum->jam_lembur) : 0.0;

            $lemburHours = $lemburStd + $lemburRapel;

            if ($hasAttendanceRecord) {
                $hadirCount = $hariKerjaVal;
                $lemburHours = $jamLemburVal;
            }

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
        $payoutPeriodStr = intval($bulan) . '-' . intval($tahun);
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

        $resolveComponentDates = function($config, $component) use ($db, $bulan, $tahun, $daysInMonth) {
            $startField = "cutoff_{$component}_start";
            $endField = "cutoff_{$component}_end";
            $refField = "cutoff_{$component}_schedule_ref";
            
            $start = ($config && isset($config->$startField)) ? intval($config->$startField) : null;
            $end = ($config && isset($config->$endField)) ? intval($config->$endField) : null;
            $refId = ($config && isset($config->$refField)) ? intval($config->$refField) : null;
            
            if ($refId) {
                $sched = $db->table('payroll_schedules')->where('id', $refId)->get()->getRow();
                if ($sched) {
                    $start = intval($sched->cutoff_start);
                    $end = intval($sched->cutoff_end);
                }
            }
            
            if ($start === null) {
                if ($component === 'gaji_pokok' && $config && isset($config->cutoff_start)) {
                    $start = intval($config->cutoff_start);
                } else {
                    $start = 21;
                }
            }
            if ($end === null) {
                if ($component === 'gaji_pokok' && $config && isset($config->cutoff_end)) {
                    $end = intval($config->cutoff_end);
                } else {
                    $end = $start - 1;
                    if ($end < 1) $end = 31;
                }
            }
            
            if ($start <= 1) {
                $startDateStr = sprintf('%d-%02d-01', $tahun, $bulan);
                $endDateStr = date('Y-m-t', strtotime($startDateStr));
            } else {
                $bulan_start = intval($bulan);
                $tahun_start = intval($tahun);
                if ($start > $end) {
                    $bulan_start--;
                    if ($bulan_start == 0) {
                        $bulan_start = 12;
                        $tahun_start--;
                    }
                }
                $startDateStr = sprintf('%d-%02d-%02d', $tahun_start, $bulan_start, $start);
                $endDateStr = sprintf('%d-%02d-%02d', $tahun, $bulan, $end);
            }
            return [$startDateStr, $endDateStr];
        };

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

        // Check for any Pending overtime logs for this client's employees in their respective cutoff periods
        $pendingOvertimeCount = 0;
        $pendingEarlyArrivalCount = 0;
        $activeEmployees = $db->table('employees')
            ->where('client_id', $clientId)
            ->where('status', 'Aktif')
            ->get()->getResultArray();

        foreach ($activeEmployees as $activeEmp) {
            $empConfig = null;
            if (!empty($activeEmp['position_id'])) {
                $empConfig = $db->table('client_payroll_configs')
                    ->where('client_id', $clientId)
                    ->where('position_id', $activeEmp['position_id'])
                    ->get()->getRow();
            }
            if (!$empConfig && !empty($activeEmp['department_id'])) {
                $empConfig = $db->table('client_payroll_configs')
                    ->where('client_id', $clientId)
                    ->where('department_id', $activeEmp['department_id'])
                    ->where('position_id IS NULL')
                    ->get()->getRow();
            }
            if (!$empConfig && !empty($activeEmp['division_id'])) {
                $empConfig = $db->table('client_payroll_configs')
                    ->where('client_id', $clientId)
                    ->where('division_id', $activeEmp['division_id'])
                    ->where('department_id IS NULL')
                    ->where('position_id IS NULL')
                    ->get()->getRow();
            }
            if (!$empConfig) {
                $empConfig = $payrollConfig;
            }

            list($empLemburStartDateStr, $empLemburEndDateStr) = $resolveComponentDates($empConfig, 'lembur');

            $empPendingCount = $db->table('overtime_logs')
                ->where('employee_id', $activeEmp['id'])
                ->where('tanggal >=', $empLemburStartDateStr)
                ->where('tanggal <=', $empLemburEndDateStr)
                ->where('status', 'Pending')
                ->countAllResults();
            if ($empPendingCount > 0) {
                $pendingOvertimeCount += $empPendingCount;
            }

            // Check pending early arrivals
            list($empAttStartDateStr, $empAttEndDateStr) = $resolveComponentDates($empConfig, 'gaji_pokok');
            $empPendingEACount = $db->table('early_arrival')
                ->where('employee_id', $activeEmp['id'])
                ->where('date >=', $empAttStartDateStr)
                ->where('date <=', $empAttEndDateStr)
                ->where('status', 'PENDING')
                ->countAllResults();
            if ($empPendingEACount > 0) {
                $pendingEarlyArrivalCount += $empPendingEACount;
            }
        }

        if ($pendingOvertimeCount > 0) {
            return $this->failValidationErrors("Terdapat $pendingOvertimeCount data lembur yang masih berstatus 'Pending' pada periode cut-off ini. Harap setujui (Approve) atau tolak (Reject) terlebih dahulu di tab Overtime.");
        }

        if ($pendingEarlyArrivalCount > 0) {
            return $this->failValidationErrors("Terdapat $pendingEarlyArrivalCount data Early Arrival yang masih berstatus 'Pending' pada periode cut-off ini. Harap setujui (Approve) atau tolak (Reject) terlebih dahulu di tab Early Arrival.");
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

            $empConfig = null;
            if (!empty($emp['position_id'])) {
                $empConfig = $db->table('client_payroll_configs')
                    ->where('client_id', $clientId)
                    ->where('position_id', $emp['position_id'])
                    ->get()->getRow();
            }
            if (!$empConfig && !empty($emp['department_id'])) {
                $empConfig = $db->table('client_payroll_configs')
                    ->where('client_id', $clientId)
                    ->where('department_id', $emp['department_id'])
                    ->where('position_id IS NULL')
                    ->get()->getRow();
            }
            if (!$empConfig && !empty($emp['division_id'])) {
                $empConfig = $db->table('client_payroll_configs')
                    ->where('client_id', $clientId)
                    ->where('division_id', $emp['division_id'])
                    ->where('department_id IS NULL')
                    ->where('position_id IS NULL')
                    ->get()->getRow();
            }
            if (!$empConfig) {
                $empConfig = $payrollConfig;
            }

            list($empStartDateStr, $empEndDateStr) = $resolveComponentDates($empConfig, 'gaji_pokok');
            list($empLemburStartDateStr, $empLemburEndDateStr) = $resolveComponentDates($empConfig, 'lembur');
            list($empInsentifStartDateStr, $empInsentifEndDateStr) = $resolveComponentDates($empConfig, 'insentif');

            // Reset old rapel flags for this employee in the current period range
            $db->table('attendance_logs')
                ->where('employee_id', $emp['id'])
                ->where('log_date >=', $empStartDateStr)
                ->where('log_date <=', $empEndDateStr)
                ->update([
                    'is_rapel' => 0,
                    'payout_period' => null
                ]);

            $db->table('overtime_logs')
                ->where('employee_id', $emp['id'])
                ->where('tanggal >=', $empLemburStartDateStr)
                ->where('tanggal <=', $empLemburEndDateStr)
                ->update([
                    'is_rapel' => 0,
                    'payout_period' => null
                ]);            // Skip employee if they joined after the cutoff period end date
            if (!empty($emp['tgl_masuk'])) {
                $joinTs = strtotime($emp['tgl_masuk']);
                $joinDateStr2 = date('Y-m-d', $joinTs);
                if ($joinDateStr2 > $empEndDateStr) {
                    $db->table('payrolls')
                        ->where('employee_id', $emp['id'])
                        ->where('bulan', $bulan)
                        ->where('tahun', $tahun)
                        ->delete();
                    continue;
                }
            }

            // Logika Rapel Karyawan Baru
            $isNewHireRapel = false;
            $nextPeriodStr = '';
            if (!empty($emp['tgl_masuk'])) {
                $joinTs = strtotime($emp['tgl_masuk']);
                $joinYear = intval(date('Y', $joinTs));
                $joinMonth = intval(date('n', $joinTs));
                $joinDay = intval(date('j', $joinTs));

                // Check if join date falls within the current period boundaries
                $joinDateStr2 = date('Y-m-d', $joinTs);
                if ($joinDateStr2 >= $empStartDateStr && $joinDateStr2 <= $empEndDateStr) {
                    $isRapelGP = ($empConfig && isset($empConfig->is_rapel_gaji_pokok)) ? intval($empConfig->is_rapel_gaji_pokok) : 1;
                    
                    // Gaji pokok cutoff start
                    $gpStartField = "cutoff_gaji_pokok_start";
                    $gpRefField = "cutoff_gaji_pokok_schedule_ref";
                    $gpStartVal = ($empConfig && isset($empConfig->$gpStartField)) ? intval($empConfig->$gpStartField) : null;
                    $gpRefId = ($empConfig && isset($empConfig->$gpRefField)) ? intval($empConfig->$gpRefField) : null;
                    if ($gpRefId) {
                        $sched = $db->table('payroll_schedules')->where('id', $gpRefId)->get()->getRow();
                        if ($sched) $gpStartVal = intval($sched->cutoff_start);
                    }
                    if ($gpStartVal === null) {
                        $gpStartVal = ($empConfig && isset($empConfig->cutoff_start)) ? intval($empConfig->cutoff_start) : 21;
                    }
                    
                    // Jika karyawan masuk pada/setelah tanggal cutoff, gajinya masuk ke bulan depan (rapel + prorate)
                    if ($joinYear === intval($tahun) && $joinMonth === intval($bulan) && $joinDay >= $gpStartVal && $isRapelGP === 1) {
                        $isNewHireRapel = true;
                        
                        $nextMonth = intval($bulan) + 1;
                        $nextYear = intval($tahun);
                        if ($nextMonth > 12) {
                            $nextMonth = 1;
                            $nextYear++;
                        }
                        $nextPeriodStr = $nextMonth . '-' . $nextYear;
                    }
                }
            }

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

            // ── PRIORITAS 1: Ambil dari contracts table (yang ditampilkan di PKWT Contract tab) ──
            $activeContract = $db->table('contracts')
                ->where('employee_id', $emp['id'])
                ->where('status_pkwt', 'Aktif')
                ->orderBy('tgl_mulai', 'DESC')
                ->get()->getRow();

            if ($activeContract && floatval($activeContract->gaji_pokok) > 0) {
                $baseSalary = floatval($activeContract->gaji_pokok);
            } elseif ($payrollConfig) {
                // ── PRIORITAS 2: Dari client_payroll_configs (UMP/UMK/Nominal) ──
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
                        
                        // Check if outside current insentif cutoff
                        list($insStartDate, $insEndDate) = $resolveComponentDates($empConfig, 'insentif');
                        $createdDate = date('Y-m-d', strtotime($comp['created_at']));
                        
                        if ($createdDate > $insEndDate) {
                            $isRapelIns = ($empConfig && isset($empConfig->is_rapel_insentif)) ? intval($empConfig->is_rapel_insentif) : 1;
                            if ($isRapelIns === 1) {
                                // Defer to next month
                                $nextMonth = intval($bulan) + 1;
                                $nextYear = intval($tahun);
                                if ($nextMonth > 12) {
                                    $nextMonth = 1;
                                    $nextYear++;
                                }
                                $nextPeriod = $nextMonth . '-' . $nextYear;
                                
                                // Update database so it is paid next month
                                $db->table('pkwt_components')
                                   ->where('id', $comp['id'])
                                   ->update(['payout_period' => $nextPeriod]);
                                
                                continue; // Skip in the current month's calculation
                            }
                        }
                        
                        $currentPeriod1 = intval($bulan) . '-' . intval($tahun);
                        $currentPeriod2 = sprintf('%02d-%d', intval($bulan), intval($tahun));
                        if ($payoutPeriod !== $currentPeriod1 && $payoutPeriod !== $currentPeriod2) {
                            continue; // Skip this component
                        }
                    }

                    $isBasic = (isset($comp['jenis_komponen']) && $comp['jenis_komponen'] === 'basic_salary') || (stripos($comp['nama'], 'Gaji Pokok') !== false);
                    if ($isBasic) {
                        // Jika sudah dapat dari contracts, skip override dari PKWT components
                        if ($activeContract && floatval($activeContract->gaji_pokok) > 0) {
                            // baseSalary sudah dari contracts, jangan di-override
                        } else {
                            $sumber_nilai = $comp['sumber_nilai'] ?? 'nominal';
                            $base_nilai = floatval($comp['nilai']);
                            if ($sumber_nilai === 'ump') {
                                $baseSalary = $umpWageValue * ($base_nilai / 100);
                            } else if ($sumber_nilai === 'umk') {
                                $baseSalary = $umkWageValue * ($base_nilai / 100);
                            } else if ($sumber_nilai === 'ump_umk') {
                                $baseSalary = $empMinimumWage * ($base_nilai / 100);
                            } else {
                                if ($emp && isset($emp['gaji_pokok']) && floatval($emp['gaji_pokok']) > 0) {
                                    $baseSalary = floatval($emp['gaji_pokok']);
                                } else if ($empMinimumWage > 0) {
                                    $baseSalary = $empMinimumWage;
                                } else {
                                    $baseSalary = $base_nilai;
                                }
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
                            'periode' => $comp['periode'] ?? '',
                            'allowance_type' => $comp['allowance_type'] ?? ''
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
                        'periode' => $comp['periode'] ?? '',
                        'allowance_type' => $comp['allowance_type'] ?? ''
                    ];
                }
            }

            // === ENGINE PERHITUNGAN BARU ===
            $workDaysConfig = isset($emp['hari_kerja']) ? intval($emp['hari_kerja']) : 5;
            if ($workDaysConfig === 7) {
                // 7 hari kerja: gunakan jumlah hari aktual di bulan tersebut (28/29/30/31)
                $defaultDays = intval(date('t', mktime(0, 0, 0, intval($bulan), 1, intval($tahun))));
            } elseif ($workDaysConfig === 6) {
                $defaultDays = 26;
            } else {
                $defaultDays = 22;
            }

            // Standar hari kerja per bulan (default dari system_settings, fallback 22, or override per employee)
            $standardDaysRow = $db->table('system_settings')->where('setting_key', 'standard_work_days')->get()->getRow();
            $systemStandardDays = $standardDaysRow ? intval($standardDaysRow->setting_value) : $defaultDays;
            $standardDays = isset($emp['custom_standard_days']) && intval($emp['custom_standard_days']) > 0 
                ? intval($emp['custom_standard_days']) 
                : $defaultDays;
            if ($standardDays <= 0) {
                $standardDays = $defaultDays;
            }

            if ($isNewHireRapel) {
                // Calculate actual present days from attendance_logs
                $actualDaysWorked = $db->table('attendance_logs')
                    ->where('employee_id', $emp['id'])
                    ->where('log_date >=', $empStartDateStr)
                    ->where('log_date <=', $empEndDateStr)
                    ->where('status', 'Hadir')
                    ->countAllResults();

                $rapelAmount = ($standardDays > 0) ? (($actualDaysWorked / $standardDays) * $baseSalary) : 0.0;

                if ($rapelAmount > 0) {
                    $existingRapel = $db->table('pkwt_components')
                        ->where('pkwt_id', $pkwt ? $pkwt->id : 0)
                        ->where('allowance_type', 'Ad-hoc')
                        ->where('payout_period', $nextPeriodStr)
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
                            'payout_period' => $nextPeriodStr
                        ]);
                    } else if ($existingRapel) {
                        $db->table('pkwt_components')
                            ->where('id', $existingRapel->id)
                            ->update([
                                'nilai' => $rapelAmount
                            ]);
                    }
                }

                // Update logs
                $db->table('attendance_logs')
                    ->where('employee_id', $emp['id'])
                    ->where('log_date >=', $empStartDateStr)
                    ->where('log_date <=', $empEndDateStr)
                    ->where('status', 'Hadir')
                    ->update([
                        'is_rapel' => 1,
                        'payout_period' => $nextPeriodStr
                    ]);

                $db->table('overtime_logs')
                    ->where('employee_id', $emp['id'])
                    ->where('tanggal >=', $empLemburStartDateStr)
                    ->where('tanggal <=', $empLemburEndDateStr)
                    ->update([
                        'is_rapel' => 1,
                        'payout_period' => $nextPeriodStr
                    ]);

                // Reset early arrival logs to NOT_PROCESSED for the period
                $db->table('early_arrival')
                    ->where('employee_id', $emp['id'])
                    ->where('date >=', $empStartDateStr)
                    ->where('date <=', $empEndDateStr)
                    ->update([
                        'status' => 'NOT_PROCESSED'
                    ]);

                // Delete existing payroll for the period
                $existingPayroll = $db->table('payrolls')
                    ->where('employee_id', $emp['id'])
                    ->where('bulan', $bulan)
                    ->where('tahun', $tahun)
                    ->get()->getRow();

                if ($existingPayroll) {
                    $db->table('payroll_details')->where('payroll_id', $existingPayroll->id)->delete();
                    $db->table('payrolls')->where('id', $existingPayroll->id)->delete();
                }

                // Insert a record in the payrolls table with 0.0 values
                $payrollData = [
                    'employee_id' => $emp['id'],
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                    'gaji_pokok' => 0.0,
                    'total_tunjangan' => 0.0,
                    'total_potongan' => 0.0,
                    'take_home_pay' => 0.0,
                    'status_pembayaran' => 'Pending'
                ];
                $this->model->insert($payrollData);

                continue; // Skip the rest of the loop!
            }

            // Overtime divisor (5 days = 40 hours, 6 days = 48 hours, 7 days = 56 hours)
            if ($workDaysConfig === 7) {
                $standardHours = 56.0;
            } elseif ($workDaysConfig === 6) {
                $standardHours = 48.0;
            } else {
                $standardHours = 40.0;
            }

            // Resolve Nominal Lembur Bulanan (contains "Lembur" or "Overtime" in name)
            $nominalLemburBulanan = 0.0;
            foreach ($empComponents as $comp) {
                $compName = $comp['nama_komponen'] ?? $comp['nama'] ?? '';
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
                $nominalLemburBulanan = $baseSalary; // fallback to base salary
            }

            $upahPerJam = $nominalLemburBulanan / $standardHours;

            // Initialize running totals for components
            $customTunjangan = 0.0;
            $customPotongan = 0.0;
            $customDetails = [];
            $bpjsWageBase = $baseSalary;
            $pphWageBase = $baseSalary;

            $effectiveStartDateStr = !empty($emp['tgl_masuk']) ? max($empStartDateStr, date('Y-m-d', strtotime($emp['tgl_masuk']))) : $empStartDateStr;
            $effectiveLemburStartDateStr = !empty($emp['tgl_masuk']) ? max($empLemburStartDateStr, date('Y-m-d', strtotime($emp['tgl_masuk']))) : $empLemburStartDateStr;

            // Langkah 1: Hitung Hari Kerja Aktual dari attendance_logs berdasarkan cutoff period
            $attendanceLogs = $db->table('attendance_logs')
                ->where('employee_id', $emp['id'])
                ->where('log_date >=', $effectiveStartDateStr)
                ->where('log_date <=', $empEndDateStr)
                ->where('status', 'Hadir')
                ->get()->getResultArray();
            $actualDaysWorked = count($attendanceLogs);

            // Query approved overtime logs for this employee in the cutoff range (excluding rapel)
            $overtimeLogs = $db->table('overtime_logs')
                ->where('overtime_logs.employee_id', $emp['id'])
                ->where('overtime_logs.tanggal >=', $effectiveLemburStartDateStr)
                ->where('overtime_logs.tanggal <=', $empLemburEndDateStr)
                ->where('overtime_logs.status', 'Approved')
                ->join('attendance_logs', 'attendance_logs.employee_id = overtime_logs.employee_id AND attendance_logs.log_date = overtime_logs.tanggal', 'left')
                ->where('(attendance_logs.is_rapel = 0 OR attendance_logs.is_rapel IS NULL)')
                ->select('overtime_logs.*')
                ->get()->getResultArray();

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

            // Apply prorated basic salary if the employee is a new hire joining within this period
            if (!empty($emp['tgl_masuk'])) {
                $joinTs = strtotime($emp['tgl_masuk']);
                $joinDateStr = date('Y-m-d', $joinTs);
                if ($joinDateStr >= $empStartDateStr && $joinDateStr <= $empEndDateStr) {
                    $baseSalary = $gajiProrata;
                    $bpjsWageBase = $baseSalary;
                    $pphWageBase = $baseSalary;
                }
            }

            // Resolve payroll scheme if available for overtime type settings
            $ps = null;
            if ($payrollConfig && !empty($payrollConfig->payroll_scheme_id)) {
                $ps = $db->table('payroll_schemes')->where('id', $payrollConfig->payroll_scheme_id)->get()->getRow();
            }

            $overtimeType = ($ps && !empty($ps->overtime_type)) ? $ps->overtime_type : 'standard';
            $overtimePay = 0.0;
            $lemburReguler = 0.0;
            $lemburLibur = 0.0;

            if ($overtimeType === 'lumpsum') {
                $lumpsumSubtype = $ps->lumpsum_subtype ?? 'per_jam';
                $lumpsumNominal = floatval($ps->lumpsum_nominal ?? 0);
                
                if (count($overtimeLogs) > 0) {
                    $totalJamLemburLog = 0;
                    foreach ($overtimeLogs as $otLog) {
                        $totalJamLemburLog += floatval($otLog['jam_lembur']);
                    }
                    $dk['lembur'] = $totalJamLemburLog;
                    
                    if ($lumpsumSubtype === 'per_jam') {
                        $overtimePay = $totalJamLemburLog * $lumpsumNominal;
                    } elseif ($lumpsumSubtype === 'harian') {
                        $overtimePay = count($overtimeLogs) * $lumpsumNominal;
                    } elseif ($lumpsumSubtype === 'bulanan') {
                        $overtimePay = $lumpsumNominal;
                    }
                } else {
                    // Backward compatible: jika tidak ada overtime_logs, gunakan data form lama
                    if (isset($dk['lembur']) && floatval($dk['lembur']) > 0) {
                        $totalJamLembur = floatval($dk['lembur']);
                        if ($lumpsumSubtype === 'per_jam') {
                            $overtimePay = $totalJamLembur * $lumpsumNominal;
                        } elseif ($lumpsumSubtype === 'harian') {
                            $overtimePay = ceil($totalJamLembur / 3) * $lumpsumNominal; // Treat max 3 hours as 1 day
                        } elseif ($lumpsumSubtype === 'bulanan') {
                            $overtimePay = $lumpsumNominal;
                        }
                    }
                }
            } else {
                // Standard progressive multiplier
                $totalEmber1 = 0; // Jam pertama tiap hari → 1.5x
                $totalEmber2 = 0; // Jam 2-3 tiap hari → 2.0x

                if (count($overtimeLogs) > 0) {
                    $totalJamLemburLog = 0;
                    foreach ($overtimeLogs as $otLog) {
                        $jam = floatval($otLog['jam_lembur']);
                        $totalJamLemburLog += $jam;
                        
                        // Check if date is holiday or sunday/saturday
                        $isHoliday = intval($otLog['is_holiday'] ?? 0);
                        if (!$isHoliday) {
                            $dayOfWeek = date('w', strtotime($otLog['tanggal']));
                            if ($dayOfWeek == 0) {
                                $isHoliday = 1;
                            } else {
                                $holiday = $db->table('holiday_calendar')->where('tanggal', $otLog['tanggal'])->get()->getRow();
                                if ($holiday) {
                                    $isHoliday = 1;
                                } elseif ($dayOfWeek == 6) {
                                    // Saturday is weekend/holiday only for 5-day work weeks
                                    $empWork = $db->table('employees')
                                        ->select('employees.hari_kerja, positions.hari_kerja as position_hari_kerja')
                                        ->join('positions', 'positions.id = employees.position_id', 'left')
                                        ->where('employees.id', intval($emp['id']))
                                        ->get()->getRow();
                                    $workDaysPerWeek = 5;
                                    if ($empWork) {
                                        $workDaysPerWeek = intval($empWork->hari_kerja ?: ($empWork->position_hari_kerja ?: 5));
                                    }
                                    $isHoliday = ($workDaysPerWeek < 6) ? 1 : 0;
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
            }

            // Potongan Absen — berdasarkan attendance_logs berdasarkan cutoff period
            $absenCount = 0;
            $absenLogs = $db->table('attendance_logs')
                ->where('employee_id', $emp['id'])
                ->where('log_date >=', $effectiveStartDateStr)
                ->where('log_date <=', $empEndDateStr)
                ->where('status', 'Absen')
                ->get()->getResultArray();
            $absenCount = count($absenLogs);

            // Backward compatible: gunakan data alpa dari form jika attendance_logs kosong
            if ($absenCount === 0 && isset($dk['alpa']) && intval($dk['alpa']) > 0) {
                $absenCount = intval($dk['alpa']);
            }
            $dk['alpa'] = $absenCount;

            // ── Potongan Alfa ─────────────────────────────────────────────────
            // Hitung jumlah hari tidak masuk: alfa + early leave dihitung alfa
            $earlyLeaveAlfaDays = $db->table('attendance_logs')
                ->where('employee_id', $emp['id'])
                ->where('log_date >=', $effectiveStartDateStr)
                ->where('log_date <=', $empEndDateStr)
                ->where('is_early_leave_alfa', 1)
                ->countAllResults();

            $totalAbsenDays = $absenCount + $earlyLeaveAlfaDays;

            // Ikut skema absensi dari payroll_scheme
            $potonganAlpa = 0.0;
            if ($payrollConfig) {
                $ps = null;
                if (!empty($payrollConfig->payroll_scheme_id)) {
                    $ps = $db->table('payroll_schemes')->where('id', $payrollConfig->payroll_scheme_id)->get()->getRow();
                }

                if ($ps && intval($ps->absen_tidak_potong ?? 0) == 1) {
                    // Attendance Does Not Deduct Salary
                    $potonganAlpa = 0.0;
                } elseif ($ps && floatval($ps->nominal_potongan ?? 0) > 0) {
                    // Attendance Deducts Nominal
                    $potonganAlpa = floatval($ps->nominal_potongan) * $totalAbsenDays;
                } else {
                    // Prorate (default): gaji / hari kerja * hari tidak masuk
                    $potonganAlpa = ($baseSalary / $standardDays) * $totalAbsenDays;
                }
            } else {
                // Fallback: prorate
                $potonganAlpa = ($baseSalary / $standardDays) * $totalAbsenDays;
            }

            // ── Potongan Keterlambatan ────────────────────────────────────────
            // Ambil total denda_terlambat dari attendance_logs
            $totalDendaLate = $db->table('attendance_logs')
                ->selectSum('denda_terlambat')
                ->selectSum('late_penalty_hours')
                ->where('employee_id', $emp['id'])
                ->where('log_date >=', $effectiveStartDateStr)
                ->where('log_date <=', $empEndDateStr)
                ->get()->getRow();

            $potonganLate = $totalDendaLate ? floatval($totalDendaLate->denda_terlambat) : 0.0;

            // Fallback lama jika belum pakai denda scheme
            if ($potonganLate == 0) {
                $lateHoursSum = $totalDendaLate ? floatval($totalDendaLate->late_penalty_hours) : 0.0;
                if ($lateHoursSum > 0) {
                    $potonganLate = $lateHoursSum * $upahPerJam;
                }
            }

            // Early leave yang dihitung alfa sudah masuk ke absent_penalty di atas
            $potonganEarly = 0.0;

            // Langkah 5: Rapel Otomatis untuk Karyawan Baru (berdasarkan cutoff date)
            if (!empty($emp['tgl_masuk'])) {
                $joinDate = strtotime($emp['tgl_masuk']);
                $joinDay5 = intval(date('j', $joinDate));
                $joinMonth5 = intval(date('n', $joinDate));
                $joinYear5 = intval(date('Y', $joinDate));
                
                // Resolve cutoff start untuk penentuan rapel
                $gpStartField5 = "cutoff_gaji_pokok_start";
                $gpRefField5 = "cutoff_gaji_pokok_schedule_ref";
                $gpStartVal5 = ($empConfig && isset($empConfig->$gpStartField5)) ? intval($empConfig->$gpStartField5) : null;
                $gpRefId5 = ($empConfig && isset($empConfig->$gpRefField5)) ? intval($empConfig->$gpRefField5) : null;
                if ($gpRefId5) {
                    $sched5 = $db->table('payroll_schedules')->where('id', $gpRefId5)->get()->getRow();
                    if ($sched5) $gpStartVal5 = intval($sched5->cutoff_start);
                }
                if ($gpStartVal5 === null) {
                    $gpStartVal5 = ($empConfig && isset($empConfig->cutoff_start)) ? intval($empConfig->cutoff_start) : 1;
                }
                
                // Jika join_date pada bulan ini dan joinDay >= cutoff start → rapel ke bulan depan
                $periodeEnd = strtotime(date('Y-m-t', strtotime("$tahun-$bulan-01")));
                $isInCurrentMonth = ($joinYear5 === intval($tahun) && $joinMonth5 === intval($bulan));
                
                if ($isInCurrentMonth && $joinDay5 >= $gpStartVal5 && $joinDate <= $periodeEnd) {
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

            // ── Denda Berdasarkan Skema ────────────────────────────────────────
            // Ambil konfigurasi denda dari scheme template yang aktif untuk karyawan
            $dendaTerlambatPerJam = 0;
            $dendaAlfaPerHari = 0;
            $earlyLeaveThreshold = 0;

            if ($schemeTemplate) {
                $dendaTerlambatPerJam = floatval($schemeTemplate['denda_terlambat_per_jam'] ?? 0);
                $dendaAlfaPerHari = floatval($schemeTemplate['denda_alfa_per_hari'] ?? 0);
                $earlyLeaveThreshold = intval($schemeTemplate['early_leave_threshold'] ?? 0);
            }

            // ── Hitung Denda Keterlambatan ────────────────────────────────────
            // Ambil semua attendance_logs untuk periode ini dan hitung denda secara real-time
            $attendanceLogsForPenalty = $db->table('attendance_logs')
                ->where('employee_id', $emp['id'])
                ->where('log_date >=', $empStartDateStr)
                ->where('log_date <=', $empEndDateStr)
                ->get()->getResultArray();

            $totalDendaTerlambat = 0;
            $totalDendaAlfa = 0;
            $totalAbsentPenalty = 0;

            foreach ($attendanceLogsForPenalty as $log) {
                $lateMinutes = intval($log['late_minutes'] ?? 0);
                $status = $log['status'] ?? '';
                $earlyLeaveMinutes = intval($log['early_leave_minutes'] ?? 0);
                
                // Hitung denda keterlambatan (ceiling per jam)
                if ($lateMinutes > 0 && $dendaTerlambatPerJam > 0) {
                    $lateHours = ceil($lateMinutes / 60); // Ceiling per jam
                    $dendaLate = $lateHours * $dendaTerlambatPerJam;
                    $totalDendaTerlambat += $dendaLate;
                    
                    // Update attendance_logs dengan nilai denda yang dihitung
                    $db->table('attendance_logs')
                        ->where('id', $log['id'])
                        ->update([
                            'late_penalty_hours' => $lateHours,
                            'denda_terlambat' => $dendaLate
                        ]);
                }
                
                // Hitung denda alfa (tidak masuk)
                if ($status === 'Absen' && $dendaAlfaPerHari > 0) {
                    $totalDendaAlfa += $dendaAlfaPerHari;
                    
                    $db->table('attendance_logs')
                        ->where('id', $log['id'])
                        ->update([
                            'denda_alfa' => $dendaAlfaPerHari,
                            'absent_penalty' => $dendaAlfaPerHari
                        ]);
                }
                
                // Hitung early leave yang melebihi threshold (dianggap alfa)
                if ($earlyLeaveMinutes > $earlyLeaveThreshold && $earlyLeaveThreshold > 0 && $dendaAlfaPerHari > 0) {
                    $totalDendaAlfa += $dendaAlfaPerHari;
                    
                    $db->table('attendance_logs')
                        ->where('id', $log['id'])
                        ->update([
                            'is_early_leave_alfa' => 1,
                            'denda_alfa' => $dendaAlfaPerHari,
                            'absent_penalty' => $dendaAlfaPerHari
                        ]);
                }
            }

            // Total semua penalty
            $totalAbsentPenalty = $totalDendaAlfa;
            $potonganAlpa = $totalAbsentPenalty;
            $potonganLate = $totalDendaTerlambat;

            // Fallback ke perhitungan lama jika belum ada skema denda
            if ($potonganAlpa == 0 && $absenCount > 0) {
                $potonganAlpa = ($baseSalary / $standardDays) * $absenCount;
            }

            if ($potonganLate == 0) {
                // Hitung late hours sum dari attendance_logs
                $lateHoursSum = 0;
                foreach ($attendanceLogsForPenalty as $log) {
                    $lateHours = floatval($log['late_hours'] ?? 0);
                    $lateHoursSum += $lateHours;
                }
                if ($lateHoursSum > 0) {
                    $potonganLate = $lateHoursSum * $upahPerJam;
                }
            }

            // Early leave yang dihitung alfa sudah masuk ke absent_penalty di atas
            $potonganEarly = 0.0;

            // Process PKWT Components
            foreach ($empComponents as $comp) {
                $nilaiKomponen = 0;
                if (!empty($comp['jenis_komponen'])) {
                    $base_nilai = floatval($comp['nilai']);
                    $sumber_nilai = $comp['sumber_nilai'] ?? 'nominal';
                    if ($sumber_nilai === 'ump') {
                        $base_nilai = $umpWageValue * ($base_nilai / 100);
                    } else if ($sumber_nilai === 'umk') {
                        $base_nilai = $umkWageValue * ($base_nilai / 100);
                    } else if ($sumber_nilai === 'ump_umk') {
                        $base_nilai = $empMinimumWage * ($base_nilai / 100);
                    }
                    
                    // Scale by period
                    if (($comp['periode'] ?? '') === 'hari' || ($comp['periode'] ?? '') === 'hari_kerja') {
                        // Komponen harian: kalikan dengan hari kerja aktual
                        $nilaiKomponen = $base_nilai * intval($dk['hadir']);
                    } elseif (($comp['periode'] ?? '') === 'minggu') {
                        $nilaiKomponen = $base_nilai * 4;
                    } elseif (($comp['periode'] ?? '') === 'tahun') {
                        $nilaiKomponen = $base_nilai / 12;
                    } else {
                        // bulanan
                        $isProrateAbs = false;
                        if ($ps && ($ps->prorate == 1)) {
                            $isProrateAbs = true;
                        }
                        $isCompAdhoc = isset($comp['allowance_type']) && $comp['allowance_type'] === 'Ad-hoc';
                        if ($isProrateAbs && isset($comp['sifat_kompensasi']) && $comp['sifat_kompensasi'] === 'tidak_tetap' && !$isCompAdhoc) {
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

            // ── Process Scheme Template Allowances ────────────────────────────
            // Tambahkan tunjangan dari scheme template dengan dukungan periode harian
            if ($schemeTemplate) {
                $schemeAllowances = [
                    'Tunjangan Transport' => floatval($schemeTemplate['tunjangan_transport'] ?? 0),
                    'Tunjangan Makan Harian' => floatval($schemeTemplate['tunjangan_makan'] ?? 0),
                    'Tunjangan Komunikasi' => floatval($schemeTemplate['tunjangan_komunikasi'] ?? 0),
                    'Tunjangan Jabatan' => floatval($schemeTemplate['tunjangan_jabatan'] ?? 0),
                    'Tunjangan Kehadiran' => floatval($schemeTemplate['tunjangan_kehadiran'] ?? 0),
                    'Tunjangan Kinerja' => floatval($schemeTemplate['tunjangan_kinerja'] ?? 0),
                ];

                foreach ($schemeAllowances as $allowanceName => $allowanceValue) {
                    if ($allowanceValue > 0) {
                        // Debug log untuk scheme template allowances
                        error_log("DEBUG: Processing scheme template allowance - Name: '$allowanceName', Value: $allowanceValue, Working Days: " . intval($dk['hadir']));
                        
                        // Tunjangan Makan Harian dikalikan dengan jumlah hari kerja
                        if ($allowanceName === 'Tunjangan Makan Harian') {
                            $finalValue = $allowanceValue * intval($dk['hadir']);
                            error_log("DEBUG: Scheme template daily meal - Base: $allowanceValue, Days: " . intval($dk['hadir']) . ", Final: $finalValue");
                        } else {
                            // Tunjangan lainnya tetap nominal bulanan
                            $finalValue = $allowanceValue;
                        }

                        $customTunjangan += $finalValue;

                        // Check BPJS and PPh inclusion for this allowance
                        $bpjsField = '';
                        $pphField = '';
                        
                        switch ($allowanceName) {
                            case 'Tunjangan Transport':
                                $bpjsField = 'bpjs_inc_transport';
                                $pphField = 'pph_inc_transport';
                                break;
                            case 'Tunjangan Makan Harian':
                                $bpjsField = 'bpjs_inc_makan';
                                $pphField = 'pph_inc_makan';
                                break;
                            case 'Tunjangan Komunikasi':
                                $bpjsField = 'bpjs_inc_komunikasi';
                                $pphField = 'pph_inc_komunikasi';
                                break;
                            case 'Tunjangan Jabatan':
                                $bpjsField = 'bpjs_inc_jabatan';
                                $pphField = 'pph_inc_jabatan';
                                break;
                            case 'Tunjangan Kehadiran':
                                $bpjsField = 'bpjs_inc_kehadiran';
                                $pphField = 'pph_inc_kehadiran';
                                break;
                            case 'Tunjangan Kinerja':
                                $bpjsField = 'bpjs_inc_kinerja';
                                $pphField = 'pph_inc_kinerja';
                                break;
                        }

                        $isBpjsInc = !empty($bpjsField) && ($schemeTemplate[$bpjsField] ?? 0) == 1;
                        $isPphInc = !empty($pphField) && ($schemeTemplate[$pphField] ?? 0) == 1;

                        if ($isBpjsInc) {
                            $bpjsWageBase += $finalValue;
                        }
                        if ($isPphInc) {
                            $pphWageBase += $finalValue;
                        }

                        $customDetails[] = [
                            'nama_komponen' => $allowanceName,
                            'tipe' => 'Tunjangan',
                            'jumlah' => $finalValue
                        ];
                    }
                }

                // Process scheme template deductions
                $schemeDeductions = [
                    'Potongan Pinjaman' => floatval($schemeTemplate['potongan_pinjaman'] ?? 0),
                    'Potongan Kasbon' => floatval($schemeTemplate['potongan_kasbon'] ?? 0),
                    'Potongan Lainnya' => floatval($schemeTemplate['potongan_lainnya'] ?? 0),
                ];

                foreach ($schemeDeductions as $deductionName => $deductionValue) {
                    if ($deductionValue > 0) {
                        $customPotongan += $deductionValue;
                        
                        $customDetails[] = [
                            'nama_komponen' => $deductionName,
                            'tipe' => 'Potongan',
                            'jumlah' => $deductionValue
                        ];
                    }
                }
            }

            // ── Comprehensive Rapel Engine ─────────────────────────────────────
            // 1. Previous month prorate difference:
            $prevUnpaidSalary = 0.0;
            if (!empty($emp['tgl_masuk'])) {
                $prevMonth = intval($bulan) - 1;
                $prevYear = intval($tahun);
                if ($prevMonth == 0) {
                    $prevMonth = 12;
                    $prevYear--;
                }
                $prevPeriodStr = $prevMonth . '-' . $prevYear;
                
                // Check if employee has a payroll record for the previous period
                $prevPayroll = $db->table('payrolls')
                    ->where('employee_id', $emp['id'])
                    ->where('bulan', $prevMonth)
                    ->where('tahun', $prevYear)
                    ->get()->getRow();
                
                // If they joined before the current period start date and have no payroll record for last month
                if (!$prevPayroll && strtotime($emp['tgl_masuk']) < strtotime($empStartDateStr)) {
                    // Resolve previous period standard days
                    $prevWorkDaysConfig = isset($emp['hari_kerja']) ? intval($emp['hari_kerja']) : 5;
                    if ($prevWorkDaysConfig === 7) {
                        // 7 hari kerja: gunakan jumlah hari aktual di bulan sebelumnya
                        $prevDefaultDays = intval(date('t', mktime(0, 0, 0, $prevMonth, 1, $prevYear)));
                    } elseif ($prevWorkDaysConfig === 6) {
                        $prevDefaultDays = 26;
                    } else {
                        $prevDefaultDays = 22;
                    }
                    $prevStandardDays = isset($emp['custom_standard_days']) && intval($emp['custom_standard_days']) > 0 
                        ? intval($emp['custom_standard_days']) 
                        : $prevDefaultDays;
                    if ($prevStandardDays <= 0) {
                        $prevStandardDays = $prevDefaultDays;
                    }
                    
                    // Get their actual attendance logs from last month (before current period start)
                    $prevPeriodLogs = $db->table('attendance_logs')
                        ->where('employee_id', $emp['id'])
                        ->where('log_date <', $empStartDateStr)
                        ->where('status', 'Hadir')
                        ->get()->getResultArray();
                    
                    $prevDaysWorked = count($prevPeriodLogs);
                    if ($prevDaysWorked > 0) {
                        $prevUnpaidSalary = ($prevDaysWorked / $prevStandardDays) * $baseSalary;
                    }
                }
            }

            // 2. Sum up overtime pay from previous month(s) marked as rapel (is_rapel = 1 and matching payout_period)
            $overtimeRapelLogs = $db->table('overtime_logs')
                ->where('employee_id', $emp['id'])
                ->where('status', 'Approved')
                ->where('is_rapel', 1)
                ->where('payout_period', $payoutPeriodStr)
                ->get()->getResultArray();

            $totalRapelEmber1 = 0;
            $totalRapelEmber2 = 0;
            $rapelLemburLibur = 0;
            $rapelOvertimePay = 0.0;
            
            if ($overtimeType === 'lumpsum') {
                $lumpsumSubtype = $ps->lumpsum_subtype ?? 'per_jam';
                $lumpsumNominal = floatval($ps->lumpsum_nominal ?? 0);
                
                if (count($overtimeRapelLogs) > 0) {
                    if ($lumpsumSubtype === 'per_jam') {
                        $totalRapelJam = 0;
                        foreach ($overtimeRapelLogs as $otLog) {
                            $totalRapelJam += floatval($otLog['jam_lembur']);
                        }
                        $rapelOvertimePay = $totalRapelJam * $lumpsumNominal;
                    } elseif ($lumpsumSubtype === 'harian') {
                        $rapelOvertimePay = count($overtimeRapelLogs) * $lumpsumNominal;
                    } elseif ($lumpsumSubtype === 'bulanan') {
                        $rapelOvertimePay = $lumpsumNominal;
                    }
                }
            } else {
                foreach ($overtimeRapelLogs as $otLog) {
                    $jam = floatval($otLog['jam_lembur']);
                    
                    $isHoliday = intval($otLog['is_holiday'] ?? 0);
                    if (!$isHoliday) {
                        $dayOfWeek = date('w', strtotime($otLog['tanggal']));
                        if ($dayOfWeek == 0) {
                            $isHoliday = 1;
                        } else {
                            $holiday = $db->table('holiday_calendar')->where('tanggal', $otLog['tanggal'])->get()->getRow();
                            if ($holiday) {
                                $isHoliday = 1;
                            } elseif ($dayOfWeek == 6) {
                                // Saturday check
                                $empWork = $db->table('employees')
                                    ->select('employees.hari_kerja, positions.hari_kerja as position_hari_kerja')
                                    ->join('positions', 'positions.id = employees.position_id', 'left')
                                    ->where('employees.id', intval($emp['id']))
                                    ->get()->getRow();
                                $workDaysPerWeek = 5;
                                if ($empWork) {
                                    $workDaysPerWeek = intval($empWork->hari_kerja ?: ($empWork->position_hari_kerja ?: 5));
                                }
                                $isHoliday = ($workDaysPerWeek < 6) ? 1 : 0;
                            }
                        }
                    }

                    if ($isHoliday) {
                        if ($jam <= 6) {
                            $rapelLemburLibur += $jam * 2 * $upahPerJam;
                        } elseif ($jam == 7) {
                            $rapelLemburLibur += (6 * 2 * $upahPerJam) + (1 * 3 * $upahPerJam);
                        } else {
                            $rapelLemburLibur += (6 * 2 * $upahPerJam) + (1 * 3 * $upahPerJam) + (($jam - 7) * 4 * $upahPerJam);
                        }
                    } else {
                        $jam = min($jam, 3);
                        $ember1 = min($jam, 1);
                        $ember2 = max($jam - 1, 0);
                        $totalRapelEmber1 += $ember1;
                        $totalRapelEmber2 += $ember2;
                    }
                }
                $rapelLemburReguler = ($totalRapelEmber1 * 1.5 * $upahPerJam) + ($totalRapelEmber2 * 2.0 * $upahPerJam);
                $rapelOvertimePay = $rapelLemburReguler + $rapelLemburLibur;
            }

            // 3. Subtract unpaid leave/absence deductions from previous month(s) marked as rapel
            $attendanceRapelLogs = $db->table('attendance_logs')
                ->where('employee_id', $emp['id'])
                ->where('is_rapel', 1)
                ->where('payout_period', $payoutPeriodStr)
                ->get()->getResultArray();

            $rapelAbsenDays = 0;
            $rapelLateHours = 0.0;
            foreach ($attendanceRapelLogs as $log) {
                if ($log['status'] === 'Absen' || intval($log['is_early_leave_alfa'] ?? 0) === 1) {
                    $rapelAbsenDays++;
                }
                if (floatval($log['late_hours'] ?? 0) > 0) {
                    $rapelLateHours += floatval($log['late_hours']);
                }
            }

            $rapelDeduction = 0.0;
            if ($ps && floatval($ps->nominal_potongan ?? 0) > 0) {
                $rapelDeduction = floatval($ps->nominal_potongan) * $rapelAbsenDays;
            } else {
                $rapelDeduction = ($baseSalary / $standardDays) * $rapelAbsenDays;
            }

            if ($dendaTerlambatPerJam > 0) {
                $rapelDeduction += $rapelLateHours * $dendaTerlambatPerJam;
            } else {
                $rapelDeduction += $rapelLateHours * $upahPerJam;
            }

            // Calculate Early Arrival Pay
            $approvedEarlyArrivals = $db->table('early_arrival')
                ->where('employee_id', $emp['id'])
                ->where('date >=', $empStartDateStr)
                ->where('date <=', $empEndDateStr)
                ->where('status', 'APPROVED')
                ->where('payroll_status', 'NOT_PROCESSED')
                ->get()->getResultArray();

            $totalEarlyArrivalMinutes = 0;
            $earlyArrivalIdsToProcess = [];
            foreach ($approvedEarlyArrivals as $eaLog) {
                $totalEarlyArrivalMinutes += intval($eaLog['eligible_minutes']);
                $earlyArrivalIdsToProcess[] = $eaLog['id'];
            }

            $earlyArrivalPay = 0.0;
            if ($totalEarlyArrivalMinutes > 0) {
                $earlyArrivalPay = ($totalEarlyArrivalMinutes / 60) * $upahPerJam;
                $customTunjangan += $earlyArrivalPay;
                $customDetails[] = [
                    'nama_komponen' => 'Early Arrival',
                    'tipe' => 'Tunjangan',
                    'jumlah' => $earlyArrivalPay
                ];

                // Update early arrival records to PROCESSED
                $db->table('early_arrival')
                    ->whereIn('id', $earlyArrivalIdsToProcess)
                    ->update([
                        'payroll_status' => 'PROCESSED',
                        'payroll_period' => $payoutPeriodStr,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
            }

            // 4. Combined Net Rapel
            $netRapel = $prevUnpaidSalary + $rapelOvertimePay - $rapelDeduction;

            if ($netRapel > 0) {
                $customTunjangan += $netRapel;
                $customDetails[] = [
                    'nama_komponen' => 'Rapel Gaji & Lembur',
                    'tipe' => 'Tunjangan',
                    'jumlah' => $netRapel
                ];
            } elseif ($netRapel < 0) {
                $absNetRapel = abs($netRapel);
                $customPotongan += $absNetRapel;
                $customDetails[] = [
                    'nama_komponen' => 'Rapel Gaji & Lembur',
                    'tipe' => 'Potongan',
                    'jumlah' => $absNetRapel
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

            // Adjust PPh wage base for attendance variations & overtime (including Rapel)
            $pphWageBaseFinal = $pphWageBase + $overtimePay - $potonganAlpa + $netRapel;

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

                // Reset early arrivals back to NOT_PROCESSED
                $db->table('early_arrival')
                    ->where('employee_id', $emp['id'])
                    ->where('payroll_period', $bulan . '-' . $tahun)
                    ->update([
                        'payroll_status' => 'NOT_PROCESSED',
                        'payroll_period' => null,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
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

            // Resolve BPJS Rates dynamically
            $bpjsSrc = $bpjsScheme ?: ($schemeTemplate ?: $taxScheme);
            $isBpjsTemplate = is_array($bpjsSrc);

            $kesRateEmp = 1.0;
            $jhtRateEmp = 2.0;
            $jpRateEmp = 1.0;
            $kesRateComp = 4.0;
            $jhtRateComp = 3.7;
            $jpRateComp = 2.0;
            $jkkRateComp = 0.24;
            $jkmRateComp = 0.30;

            if ($bpjsSrc) {
                $kesRateEmp = floatval($isBpjsTemplate ? ($bpjsSrc['bpjs_kes_karyawan'] ?? 1.0) : ($bpjsSrc->bpjs_kes_karyawan ?? 1.0));
                $jhtRateEmp = floatval($isBpjsTemplate ? ($bpjsSrc['bpjs_jht_karyawan'] ?? 2.0) : ($bpjsSrc->bpjs_jht_karyawan ?? 2.0));
                $jpRateEmp = floatval($isBpjsTemplate ? ($bpjsSrc['bpjs_jp_karyawan'] ?? 1.0) : ($bpjsSrc->bpjs_jp_karyawan ?? 1.0));
                $kesRateComp = floatval($isBpjsTemplate ? ($bpjsSrc['bpjs_kes_perusahaan'] ?? 4.0) : ($bpjsSrc->bpjs_kes_perusahaan ?? 4.0));
                $jhtRateComp = floatval($isBpjsTemplate ? ($bpjsSrc['bpjs_jht_perusahaan'] ?? 3.7) : ($bpjsSrc->bpjs_jht_perusahaan ?? 3.7));
                $jpRateComp = floatval($isBpjsTemplate ? ($bpjsSrc['bpjs_jp_perusahaan'] ?? 2.0) : ($bpjsSrc->bpjs_jp_perusahaan ?? 2.0));
                $jkkRateComp = floatval($isBpjsTemplate ? ($bpjsSrc['bpjs_jkk_perusahaan'] ?? 0.24) : ($bpjsSrc->bpjs_jkk_perusahaan ?? 0.24));
                $jkmRateComp = floatval($isBpjsTemplate ? ($bpjsSrc['bpjs_jkm_perusahaan'] ?? 0.30) : ($bpjsSrc->bpjs_jkm_perusahaan ?? 0.30));
            }

            // Simpan Detail standar
            if ($overtimePay > 0) $detailModel->insert(['payroll_id' => $payrollId, 'nama_komponen' => 'Lembur', 'tipe' => 'Tunjangan', 'jumlah' => $overtimePay]);
            if ($taxAllowance > 0) $detailModel->insert(['payroll_id' => $payrollId, 'nama_komponen' => 'Tunjangan Pajak (Gross Up)', 'tipe' => 'Tunjangan', 'jumlah' => $taxAllowance]);
            if ($bpjsKesKaryawan > 0) $detailModel->insert(['payroll_id' => $payrollId, 'nama_komponen' => 'BPJS Kesehatan (' . floatval($kesRateEmp) . '% Karyawan)', 'tipe' => 'Potongan', 'jumlah' => $bpjsKesKaryawan]);
            if ($bpjsJhtKaryawan > 0) $detailModel->insert(['payroll_id' => $payrollId, 'nama_komponen' => 'BPJS TK JHT (' . floatval($jhtRateEmp) . '% Karyawan)', 'tipe' => 'Potongan', 'jumlah' => $bpjsJhtKaryawan]);
            if ($bpjsJpKaryawan > 0) $detailModel->insert(['payroll_id' => $payrollId, 'nama_komponen' => 'BPJS TK JP (' . floatval($jpRateEmp) . '% Karyawan)', 'tipe' => 'Potongan', 'jumlah' => $bpjsJpKaryawan]);
            if ($potonganAlpa > 0) $detailModel->insert(['payroll_id' => $payrollId, 'nama_komponen' => 'Potongan Alfa / Early Leave', 'tipe' => 'Potongan', 'jumlah' => $potonganAlpa]);
            if ($potonganLate > 0) $detailModel->insert(['payroll_id' => $payrollId, 'nama_komponen' => 'Denda Keterlambatan', 'tipe' => 'Potongan', 'jumlah' => $potonganLate]);
            if ($pph21 > 0 && $curTaxMethod !== 'Net') {
                $detailModel->insert(['payroll_id' => $payrollId, 'nama_komponen' => 'Pajak PPh 21', 'tipe' => 'Potongan', 'jumlah' => $pph21]);
            }

            // Simpan Detail Beban Perusahaan (Informasi)
            if ($calc['bpjs_kes_perusahaan'] > 0) $detailModel->insert(['payroll_id' => $payrollId, 'nama_komponen' => 'BPJS Kesehatan (' . floatval($kesRateComp) . '% Beban Perusahaan)', 'tipe' => 'Beban Perusahaan', 'jumlah' => $calc['bpjs_kes_perusahaan']]);
            if ($calc['bpjs_jht_perusahaan'] > 0) $detailModel->insert(['payroll_id' => $payrollId, 'nama_komponen' => 'BPJS TK JHT (' . floatval($jhtRateComp) . '% Beban Perusahaan)', 'tipe' => 'Beban Perusahaan', 'jumlah' => $calc['bpjs_jht_perusahaan']]);
            if ($calc['bpjs_jp_perusahaan'] > 0) $detailModel->insert(['payroll_id' => $payrollId, 'nama_komponen' => 'BPJS TK JP (' . floatval($jpRateComp) . '% Beban Perusahaan)', 'tipe' => 'Beban Perusahaan', 'jumlah' => $calc['bpjs_jp_perusahaan']]);
            if ($calc['bpjs_jkk_perusahaan'] > 0) $detailModel->insert(['payroll_id' => $payrollId, 'nama_komponen' => 'BPJS TK JKK (' . floatval($jkkRateComp) . '% Beban Perusahaan)', 'tipe' => 'Beban Perusahaan', 'jumlah' => $calc['bpjs_jkk_perusahaan']]);
            if ($calc['bpjs_jkm_perusahaan'] > 0) $detailModel->insert(['payroll_id' => $payrollId, 'nama_komponen' => 'BPJS TK JKM (' . floatval($jkmRateComp) . '% Beban Perusahaan)', 'tipe' => 'Beban Perusahaan', 'jumlah' => $calc['bpjs_jkm_perusahaan']]);

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
        $payroll = $this->model->find($id);
        if ($payroll) {
            $db = \Config\Database::connect();
            $db->table('early_arrival')
                ->where('employee_id', $payroll['employee_id'])
                ->where('payroll_period', $payroll['bulan'] . '-' . $payroll['tahun'])
                ->update([
                    'payroll_status' => 'NOT_PROCESSED',
                    'payroll_period' => null,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
        }
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




