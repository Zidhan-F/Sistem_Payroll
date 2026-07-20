<?php

namespace App\Controllers;

use App\Models\ContractCompensationModel;
use App\Models\EmployeeModel;
use App\Models\ContractModel;
use App\Models\SystemLogModel;
use CodeIgniter\RESTful\ResourceController;

class ContractCompensation extends ResourceController
{
    protected $modelName = 'App\Models\ContractCompensationModel';
    protected $format    = 'json';

    /**
     * Tampilkan semua kompensasi kontrak untuk client
     */
    public function index()
    {
        $clientId = $this->request->getGet('client_id');
        if (empty($clientId)) {
            return $this->respond([]);
        }

        $query = $this->model
            ->select('contract_compensations.*, employees.nama as nama_karyawan, employees.nik')
            ->join('employees', 'employees.id = contract_compensations.employee_id')
            ->where('contract_compensations.client_id', $clientId)
            ->orderBy('contract_compensations.id', 'DESC');

        $data = $query->findAll();
        return $this->respond($data);
    }

    /**
     * Tampilkan detail kompensasi kontrak
     */
    public function show($id = null)
    {
        $data = $this->model
            ->select('contract_compensations.*, employees.nama as nama_karyawan, employees.nik')
            ->join('employees', 'employees.id = contract_compensations.employee_id')
            ->find($id);

        if (!$data) {
            return $this->failNotFound('Kompensasi kontrak tidak ditemukan');
        }
        return $this->respond($data);
    }

