<?php
$db = new PDO('sqlsrv:Server=localhost;Database=payroll_db','','');
$rows = $db->query("SELECT * FROM attendance_logs WHERE employee_id IN (2549, 2550) ORDER BY employee_id, log_date")->fetchAll(PDO::FETCH_ASSOC);
echo "=== ATTENDANCE LOGS ===\n";
foreach($rows as $r) {
    echo "Emp: {$r['employee_id']} | Date: {$r['log_date']} | In: {$r['check_in']} | Out: {$r['check_out']} | OT: {$r['calculated_overtime_hours']} | Period: {$r['payout_period']}\n";
}

$otRows = $db->query("SELECT * FROM overtime_logs WHERE employee_id IN (2549, 2550) ORDER BY employee_id, tanggal")->fetchAll(PDO::FETCH_ASSOC);
echo "\n=== OVERTIME LOGS ===\n";
foreach($otRows as $r) {
    echo "Emp: {$r['employee_id']} | Date: {$r['tanggal']} | Hours: {$r['jam_lembur']} | Status: {$r['status']} | Keterangan: {$r['keterangan']}\n";
}
