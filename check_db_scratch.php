<?php
define('FCPATH', 'c:/Users/Zidhan/Downloads/Sistem_Payroll-main/public/');
require 'c:/Users/Zidhan/Downloads/Sistem_Payroll-main/app/Config/Paths.php';
$paths = new Config\Paths();
require $paths->systemDirectory . '/Boot.php';
if (! defined('ENVIRONMENT')) {
    define('ENVIRONMENT', 'development');
}
CodeIgniter\Boot::bootConsole($paths);

$db = \Config\Database::connect();
$tables = $db->listTables();
echo json_encode($tables, JSON_PRETTY_PRINT) . PHP_EOL;
