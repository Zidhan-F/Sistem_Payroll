<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\RESTful\ResourceController;

class UserManagement extends ResourceController
{
    protected $format = 'json';

    /**
     * Daftar role yang tersedia di sistem
     */
    private const VALID_ROLES = [
        'admin'                => ['label' => 'Admin', 'color' => '#8b5cf6'],
        'payroll'              => ['label' => 'Payroll', 'color' => '#06b6d4'],
        'business_development' => ['label' => 'Business Development', 'color' => '#f59e0b'],
        'recruiter'            => ['label' => 'Recruiter', 'color' => '#10b981'],
        'client_superior'      => ['label' => 'Client / Superior', 'color' => '#ef4444'],
        'hc_ops'               => ['label' => 'HC Ops', 'color' => '#3b82f6'],
        'staff'                => ['label' => 'Staff', 'color' => '#6b7280'],
    ];

    /**
     * Cek apakah request berasal dari user admin
     */
    private function checkAdmin(): bool
    {
        $db = \Config\Database::connect();
        $username = $this->request->getHeaderLine('X-User-Action');
        if (!$username) return false;

        $user = $db->table('users')->where('username', $username)->get()->getRow();
        return $user && $user->role === 'admin';
    }

    /**
     * GET /api/users — List semua user (password tidak dikembalikan)
     */
    public function getUsers()
    {
        if (!$this->checkAdmin()) {
            return $this->failForbidden('Hanya admin yang dapat mengakses halaman ini');
        }

        $db = \Config\Database::connect();
        $users = $db->table('users')
            ->select('id, username, email, role, full_name, is_active, created_at, updated_at')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        return $this->respond(['data' => $users]);
    }

    /**
     * POST /api/users — Buat user baru
     */
    public function createUser()
    {
        if (!$this->checkAdmin()) {
            return $this->failForbidden('Hanya admin yang dapat mengakses fitur ini');
        }

        $json = $this->request->getJSON(true);
        $username  = trim($json['username'] ?? '');
        $email     = trim($json['email'] ?? '');
        $password  = $json['password'] ?? '';
        $role      = $json['role'] ?? 'staff';
        $fullName  = trim($json['full_name'] ?? '');

        // Validasi
        if (empty($username)) {
            return $this->fail('Username wajib diisi');
        }
        if (empty($password)) {
            return $this->fail('Password wajib diisi');
        }
        if (!array_key_exists($role, self::VALID_ROLES)) {
            return $this->fail('Role tidak valid');
        }

        // Cek duplikat username
        $db = \Config\Database::connect();
        $exists = $db->table('users')->where('username', $username)->countAllResults();
        if ($exists > 0) {
            return $this->fail('Username sudah digunakan');
        }

        $data = [
            'username'  => $username,
            'email'     => $email,
            'password'  => $password, // Note: In production, use password_hash()
            'role'      => $role,
            'full_name' => $fullName,
            'is_active' => 1,
        ];

        $db->table('users')->insert($data);
        $insertId = $db->insertID();

        // Log activity
        $this->logActivity("User baru '$username' dibuat dengan role '$role'");

        return $this->respond([
            'success' => true,
            'message' => "User '$username' berhasil dibuat",
            'id'      => $insertId
        ]);
    }

    /**
     * PUT /api/users/:id — Update data user
     */
    public function updateUser($id = null)
    {
        if (!$this->checkAdmin()) {
            return $this->failForbidden('Hanya admin yang dapat mengakses fitur ini');
        }

        $db = \Config\Database::connect();
        $user = $db->table('users')->where('id', $id)->get()->getRow();
        if (!$user) {
            return $this->failNotFound('User tidak ditemukan');
        }

        $json = $this->request->getJSON(true);
        $updateData = [];

        if (isset($json['username'])) {
            $newUsername = trim($json['username']);
            // Cek duplikat jika username berubah
            if ($newUsername !== $user->username) {
                $exists = $db->table('users')->where('username', $newUsername)->where('id !=', $id)->countAllResults();
                if ($exists > 0) {
                    return $this->fail('Username sudah digunakan');
                }
            }
            $updateData['username'] = $newUsername;
        }

        if (isset($json['email'])) {
            $updateData['email'] = trim($json['email']);
        }

        if (!empty($json['password'])) {
            $updateData['password'] = $json['password']; // Note: In production, use password_hash()
        }

        if (isset($json['role'])) {
            if (!array_key_exists($json['role'], self::VALID_ROLES)) {
                return $this->fail('Role tidak valid');
            }
            // Proteksi: Jangan biarkan admin mengubah role diri sendiri
            $currentAdmin = $this->request->getHeaderLine('X-User-Action');
            if ($user->username === $currentAdmin && $json['role'] !== 'admin') {
                return $this->fail('Anda tidak dapat mengubah role diri sendiri');
            }
            $updateData['role'] = $json['role'];
        }

        if (isset($json['full_name'])) {
            $updateData['full_name'] = trim($json['full_name']);
        }

        if (isset($json['is_active'])) {
            // Proteksi: Jangan biarkan admin menonaktifkan diri sendiri
            $currentAdmin = $this->request->getHeaderLine('X-User-Action');
            if ($user->username === $currentAdmin && !$json['is_active']) {
                return $this->fail('Anda tidak dapat menonaktifkan akun diri sendiri');
            }
            $updateData['is_active'] = $json['is_active'] ? 1 : 0;
        }

        if (empty($updateData)) {
            return $this->fail('Tidak ada data yang diubah');
        }

        $db->table('users')->where('id', $id)->update($updateData);

        $this->logActivity("User ID $id diupdate");

        return $this->respond([
            'success' => true,
            'message' => 'User berhasil diupdate'
        ]);
    }

    /**
     * DELETE /api/users/:id — Hapus user
     */
    public function deleteUser($id = null)
    {
        if (!$this->checkAdmin()) {
            return $this->failForbidden('Hanya admin yang dapat mengakses fitur ini');
        }

        $db = \Config\Database::connect();
        $user = $db->table('users')->where('id', $id)->get()->getRow();
        if (!$user) {
            return $this->failNotFound('User tidak ditemukan');
        }

        // Proteksi: Jangan biarkan admin menghapus diri sendiri
        $currentAdmin = $this->request->getHeaderLine('X-User-Action');
        if ($user->username === $currentAdmin) {
            return $this->fail('Anda tidak dapat menghapus akun diri sendiri');
        }

        $db->table('users')->where('id', $id)->delete();

        $this->logActivity("User '$user->username' dihapus");

        return $this->respond([
            'success' => true,
            'message' => "User '$user->username' berhasil dihapus"
        ]);
    }

    /**
     * GET /api/roles — Return daftar role yang tersedia
     */
    public function getRoles()
    {
        $roles = [];
        foreach (self::VALID_ROLES as $key => $info) {
            $roles[] = [
                'value' => $key,
                'label' => $info['label'],
                'color' => $info['color']
            ];
        }
        return $this->respond(['data' => $roles]);
    }

    /**
     * Log activity ke tabel system_logs jika ada
     */
    private function logActivity(string $message)
    {
        $db = \Config\Database::connect();
        $username = $this->request->getHeaderLine('X-User-Action') ?: 'system';
        try {
            $db->table('system_logs')->insert([
                'action'    => $message,
                'user'      => $username,
            ]);
        } catch (\Exception $e) {
            // Ignore jika tabel belum ada
        }
    }
}
