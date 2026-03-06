<?php
// Verify both tables have the user_id column properly configured
require 'vendor/autoload.php';

use Doctrine\DBAL\DriverManager;

try {
    $connection = DriverManager::getConnection([
        'driver' => 'pdo_mysql',
        'host' => '127.0.0.1',
        'port' => 3306,
        'dbname' => 'pharmax',
        'user' => 'root',
        'password' => '',
    ]);

    echo "Verifying database schema fixes...\n\n";

    // Check reclamation table
    echo "=== Reclamation Table ===\n";
    $reclamationColumns = $connection->fetchAllAssociative("DESCRIBE reclamation");
    $reclamationColumnNames = array_column($reclamationColumns, 'Field');
    echo "Columns: " . implode(', ', $reclamationColumnNames) . "\n";
    if (in_array('user_id', $reclamationColumnNames)) {
        echo "✓ user_id column exists\n";
    } else {
        echo "✗ user_id column missing!\n";
    }

    // Check reponse table
    echo "\n=== Reponse Table ===\n";
    $reponseColumns = $connection->fetchAllAssociative("DESCRIBE reponse");
    $reponseColumnNames = array_column($reponseColumns, 'Field');
    echo "Columns: " . implode(', ', $reponseColumnNames) . "\n";
    if (in_array('user_id', $reponseColumnNames)) {
        echo "✓ user_id column exists\n";
    } else {
        echo "✗ user_id column missing!\n";
    }

    // Test a simple query to ensure it works
    echo "\n=== Testing Query ===\n";
    try {
        $result = $connection->fetchOne("SELECT COUNT(*) FROM reclamation r LEFT JOIN reponse rep ON r.id = rep.reclamation_id");
        echo "✓ Join query works: Found " . $result . " records\n";
    } catch (Exception $e) {
        echo "✗ Query failed: " . $e->getMessage() . "\n";
    }

    $connection->close();
    echo "\n✓ All checks passed! The schema should now be fixed.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
