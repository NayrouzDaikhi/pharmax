<?php
/**
 * Direct database manipulation script
 * Adds the data_face_api column and creates the nayrouzdaikhi admin user
 */

// Database config from .env
$dbHost = '127.0.0.1';
$dbPort = 3306;
$dbName = 'pharmax';
$dbUser = 'root';
$dbPass = '';

try {
    // Create PDO connection
    $dsn = "mysql:host=$dbHost;port=$dbPort;dbname=$dbName";
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]);
    
    echo "✅ Connected to database\n\n";
    
    // Step 1: Add column if it doesn't exist
    echo "Step 1: Checking/Adding data_face_api column...\n";
    try {
        $pdo->exec('ALTER TABLE `user` ADD COLUMN `data_face_api` LONGTEXT DEFAULT NULL');
        echo "  ✅ Column added\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "  ℹ️  Column already exists\n";
        } else {
            throw $e;
        }
    }
    
    // Step 2: Check if user already exists
    echo "\nStep 2: Checking if user exists...\n";
    $stmt = $pdo->prepare('SELECT id FROM `user` WHERE email = ?');
    $stmt->execute(['nayrouzdaikhi@gmail.com']);
    $existing = $stmt->fetch();
    
    if ($existing) {
        echo "  ⚠️  User already exists (ID: " . $existing['id'] . ")\n";
    } else {
        // Step 3: Create user
        echo "\nStep 3: Creating admin user...\n";
        
        // Password hash for 'nayrouz123'
        // Using bcrypt - generated with proper salt
        $password = 'nayrouz123';
        
        // Use a bcrypt hash (you may want to update this with a real hash)
        // For now, using a placeholder - in production, generate this properly
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        
        $sql = <<<SQL
            INSERT INTO `user` (
                `email`,
                `roles`,
                `password`,
                `first_name`,
                `last_name`,
                `status`,
                `created_at`,
                `updated_at`,
                `data_face_api`
            ) VALUES (
                'nayrouzdaikhi@gmail.com',
                '["ROLE_SUPER_ADMIN", "ROLE_USER"]',
                ?,
                'Nayrouzdaikhi',
                'Admin',
                'UNBLOCKED',
                NOW(),
                NOW(),
                NULL
            )
        SQL;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$passwordHash]);
        
        $userId = $pdo->lastInsertId();
        echo "  ✅ User created with ID: $userId\n";
        echo "  📧 Email: nayrouzdaikhi@gmail.com\n";
        echo "  🔑 Password: nayrouz123\n";
        echo "  👤 Roles: ROLE_SUPER_ADMIN, ROLE_USER\n";
        echo "  😊 Face Recognition: Not required (optional)\n";
    }
    
    echo "\n✅ Complete! User is ready to login.\n";
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
