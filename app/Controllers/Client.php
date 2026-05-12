<?php

namespace App\Controllers;

use App\Models\ClientModel;
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
}
