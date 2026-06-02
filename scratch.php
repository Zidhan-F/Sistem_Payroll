<?php
$db = new SQLite3('writable/database.sqlite');
if (!$db) {
    echo "Could not connect to database";
    exit;
}

$res = $db->query("SELECT * FROM pkwt_components");
$components = [];
while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
    $components[] = $row;
}
echo json_encode($components, JSON_PRETTY_PRINT);

$res2 = $db->query("SELECT * FROM payroll_components");
$components2 = [];
while ($row = $res2->fetchArray(SQLITE3_ASSOC)) {
    $components2[] = $row;
}
echo "\n--- MASTER COMPONENTS ---\n";
echo json_encode($components2, JSON_PRETTY_PRINT);
