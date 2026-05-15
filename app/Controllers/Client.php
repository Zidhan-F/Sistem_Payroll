<?php

namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\ClientSchemaModel;
use App\Models\PayrollComponentModel;
use CodeIgniter\RESTful\ResourceController;

class Client extends ResourceController
{
    protected $modelName = 'App\Models\ClientModel';
    protected $format    = 'json';

    public function index()
    {
        return $this->respond($this->model->orderBy('id', 'DESC')->findAll());
    }

    public function create()
    {
        $data = $this->request->getJSON(true);
        
        // Generate No Klien Otomatis (Format: CLI-001)
        $count = $this->model->countAllResults();
        $data['no_klien'] = 'CLI-' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);

        if ($id = $this->model->insert($data)) {
            $data['id'] = $id;
            return $this->respondCreated($data);
        }
        return $this->fail($this->model->errors());
    }

    public function update($id = null)
    {
        $data = $this->request->getJSON(true);
        if ($this->model->update($id, $data)) {
            $data['id'] = $id;
            return $this->respond($data);
        }
        return $this->fail($this->model->errors());
    }

    public function delete($id = null)
    {
        if ($this->model->delete($id)) {
            return $this->respondDeleted(['message' => 'Client dihapus']);
        }
        return $this->failNotFound('Client tidak ditemukan');
    }

    // --- Schema Payroll & Pajak ---

    public function getSchema($clientId)
    {
        $schemaModel = new ClientSchemaModel();
        $schema = $schemaModel->where('client_id', $clientId)->first();
        return $this->respond($schema ?: []);
    }

    public function saveSchema()
    {
        $data = $this->request->getJSON(true);
        $schemaModel = new ClientSchemaModel();
        $existing = $schemaModel->where('client_id', $data['client_id'])->first();

        if ($existing) {
            $schemaModel->update($existing['id'], $data);
        } else {
            $schemaModel->insert($data);
        }
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
            return $this->respond(['message' => 'Komponen diperbarui']);
        } else {
            $id = $compModel->insert($data);
            if ($id) {
                $data['id'] = $id;
                return $this->respondCreated($data);
            }
            return $this->fail($compModel->errors());
        }
    }

    public function deleteComponent($id)
    {
        $compModel = new PayrollComponentModel();
        if ($compModel->delete($id)) {
            return $this->respondDeleted(['message' => 'Komponen dihapus']);
        }
        return $this->failNotFound('Komponen tidak ditemukan');
    }
}
