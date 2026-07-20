<?php

namespace App\Controllers;

use App\Models\FpkMasterModel;
use App\Models\FpkAssignmentModel;
use App\Models\EmployeeModel;
use App\Models\SystemLogModel;
use CodeIgniter\RESTful\ResourceController;

class Fpk extends ResourceController
{
    protected $format = 'json';

    // =====================================================================
    // MASTER FPK CRUD
    // =====================================================================

    /**
     * GET /api/fpk — List all FPK master data
     */
    public function index()
    {
        $model = new FpkMasterModel();
        return $this->respond($model->orderBy('created_at', 'DESC')->findAll());
    }

    /**
     * POST /api/fpk — Create a new FPK
     */
    public function create()
    {
        $model = new FpkMasterModel();
        $data = $this->request->getJSON(true);

        $data['status'] = 'Open';

        if (!$model->validate($data)) {
            return $this->fail($model->errors());
        }

        $id = $model->insert($data);
        if (!$id) {
            return $this->fail('Gagal menyimpan data FPK.');
        }

        // Log activity
        $logModel = new SystemLogModel();
        $logModel->logAction('CREATE_FPK', "FPK '{$data['nomor_fpk']}' berhasil ditambahkan.");

        return $this->respondCreated(['id' => $id, 'message' => 'FPK berhasil ditambahkan.']);
    }

    /**
     * PUT /api/fpk/{id} — Update existing FPK
     */
    public function update($id = null)
    {
        $model = new FpkMasterModel();
        $fpk = $model->find($id);

        if (!$fpk) {
            return $this->failNotFound('Data FPK tidak ditemukan.');
        }

        if ($fpk['status'] === 'Closed') {
            return $this->fail('FPK yang berstatus Closed tidak bisa diedit.');
        }

        $data = $this->request->getJSON(true);
        $data['id'] = $id; // For unique validation bypass

        if (!$model->validate($data)) {
            return $this->fail($model->errors());
        }

        $model->update($id, $data);

        // Log activity
        $logModel = new SystemLogModel();
        $logModel->logAction('UPDATE_FPK', "FPK '{$data['nomor_fpk']}' berhasil diperbarui.");

        return $this->respond(['message' => 'FPK berhasil diperbarui.']);
    }

    /**
     * DELETE /api/fpk/{id} — Delete FPK (only Open status)
     */
    public function delete($id = null)
    {
        $model = new FpkMasterModel();
        $fpk = $model->find($id);

        if (!$fpk) {
            return $this->failNotFound('Data FPK tidak ditemukan.');
        }

        if ($fpk['status'] === 'Closed') {
            return $this->fail('FPK yang berstatus Closed tidak bisa dihapus. Cabut penempatan karyawan terlebih dahulu.');
        }

        $model->delete($id);

        // Log activity
        $logModel = new SystemLogModel();
        $logModel->logAction('DELETE_FPK', "FPK '{$fpk['nomor_fpk']}' berhasil dihapus.");

        return $this->respondDeleted(['message' => 'FPK berhasil dihapus.']);
    }

    // =====================================================================
    // FPK ASSIGNMENTS (Penempelan Karyawan)
    // =====================================================================

    /**
     * GET /api/fpk/assignments — List all FPK assignments
     */
    public function getAssignments()
    {
        $model = new FpkAssignmentModel();
        return $this->respond($model->orderBy('created_at', 'DESC')->findAll());
    }

