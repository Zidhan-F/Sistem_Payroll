<?php
// Quick script to check attendance logs with overtime for SIKET AAA, Juni 2026
$db = new PDO(
    'sqlsrv:Server=localhost;Database=payroll_db',
    '', '',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// Get SIKET AAA client ID
$client = $db->query("SELECT id, nama FROM clients WHERE nama LIKE '%SIKET AAA%'")->fetch(PDO::FETCH_ASSOC);
if (!$client) { echo "Client SIKET AAA tidak ditemukan.\n"; exit; }
echo "Client: {$client['nama']} (ID: {$client['id']})\n\n";

// Get employees for this client
$emps = $db->prepare("SELECT id, nik, nama FROM employees WHERE client_id = ?");
$emps->execute([$client['id']]);
$employees = $emps->fetchAll(PDO::FETCH_ASSOC);
$empIds = array_column($employees, 'id');
$empMap = [];
foreach ($employees as $e) { $empMap[$e['id']] = $e['nama'] . ' (' . $e['nik'] . ')'; }

echo "=== ATTENDANCE LOGS JUNI 2026 (yang ada overtime) ===\n";
echo str_pad("Karyawan", 30) . str_pad("Tanggal", 14) . str_pad("Masuk", 8) . str_pad("Keluar", 8) . str_pad("OT Hours", 10) . str_pad("Period", 10) . "\n";
echo str_repeat("-", 80) . "\n";

$sql = "SELECT al.employee_id, al.log_date, al.check_in, al.check_out, 
               al.calculated_overtime_hours, al.payout_period, al.shift_scheme_id
        FROM attendance_logs al
        WHERE al.employee_id IN (" . implode(',', $empIds) . ")
          AND MONTH(al.log_date) = 6 AND YEAR(al.log_date) = 2026
        ORDER BY al.employee_id, al.log_date";
$rows = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

$otCount = 0;
$totalCount = count($rows);
foreach ($rows as $r) {
    $ot = floatval($r['calculated_overtime_hours']);
    if ($ot > 0) {
        $otCount++;
        echo str_pad($empMap[$r['employee_id']] ?? $r['employee_id'], 30)
           . str_pad($r['log_date'], 14)
           . str_pad($r['check_in'] ?? '-', 8)
           . str_pad($r['check_out'] ?? '-', 8)
           . str_pad($ot . ' hr', 10)
           . str_pad($r['payout_period'] ?? '-', 10)
           . "\n";
    }
}

echo "\nTotal attendance logs bulan Juni: {$totalCount}\n";
echo "Yang memiliki overtime: {$otCount}\n";

echo "\n=== OVERTIME LOGS JUNI 2026 ===\n";
echo str_pad("Karyawan", 30) . str_pad("Tanggal", 14) . str_pad("Jam Lembur", 12) . str_pad("Status", 12) . str_pad("Period", 10) . str_pad("Keterangan", 30) . "\n";
echo str_repeat("-", 108) . "\n";

$sql2 = "SELECT ol.employee_id, ol.tanggal, ol.jam_lembur, ol.status, ol.payout_period, ol.keterangan
         FROM overtime_logs ol
         WHERE ol.employee_id IN (" . implode(',', $empIds) . ")
           AND MONTH(ol.tanggal) = 6 AND YEAR(ol.tanggal) = 2026
         ORDER BY ol.employee_id, ol.tanggal";
$otRows = $db->query($sql2)->fetchAll(PDO::FETCH_ASSOC);

foreach ($otRows as $r) {
    echo str_pad($empMap[$r['employee_id']] ?? $r['employee_id'], 30)
       . str_pad($r['tanggal'], 14)
       . str_pad($r['jam_lembur'] . ' hr', 12)
       . str_pad($r['status'], 12)
       . str_pad($r['payout_period'] ?? '-', 10)
       . str_pad(substr($r['keterangan'] ?? '-', 0, 28), 30)
       . "\n";
}
echo "\nTotal overtime logs bulan Juni: " . count($otRows) . "\n";
