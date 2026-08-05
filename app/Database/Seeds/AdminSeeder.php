<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run()
    {
        $data = [
            'username' => 'admin',
            'email'    => 'admin@example.com',
            'password' => 'admin123', // Menggunakan plain text sesuai request dan controller Auth.php
            'role'     => 'admin'
        ];

        $existing = $this->db->table('users')->where('username', 'admin')->get()->getRowArray();
        if (!$existing) {
            $this->db->table('users')->insert($data);
        }

        // Set all pending users (like newly registered account) to admin as well
        $this->db->table('users')->where('role', 'pending')->update(['role' => 'admin', 'is_active' => 1]);
    }
}
