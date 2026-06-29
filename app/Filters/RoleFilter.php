<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * RoleFilter — Backend RBAC middleware for API routes.
 *
 * Usage in Routes.php:
 *   $routes->group('api', ['filter' => 'role:admin,payroll'], function($routes) { ... });
 *
 * The filter reads the 'X-User-Action' header (username), queries the database
 * for the user's role, and checks if the role is in the allowed list.
 */
class RoleFilter implements FilterInterface
{
    /**
     * @param RequestInterface $request
     * @param array|null       $arguments  Allowed roles, e.g. ['admin', 'payroll']
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // If no roles specified, allow all authenticated users
        $allowedRoles = $arguments ?? [];

        // Read the username from the X-User-Action header
        $username = $request->getHeaderLine('X-User-Action');

        if (empty($username)) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON([
                    'status'   => 401,
                    'error'    => 401,
                    'messages' => ['error' => 'Autentikasi diperlukan. Header X-User-Action tidak ditemukan.']
                ]);
        }

        // Query the user's role from the database
        $db   = \Config\Database::connect();
        $user = $db->table('users')
                   ->select('role, is_active')
                   ->where('username', $username)
                   ->get()
                   ->getRow();

        if (!$user) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON([
                    'status'   => 401,
                    'error'    => 401,
                    'messages' => ['error' => 'User tidak ditemukan.']
                ]);
        }

        // Check if user is active
        if (isset($user->is_active) && !$user->is_active) {
            return service('response')
                ->setStatusCode(403)
                ->setJSON([
                    'status'   => 403,
                    'error'    => 403,
                    'messages' => ['error' => 'Akun Anda telah dinonaktifkan.']
                ]);
        }

        // Check if user role is pending
        if (($user->role ?? '') === 'pending') {
            return service('response')
                ->setStatusCode(403)
                ->setJSON([
                    'status'   => 403,
                    'error'    => 403,
                    'messages' => ['error' => 'Akun Anda belum disetujui oleh Administrator.']
                ]);
        }

        $userRole = $user->role ?? 'staff';

        // Admin always has access to everything
        if ($userRole === 'admin') {
            return;
        }

        // If allowed roles are specified, check if user's role is in the list
        if (!empty($allowedRoles) && !in_array($userRole, $allowedRoles, true)) {
            return service('response')
                ->setStatusCode(403)
                ->setJSON([
                    'status'   => 403,
                    'error'    => 403,
                    'messages' => ['error' => "Akses ditolak. Role '$userRole' tidak memiliki izin untuk operasi ini."]
                ]);
        }

        // All checks passed — allow the request
        return;
    }

    /**
     * After filter — not used.
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No post-processing needed
    }
}
