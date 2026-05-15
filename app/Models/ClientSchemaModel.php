<?php

namespace App\Models;

use CodeIgniter\Model;

class ClientSchemaModel extends Model
{
    protected $table            = 'client_schemas';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'client_id', 'bpjs_kes_percent', 'bpjs_jht_percent', 
        'overtime_rate_per_hour', 'tax_method', 'cut_off_start', 'cut_off_end'
    ];
    protected $useTimestamps    = true;
}