    /**
     * Hitung preview kompensasi dan simpan/update sebagai DRAFT
     */
    public function calculate()
    {
        $json = $this->request->getJSON(true);
        $employeeId = $json['employee_id'] ?? null;
        
        if (!$employeeId) {
            return $this->fail('Employee ID diperlukan');
        }

        $employeeModel = new EmployeeModel();
        $employee = $employeeModel->find($employeeId);
        if (!$employee) {
            return $this->failNotFound('Karyawan tidak ditemukan');
        }

        $clientId = $employee['client_id'];
        
        // Dapatkan data tanggal & gaji pokok
        $tglMulai = $json['tgl_mulai_kerja'] ?? $employee['tgl_masuk'] ?? null;
        $tglAkhir = $json['tgl_akhir_kontrak'] ?? $employee['end_contract'] ?? null;
        $basicSalary = isset($json['basic_salary']) ? floatval($json['basic_salary']) : floatval($employee['gaji_pokok'] ?? 0);
        $actualDays = isset($json['actual_days']) ? intval($json['actual_days']) : null;

        // Ambil contract_id jika ada
        $contractModel = new ContractModel();
        $contract = $contractModel->where('employee_id', $employeeId)->where('status_pkwt', 'Aktif')->first();
        $contractId = $contract ? $contract['id'] : null;

        if (empty($tglMulai) || empty($tglAkhir)) {
            return $this->fail('Tanggal mulai kerja dan tanggal akhir kontrak diperlukan untuk perhitungan');
        }

        // Tentukan standard working days (default 22)
        $db = \Config\Database::connect();
        $empConfig = null;
        if (!empty($employee['position_id'])) {
            $empConfig = $db->table('client_payroll_configs')
                ->where('client_id', $clientId)
                ->where('position_id', $employee['position_id'])
                ->get()->getRow();
        }
        if (!$empConfig && !empty($employee['department_id'])) {
            $empConfig = $db->table('client_payroll_configs')
                ->where('client_id', $clientId)
                ->where('department_id', $employee['department_id'])
                ->where('position_id IS NULL')
                ->get()->getRow();
        }
        if (!$empConfig) {
            $empConfig = $db->table('client_payroll_configs')
                ->where('client_id', $clientId)
                ->where('division_id IS NULL')
                ->where('department_id IS NULL')
                ->where('position_id IS NULL')
                ->get()->getRow();
        }

        $standardDays = ($empConfig && isset($empConfig->standard_work_days) && intval($empConfig->standard_work_days) > 0) 
            ? intval($empConfig->standard_work_days) 
            : 22;

        if (isset($employee['custom_standard_days']) && intval($employee['custom_standard_days']) > 0) {
            $standardDays = intval($employee['custom_standard_days']);
        }

        // Lakukan kalkulasi kompensasi
        $calc = $this->model->calculateCompensation($basicSalary, $tglMulai, $tglAkhir, $standardDays, $actualDays);

        // Cek jika sudah ada draft untuk employee ini pada rentang kontrak tersebut
        $existing = $this->model->where([
            'employee_id' => $employeeId,
            'client_id' => $clientId,
        ])->whereIn('status', ['Draft', 'Ditetapkan', 'Ditolak'])->first();

        $dataSave = [
            'employee_id' => $employeeId,
            'contract_id' => $contractId,
            'client_id' => $clientId,
            'basic_salary' => $basicSalary,
            'masa_kerja_tahun' => $calc['tahun'],
            'masa_kerja_bulan' => $calc['bulan'],
            'masa_kerja_hari' => $calc['hari'],
            'multiplier' => $calc['multiplier'],
            'nilai_kompensasi' => $calc['nilai'],
            'tgl_mulai_kerja' => $tglMulai,
            'tgl_akhir_kontrak' => $tglAkhir,
            'status' => $existing ? $existing['status'] : 'Draft',
        ];

        if ($existing) {
            $this->model->update($existing['id'], $dataSave);
            $dataSave['id'] = $existing['id'];
        } else {
            $dataSave['status'] = 'Draft';
            $id = $this->model->insert($dataSave);
            $dataSave['id'] = $id;
        }

        // Log action
        $log = new SystemLogModel();
        $username = $this->request->getHeaderLine('X-User-Action') ?: 'Sistem';
        $log->insert([
            'action' => 'CALCULATE_COMPENSATION',
            'description' => "Menghitung draf kompensasi kontrak untuk {$employee['nama']} (Nilai: Rp " . number_format($calc['nilai'], 0, ',', '.') . ")",
            'client_id' => $clientId,
            'created_by' => 1,
            'user_name' => $username,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return $this->respond([
            'status' => 'success',
            'message' => 'Kompensasi kontrak berhasil dihitung',
            'data' => $dataSave
        ]);
    }

    /**
     * HCOPS menetapkan nilai kompensasi final
     */
    public function setCompensation($id = null)
    {
        $comp = $this->model->find($id);
        if (!$comp) {
            return $this->failNotFound('Kompensasi kontrak tidak ditemukan');
        }

        $json = $this->request->getJSON(true);
        $nilaiFinal = isset($json['nilai_kompensasi_final']) ? floatval($json['nilai_kompensasi_final']) : $comp['nilai_kompensasi'];
        $catatan = $json['catatan'] ?? '';

        $username = $this->request->getHeaderLine('X-User-Action') ?: 'HCOPS';

        $dataUpdate = [
            'nilai_kompensasi_final' => $nilaiFinal,
            'status' => 'Ditetapkan',
            'ditetapkan_oleh' => $username,
            'ditetapkan_pada' => date('Y-m-d H:i:s'),
            'catatan' => $catatan
        ];

        if ($this->model->update($id, $dataUpdate)) {
            $empModel = new EmployeeModel();
            $emp = $empModel->find($comp['employee_id']);

            $log = new SystemLogModel();
            $log->insert([
                'action' => 'SET_COMPENSATION',
                'description' => "Menetapkan nilai kompensasi kontrak untuk {$emp['nama']} sebesar Rp " . number_format($nilaiFinal, 0, ',', '.'),
                'client_id' => $comp['client_id'],
                'created_by' => 1,
                'user_name' => $username,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            return $this->respond([
                'status' => 'success',
                'message' => 'Nilai kompensasi berhasil ditetapkan dan menunggu persetujuan superior'
            ]);
        }

        return $this->fail('Gagal menetapkan kompensasi');
    }

    /**
     * Superior menyetujui nilai kompensasi
     */
    public function approve($id = null)
    {
        $comp = $this->model->find($id);
        if (!$comp) {
            return $this->failNotFound('Kompensasi kontrak tidak ditemukan');
        }

        $username = $this->request->getHeaderLine('X-User-Action') ?: 'Superior';

        $dataUpdate = [
            'status' => 'Disetujui',
            'disetujui_oleh' => $username,
            'disetujui_pada' => date('Y-m-d H:i:s')
        ];

        if ($this->model->update($id, $dataUpdate)) {
            $empModel = new EmployeeModel();
            $emp = $empModel->find($comp['employee_id']);

            $log = new SystemLogModel();
            $log->insert([
                'action' => 'APPROVE_COMPENSATION',
                'description' => "Menyetujui kompensasi kontrak untuk {$emp['nama']} (Nilai final: Rp " . number_format($comp['nilai_kompensasi_final'] ?? $comp['nilai_kompensasi'], 0, ',', '.') . ")",
                'client_id' => $comp['client_id'],
                'created_by' => 1,
                'user_name' => $username,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            return $this->respond([
                'status' => 'success',
                'message' => 'Kompensasi kontrak berhasil disetujui'
            ]);
        }

        return $this->fail('Gagal menyetujui kompensasi');
    }

    /**
     * Superior menolak nilai kompensasi
     */
    public function reject($id = null)
    {
        $comp = $this->model->find($id);
        if (!$comp) {
            return $this->failNotFound('Kompensasi kontrak tidak ditemukan');
        }

        $json = $this->request->getJSON(true);
        $catatan = $json['catatan'] ?? 'Ditolak oleh Superior';

        $username = $this->request->getHeaderLine('X-User-Action') ?: 'Superior';

        $dataUpdate = [
            'status' => 'Ditolak',
            'catatan' => $catatan
        ];

        if ($this->model->update($id, $dataUpdate)) {
            $empModel = new EmployeeModel();
            $emp = $empModel->find($comp['employee_id']);

            $log = new SystemLogModel();
            $log->insert([
                'action' => 'REJECT_COMPENSATION',
                'description' => "Menolak kompensasi kontrak untuk {$emp['nama']} dengan catatan: {$catatan}",
                'client_id' => $comp['client_id'],
                'created_by' => 1,
                'user_name' => $username,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            return $this->respond([
                'status' => 'success',
                'message' => 'Kompensasi kontrak ditolak'
            ]);
        }

        return $this->fail('Gagal menolak kompensasi');
    }

    /**
     * Hapus draft kompensasi kontrak
     */
    public function delete($id = null)
    {
        $comp = $this->model->find($id);
        if (!$comp) {
            return $this->failNotFound('Kompensasi kontrak tidak ditemukan');
        }

        if ($comp['status'] !== 'Draft' && $comp['status'] !== 'Ditolak') {
            return $this->fail('Hanya draf atau status ditolak yang dapat dihapus');
        }

        if ($this->model->delete($id)) {
            $empModel = new EmployeeModel();
            $emp = $empModel->find($comp['employee_id']);
            $username = $this->request->getHeaderLine('X-User-Action') ?: 'HCOPS';

            $log = new SystemLogModel();
            $log->insert([
                'action' => 'DELETE_COMPENSATION',
                'description' => "Menghapus draf kompensasi kontrak untuk {$emp['nama']}",
                'client_id' => $comp['client_id'],
                'created_by' => 1,
                'user_name' => $username,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            return $this->respondDeleted([
                'id' => $id,
                'message' => 'Draf kompensasi kontrak berhasil dihapus'
            ]);
        }

        return $this->fail('Gagal menghapus kompensasi');
    }
}
