<?php

namespace App\Models;

use CodeIgniter\Model;

class SystemLogModel extends Model
{
    protected $table            = 'system_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'description', 'client_id', 'action', 'created_by', 'created_at'
    ];
    
    // Gunakan useTimestamps true tapi hanya untuk created_at, kita bisa override
    protected $useTimestamps    = false; 

    public function logAction($action, $description, $clientId = null, $createdBy = null)
    {
        return $this->insert([
            'action'      => $action,
            'description' => $description,
            'client_id'   => $clientId,
            'created_by' => $createdBy,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
}
