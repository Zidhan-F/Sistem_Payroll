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
        
        $divModel = new DivisionModel();
        $deptModel = new DepartmentModel();
        $posModel = new PositionModel();

        $divBuilder = $divModel->orderBy('id', 'ASC');
        if ($clientId) {
            $divBuilder->where('client_id', $clientId);
        }
        $divs = $divBuilder->findAll();
        
        $depts = $deptModel->orderBy('id', 'ASC')->findAll();
        $pos = $posModel->orderBy('id', 'ASC')->findAll();

        $structure = array_map(function($div) use ($depts, $pos) {
            $divDepts = array_filter($depts, fn($d) => $d['division_id'] == $div['id']);
            $div['departments'] = array_map(function($dept) use ($pos) {
                $dept['positions'] = array_values(array_filter($pos, fn($p) => $p['department_id'] == $dept['id']));
                return $dept;
            }, array_values($divDepts));
            return $div;
        }, $divs);

        return $this->respond($structure);
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
        $data = $this->request->getJSON(true);
        if ($id = $model->insert($data)) {
            $data['id'] = $id;
            return $this->respondCreated($data);
        }
        return $this->fail($model->errors());
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
