<?php
try {
    $pdo = new PDO("sqlsrv:Server=localhost\SQLEXPRESS;Database=payroll_db", "", "");
    $stmt = $pdo->query("SELECT TOP 1 raw_components FROM payroll_final ORDER BY id DESC");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $data = json_decode($row['raw_components'], true);
    
    foreach ($data as $comp) {
        echo "- " . $comp['nama'] . ": " . $comp['nilai'] . " (" . $comp['jenis_komponen'] . ")\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
