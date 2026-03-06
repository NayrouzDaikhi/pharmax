<?php
/**
 * Add missing promotion_pourcentage column to produit table
 */

$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=pharmax', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
]);

try {
    echo "Checking if promotion_pourcentage column exists...\n";
    
    $sm = $pdo->query("SHOW COLUMNS FROM `produit` LIKE 'promotion_pourcentage'");
    $exists = $sm->fetch();
    
    if ($exists) {
        echo "✅ Column 'promotion_pourcentage' already exists.\n";
    } else {
        echo "Adding column 'promotion_pourcentage'...\n";
        
        $pdo->exec('ALTER TABLE `produit` ADD COLUMN `promotion_pourcentage` INT DEFAULT NULL');
        
        echo "✅ Column 'promotion_pourcentage' added successfully.\n";
    }
    
    // Verify all required columns exist
    echo "\nVerifying all product columns:\n";
    $columns = $pdo->query("SHOW COLUMNS FROM `produit`")->fetchAll(PDO::FETCH_COLUMN);
    
    $required = ['id', 'categorie_id', 'nom', 'description', 'prix', 'image', 'date_expiration', 'statut', 'created_at', 'quantite', 'promotion_pourcentage'];
    
    foreach ($required as $col) {
        if (in_array($col, $columns)) {
            echo "  ✅ $col\n";
        } else {
            echo "  ❌ $col (MISSING)\n";
        }
    }
    
    echo "\n✅ Database schema check complete!\n";
    exit(0);
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
