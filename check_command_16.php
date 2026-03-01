<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=pharm', 'root', '');

echo "=== Checking Command 16 ===\n";
$result = $pdo->query('SELECT id, statut, utilisateur_id FROM commandes WHERE id = 16');
$row = $result->fetch(PDO::FETCH_ASSOC);

if ($row) {
    echo "Command 16 FOUND:\n";
    print_r($row);
} else {
    echo "Command 16 NOT FOUND in database\n";
}

echo "\n=== All Recent Commands ===\n";
$all = $pdo->query('SELECT id, statut, utilisateur_id, created_at FROM commandes ORDER BY id DESC LIMIT 10');
$rows = $all->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo "ID: {$r['id']}, Statut: {$r['statut']}, User: {$r['utilisateur_id']}, Created: {$r['created_at']}\n";
}

echo "\n=== Table Structure ===\n";
$structure = $pdo->query('DESCRIBE commandes');
$cols = $structure->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $col) {
    echo "{$col['Field']}: {$col['Type']} (Null: {$col['Null']})\n";
}
