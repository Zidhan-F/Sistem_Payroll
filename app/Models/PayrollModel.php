<?php

namespace App\Models;

use CodeIgniter\Model;

class PayrollModel extends Model
{
    protected $table            = 'payrolls';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'employee_id', 'bulan', 'tahun', 'gaji_pokok', 
        'total_tunjangan', 'total_potongan', 'take_home_pay', 'status_pembayaran',
        'bpjs_kes_karyawan', 'bpjs_kes_perusahaan', 'bpjs_jht_karyawan', 'bpjs_jht_perusahaan',
        'bpjs_jp_karyawan', 'bpjs_jp_perusahaan', 'bpjs_jkk_perusahaan', 'bpjs_jkm_perusahaan',
        'pph21', 'tax_allowance', 'tax_method', 'ptkp_status', 'potongan_absen', 'lembur_pay', 'bonus_tambahan'
    ];
    protected $useTimestamps    = true;
    protected $updatedField     = ''; // Only created_at is in schema
}
