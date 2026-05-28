<?php

namespace App\Controllers;

use App\Models\GlobalDivisionModel;
use App\Models\GlobalDepartmentModel;
use App\Models\GlobalPositionModel;
use CodeIgniter\RESTful\ResourceController;

class GlobalSto extends ResourceController
{
    protected $format = 'json';

    // Divisions CRUD
    public function getDivisions()
    {
        $model = new GlobalDivisionModel();
        return $this->respond($model->orderBy('nama', 'ASC')->findAll());
    }

    public function createDivision()
    {
        $model = new GlobalDivisionModel();
        $data = $this->request->getJSON(true);
        if (empty($data['nama'])) {
            return $this->fail('Nama divisi harus diisi');
        }
        if ($id = $model->insert($data)) {
            $data['id'] = $id;
            return $this->respondCreated($data);
        }
        return $this->fail($model->errors());
    }

    public function updateDivision($id = null)
    {
        $model = new GlobalDivisionModel();
        $data = $this->request->getJSON(true);
        if (empty($data['nama'])) {
            return $this->fail('Nama divisi harus diisi');
        }
        if ($model->update($id, $data)) {
            $data['id'] = $id;
            return $this->respond($data);
        }
        return $this->fail($model->errors());
    }

    public function deleteDivision($id = null)
    {
        $model = new GlobalDivisionModel();
        if ($model->delete($id)) {
            return $this->respondDeleted(['message' => 'Deleted successfully']);
        }
        return $this->fail('Gagal menghapus');
    }

    // Departments CRUD
    public function getDepartments()
    {
        $model = new GlobalDepartmentModel();
        return $this->respond($model->orderBy('nama', 'ASC')->findAll());
    }

    public function createDepartment()
    {
        $model = new GlobalDepartmentModel();
        $data = $this->request->getJSON(true);
        if (empty($data['nama'])) {
            return $this->fail('Nama departemen harus diisi');
        }
        if ($id = $model->insert($data)) {
            $data['id'] = $id;
            return $this->respondCreated($data);
        }
        return $this->fail($model->errors());
    }

    public function updateDepartment($id = null)
    {
        $model = new GlobalDepartmentModel();
        $data = $this->request->getJSON(true);
        if (empty($data['nama'])) {
            return $this->fail('Nama departemen harus diisi');
        }
        if ($model->update($id, $data)) {
            $data['id'] = $id;
            return $this->respond($data);
        }
        return $this->fail($model->errors());
    }

    public function deleteDepartment($id = null)
    {
        $model = new GlobalDepartmentModel();
        if ($model->delete($id)) {
            return $this->respondDeleted(['message' => 'Deleted successfully']);
        }
        return $this->fail('Gagal menghapus');
    }

    // Positions CRUD
    public function getPositions()
    {
        $model = new GlobalPositionModel();
        return $this->respond($model->orderBy('nama', 'ASC')->findAll());
    }

    public function createPosition()
    {
        $model = new GlobalPositionModel();
        $data = $this->request->getJSON(true);
        if (empty($data['nama'])) {
            return $this->fail('Nama posisi harus diisi');
        }
        if ($id = $model->insert($data)) {
            $data['id'] = $id;
            return $this->respondCreated($data);
        }
        return $this->fail($model->errors());
    }

    public function updatePosition($id = null)
    {
        $model = new GlobalPositionModel();
        $data = $this->request->getJSON(true);
        if (empty($data['nama'])) {
            return $this->fail('Nama posisi harus diisi');
        }
        if ($model->update($id, $data)) {
            $data['id'] = $id;
            return $this->respond($data);
        }
        return $this->fail($model->errors());
    }

    public function deletePosition($id = null)
    {
        $model = new GlobalPositionModel();
        if ($model->delete($id)) {
            return $this->respondDeleted(['message' => 'Deleted successfully']);
        }
        return $this->fail('Gagal menghapus');
    }
}
