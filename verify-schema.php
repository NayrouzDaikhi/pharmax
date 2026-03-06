<?php
/**
 * Comprehensive database schema check and fix
 * Compares database schema with known entity requirements
 */

$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=pharmax', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
]);

// Define all required columns per table
$schema = [
    'user' => [
        'id' => 'INT PRIMARY KEY AUTO_INCREMENT',
        'email' => 'VARCHAR(180) UNIQUE',
        'roles' => 'JSON',
        'password' => 'VARCHAR(255)',
        'first_name' => 'VARCHAR(255)',
        'last_name' => 'VARCHAR(255)',
        'status' => 'VARCHAR(16)',
        'created_at' => 'DATETIME',
        'updated_at' => 'DATETIME',
        'google_id' => 'VARCHAR(255)',
        'avatar' => 'VARCHAR(255)',
        'google_authenticator_secret' => 'VARCHAR(255)',
        'google_authenticator_secret_pending' => 'VARCHAR(255)',
        'is_2fa_setup_in_progress' => 'TINYINT(1)',
        'data_face_api' => 'LONGTEXT'
    ],
    'article' => [
        'id' => 'INT PRIMARY KEY AUTO_INCREMENT',
        'titre' => 'VARCHAR(255)',
        'contenu' => 'LONGTEXT',
        'contenu_en' => 'LONGTEXT',
        'image' => 'VARCHAR(255)',
        'created_at' => 'DATETIME',
        'updated_at' => 'DATETIME',
        'likes' => 'INT',
        'is_draft' => 'TINYINT(1)'
    ],
    'produit' => [
        'id' => 'INT PRIMARY KEY AUTO_INCREMENT',
        'categorie_id' => 'INT',
        'nom' => 'VARCHAR(255)',
        'description' => 'LONGTEXT',
        'prix' => 'DOUBLE',
        'image' => 'VARCHAR(255)',
        'date_expiration' => 'DATE',
        'statut' => 'TINYINT(1)',
        'created_at' => 'DATETIME',
        'quantite' => 'INT',
        'promotion_pourcentage' => 'INT'
    ],
    'commandes' => [
        'id' => 'INT PRIMARY KEY AUTO_INCREMENT',
        'utilisateur_id' => 'INT',
        'produits' => 'LONGTEXT',
        'totales' => 'DOUBLE',
        'statut' => 'VARCHAR(50)',
        'created_at' => 'DATETIME'
    ]
];

echo "=== Database Schema Verification ===\n\n";

$issues = [];

foreach ($schema as $table => $columns) {
    echo "Checking table: $table\n";
    
    try {
        $existingColumns = [];
        $result = $pdo->query("SHOW COLUMNS FROM `$table`");
        
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $existingColumns[] = $row['Field'];
        }
        
        foreach ($columns as $col => $type) {
            if (in_array($col, $existingColumns)) {
                echo "  ✅ $col\n";
            } else {
                echo "  ❌ $col (MISSING)\n";
                $issues[$table][] = $col;
            }
        }
        
    } catch (Exception $e) {
        echo "  ⚠️  Table doesn't exist: {$e->getMessage()}\n";
    }
    
    echo "\n";
}

if (!empty($issues)) {
    echo "\n=== Issues Found ===\n\n";
    
    foreach ($issues as $table => $cols) {
        echo "Table '$table' is missing columns:\n";
        foreach ($cols as $col) {
            echo "  - $col\n";
        }
    }
    
    echo "\n⚠️  Run doctrine:schema:update to fix these issues:\n";
    echo "php bin/console doctrine:schema:update --dump-sql\n";
    echo "php bin/console doctrine:schema:update --force\n";
} else {
    echo "✅ All columns exist! Database schema is complete.\n";
}
