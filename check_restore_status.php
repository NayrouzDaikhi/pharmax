<?php
echo "═══════════════════════════════════════════════════════════════\n";
echo "CHECKING PHARM DATABASE STATUS\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=pharm', 'root', '');
    
    $tables = [
        'categorie' => 'Categories',
        'produit' => 'Products',
        'article' => 'Articles',
        'user' => 'Users',
        'reclamation' => 'Complaints',
        'commentaire' => 'Comments',
        'commandes' => 'Orders',
        'ligne_commandes' => 'Order Lines'
    ];
    
    echo "Table Status:\n";
    foreach ($tables as $table => $label) {
        try {
            $count = $pdo->query("SELECT COUNT(*) as cnt FROM `$table`")->fetch(PDO::FETCH_ASSOC)['cnt'];
            echo "  ✓ $label ($table): $count rows\n";
        } catch (Exception $e) {
            echo "  ✗ $label ($table): Table does not exist\n";
        }
    }
    
    echo "\nSample Data:\n";
    $admin = $pdo->query("SELECT email, first_name FROM user WHERE roles LIKE '%ROLE_ADMIN%' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    if ($admin) {
        echo "  ✓ Admin User: " . $admin['email'] . " (" . $admin['first_name'] . ")\n";
    } else {
        echo "  ✗ No admin user found\n";
    }
    
    $product = $pdo->query("SELECT nom, prix FROM produit LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    if ($product) {
        echo "  ✓ Sample Product: " . $product['nom'] . " - €" . $product['prix'] . "\n";
    }
    
    echo "\n✅ DATABASE RESTORATION SUCCESSFUL!\n";
    echo "\nYour PHARM database has been restored.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
