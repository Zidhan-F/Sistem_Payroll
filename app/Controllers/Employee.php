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
        $clientId = $this->request->getGet('client_id');
        if ($clientId) {
            $data = $this->model->select('employees.*, positions.nama as nama_posisi, departments.nama as nama_dept, divisions.nama as nama_divisi, clients.nama as nama_klien, positions.department_id as department_id, departments.division_id as division_id, COALESCE(NULLIF(CAST(employees.alamat AS VARCHAR(MAX)), \'\'), minimum_wages.nama_daerah) as alamat, minimum_wages.tipe as umr_tipe, minimum_wages.nominal as umr_nominal, work_locations.lokasi_kerja as nama_lokasi')
                        ->join('positions', 'positions.id = employees.position_id', 'left')
                        ->join('departments', 'departments.id = positions.department_id', 'left')
                        ->join('divisions', 'divisions.id = departments.division_id', 'left')
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
        $countInYear = $db2->table('employees')
                           ->where("employ_id LIKE '" . $contractYear . "%'")
                           ->countAllResults();
        $nextSeq = $countInYear + 1;
        $data['employ_id'] = $contractYear . str_pad($nextSeq, 5, '0', STR_PAD_LEFT);

        if ($id = $this->model->insert($data)) {
            $data['id'] = $id;

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
        $oldEmp = $this->model->find($id);
        
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
        $countInYear = $db->table('employees')
                          ->where("employ_id LIKE '" . $year . "%'")
                          ->countAllResults();
        $nextSeq = $countInYear + 1;
        $employId = $year . str_pad($nextSeq, 5, '0', STR_PAD_LEFT);
        return $this->respond(['employ_id' => $employId]);
    }
}
