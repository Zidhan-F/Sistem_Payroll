<?php
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);
require FCPATH . '../app/Config/Paths.php';
$paths = new Config\Paths();
require rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';
$db = \Config\Database::connect();
$query = $db->query("SELECT TOP 5 id, action, description, created_at FROM system_logs ORDER BY id DESC");
$results = $query->getResultArray();
if (empty($results)) {
    echo "Tabel system_logs masih kosong.\n";
} else {
    foreach ($results as $row) {
        echo "ID: " . $row['id'] . "\n";
        echo "Action: " . $row['action'] . "\n";
        echo "Description:\n" . $row['description'] . "\n";
        echo "Created At: " . $row['created_at'] . "\n";
        echo "----------------------------------------\n";
    }
}
