<?php
// Bootstrap CodeIgniter 4
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
$pathsPath = realpath(FCPATH . '../app/Config/Paths.php') ?: FCPATH . '../app/Config/Paths.php';
require $pathsPath;
$paths = new Config\Paths();
$bootstrap = rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';
require $bootstrap;

$db = \Config\Database::connect();

echo "=== CLIENTS ===\n";
$clients = $db->table('clients')->get()->getResultArray();
foreach ($clients as $c) {
    echo "ID: {$c['id']}, Name: {$c['nama']}\n";
}

echo "\n=== EMPLOYEES ===\n";
$employees = $db->table('employees')->get()->getResultArray();
foreach ($employees as $e) {
    echo "ID: {$e['id']}, Name: {$e['nama']}, Client ID: {$e['client_id']}\n";
}

echo "\n=== PKWT ===\n";
$pkwts = $db->table('pkwt')->get()->getResultArray();
foreach ($pkwts as $p) {
    echo "ID: {$p['id']}, Name: {$p['employee_name']}, Client ID: {$p['client_id']}\n";
}
