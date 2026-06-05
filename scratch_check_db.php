<?php
// Bootstrap CodeIgniter
define('FCPATH', __DIR__ . '/public/');
require __DIR__ . '/app/Config/Paths.php';
$paths = new Config\Paths();
require $paths->systemDirectory . '/bootstrap.php';

$db = \Config\Database::connect();

echo "--- OVERTIME LOGS ---\n";
$logs = $db->table('overtime_logs')->get()->getResultArray();
print_r($logs);

echo "\n--- EMPLOYEES ---\n";
$employees = $db->table('employees')->select('id, nama, client_id')->get()->getResultArray();
print_r($employees);
