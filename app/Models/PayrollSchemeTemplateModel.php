<?php

namespace App\Models;

use CodeIgniter\Model;

class PayrollSchemeTemplateModel extends Model
{
    protected $table            = 'payroll_scheme_templates';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'client_id',
        'division_id',
        'department_id',
        'position_id',
        'nama_skema',
        'deskripsi',
        'sumber_gaji',
        'nilai_gaji_pokok',
        'minimum_wage_id',
        'tunjangan_transport',
        'tunjangan_makan',
        'tunjangan_komunikasi',
        'tunjangan_jabatan',
        'tunjangan_kehadiran',
        'tunjangan_kinerja',
        'potongan_pinjaman',
        'potongan_kasbon',
        'potongan_lainnya',
        'potongan_per_alpa',
        'bonus_per_hadir',
        'rate_lembur_per_jam',
        'bpjs_kes_karyawan',
        'bpjs_kes_perusahaan',
        'bpjs_jht_karyawan',
        'bpjs_jht_perusahaan',
        'bpjs_jp_karyawan',
        'bpjs_jp_perusahaan',
        'bpjs_jkk_perusahaan',
        'bpjs_jkm_perusahaan',
        'metode_pajak',
        'ptkp_status',
        'grace_period_late',
        'grace_period_early',
        'min_overtime',
        'denda_terlambat_per_jam',
        'denda_alfa_per_hari',
        'early_leave_threshold',
        'is_active',
        'bpjs_inc_transport',
        'pph_inc_transport',
        'bpjs_inc_makan',
        'pph_inc_makan',
        'bpjs_inc_komunikasi',
        'pph_inc_komunikasi',
        'bpjs_inc_jabatan',
        'pph_inc_jabatan',
        'bpjs_inc_kehadiran',
        'pph_inc_kehadiran',
        'bpjs_inc_kinerja',
        'pph_inc_kinerja',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'client_id' => 'required|integer',
        'nama_skema' => 'required|string|max_length[255]',
        'sumber_gaji' => 'required|in_list[ump,umk,nominal]',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Get all schemes for a specific client
     */
    public function getByClient($clientId)
    {
        return $this->select('payroll_scheme_templates.*, 
                              divisions.nama as division_name,
                              departments.nama as department_name,
                              positions.nama as position_name,
                              minimum_wages.nama_daerah as minimum_wage_name,
                              minimum_wages.nominal as minimum_wage_nominal')
                    ->join('divisions', 'divisions.id = payroll_scheme_templates.division_id', 'left')
                    ->join('departments', 'departments.id = payroll_scheme_templates.department_id', 'left')
                    ->join('positions', 'positions.id = payroll_scheme_templates.position_id', 'left')
                    ->join('minimum_wages', 'minimum_wages.id = payroll_scheme_templates.minimum_wage_id', 'left')
                    ->where('payroll_scheme_templates.client_id', $clientId)
                    ->orderBy('payroll_scheme_templates.created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Get schemes filtered by division, department, and position
     */
    public function getByOrgStructure($clientId, $divisionId = null, $departmentId = null, $positionId = null)
    {
        $builder = $this->select('payroll_scheme_templates.*, 
                                  divisions.nama as division_name,
                                  departments.nama as department_name,
                                  positions.nama as position_name,
                                  minimum_wages.nama_daerah as minimum_wage_name,
                                  minimum_wages.nominal as minimum_wage_nominal')
                        ->join('divisions', 'divisions.id = payroll_scheme_templates.division_id', 'left')
                        ->join('departments', 'departments.id = payroll_scheme_templates.department_id', 'left')
                        ->join('positions', 'positions.id = payroll_scheme_templates.position_id', 'left')
                        ->join('minimum_wages', 'minimum_wages.id = payroll_scheme_templates.minimum_wage_id', 'left')
                        ->where('payroll_scheme_templates.client_id', $clientId)
                        ->where('payroll_scheme_templates.is_active', 1);

        if ($divisionId !== null) {
            $builder->where('payroll_scheme_templates.division_id', $divisionId);
        }
        
        if ($departmentId !== null) {
            $builder->where('payroll_scheme_templates.department_id', $departmentId);
        }
        
        if ($positionId !== null) {
            $builder->where('payroll_scheme_templates.position_id', $positionId);
        }

        return $builder->orderBy('payroll_scheme_templates.created_at', 'DESC')->findAll();
    }

    /**
     * Get the most specific scheme for an employee based on their org structure
     * Priority: Position > Department > Division > Client Default
     */
    public function getSchemeForEmployee($clientId, $divisionId, $departmentId, $positionId)
    {
        // Try exact match first (all three specified)
        $scheme = $this->where([
            'client_id' => $clientId,
            'division_id' => $divisionId,
            'department_id' => $departmentId,
            'position_id' => $positionId,
            'is_active' => 1
        ])->first();
        
        if ($scheme) return $scheme;

        // Try position + department (any division)
        $scheme = $this->where([
            'client_id' => $clientId,
            'division_id' => null,
            'department_id' => $departmentId,
            'position_id' => $positionId,
            'is_active' => 1
        ])->first();
        
        if ($scheme) return $scheme;

        // Try position only (any department, any division)
        $scheme = $this->where([
            'client_id' => $clientId,
            'division_id' => null,
            'department_id' => null,
            'position_id' => $positionId,
            'is_active' => 1
        ])->first();
        
        if ($scheme) return $scheme;

        // Try department only (any position)
        $scheme = $this->where([
            'client_id' => $clientId,
            'division_id' => null,
            'department_id' => $departmentId,
            'position_id' => null,
            'is_active' => 1
        ])->first();
        
        if ($scheme) return $scheme;

        // Try division only
        $scheme = $this->where([
            'client_id' => $clientId,
            'division_id' => $divisionId,
            'department_id' => null,
            'position_id' => null,
            'is_active' => 1
        ])->first();
        
        if ($scheme) return $scheme;

        // Fallback: client default (all null)
        return $this->where([
            'client_id' => $clientId,
            'division_id' => null,
            'department_id' => null,
            'position_id' => null,
            'is_active' => 1
        ])->first();
    }
}
