<?php
// Direct database test
try {
    $pdo = new PDO(
        'mysql:host=127.0.0.1;dbname=pharm',
        'root',
        ''
    );
    
    echo "=== Direct INSERT test ===\n";
    $now = date('Y-m-d H:i:s');
    $sql = "INSERT INTO commandes (totales, statut, created_at, utilisateur_id, produits) VALUES (100.00, 'test_direct', '{$now}', 3, '[]')";
    $pdo->exec($sql);
    $id = $pdo->lastInsertId();
    echo "✓ Direct INSERT successful! New ID: $id\n";
    
    // Check if it exists
    $result = $pdo->query("SELECT * FROM commandes WHERE id = $id");
    $row = $result->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        echo "✓ Verification: Record found in database\n";
        print_r($row);
    } else {
        echo "✗ Verification: Record NOT found\n";
    }
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
