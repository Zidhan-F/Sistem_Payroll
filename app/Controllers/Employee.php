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

            $logModel = new SystemLogModel();
            $logModel->logAction('CREATE_EMPLOYEE', 'Menambahkan data karyawan baru bernama ' . ($data['nama'] ?? 'Unknown'), $data['client_id'] ?? null, session()->get('user_id') ?? 1);

            return $this->respondCreated($data);
        }
        return $this->fail($this->model->errors());
    }

    public function update($id = null)
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
            $logModel = new SystemLogModel();
            $logModel->logAction('UPDATE_EMPLOYEE', 'Memperbarui data karyawan bernama ' . ($emp['nama'] ?? 'ID '.$id), $emp['client_id'] ?? null, session()->get('user_id') ?? 1);

            return $this->respond($data);
        }
        return $this->fail($this->model->errors());
    }

    public function delete($id = null)
    {
        $emp = $this->model->find($id);
        if ($emp && $this->model->delete($id)) {
            $logModel = new SystemLogModel();
            $logModel->logAction('DELETE_EMPLOYEE', 'Menghapus data karyawan bernama ' . ($emp['nama'] ?? 'ID '.$id), $emp['client_id'] ?? null, session()->get('user_id') ?? 1);
            return $this->respondDeleted(['id' => $id]);
        }
        return $this->failNotFound();
    }
}
