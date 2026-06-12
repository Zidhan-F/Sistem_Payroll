<?php
// Script to regenerate missing overtime_logs from existing attendance_logs
$db = new PDO(
    'sqlsrv:Server=localhost;Database=payroll_db',
    '', '',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$client = $db->query("SELECT id FROM clients WHERE nama LIKE '%SIKET AAA%'")->fetch(PDO::FETCH_ASSOC);
$clientId = $client['id'];

$emps = $db->query("SELECT id FROM employees WHERE client_id = {$clientId}")->fetchAll(PDO::FETCH_COLUMN);
$empIdList = implode(',', $emps);

// Get all attendance logs with overtime > 0 that DON'T have a matching overtime_log
$sql = "SELECT al.employee_id, al.log_date, al.check_in, al.check_out, 
               al.calculated_overtime_hours, al.payout_period, al.shift_scheme_id
        FROM attendance_logs al
        LEFT JOIN overtime_logs ol ON ol.employee_id = al.employee_id AND ol.tanggal = al.log_date
        WHERE al.employee_id IN ({$empIdList})
          AND al.calculated_overtime_hours > 0
          AND ol.id IS NULL
        ORDER BY al.employee_id, al.log_date";

$missing = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
echo "Ditemukan " . count($missing) . " attendance logs TANPA overtime log.\n\n";

$inserted = 0;
foreach ($missing as $row) {
    $tanggal = $row['log_date'];
    $dayOfWeek = date('w', strtotime($tanggal));
    $isHoliday = ($dayOfWeek == 0) ? 1 : 0; // Sunday = holiday
    
    if (!$isHoliday) {
        $hol = $db->prepare("SELECT id FROM holiday_calendar WHERE tanggal = ?");
        $hol->execute([$tanggal]);
        if ($hol->fetch()) $isHoliday = 1;
    }

    // Get shift name
    $shiftName = 'Unknown';
    if ($row['shift_scheme_id']) {
        $s = $db->prepare("SELECT name FROM shift_schemes WHERE id = ?");
        $s->execute([$row['shift_scheme_id']]);
        $sRow = $s->fetch(PDO::FETCH_ASSOC);
        if ($sRow) $shiftName = $sRow['name'];
    }

    $stmt = $db->prepare("INSERT INTO overtime_logs (employee_id, tanggal, jam_lembur, is_holiday, keterangan, status, payout_period, is_rapel)
                          VALUES (?, ?, ?, ?, ?, 'Pending', ?, 0)");
    $stmt->execute([
        $row['employee_id'],
        $tanggal,
        $row['calculated_overtime_hours'],
        $isHoliday,
        'Auto: shift ' . $shiftName,
        $row['payout_period']
    ]);
    $inserted++;
    echo "  + Inserted: emp={$row['employee_id']} | date={$tanggal} | OT={$row['calculated_overtime_hours']}hr | period={$row['payout_period']} | shift={$shiftName}\n";
}

echo "\nTotal inserted: {$inserted}\n";

// Verify
echo "\n=== VERIFIKASI: Semua overtime_logs Juni 2026 ===\n";
$verify = $db->query("SELECT ol.employee_id, e.nama, e.nik, ol.tanggal, ol.jam_lembur, ol.status, ol.payout_period, ol.keterangan
    FROM overtime_logs ol
    JOIN employees e ON e.id = ol.employee_id
    WHERE ol.employee_id IN ({$empIdList}) AND MONTH(ol.tanggal) = 6 AND YEAR(ol.tanggal) = 2026
    ORDER BY ol.employee_id, ol.tanggal")->fetchAll(PDO::FETCH_ASSOC);

echo str_pad("Karyawan", 28) . str_pad("Tanggal", 14) . str_pad("OT", 8) . str_pad("Status", 10) . str_pad("Period", 10) . "\n";
echo str_repeat("-", 70) . "\n";
foreach ($verify as $v) {
    echo str_pad($v['nama'], 28) . str_pad($v['tanggal'], 14) . str_pad($v['jam_lembur'].'hr', 8) . str_pad($v['status'], 10) . str_pad($v['payout_period'] ?? '-', 10) . "\n";
}
echo "\nTotal overtime logs Juni: " . count($verify) . "\n";
