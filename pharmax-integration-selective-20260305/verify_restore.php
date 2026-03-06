<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=pharm', 'root', '');

echo "=== PHARM Database Data Status ===\n\n";

$tables = ['categorie', 'produit', 'article', 'user', 'commandes'];

foreach ($tables as $table) {
    try {
        $result = $pdo->query("SELECT COUNT(*) as cnt FROM `$table`");
        $row = $result->fetch(PDO::FETCH_ASSOC);
        echo "✓ $table: " . $row['cnt'] . " rows\n";
    } catch (Exception $e) {
        echo "✗ $table: Error - " . $e->getMessage() . "\n";
    }
}

echo "\n=== Sample Data ===\n";
$products = $pdo->query("SELECT nom, prix FROM produit LIMIT 3");
$rows = $products->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $row) {
    echo "- {$row['nom']}: €{$row['prix']}\n";
}
