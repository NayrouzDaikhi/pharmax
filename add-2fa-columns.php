<?php
/**
 * Add missing 2FA columns to user table
 */

$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=pharmax', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
]);

try {
    echo "Adding missing 2FA columns to user table...\n\n";
    
    // Add columns one by one
    $columns = [
        'google_authenticator_secret' => 'VARCHAR(255) DEFAULT NULL',
        'google_authenticator_secret_pending' => 'VARCHAR(255) DEFAULT NULL',
        'is_2fa_setup_in_progress' => 'TINYINT(1) DEFAULT 0'
    ];
    
    foreach ($columns as $col => $type) {
        $sm = $pdo->query("SHOW COLUMNS FROM `user` LIKE '$col'");
        $exists = $sm->fetch();
        
        if ($exists) {
            echo "  ℹ️  Column '$col' already exists\n";
        } else {
            echo "  Adding '$col'...\n";
            $pdo->exec("ALTER TABLE `user` ADD COLUMN `$col` $type");
            echo "    ✅ Added successfully\n";
        }
    }
    
    echo "\n✅ All 2FA columns added successfully!\n";
    
    // Verify final schema
    echo "\nVerifying final schema:\n";
    $result = $pdo->query("SHOW COLUMNS FROM `user`");
    $columns = $result->fetchAll(PDO::FETCH_COLUMN);
    
    $required = [
        'id', 'email', 'roles', 'password', 'first_name', 'last_name', 'status',
        'created_at', 'updated_at', 'google_id', 'avatar', 
        'google_authenticator_secret', 'google_authenticator_secret_pending',
        'is_2fa_setup_in_progress', 'data_face_api'
    ];
    
    $all_good = true;
    foreach ($required as $col) {
        if (in_array($col, $columns)) {
            echo "  ✅ $col\n";
        } else {
            echo "  ❌ $col\n";
            $all_good = false;
        }
    }
    
    if ($all_good) {
        echo "\n✅ User table schema is complete!\n";
    }
    
    exit(0);
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
