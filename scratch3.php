<?php
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);
chdir(FCPATH);
$pathsPath = realpath(FCPATH . '../app/Config/Paths.php');
require $pathsPath;
$paths = new Config\Paths();
$bootstrap = rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'Boot.php';
require $bootstrap;

// Initialize CI4
\CodeIgniter\Boot::bootWeb($paths);

$db = \Config\Database::connect();
// Get latest payroll final records
$payroll = $db->table('payroll_final')->orderBy('id', 'DESC')->limit(10)->get()->getResultArray();
echo json_encode($payroll, JSON_PRETTY_PRINT);
