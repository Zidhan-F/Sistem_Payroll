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
        'description', 'client_id', 'action', 'created_by', 'user_name', 'created_at'
    ];
    
    // Gunakan useTimestamps true tapi hanya untuk created_at, kita bisa override
    protected $useTimestamps    = false; 

    public function logAction($action, $description, $clientId = null, $createdBy = null)
    {
        $createdBy = $createdBy ?? session()->get('user_id') ?? 1;
        $userName = session()->get('username') ?? 'Sistem/Unknown';

        return $this->insert([
            'action'      => $action,
            'description' => $description,
            'client_id'   => $clientId,
            'created_by'  => $createdBy,
            'user_name'   => $userName,
            'created_at'  => date('Y-m-d H:i:s')
        ]);
    }
}
