<?php

namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\PayrollComponentModel;
use App\Models\SystemLogModel;
use CodeIgniter\RESTful\ResourceController;

class Client extends ResourceController
{
    protected $modelName = 'App\Models\ClientModel';
    protected $format    = 'json';

    public function index()
    {
        $data = $this->model->orderBy('id', 'DESC')->findAll();
        foreach ($data as &$client) {
            if (isset($client['npwp'])) {
                $client['npwp'] = (string)$client['npwp'];
            }
        }
        return $this->respond($data);
    }

    public function create()
    {
        $data = $this->request->getJSON(true);
        if (isset($data['npwp'])) {
            $data['npwp'] = (string)$data['npwp'];
        }
        
        // Generate No Klien Otomatis (Format: CLI-001)
        $count = $this->model->countAllResults();
        $data['no_klien'] = 'CLI-' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);

        if ($id = $this->model->insert($data)) {
            $data['id'] = $id;
            $log = new SystemLogModel();
            $log->logAction('CREATE_CLIENT', 'Menambahkan Klien baru: ' . ($data['nama'] ?? $data['no_klien']), $id, session()->get('user_id') ?? 1);
            return $this->respondCreated($data);
        }
        return $this->fail($this->model->errors());
    }

    public function update($id = null)
    {
        $data = $this->request->getJSON(true);
        if (isset($data['npwp'])) {
            $data['npwp'] = (string)$data['npwp'];
        }
        if ($this->model->update($id, $data)) {
            $data['id'] = $id;
            $log = new SystemLogModel();
            $log->logAction('UPDATE_CLIENT', 'Memperbarui data Klien ID: ' . $id, $id, session()->get('user_id') ?? 1);
            return $this->respond($data);
        }
        return $this->fail($this->model->errors());
    }

    public function delete($id = null)
    {
        $client = $this->model->find($id);
        if ($client && $this->model->delete($id)) {
            $log = new SystemLogModel();
            $log->logAction('DELETE_CLIENT', 'Menghapus data Klien: ' . ($client['nama'] ?? 'ID '.$id), $id, session()->get('user_id') ?? 1);
            return $this->respondDeleted(['message' => 'Client dihapus']);
        }
        return $this->failNotFound('Client tidak ditemukan');
    }

    // --- Schema Payroll & Pajak ---

    public function getSchema($clientId)
    {
        $schema = $this->model->find($clientId);
        return $this->respond($schema ?: []);
    }

    public function saveSchema()
    {
        $data = $this->request->getJSON(true);
        $clientId = $data['client_id'];
        unset($data['client_id']); // Remove client_id since we update by primary key id
        
        $this->model->update($clientId, $data);
        
        $log = new SystemLogModel();
        $log->logAction('UPDATE_CLIENT_SCHEMA', 'Memperbarui skema payroll/pajak Klien ID: ' . $clientId, $clientId, session()->get('user_id') ?? 1);
        
        return $this->respond(['message' => 'Schema saved']);
    }

    // --- Komponen Payroll (Tunjangan/Potongan custom per klien) ---

    public function getComponents($clientId)
    {
        $compModel = new PayrollComponentModel();
        $components = $compModel->getByClient($clientId);
        return $this->respond($components);
    }

    public function saveComponent()
    {
        $data = $this->request->getJSON(true);
        $compModel = new PayrollComponentModel();

        if (!empty($data['id'])) {
            $compModel->update($data['id'], $data);
            $log = new SystemLogModel();
            $log->logAction('UPDATE_COMPONENT', 'Memperbarui komponen ID: ' . $data['id'], $data['client_id'] ?? null, session()->get('user_id') ?? 1);
            return $this->respond(['message' => 'Komponen diperbarui']);
        } else {
            $id = $compModel->insert($data);
            if ($id) {
                $data['id'] = $id;
                $log = new SystemLogModel();
                $log->logAction('CREATE_COMPONENT', 'Menambahkan komponen baru', $data['client_id'] ?? null, session()->get('user_id') ?? 1);
                return $this->respondCreated($data);
            }
            return $this->fail($compModel->errors());
        }
    }

    public function deleteComponent($id)
    {
        $compModel = new PayrollComponentModel();
        $comp = $compModel->find($id);
        if ($comp && $compModel->delete($id)) {
            $log = new SystemLogModel();
            $log->logAction('DELETE_COMPONENT', 'Menghapus komponen ID: ' . $id, $comp['client_id'] ?? null, session()->get('user_id') ?? 1);
            return $this->respondDeleted(['message' => 'Komponen dihapus']);
        }
        return $this->failNotFound('Komponen tidak ditemukan');
    }
}
