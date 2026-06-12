<?php
try {
    $conn = new PDO("sqlsrv:Server=localhost\\SQLEXPRESS;Database=payroll_db");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== SHIFT SCHEMES ===\n";
    $stmt = $conn->query("SELECT id, name, start_time, end_time, duration, break_duration FROM shift_schemes");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        printf("ID: %d | Name: %s | Start: %s | End: %s | Duration: %s | Break: %s\n", 
            $row['id'], $row['name'], $row['start_time'], $row['end_time'], $row['duration'], $row['break_duration']);
    }
} catch(PDOException $e) {
    echo "Failed: " . $e->getMessage() . "\n";
}
