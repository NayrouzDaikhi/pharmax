<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=pharm', 'root', '');

echo "=== Tables in PHARM database ===\n\n";
$result = $pdo->query('SHOW TABLES');
$tables = $result->fetchAll(PDO::FETCH_COLUMN);
foreach ($tables as $table) {
    $count = $pdo->query("SELECT COUNT(*) as cnt FROM `$table`")->fetch(PDO::FETCH_ASSOC);
    echo "$table: " . $count['cnt'] . " rows\n";
}
