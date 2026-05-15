<?php

namespace App\Models;

use CodeIgniter\Model;

class ClientModel extends Model
{
    protected $table            = 'clients';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['no_klien', 'nama', 'email', 'telepon', 'sektor', 'nib', 'npwp', 'tgl_gabung', 'alamat', 'status'];
}
