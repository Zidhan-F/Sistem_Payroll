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

        // Simple Queries
        $this->db->table('users')->insert($data);
    }
}
