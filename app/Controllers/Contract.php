<?php

namespace App\Controllers;

use App\Models\ContractModel;
use App\Models\EmployeeModel;
use App\Models\ClientSchemaModel;
use App\Models\PayrollComponentModel;
use CodeIgniter\RESTful\ResourceController;

class Contract extends ResourceController
{
    protected $modelName = 'App\Models\ContractModel';
    protected $format    = 'json';

    /**
     * Get all contracts for a specific client
     */
    public function getByClient($clientId)
    {
        $contracts = $this->model
            ->select('contracts.*, employees.nama as nama_karyawan, employees.nik')
            ->join('employees', 'employees.id = contracts.employee_id', 'left')
            ->where('contracts.client_id', $clientId)
            ->orderBy('contracts.id', 'DESC')
            ->findAll();

        return $this->respond($contracts);
    }

    /**
     * Create a new PKWT contract
     * When creating PKWT, salary is auto-generated based on client schema
     */
    public function create()
    {
        $data = $this->request->getJSON(true);

        // Generate No Kontrak
        $count = $this->model->countAllResults();
        $data['no_kontrak'] = 'PKWT-' . date('Ym') . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);

        // Insert contract
        $id = $this->model->insert($data);
        if (!$id) {
            return $this->fail($this->model->errors());
        }

        // Update gaji_pokok di employee sesuai PKWT
        $empModel = new EmployeeModel();
        $empModel->update($data['employee_id'], [
            'gaji_pokok' => $data['gaji_pokok'] ?? 0
        ]);

        $data['id'] = $id;
        return $this->respondCreated([
            'message' => 'PKWT berhasil dibuat. Gaji karyawan disesuaikan dengan skema.',
            'contract' => $data
        ]);
    }

    /**
     * Update existing contract
     */
    public function update($id = null)
    {
        $data = $this->request->getJSON(true);
        
        if ($this->model->update($id, $data)) {
            // Sync gaji_pokok ke employee jika berubah
            if (isset($data['gaji_pokok'])) {
                $contract = $this->model->find($id);
                if ($contract) {
                    $empModel = new EmployeeModel();
                    $empModel->update($contract['employee_id'], [
                        'gaji_pokok' => $data['gaji_pokok']
                    ]);
                }
            }
            return $this->respond(['message' => 'PKWT berhasil diperbarui']);
        }
        return $this->fail($this->model->errors());
    }

    /**
     * Terminate / End a contract
     */
    public function terminate($id)
    {
        $contract = $this->model->find($id);
        if (!$contract) {
            return $this->failNotFound('Kontrak tidak ditemukan');
        }

        $this->model->update($id, [
            'status_pkwt'   => 'Berakhir',
            'tgl_berakhir'  => date('Y-m-d')
        ]);

        return $this->respond(['message' => 'Kontrak berhasil diakhiri']);
    }

    /**
     * Get single contract detail
     */
    public function show($id = null)
    {
        $contract = $this->model
            ->select('contracts.*, employees.nama as nama_karyawan, employees.nik')
            ->join('employees', 'employees.id = contracts.employee_id', 'left')
            ->find($id);

        if (!$contract) {
            return $this->failNotFound();
        }
        return $this->respond($contract);
    }
}
