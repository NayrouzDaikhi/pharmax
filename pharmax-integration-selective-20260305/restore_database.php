<?php
echo "═══════════════════════════════════════════════════════════════\n";
echo "PHARMAX DATABASE RESTORATION\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$pdo = new PDO('mysql:host=127.0.0.1;dbname=pharm', 'root', '');

echo "[1] Restoring from import_mysql.sql...\n";
$sql = file_get_contents('import_mysql.sql');
$statements = array_filter(array_map('trim', explode(';', $sql)));

$count = 0;
foreach ($statements as $statement) {
    if (empty($statement) || substr($statement, 0, 2) === '--') {
        continue;
    }
    try {
        $pdo->exec($statement);
        $count++;
    } catch (Exception $e) {
        echo "   Warning: " . $e->getMessage() . "\n";
    }
}
echo "✓ Executed $count SQL statements\n\n";

echo "[2] Creating admin user...\n";
try {
    $pdo->prepare(
        "INSERT INTO user (email, roles, password, first_name, last_name, status, created_at) 
         VALUES (?, ?, ?, ?, ?, ?, NOW())
         ON DUPLICATE KEY UPDATE password=VALUES(password)"
    )->execute([
        'nayrouzdaikhi@gmail.com',
        '["ROLE_ADMIN"]',
        'nayrouz123',
        'nayrouz',
        'daikhi',
        'UNBLOCKED'
    ]);
    echo "✓ Admin user created/updated\n\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

echo "[3] Verifying data...\n";
$tables = [
    'categorie' => 'Categories',
    'produit' => 'Products',
    'article' => 'Articles',
    'user' => 'Users',
    'reclamation' => 'Complaints',
    'commentaire' => 'Comments'
];

foreach ($tables as $table => $label) {
    $count = $pdo->query("SELECT COUNT(*) as cnt FROM `$table`")->fetch(PDO::FETCH_ASSOC)['cnt'];
    echo "   ✓ $label: $count rows\n";
}

echo "\n═══════════════════════════════════════════════════════════════\n";
echo "RESTORATION COMPLETE!\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "\nYour PHARM database has been restored with original data.\n";
echo "The old 'pharmax' database still exists if you need the test data.\n";
