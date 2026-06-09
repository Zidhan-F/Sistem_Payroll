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
$db->transBegin();
$db->query("
    WITH CTE AS (
        SELECT *,
               ROW_NUMBER() OVER (PARTITION BY employee_id, log_date ORDER BY COALESCE(shift_scheme_id, 0) DESC, id DESC) as rn
        FROM attendance_logs
    )
    DELETE FROM CTE WHERE rn > 1
");
$affected = $db->affectedRows();
$db->transCommit();
echo "Cleaned up duplicates: $affected rows deleted.\n";



