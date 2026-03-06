<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=pharm', 'root', '');

echo "=== Checking AUTO_INCREMENT ===\n";
$result = $pdo->query('SHOW TABLE STATUS LIKE "commandes"');
$row = $result->fetch(PDO::FETCH_ASSOC);
echo "Current AUTO_INCREMENT: " . $row['Auto_increment'] . "\n";

echo "\n=== Getting MAX ID from table ===\n";
$result = $pdo->query('SELECT MAX(id) as max_id FROM commandes');
$row = $result->fetch(PDO::FETCH_ASSOC);
$maxId = $row['max_id'] ?? 0;
echo "Max ID in table: " . $maxId . "\n";

$nextId = $maxId + 1;
echo "Setting AUTO_INCREMENT to: " . $nextId . "\n";

// Reset AUTO_INCREMENT
$pdo->exec("ALTER TABLE commandes AUTO_INCREMENT = $nextId");
echo "✓ AUTO_INCREMENT reset successfully!\n";

// Verify
$result = $pdo->query('SHOW TABLE STATUS LIKE "commandes"');
$row = $result->fetch(PDO::FETCH_ASSOC);
echo "New AUTO_INCREMENT: " . $row['Auto_increment'] . "\n";
