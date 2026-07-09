<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\RESTful\ResourceController;

class Auth extends ResourceController
{
    protected $modelName = 'App\Models\UserModel';
    protected $format    = 'json';

    /**
     * POST /api/login — Sign In dengan Username atau Email
     */
    public function login()
    {
        $json = $this->request->getJSON();
        $usernameOrEmail = trim($json->username ?? '');
        $password = $json->password ?? '';

        if (empty($usernameOrEmail) || empty($password)) {
            return $this->fail('Username/Email dan password wajib diisi');
        }

        // Cek apakah input adalah email dengan memindai karakter '@'
        $isEmail = strpos($usernameOrEmail, '@') !== false;

        if ($isEmail) {
            // Validasi email
            if (!filter_var($usernameOrEmail, FILTER_VALIDATE_EMAIL)) {
                return $this->fail('Format email tidak valid');
            }

            $user = $this->model->where('email', $usernameOrEmail)
                                ->where('password', $password)
                                ->first();
        } else {
            $user = $this->model->where('username', $usernameOrEmail)
                                ->where('password', $password)
                                ->first();
        }

        if ($user) {
            // Cek apakah user aktif
            if (isset($user['is_active']) && !$user['is_active']) {
                return $this->failUnauthorized('Akun Anda telah dinonaktifkan. Hubungi administrator.');
            }

            // Cek apakah role masih pending
            if (($user['role'] ?? '') === 'pending') {
                return $this->failUnauthorized('Akun Anda belum disetujui atau belum diberi role oleh Administrator.');
            }

            session()->set([
                'user_id'  => $user['id'] ?? 1,
                'username' => $user['username'],
                'role'     => $user['role'] ?? 'admin'
            ]);

            return $this->respond([
                'success' => true,
                'message' => 'Login successful',
                'user'    => [
                    'username'  => $user['username'],
                    'email'     => $user['email'],
                    'role'      => $user['role'] ?? 'admin',
                    'full_name' => $user['full_name'] ?? $user['username']
                ]
            ]);
        }

        return $this->failUnauthorized('Username/Email atau password salah');
    }

    /**
     * POST /api/auth/register — Pendaftaran User Baru (Role: pending)
     */
    public function register()
    {
        $json = $this->request->getJSON();
        $username = trim($json->username ?? '');
        $fullName = trim($json->full_name ?? '');
        $email = trim($json->email ?? '');
        $password = $json->password ?? '';
        $confirmPassword = $json->confirm_password ?? '';

        if (empty($username) || empty($email) || empty($password)) {
            return $this->fail('Username, email, dan password wajib diisi');
        }

        // Validasi format email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->fail('Format email tidak valid');
        }

        if ($password !== $confirmPassword) {
            return $this->fail('Konfirmasi password tidak cocok');
        }

        if (strlen($password) < 6) {
            return $this->fail('Password baru minimal harus 6 karakter');
        }

        // Cek jika username sudah terpakai
        $existingUser = $this->model->where('username', $username)->first();
        if ($existingUser) {
            return $this->fail('Username sudah terdaftar');
        }

        // Cek jika email sudah terpakai
        $existingEmail = $this->model->where('email', $email)->first();
        if ($existingEmail) {
            return $this->fail('Email is already registered');
        }

        // Simpan user baru dengan role 'pending'
        $this->model->insert([
            'username'  => $username,
            'full_name' => $fullName,
            'email'     => $email,
            'password'  => $password, // Plaintext sesuai standar proyek
            'role'      => 'pending',
            'is_active' => 1 // Status aktif tapi tertahan karena role pending
        ]);

        return $this->respond([
            'success' => true,
            'message' => 'Registration successful! Your account is pending approval and role assignment by the Administrator.'
        ]);
    }

    /**
     * POST /api/auth/forgot-password — Minta OTP untuk Reset Password
     */
    public function forgotPassword()
    {
        $json = $this->request->getJSON();
        $email = trim($json->email ?? '');

        if (empty($email)) {
            return $this->fail('Email wajib diisi');
        }

        // Validasi format email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->fail('Format email tidak valid');
        }

        // Cek apakah user dengan email tersebut ada
        $user = $this->model->where('email', $email)->first();
        if (!$user) {
            return $this->failNotFound('Email tidak terdaftar dalam sistem');
        }

        // Generate OTP 6 digit
        $otp = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
        
        $db = \Config\Database::connect();
        
        // Hapus token lama untuk email ini agar tidak menumpuk
        $db->table('password_resets')->where('email', $email)->delete();

        // Simpan token baru yang berlaku selama 15 menit
        $db->table('password_resets')->insert([
            'email'      => $email,
            'token'      => $otp,
            'created_at' => date('Y-m-d H:i:s'),
            'expires_at' => date('Y-m-d H:i:s', strtotime('+15 minutes'))
        ]);

        return $this->respond([
            'success' => true,
            'message' => 'OTP code generated successfully (Simulation)',
            'email'   => $email,
            'otp'     => $otp // Mengembalikan OTP untuk simulasi frontend toast
        ]);
    }

    /**
     * POST /api/auth/reset-password — Reset password menggunakan OTP
     */
    public function resetPassword()
    {
        $json = $this->request->getJSON();
        $email = trim($json->email ?? '');
        $token = trim($json->token ?? '');
        $password = $json->password ?? '';
        $confirmPassword = $json->confirm_password ?? '';

        if (empty($email) || empty($token) || empty($password) || empty($confirmPassword)) {
            return $this->fail('Semua field wajib diisi');
        }

        if ($password !== $confirmPassword) {
            return $this->fail('Konfirmasi password tidak cocok');
        }

        if (strlen($password) < 6) {
            return $this->fail('Password baru minimal harus 6 karakter');
        }

        $db = \Config\Database::connect();

        // Cari token yang cocok dan belum kedaluwarsa
        $reset = $db->table('password_resets')
                    ->where('email', $email)
                    ->where('token', $token)
                    ->where('expires_at >=', date('Y-m-d H:i:s'))
                    ->get()
                    ->getRow();

        if (!$reset) {
            return $this->fail('Kode OTP salah atau telah kedaluwarsa');
        }

        // Cari user untuk diupdate
        $user = $this->model->where('email', $email)->first();
        if (!$user) {
            return $this->failNotFound('User tidak ditemukan');
        }

        // Update password (mengikuti standar plaintext proyek ini)
        $this->model->update($user['id'], [
            'password' => $password
        ]);

        // Hapus token yang sudah terpakai
        $db->table('password_resets')->where('email', $email)->delete();

        return $this->respond([
            'success' => true,
            'message' => 'Password updated successfully. Please log in again.'
        ]);
    }
}
