<?php

namespace App\Controllers;

use App\Models\WorkLocationModel;
use CodeIgniter\RESTful\ResourceController;

class WorkLocation extends ResourceController
{
    protected $modelName = 'App\Models\WorkLocationModel';
    protected $format    = 'json';

    public function index()
    {
        $clientId = $this->request->getGet('client_id');
        if ($clientId) {
            $data = $this->model->select('work_locations.*, clients.nama as nama_klien, divisions.nama as nama_divisi, departments.nama as nama_dept, positions.nama as nama_posisi')
                        ->join('clients', 'clients.id = work_locations.client_id', 'left')
                        ->join('divisions', 'divisions.id = work_locations.division_id', 'left')
                        ->join('departments', 'departments.id = work_locations.department_id', 'left')
                        ->join('positions', 'positions.id = work_locations.position_id', 'left')
                        ->where('work_locations.client_id', $clientId)
                        ->findAll();
            return $this->respond($data);
        }
        return $this->respond($this->model->getFullData());
    }

    public function show($id = null)
    {
        $data = $this->model->find($id);
        if (!$data) {
            return $this->failNotFound('Data lokasi kerja tidak ditemukan');
        }
        return $this->respond($data);
    }

    public function create()
    {
        $data = $this->request->getJSON(true);
        
        // Handle empty values for foreign keys to set to null
        $fields = ['division_id', 'department_id', 'position_id'];
        foreach ($fields as $field) {
            if (isset($data[$field]) && ($data[$field] === '' || $data[$field] === 'null' || $data[$field] == 0)) {
                $data[$field] = null;
            }
        }

        if ($id = $this->model->insert($data)) {
            $data['id'] = $id;
            return $this->respondCreated($data);
        }
        return $this->fail($this->model->errors());
    }

    public function update($id = null)
    {
        $data = $this->request->getJSON(true);
        
        // Handle empty values for foreign keys to set to null
        $fields = ['division_id', 'department_id', 'position_id'];
        foreach ($fields as $field) {
            if (isset($data[$field]) && ($data[$field] === '' || $data[$field] === 'null' || $data[$field] == 0)) {
                $data[$field] = null;
            }
        }

        if ($this->model->update($id, $data)) {
            $data['id'] = $id;
            return $this->respond($data);
        }
        return $this->fail($this->model->errors());
    }

    public function delete($id = null)
    {
        if ($this->model->delete($id)) {
            return $this->respondDeleted(['message' => 'Lokasi kerja berhasil dihapus']);
        }
        return $this->fail('Gagal menghapus lokasi kerja');
    }
}
