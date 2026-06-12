<?php
try {
    $conn = new PDO("sqlsrv:Server=localhost\\SQLEXPRESS;Database=payroll_db");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== INSERTING SHIFT SCHEME ===\n";
    $sql = "INSERT INTO shift_schemes (name, start_time, end_time, duration, break_start_time, break_end_time, break_duration, created_at, updated_at) 
            VALUES (:name, :start_time, :end_time, :duration, :break_start_time, :break_end_time, :break_duration, GETDATE(), GETDATE())";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':name' => 'Shift Malam Test',
        ':start_time' => '20:00',
        ':end_time' => '05:00',
        ':duration' => 8.0,
        ':break_start_time' => '00:00',
        ':break_end_time' => '01:00',
        ':break_duration' => 1.0
    ]);
    
    $lastId = $conn->lastInsertId();
    echo "Inserted ID: $lastId\n\n";
    
    echo "=== SELECTING INSERTED SHIFT SCHEME ===\n";
    $stmt = $conn->prepare("SELECT * FROM shift_schemes WHERE id = :id");
    $stmt->execute([':id' => $lastId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    print_r($row);
    
    echo "\n=== DELETING TEST SHIFT SCHEME ===\n";
    $stmt = $conn->prepare("DELETE FROM shift_schemes WHERE id = :id");
    $stmt->execute([':id' => $lastId]);
    echo "Deleted successfully.\n";
    
} catch(PDOException $e) {
    echo "Failed: " . $e->getMessage() . "\n";
}
