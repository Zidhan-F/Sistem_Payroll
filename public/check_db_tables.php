<?php
try {
    $conn = new PDO("sqlsrv:Server=localhost\\SQLEXPRESS;Database=payroll_db", "", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== COLUMNS FOR payroll_periods ===\n";
    $stmt = $conn->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'payroll_periods'");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['COLUMN_NAME'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
