<?php
// Simple script to execute SQL to fix the schema
require 'vendor/autoload.php';

use Doctrine\DBAL\DriverManager;

try {
    // Create a connection
    $connection = DriverManager::getConnection([
        'driver' => 'pdo_mysql',
        'host' => '127.0.0.1',
        'port' => 3306,
        'dbname' => 'pharmax',
        'user' => 'root',
        'password' => '',
    ]);

    echo "Connected to database\n";

    // Check if user_id column exists in reponse table
    $result = $connection->fetchAllAssociative("DESCRIBE reponse");
    $columnNames = array_column($result, 'Field');
    
    echo "Reponse table columns: " . implode(', ', $columnNames) . "\n";
    
    if (!in_array('user_id', $columnNames)) {
        echo "\nAdding user_id column to reponse table...\n";
        
        // Add the column
        $connection->executeStatement("ALTER TABLE reponse ADD COLUMN user_id INT DEFAULT NULL");
        echo "✓ Added user_id column\n";
        
        // Add foreign key constraint
        try {
            $connection->executeStatement("ALTER TABLE reponse ADD CONSTRAINT FK_5FB6DEC7A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE SET NULL");
            echo "✓ Added foreign key constraint\n";
        } catch (Exception $e) {
            echo "Note: Foreign key might already exist: " . $e->getMessage() . "\n";
        }
        
        // Create index
        $connection->executeStatement("CREATE INDEX IDX_5FB6DEC7A76ED395 ON reponse (user_id)");
        echo "✓ Created index on user_id\n";
        
        echo "\n✓ Schema update completed successfully!\n";
    } else {
        echo "\n✓ user_id column already exists in reponse table\n";
    }
    
    $connection->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
