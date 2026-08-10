<?php

namespace App\Models;

use CodeIgniter\Model;

class UserClientModel extends Model
{
    protected $table            = 'user_clients';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['user_id', 'client_id', 'created_at'];

    /**
     * Get list of allowed client IDs for a given user ID
     */
    public function getUserClientIds($userId)
    {
        $rows = $this->where('user_id', $userId)->findAll();
        if (empty($rows)) {
            return [];
        }
        return array_map(function ($r) {
            return (int)$r['client_id'];
        }, $rows);
    }

    /**
     * Sync/replace client assignments for a user ID
     */
    public function syncUserClients($userId, array $clientIds)
    {
        // Remove existing assignments
        $this->where('user_id', $userId)->delete();

        // Re-insert new assignments
        $now = date('Y-m-d H:i:s');
        foreach ($clientIds as $cid) {
            $cid = (int)$cid;
            if ($cid > 0) {
                $this->insert([
                    'user_id'   => $userId,
                    'client_id' => $cid,
                    'created_at'=> $now
                ]);
            }
        }
    }
}
