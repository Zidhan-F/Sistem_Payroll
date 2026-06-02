<?php
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);
chdir(FCPATH);
require FCPATH . '../app/Config/Paths.php';
$paths = new Config\Paths();
require rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';

$db = \Config\Database::connect();
$employees = $db->table('employees')->get()->getResultArray();
echo json_encode($employees, JSON_PRETTY_PRINT);

$p = $db->table('payroll_final')->where('period_id', 4)->get()->getResultArray();
echo "\n--- PAYROLL FINAL ---\n";
echo json_encode($p, JSON_PRETTY_PRINT);
