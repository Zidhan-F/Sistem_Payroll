<?php

namespace App\Controllers;

use App\Models\DivisionModel;
use App\Models\DepartmentModel;
use App\Models\PositionModel;
use CodeIgniter\RESTful\ResourceController;

class Org extends ResourceController
{
    protected $format = 'json';

    public function index()
    {
        $clientId = $this->request->getGet('client_id');
        $divModel = new \App\Models\DivisionModel();
        $deptModel = new \App\Models\DepartmentModel();
        $posModel = new \App\Models\PositionModel();
        $empModel = new \App\Models\EmployeeModel();

        if (!$clientId) {
            return $this->respond(['error' => 'Client ID is required'], 400);
        }
        // Ambil Divisi
        $divisions = $divModel->where('client_id', $clientId)->orderBy('nama', 'ASC')->findAll();

        foreach ($divisions as &$div) {
            // Ambil Departemen per Divisi
            $div['departments'] = $deptModel->where('division_id', $div['id'])->orderBy('nama', 'ASC')->findAll();

            foreach ($div['departments'] as &$dept) {
                // Ambil Posisi per Departemen
                $dept['positions'] = $posModel->where('department_id', $dept['id'])->orderBy('nama', 'ASC')->findAll();

                foreach ($dept['positions'] as &$pos) {
                    // Ambil Karyawan di posisi tsb
                    $pos['employees'] = $empModel->where('position_id', $pos['id'])->findAll();
                }
            }
        }

        return $this->respond($divisions);
    }

    public function createDivision()
    {
        $model = new DivisionModel();
        $data = $this->request->getJSON(true);
        if ($id = $model->insert($data)) {
            $data['id'] = $id;
            return $this->respondCreated($data);
        }
        return $this->fail($model->errors());
    }

    public function createDepartment()
    {
        $model = new DepartmentModel();
        $data = $this->request->getJSON(true);
        if ($id = $model->insert($data)) {
            $data['id'] = $id;
            return $this->respondCreated($data);
        }
        return $this->fail($model->errors());
    }

    public function createPosition()
    {
        $model = new PositionModel();
        $empModel = new \App\Models\EmployeeModel();
        $data = $this->request->getJSON(true);
        
        if (empty($data['nama'])) return $this->fail('Nama jabatan harus diisi');

        $db = \Config\Database::connect();
        $db->transStart();

        $posData = [
            'nama' => $data['nama'],
            'department_id' => $data['department_id'],
            'employee_name' => $data['employee_name'] ?? '', // Ensure empty string instead of null
            'email' => $data['email'] ?? '',
            'phone' => $data['phone'] ?? ''
        ];

        $posId = $model->insert($posData);
        
        if ($posId) {
            if (!empty($data['employee_name']) && !empty($data['nik'])) {
                
                $deptModel = new \App\Models\DepartmentModel();
                $dept = $deptModel->select('divisions.client_id')
                                  ->join('divisions', 'divisions.id = departments.division_id')
                                  ->where('departments.id', $data['department_id'])
                                  ->first();

                $empData = [
                    'nama' => $data['employee_name'],
                    'nik' => $data['nik'],
                    'email' => $data['email'] ?? '',
                    'no_rekening' => '-', // Default value to prevent DB error
                    'gaji_pokok' => $data['gaji_pokok'] ?? 0,
                    'position_id' => $posId,
                    'client_id' => $dept['client_id'] ?? $data['client_id'] ?? null,
                    'tgl_masuk' => date('Y-m-d'),
                    'status' => 'Aktif'
                ];
                
                if (!$empModel->insert($empData)) {
                    $errors = $empModel->errors();
                    $db->transRollback();
                    return $this->fail($errors ?: 'Gagal membuat data karyawan (Cek duplikasi NIK)');
                }
            }
            
            $db->transComplete();
            if ($db->transStatus() === FALSE) {
                return $this->fail('Gagal melakukan transaksi database');
            }
            
            return $this->respondCreated(['id' => $posId]);
        }
        
        return $this->fail($model->errors() ?: 'Gagal membuat data jabatan');
    }

    public function getPositionsByClient($clientId)
    {
        $posModel = new PositionModel();
        $positions = $posModel->select('positions.*')
                             ->join('departments', 'departments.id = positions.department_id')
                             ->join('divisions', 'divisions.id = departments.division_id')
                             ->where('divisions.client_id', $clientId)
                             ->findAll();
        return $this->respond($positions);
    }

    public function update($type = null, $id = null)
    {
        $data = $this->request->getJSON(true);
        $model = null;

        if ($type === 'divisi') $model = new DivisionModel();
        elseif ($type === 'department') $model = new DepartmentModel();
        elseif ($type === 'posisi') $model = new PositionModel();

        if ($model && $model->update($id, $data)) {
            $data['id'] = $id;
            return $this->respond($data);
        }
        return $this->fail($model ? $model->errors() : 'Tipe tidak valid');
    }

    public function delete($type = null, $id = null)
    {
        $model = null;
        if ($type === 'divisi') $model = new DivisionModel();
        elseif ($type === 'department') $model = new DepartmentModel();
        elseif ($type === 'posisi') $model = new PositionModel();

        if ($model && $model->delete($id)) {
            return $this->respondDeleted(['message' => 'Deleted successfully']);
        }
        return $this->fail($model ? 'Gagal menghapus' : 'Tipe tidak valid');
    }
}
