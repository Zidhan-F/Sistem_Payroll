<?php

namespace App\Controllers;

use App\Models\EmployeeModel;
use App\Models\SystemLogModel;
use CodeIgniter\RESTful\ResourceController;

class Employee extends ResourceController
{
    protected $modelName = 'App\Models\EmployeeModel';
    protected $format    = 'json';

    public function index()
    {
        // Automatically sync/update missing minimum_wage_ids for existing employees
        $db = \Config\Database::connect();
        $missingEmps = $db->table('employees')
                          ->where('work_location_id IS NOT NULL')
                          ->where('minimum_wage_id IS NULL')
                          ->get()
                          ->getResult();
        if (!empty($missingEmps)) {
            foreach ($missingEmps as $emp) {
                $wageId = $this->resolveMinimumWageId(
                    $emp->work_location_id,
                    $emp->client_id,
                    $emp->division_id,
                    $emp->department_id,
                    $emp->position_id
                );
                if ($wageId) {
                    $db->table('employees')->where('id', $emp->id)->update(['minimum_wage_id' => $wageId]);
                }
            }
        }

        $clientId = $this->request->getGet('client_id');
        if ($clientId) {
            $today = date('Y-m-d');
            $data = $this->model->select('employees.*, positions.nama as nama_posisi, departments.nama as nama_dept, divisions.nama as nama_divisi, COALESCE(employees.division_id, departments.division_id) as division_id, COALESCE(employees.department_id, positions.department_id) as department_id, clients.nama as nama_klien, COALESCE(NULLIF(CAST(employees.alamat AS VARCHAR(MAX)), \'\'), minimum_wages.nama_daerah) as alamat, minimum_wages.tipe as umr_tipe, minimum_wages.nominal as umr_nominal, work_locations.lokasi_kerja as nama_lokasi, work_locations.provinsi as provinsi, work_locations.kota_kabupaten as kota_kabupaten, (SELECT TOP 1 shift_scheme_id FROM employee_shifts WHERE employee_shifts.employee_id = employees.id AND (employee_shifts.end_date IS NULL OR employee_shifts.end_date >= \'' . $today . '\') ORDER BY employee_shifts.start_date DESC) as shift_scheme_id, (SELECT TOP 1 shift_schemes.name FROM employee_shifts LEFT JOIN shift_schemes ON shift_schemes.id = employee_shifts.shift_scheme_id WHERE employee_shifts.employee_id = employees.id AND (employee_shifts.end_date IS NULL OR employee_shifts.end_date >= \'' . $today . '\') ORDER BY employee_shifts.start_date DESC) as shift_name')
                        ->join('positions', 'positions.id = employees.position_id', 'left')
                        ->join('departments', 'departments.id = COALESCE(employees.department_id, positions.department_id)', 'left')
                        ->join('divisions', 'divisions.id = COALESCE(employees.division_id, departments.division_id)', 'left')
                        ->join('clients', 'clients.id = employees.client_id', 'left')
                        ->join('minimum_wages', 'minimum_wages.id = employees.minimum_wage_id', 'left')
                        ->join('work_locations', 'work_locations.id = employees.work_location_id', 'left')
                        ->where('employees.client_id', $clientId)
                        ->findAll();
            return $this->respond($data);
        }
        return $this->respond($this->model->getFullData());
    }

