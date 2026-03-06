<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=pharm', 'root', '');
echo "=== Current AUTO_INCREMENT status ===\n";
$result = $pdo->query('SHOW TABLE STATUS LIKE "commandes"');
$row = $result->fetch(PDO::FETCH_ASSOC);
echo "Current AUTO_INCREMENT: " . $row['Auto_increment'] . "\n";

echo "\n=== Actual Max ID in table ===\n";
$result = $pdo->query('SELECT MAX(id) as max_id FROM commandes');
$row = $result->fetch(PDO::FETCH_ASSOC);
echo "Max ID: " . ($row['max_id'] ?? 'NULL') . "\n";

echo "\n=== All IDs in table ===\n";
$result = $pdo->query('SELECT id FROM commandes ORDER BY id');
$rows = $result->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo $r['id'] . ", ";
}
echo "\n";
