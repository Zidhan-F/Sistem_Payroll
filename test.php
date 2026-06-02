<?php
require 'vendor/autoload.php';
$app = new \Config\Paths();
require SYSTEMPATH . 'bootstrap.php';
$db = \Config\Database::connect();
$q = $db->query("SELECT * FROM employees WHERE nama='Gita Susanto'");
print_r($q->getResultArray());
