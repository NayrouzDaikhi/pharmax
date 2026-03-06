<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=pharm', 'root', '');

echo "=== Checking MySQL Privileges ===\n";
$result = $pdo->query("SHOW GRANTS FOR CURRENT_USER()");
$grants = $result->fetchAll(PDO::FETCH_ASSOC);
foreach ($grants as $grant) {
    echo $grant['Grants for root@localhost'] . "\n";
}

echo "\n=== Checking commandes Table ===\n";
$result = $pdo->query("SHOW CREATE TABLE commandes");
$table = $result->fetch(PDO::FETCH_ASSOC);
echo $table['Create Table'] . "\n";

echo "\n=== Checking Current User's Connection ===\n";
$result = $pdo->query("SELECT USER(), DATABASE(), @@AUTO_INCREMENT_INCREMENT");
$info = $result->fetch(PDO::FETCH_ASSOC);
print_r($info);

echo "\n=== Testing INSERT Privilege ===\n";
try {
    $now = date('Y-m-d H:i:s');
    $insert = $pdo->prepare("INSERT INTO commandes (totales, statut, created_at, utilisateur_id, produits) VALUES (?, ?, ?, ?, ?)");
    $result = $insert->execute([100.00, 'privilege_test', $now, 3, '[]']);
    if ($result) {
        $id = $pdo->lastInsertId();
        echo "✓ INSERT privilege confirmed. Last ID: $id\n";
        // Verify it exists
        $verify = $pdo->query("SELECT COUNT(*) as cnt FROM commandes WHERE id = $id");
        $row = $verify->fetch(PDO::FETCH_ASSOC);
        echo "✓ Verification: Record count = " . $row['cnt'] . "\n";
    }
} catch (\Exception $e) {
    echo "✗ INSERT failed: " . $e->getMessage() . "\n";
}
