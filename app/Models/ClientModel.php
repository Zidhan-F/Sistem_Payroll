<?php

namespace App\Models;

use CodeIgniter\Model;

class ClientModel extends Model
{
    protected $table            = 'clients';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'no_klien', 'nama', 'email', 'telepon', 'sektor', 'nib', 'npwp', 'tgl_gabung', 'alamat', 'status',
        'bpjs_kes_percent', 'bpjs_jht_percent', 'overtime_rate_per_hour', 'tax_method', 'cut_off_start', 'cut_off_end'
    ];
}
