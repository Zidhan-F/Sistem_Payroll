<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\RESTful\ResourceController;

class Auth extends ResourceController
{
    protected $modelName = 'App\Models\UserModel';
    protected $format    = 'json';

    public function login()
    {
        $json = $this->request->getJSON();
        $username = $json->username ?? '';
        $password = $json->password ?? '';

        $user = $this->model->where('username', $username)
                            ->where('password', $password) // Note: In production use password_verify
                            ->first();

        if ($user) {
            session()->set([
                'user_id'  => $user['id'] ?? 1,
                'username' => $user['username'],
                'role'     => $user['role']
            ]);

            return $this->respond([
                'success' => true,
                'message' => 'Login berhasil',
                'user'    => [
                    'username' => $user['username'],
                    'email'    => $user['email'],
                    'role'     => $user['role']
                ]
            ]);
        }

        return $this->failUnauthorized('Username atau password salah');
    }
}