    /**
     * POST /api/fpk/assign — Assign employee to FPK
     */
    public function assign()
    {
        $fpkModel = new FpkMasterModel();
        $assignModel = new FpkAssignmentModel();
        $empModel = new EmployeeModel();
        $logModel = new SystemLogModel();

        $data = $this->request->getJSON(true);

        $fpkId = $data['fpk_id'] ?? null;
        $employeeId = $data['employee_id'] ?? null;

        if (!$fpkId || !$employeeId) {
            return $this->fail('FPK dan Karyawan wajib dipilih.');
        }

        // Validate FPK exists and is Open
        $fpk = $fpkModel->find($fpkId);
        if (!$fpk) {
            return $this->failNotFound('Data FPK tidak ditemukan.');
        }
        if ($fpk['status'] !== 'Open') {
            return $this->fail('FPK ini sudah berstatus Closed. Tidak bisa menerima karyawan lagi.');
        }

        // Validate employee exists
        $db = \Config\Database::connect();
        $employee = $db->table('employees')
                       ->select('employees.*, positions.nama as nama_posisi, departments.nama as nama_dept')
                       ->join('positions', 'positions.id = employees.position_id', 'left')
                       ->join('departments', 'departments.id = COALESCE(employees.department_id, positions.department_id)', 'left')
                       ->where('employees.id', $employeeId)
                       ->get()
                       ->getRowArray();

        if (!$employee) {
            return $this->failNotFound('Data Karyawan tidak ditemukan.');
        }

        // Check if employee already has an active FPK assignment
        $existingAssignment = $assignModel->where('employee_id', $employeeId)
                                          ->where('status', 'Active')
                                          ->first();
        if ($existingAssignment) {
            return $this->fail('Karyawan ini sudah memiliki FPK aktif (' . $existingAssignment['nomor_fpk'] . '). Cabut penempatan terlebih dahulu.');
        }

        // Create assignment record
        $userName = session()->get('username') ?? $this->request->getHeaderLine('X-User-Action') ?? 'System';
        
        $assignData = [
            'fpk_id'              => $fpkId,
            'employee_id'         => $employeeId,
            'nik'                 => $employee['nik'],
            'nama_karyawan'       => $employee['nama'],
            'nomor_fpk'           => $fpk['nomor_fpk'],
            'nama_fpk'            => $fpk['nama_fpk'],
            'provinsi'            => $fpk['provinsi'],
            'city'                => $fpk['city'],
            'tanggal_penempatan'  => date('Y-m-d H:i:s'),
            'user_submit'         => $userName,
            'status'              => 'Active'
        ];

        $assignId = $assignModel->insert($assignData);
        if (!$assignId) {
            return $this->fail('Gagal menyimpan penempatan FPK.');
        }

        // Update employee fpk_id
        $empModel->update($employeeId, ['fpk_id' => $fpkId]);

        // Update FPK status to Closed (1:1 ratio)
        $fpkModel->update($fpkId, ['status' => 'Closed']);

        // Log activity
        $logModel->logAction('ASSIGN_FPK', "Karyawan '{$employee['nama']}' (NIK: {$employee['nik']}) ditempelkan ke FPK '{$fpk['nomor_fpk']}'.");

        return $this->respondCreated([
            'id'      => $assignId,
            'message' => 'Karyawan berhasil ditempelkan ke FPK.'
        ]);
    }

    /**
     * DELETE /api/fpk/assignments/{id} — Revoke FPK assignment
     */
    public function revokeAssignment($id = null)
    {
        $assignModel = new FpkAssignmentModel();
        $fpkModel = new FpkMasterModel();
        $empModel = new EmployeeModel();
        $logModel = new SystemLogModel();

        $assignment = $assignModel->find($id);
        if (!$assignment) {
            return $this->failNotFound('Data penempatan tidak ditemukan.');
        }

        // Remove fpk_id from employee
        $empModel->update($assignment['employee_id'], ['fpk_id' => null]);

        // Reopen FPK
        $fpkModel->update($assignment['fpk_id'], ['status' => 'Open']);

        // Delete assignment record
        $assignModel->delete($id);

        // Log activity
        $logModel->logAction('REVOKE_FPK', "Penempatan FPK '{$assignment['nomor_fpk']}' untuk karyawan '{$assignment['nama_karyawan']}' (NIK: {$assignment['nik']}) dicabut.");

        return $this->respondDeleted(['message' => 'Penempatan FPK berhasil dicabut.']);
    }
}
