<?php

namespace App\Controllers;

use App\Models\EmployeeModel;
use CodeIgniter\RESTful\ResourceController;

class Employee extends ResourceController
{
    protected $modelName = 'App\Models\EmployeeModel';
    protected $format    = 'json';

    public function index()
    {
        $clientId = $this->request->getGet('client_id');
        if ($clientId) {
            $data = $this->model->select('employees.*, positions.nama as nama_posisi, departments.nama as nama_dept, divisions.nama as nama_divisi, clients.nama as nama_klien, positions.department_id as department_id, departments.division_id as division_id')
                        ->join('positions', 'positions.id = employees.position_id', 'left')
                        ->join('departments', 'departments.id = positions.department_id', 'left')
                        ->join('divisions', 'divisions.id = departments.division_id', 'left')
                        ->join('clients', 'clients.id = employees.client_id', 'left')
                        ->where('employees.client_id', $clientId)
                        ->findAll();
            return $this->respond($data);
        }
        return $this->respond($this->model->getFullData());
    }

    public function create()
    {
        $data = $this->request->getJSON(true);
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

            return $this->respondCreated($data);
        }
        return $this->fail($this->model->errors());
    }

    public function update($id = null)
    {
        $data = $this->request->getJSON(true);
        if ($this->model->update($id, $data)) {
            
            // Sync Contract / PKWT Gaji Pokok if updated
            if (isset($data['gaji_pokok'])) {
                $contractModel = new \App\Models\ContractModel();
                $contract = $contractModel->where('employee_id', $id)->where('status_pkwt', 'Aktif')->first();
                if ($contract) {
                    $contractModel->update($contract['id'], ['gaji_pokok' => $data['gaji_pokok']]);
                }
            }

            return $this->respond($data);
        }
        return $this->fail($this->model->errors());
    }

    public function delete($id = null)
    {
        if ($this->model->delete($id)) {
            return $this->respondDeleted(['id' => $id]);
        }
        return $this->failNotFound();
    }
}
