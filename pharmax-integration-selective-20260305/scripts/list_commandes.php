<?php
// Quick debug script to list latest commandes
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=pharm;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $pdo->query("SELECT id, statut, utilisateur_id, created_at FROM commandes ORDER BY id DESC LIMIT 10");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!$rows) {
        echo "No commandes found.\n";
        exit(0);
    }
    foreach ($rows as $r) {
        echo sprintf(
            "id=%d | statut=%s | utilisateur_id=%s | created_at=%s\n",
            $r['id'],
            $r['statut'],
            $r['utilisateur_id'] ?? 'NULL',
            $r['created_at']
        );
    }
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

