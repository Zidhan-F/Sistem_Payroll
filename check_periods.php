<?php
$db = new PDO('sqlsrv:Server=localhost;Database=payroll_db','','');
$periods = $db->query("SELECT * FROM payroll_periods")->fetchAll(PDO::FETCH_ASSOC);
foreach($periods as $p) {
    echo "ID: {$p['id']} | Name: {$p['nama']} | Month: {$p['bulan']} | Year: {$p['tahun']} | Status: {$p['status']}\n";
}
