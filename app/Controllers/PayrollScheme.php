<?php

namespace App\Controllers;

use App\Models\PayrollSchemeTemplateModel;
use App\Models\SystemLogModel;
use CodeIgniter\RESTful\ResourceController;

class PayrollScheme extends ResourceController
{
    protected $modelName = 'App\Models\PayrollSchemeTemplateModel';
    protected $format    = 'json';

    /**
     * Get all schemes for a client
     */
    public function index()
    {
        $clientId = $this->request->getGet('client_id');
        
        if (!$clientId) {
            return $this->fail('client_id is required');
        }

        $schemes = $this->model->getByClient($clientId);
        return $this->respond($schemes);
    }

    /**
     * Get schemes filtered by org structure
     */
    public function getByOrgStructure()
    {
        $clientId = $this->request->getGet('client_id');
        $divisionId = $this->request->getGet('division_id');
        $departmentId = $this->request->getGet('department_id');
        $positionId = $this->request->getGet('position_id');

        if (!$clientId) {
            return $this->fail('client_id is required');
        }

        $schemes = $this->model->getByOrgStructure(
            $clientId,
            $divisionId ?: null,
            $departmentId ?: null,
            $positionId ?: null
        );

        return $this->respond($schemes);
    }

    /**
     * Get a single scheme by ID
     */
    public function show($id = null)
    {
        $scheme = $this->model->select('payroll_scheme_templates.*, 
                                        divisions.nama as division_name,
                                        departments.nama as department_name,
                                        positions.nama as position_name,
                                        minimum_wages.nama as minimum_wage_name,
                                        minimum_wages.nominal as minimum_wage_nominal')
                              ->join('divisions', 'divisions.id = payroll_scheme_templates.division_id', 'left')
                              ->join('departments', 'departments.id = payroll_scheme_templates.department_id', 'left')
                              ->join('positions', 'positions.id = payroll_scheme_templates.position_id', 'left')
                              ->join('minimum_wages', 'minimum_wages.id = payroll_scheme_templates.minimum_wage_id', 'left')
                              ->find($id);

        if (!$scheme) {
            return $this->failNotFound('Skema tidak ditemukan');
        }

        return $this->respond($scheme);
    }

    /**
     * Create a new scheme
     */
    public function create()
    {
        $data = $this->request->getJSON(true);

        // Convert empty strings to null for foreign keys
        $data['division_id'] = !empty($data['division_id']) ? $data['division_id'] : null;
        $data['department_id'] = !empty($data['department_id']) ? $data['department_id'] : null;
        $data['position_id'] = !empty($data['position_id']) ? $data['position_id'] : null;
        $data['minimum_wage_id'] = !empty($data['minimum_wage_id']) ? $data['minimum_wage_id'] : null;

        if ($id = $this->model->insert($data)) {
            $data['id'] = $id;

            // Log activity
            $log = new SystemLogModel();
            $orgInfo = $this->getOrgInfo($data);
            $log->logAction(
                'CREATE_PAYROLL_SCHEME',
                "Menambahkan skema payroll baru: {$data['nama_skema']} untuk {$orgInfo}",
                $data['client_id'],
                session()->get('user_id') ?? 1
            );

            return $this->respondCreated($data);
        }

        return $this->fail($this->model->errors());
    }

    /**
     * Update an existing scheme
     */
    public function update($id = null)
    {
        $data = $this->request->getJSON(true);
        $oldScheme = $this->model->find($id);

        if (!$oldScheme) {
            return $this->failNotFound('Skema tidak ditemukan');
        }

        // Convert empty strings to null for foreign keys
        $data['division_id'] = !empty($data['division_id']) ? $data['division_id'] : null;
        $data['department_id'] = !empty($data['department_id']) ? $data['department_id'] : null;
        $data['position_id'] = !empty($data['position_id']) ? $data['position_id'] : null;
        $data['minimum_wage_id'] = !empty($data['minimum_wage_id']) ? $data['minimum_wage_id'] : null;

        if ($this->model->update($id, $data)) {
            $data['id'] = $id;

            // Log activity
            $log = new SystemLogModel();
            $orgInfo = $this->getOrgInfo($data);
            $log->logAction(
                'UPDATE_PAYROLL_SCHEME',
                "Memperbarui skema payroll: {$oldScheme['nama_skema']} untuk {$orgInfo}",
                $oldScheme['client_id'],
                session()->get('user_id') ?? 1
            );

            return $this->respond($data);
        }

        return $this->fail($this->model->errors());
    }

    /**
     * Delete a scheme
     */
    public function delete($id = null)
    {
        $scheme = $this->model->find($id);

        if (!$scheme) {
            return $this->failNotFound('Skema tidak ditemukan');
        }

        if ($this->model->delete($id)) {
            // Log activity
            $log = new SystemLogModel();
            $orgInfo = $this->getOrgInfo($scheme);
            $log->logAction(
                'DELETE_PAYROLL_SCHEME',
                "Menghapus skema payroll: {$scheme['nama_skema']} untuk {$orgInfo}",
                $scheme['client_id'],
                session()->get('user_id') ?? 1
            );

            return $this->respondDeleted(['message' => 'Skema berhasil dihapus']);
        }

        return $this->fail('Gagal menghapus skema');
    }

    /**
     * Toggle active status
     */
    public function toggleActive($id = null)
    {
        $scheme = $this->model->find($id);

        if (!$scheme) {
            return $this->failNotFound('Skema tidak ditemukan');
        }

        $newStatus = $scheme['is_active'] == 1 ? 0 : 1;

        if ($this->model->update($id, ['is_active' => $newStatus])) {
            $statusText = $newStatus == 1 ? 'diaktifkan' : 'dinonaktifkan';

            // Log activity
            $log = new SystemLogModel();
            $log->logAction(
                'TOGGLE_PAYROLL_SCHEME',
                "Skema payroll '{$scheme['nama_skema']}' {$statusText}",
                $scheme['client_id'],
                session()->get('user_id') ?? 1
            );

            return $this->respond([
                'message' => "Skema berhasil {$statusText}",
                'is_active' => $newStatus
            ]);
        }

        return $this->fail('Gagal mengubah status skema');
    }

    /**
     * Get the best matching scheme for an employee
     */
    public function getSchemeForEmployee()
    {
        $clientId = $this->request->getGet('client_id');
        $divisionId = $this->request->getGet('division_id');
        $departmentId = $this->request->getGet('department_id');
        $positionId = $this->request->getGet('position_id');

        if (!$clientId) {
            return $this->fail('client_id is required');
        }

        $scheme = $this->model->getSchemeForEmployee(
            $clientId,
            $divisionId ?: null,
            $departmentId ?: null,
            $positionId ?: null
        );

        if (!$scheme) {
            return $this->failNotFound('Tidak ada skema yang cocok ditemukan');
        }

        return $this->respond($scheme);
    }

    /**
     * Helper: Get organization info string for logging
     */
    private function getOrgInfo($data)
    {
        $db = \Config\Database::connect();
        $parts = [];

        if (!empty($data['division_id'])) {
            $div = $db->table('divisions')->where('id', $data['division_id'])->get()->getRow();
            if ($div) $parts[] = "Divisi: {$div->nama}";
        }

        if (!empty($data['department_id'])) {
            $dept = $db->table('departments')->where('id', $data['department_id'])->get()->getRow();
            if ($dept) $parts[] = "Departemen: {$dept->nama}";
        }

        if (!empty($data['position_id'])) {
            $pos = $db->table('positions')->where('id', $data['position_id'])->get()->getRow();
            if ($pos) $parts[] = "Posisi: {$pos->nama}";
        }

        return count($parts) > 0 ? implode(', ', $parts) : 'Semua struktur organisasi';
    }
}
