<?php
$file = 'c:\\Users\\steph\\Downloads\\Sistem_Payroll-main\\Sistem_Payroll-main\\app\\Controllers\\Api.php';
$content = file_get_contents($file);
$lines = explode("\n", $content);
foreach ($lines as $num => $line) {
    if (stripos($line, 'function getPeriods') !== false) {
        echo ($num + 1) . ": " . trim($line) . "\n";
    }
}