    public function create()
    {
        $data = $this->request->getJSON(true);

        if (!empty($data['npwp'])) {
            $digitsOnly = preg_replace('/\D/', '', $data['npwp']);
            if (strlen($digitsOnly) < 15) {
                return $this->fail('Format NPWP tidak valid. NPWP minimal harus memiliki 15 digit angka.');
            }
        }
        
        if (!empty($data['work_location_id'])) {
            $data['minimum_wage_id'] = $this->resolveMinimumWageId(
                $data['work_location_id'],
                $data['client_id'] ?? null,
                $data['division_id'] ?? null,
                $data['department_id'] ?? null,
                $data['position_id'] ?? null
            );
        }

        if (isset($data['gaji_pokok']) && isset($data['hari_kerja'])) {
            $hk = (int)$data['hari_kerja'];
            $pembagi = ($hk == 5) ? 22 : (($hk == 6) ? 26 : (($hk == 7) ? 30 : 22));
            $data['denda_absen'] = floatval($data['gaji_pokok']) / $pembagi;
        }
        
        $db = \Config\Database::connect();
        if (isset($data['umr_tipe']) && $data['umr_tipe'] === 'NOMINAL' && isset($data['custom_nominal']) && floatval($data['custom_nominal']) > 0) {
            $customNominal = floatval($data['custom_nominal']);
            $exist = $db->table('minimum_wages')
                        ->where('tipe', 'NOMINAL')
                        ->where('nominal', $customNominal)
                        ->get()
                        ->getRow();
            if ($exist) {
                $data['minimum_wage_id'] = $exist->id;
            } else {
                $db->table('minimum_wages')->insert([
                    'tipe' => 'NOMINAL',
                    'nama_daerah' => 'Nominal Kesepakatan',
                    'nominal' => $customNominal,
                    'tahun' => (int)date('Y'),
                    'kode_daerah' => 'NOMINAL',
                    'provinsi' => ''
                ]);
                $data['minimum_wage_id'] = $db->insertID();
            }
        }
        
        unset($data['umr_tipe']);
        unset($data['custom_nominal']);

        // Auto-generate employ_id: {tahun_kontrak}{urutan_5digit}
        $contractYear = date('Y');
        if (!empty($data['start_contract'])) {
            $contractYear = date('Y', strtotime($data['start_contract']));
        }
        $db2 = \Config\Database::connect();
        $db2->transStart();
        $seqRow = $db2->table('employee_sequences')->where('year', $contractYear)->get()->getRow();
        if ($seqRow) {
            $nextSeq = $seqRow->last_sequence + 1;
            $db2->table('employee_sequences')->where('year', $contractYear)->update(['last_sequence' => $nextSeq]);
        } else {
            $lastEmp = $db2->table('employees')
                           ->select('employ_id')
                           ->where("employ_id LIKE '" . $contractYear . "%'")
                           ->orderBy('employ_id', 'DESC')
                           ->limit(1)
                           ->get()
                           ->getRow();
            $nextSeq = 1;
            if ($lastEmp && $lastEmp->employ_id) {
                $nextSeq = ((int) substr($lastEmp->employ_id, 4)) + 1;
            }
            $db2->table('employee_sequences')->insert(['year' => $contractYear, 'last_sequence' => $nextSeq]);
        }
        $db2->transComplete();
        $data['employ_id'] = $contractYear . str_pad($nextSeq, 5, '0', STR_PAD_LEFT);
        $data['nik'] = $data['employ_id'];

        if ($id = $this->model->insert($data)) {
            $data['id'] = $id;

            // Assign shift scheme if provided
            if (!empty($data['shift_scheme_id'])) {
                $dbShift = \Config\Database::connect();
                $dbShift->table('employee_shifts')->insert([
                    'employee_id' => $id,
                    'shift_scheme_id' => intval($data['shift_scheme_id']),
                    'start_date' => $data['start_contract'] ?? date('Y-m-d'),
                    'end_date' => null,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }

            // Generate PKWT
            $contractModel = new \App\Models\ContractModel();
            $tglMulai = $data['tgl_masuk'] ?? date('Y-m-d');
            $tglBerakhir = date('Y-m-d', strtotime('+1 year', strtotime($tglMulai)));
            $contractData = [
                'employee_id' => $id,
                'client_id'   => $data['client_id'] ?? null,
                'no_kontrak'  => 'PKWT-' . date('Ym') . '-' . str_pad($id, 4, '0', STR_PAD_LEFT),
                'tgl_mulai'   => $tglMulai,
                'tgl_berakhir'=> $tglBerakhir,
                'gaji_pokok'  => $data['gaji_pokok'] ?? 0,
                'status_pkwt' => 'Aktif'
            ];
            $contractModel->insert($contractData);

            $desc = 'Menambahkan karyawan baru: Nama: ' . ($data['nama'] ?? 'Unknown');
            $fields = [];
            foreach ($data as $key => $val) {
                if ($key !== 'id' && $key !== 'nama' && !is_array($val) && !is_object($val)) {
                    $fields[] = "$key: $val";
                }
            }
            if (count($fields) > 0) {
                $desc .= "\nDetail: " . implode(", ", $fields);
            }

            $logModel = new SystemLogModel();
            $logModel->logAction('CREATE_EMPLOYEE', $desc, $data['client_id'] ?? null, session()->get('user_id') ?? 1);

            return $this->respondCreated($data);
        }
        return $this->fail($this->model->errors());
    }

    public function update($id = null)
    {
        $data = $this->request->getJSON(true);

        if (!empty($data['npwp'])) {
            $digitsOnly = preg_replace('/\D/', '', $data['npwp']);
            if (strlen($digitsOnly) < 15) {
                return $this->fail('Format NPWP tidak valid. NPWP minimal harus memiliki 15 digit angka.');
            }
        }
        $oldEmp = $this->model->find($id);
        
        if (isset($data['work_location_id'])) {
            $data['minimum_wage_id'] = $this->resolveMinimumWageId(
                $data['work_location_id'],
                $data['client_id'] ?? $oldEmp['client_id'] ?? null,
                $data['division_id'] ?? $oldEmp['division_id'] ?? null,
                $data['department_id'] ?? $oldEmp['department_id'] ?? null,
                $data['position_id'] ?? $oldEmp['position_id'] ?? null
            );
        }

        // Auto-calculate denda_absen if gaji_pokok or hari_kerja is updated
        $gaji = isset($data['gaji_pokok']) ? $data['gaji_pokok'] : ($oldEmp['gaji_pokok'] ?? 0);
        $hk = isset($data['hari_kerja']) ? (int)$data['hari_kerja'] : (int)($oldEmp['hari_kerja'] ?? 5);
        if ($gaji > 0 && $hk > 0) {
            $pembagi = ($hk == 5) ? 22 : (($hk == 6) ? 26 : (($hk == 7) ? 30 : 22));
            $data['denda_absen'] = floatval($gaji) / $pembagi;
        }
        
        $db = \Config\Database::connect();
        if (isset($data['umr_tipe']) && $data['umr_tipe'] === 'NOMINAL' && isset($data['custom_nominal']) && floatval($data['custom_nominal']) > 0) {
            $customNominal = floatval($data['custom_nominal']);
            $exist = $db->table('minimum_wages')
                        ->where('tipe', 'NOMINAL')
                        ->where('nominal', $customNominal)
                        ->get()
                        ->getRow();
            if ($exist) {
                $data['minimum_wage_id'] = $exist->id;
            } else {
                $db->table('minimum_wages')->insert([
                    'tipe' => 'NOMINAL',
                    'nama_daerah' => 'Nominal Kesepakatan',
                    'nominal' => $customNominal,
                    'tahun' => (int)date('Y'),
                    'kode_daerah' => 'NOMINAL',
                    'provinsi' => ''
                ]);
                $data['minimum_wage_id'] = $db->insertID();
            }
        }
        
        unset($data['umr_tipe']);
        unset($data['custom_nominal']);

        if ($this->model->update($id, $data)) {
            
            // Sync Contract / PKWT Gaji Pokok if updated
            if (isset($data['gaji_pokok'])) {
                $contractModel = new \App\Models\ContractModel();
                $contract = $contractModel->where('employee_id', $id)->where('status_pkwt', 'Aktif')->first();
                if ($contract) {
                    $contractModel->update($contract['id'], ['gaji_pokok' => $data['gaji_pokok']]);
                }
            }

            // Sync employee shifts if updated
            if (isset($data['shift_scheme_id'])) {
                $dbShift = \Config\Database::connect();
                $newShiftId = !empty($data['shift_scheme_id']) ? intval($data['shift_scheme_id']) : null;
                $today = date('Y-m-d');

                // Get current active shift
                $currentActive = $dbShift->table('employee_shifts')
                    ->where('employee_id', $id)
                    ->where("(end_date IS NULL OR end_date >= '{$today}')")
                    ->get()->getRow();

                if ($newShiftId) {
                    if ($currentActive) {
                        if ($currentActive->shift_scheme_id != $newShiftId) {
                            $yesterday = date('Y-m-d', strtotime(' -1 day'));
                            if ($currentActive->start_date < $today) {
                                $dbShift->table('employee_shifts')
                                    ->where('id', $currentActive->id)
                                    ->update([
                                        'end_date' => $yesterday,
                                        'updated_at' => date('Y-m-d H:i:s')
                                    ]);
                            } else {
                                $dbShift->table('employee_shifts')
                                    ->where('id', $currentActive->id)
                                    ->delete();
                            }

                            $dbShift->table('employee_shifts')->insert([
                                'employee_id' => $id,
                                'shift_scheme_id' => $newShiftId,
                                'start_date' => $today,
                                'end_date' => null,
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s')
                            ]);
                        }
                    } else {
                        $dbShift->table('employee_shifts')->insert([
                            'employee_id' => $id,
                            'shift_scheme_id' => $newShiftId,
                            'start_date' => $today,
                            'end_date' => null,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
                    }
                } else {
                    if ($currentActive) {
                        $yesterday = date('Y-m-d', strtotime(' -1 day'));
                        if ($currentActive->start_date < $today) {
                            $dbShift->table('employee_shifts')
                                ->where('id', $currentActive->id)
                                ->update([
                                    'end_date' => $yesterday,
                                    'updated_at' => date('Y-m-d H:i:s')
                                ]);
                        } else {
                            $dbShift->table('employee_shifts')
                                ->where('id', $currentActive->id)
                                ->delete();
                        }
                    }
                }
            }
            
            $emp = $this->model->find($id);
            
            $changes = [];
            if ($oldEmp) {
                foreach ($data as $key => $val) {
                    if (array_key_exists($key, $oldEmp) && $oldEmp[$key] != $val && !is_array($val) && !is_object($val)) {
                        $changes[] = "- $key diubah dari '{$oldEmp[$key]}' menjadi '$val'";
                    }
                }
            }
            $desc = 'Memperbarui data karyawan bernama ' . ($emp['nama'] ?? 'ID '.$id) . " (ID: $id)";
            if (count($changes) > 0) {
                $desc .= ".\nPerubahan:\n" . implode("\n", $changes);
            } else {
                $desc .= " (Tidak ada perubahan kolom)";
            }
            
            $logModel = new SystemLogModel();
            $logModel->logAction('UPDATE_EMPLOYEE', $desc, $emp['client_id'] ?? null, session()->get('user_id') ?? 1);

            return $this->respond($data);
        }
        return $this->fail($this->model->errors());
    }

    public function delete($id = null)
    {
        $emp = $this->model->find($id);
        if ($emp && $this->model->delete($id)) {
            $desc = 'Menghapus data karyawan: Nama: ' . ($emp['nama'] ?? 'ID '.$id);
            $fields = [];
            foreach ($emp as $key => $val) {
                if ($key !== 'id' && $key !== 'nama' && !is_array($val) && !is_object($val)) {
                    $fields[] = "$key: $val";
                }
            }
            if (count($fields) > 0) {
                $desc .= "\nData terakhir: " . implode(", ", $fields);
            }
            
            $logModel = new SystemLogModel();
            $logModel->logAction('DELETE_EMPLOYEE', $desc, $emp['client_id'] ?? null, session()->get('user_id') ?? 1);
            return $this->respondDeleted(['id' => $id]);
        }
        return $this->failNotFound();
    }

    public function nextEmployId()
    {
        $year = $this->request->getGet('year') ?? date('Y');
        $db = \Config\Database::connect();
        $seqRow = $db->table('employee_sequences')->where('year', $year)->get()->getRow();
        if ($seqRow) {
            $nextSeq = $seqRow->last_sequence + 1;
        } else {
            $lastEmp = $db->table('employees')
                          ->select('employ_id')
                          ->where("employ_id LIKE '" . $year . "%'")
                          ->orderBy('employ_id', 'DESC')
                          ->limit(1)
                          ->get()
                          ->getRow();
            $nextSeq = 1;
            if ($lastEmp && $lastEmp->employ_id) {
                $nextSeq = ((int) substr($lastEmp->employ_id, 4)) + 1;
            }
        }
        $employId = $year . str_pad($nextSeq, 5, '0', STR_PAD_LEFT);
        return $this->respond(['employ_id' => $employId]);
    }

    private function resolveClientConfig($clientId, $divisionId = null, $departmentId = null, $positionId = null)
    {
        $db = \Config\Database::connect();
        $config = null;
        
        if ($positionId) {
            $config = $db->table('client_payroll_configs')
                ->where('client_id', $clientId)
                ->where('position_id', $positionId)
                ->get()->getRow();
        }
        if (!$config && $departmentId) {
            $config = $db->table('client_payroll_configs')
                ->where('client_id', $clientId)
                ->where('department_id', $departmentId)
                ->where('position_id IS NULL')
                ->get()->getRow();
        }
        if (!$config && $divisionId) {
            $config = $db->table('client_payroll_configs')
                ->where('client_id', $clientId)
                ->where('division_id', $divisionId)
                ->where('department_id IS NULL')
                ->where('position_id IS NULL')
                ->get()->getRow();
        }
        if (!$config) {
            $config = $db->table('client_payroll_configs')
                ->where('client_id', $clientId)
                ->where('division_id IS NULL')
                ->where('department_id IS NULL')
                ->where('position_id IS NULL')
                ->get()->getRow();
        }
        return $config;
    }

    private function resolveMinimumWageId($workLocationId, $clientId = null, $divisionId = null, $departmentId = null, $positionId = null)
    {
        if (!$workLocationId) return null;
        $db = \Config\Database::connect();
        
        // 1. Get work location
        $loc = $db->table('work_locations')->where('id', $workLocationId)->get()->getRow();
        if (!$loc) return null;
        
        // 2. Resolve payroll type from config
        $payrollType = 'UMP/UMK'; // Default fallback
        if ($clientId) {
            $config = $this->resolveClientConfig($clientId, $divisionId, $departmentId, $positionId);
            if ($config && !empty($config->payroll_type)) {
                $payrollType = $config->payroll_type;
                if ($payrollType === 'Template' && !empty($config->payroll_scheme_id)) {
                    $basicComp = $db->table('payroll_components')
                        ->where('scheme_id', $config->payroll_scheme_id)
                        ->groupStart()
                            ->where('jenis_komponen', 'basic_salary')
                            ->orLike('nama', 'Gaji Pokok')
                        ->groupEnd()
                        ->get()
                        ->getRow();
                    if ($basicComp && !empty($basicComp->sumber_nilai)) {
                        if ($basicComp->sumber_nilai === 'ump') {
                            $payrollType = 'UMP';
                        } else if ($basicComp->sumber_nilai === 'umk') {
                            $payrollType = 'UMK';
                        } else if ($basicComp->sumber_nilai === 'ump_umk') {
                            $payrollType = 'UMP/UMK';
                        }
                    }
                }
            }
        }
        
        $mw = null;
        $searchCity = !empty($loc->kota_kabupaten) ? trim(strtolower($loc->kota_kabupaten)) : null;
        $searchProv = !empty($loc->provinsi) ? trim(strtolower($loc->provinsi)) : null;

        if ($payrollType === 'UMK') {
            if ($searchCity) {
                $mw = $db->table('minimum_wages')->where(['tipe' => 'UMK', 'nama_daerah' => $loc->kota_kabupaten])->orderBy('tahun', 'DESC')->get()->getRow();
                if (!$mw) {
                    $mw = $db->table('minimum_wages')->where('tipe', 'UMK')->where('LOWER(nama_daerah)', $searchCity)->orderBy('tahun', 'DESC')->get()->getRow();
                }
            }
        } else if ($payrollType === 'UMP') {
            if ($searchProv) {
                $mw = $db->table('minimum_wages')->where(['tipe' => 'UMP', 'nama_daerah' => $loc->provinsi])->orderBy('tahun', 'DESC')->get()->getRow();
                if (!$mw) {
                    $mw = $db->table('minimum_wages')->where('tipe', 'UMP')->where('LOWER(nama_daerah)', $searchProv)->orderBy('tahun', 'DESC')->get()->getRow();
                }
            }
        } else { // UMP/UMK
            if ($searchCity) {
                $mw = $db->table('minimum_wages')->where(['tipe' => 'UMK', 'nama_daerah' => $loc->kota_kabupaten])->orderBy('tahun', 'DESC')->get()->getRow();
                if (!$mw) {
                    $mw = $db->table('minimum_wages')->where('tipe', 'UMK')->where('LOWER(nama_daerah)', $searchCity)->orderBy('tahun', 'DESC')->get()->getRow();
                }
            }
            if (!$mw && $searchProv) {
                $mw = $db->table('minimum_wages')->where(['tipe' => 'UMP', 'nama_daerah' => $loc->provinsi])->orderBy('tahun', 'DESC')->get()->getRow();
                if (!$mw) {
                    $mw = $db->table('minimum_wages')->where('tipe', 'UMP')->where('LOWER(nama_daerah)', $searchProv)->orderBy('tahun', 'DESC')->get()->getRow();
                }
            }
        }

        if ($mw) {
            return $mw->id;
        }
        return null;
    }
}
