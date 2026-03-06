<?php
// Add user nayrouzdaikhi@gmail.com - Simple version

require 'vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\DBAL\DriverManager;

// Load .env file
(new Dotenv())->load(__DIR__ . '/.env');

// Get database URL
$dbUrl = $_ENV['DATABASE_URL'] ?? '';

// Create connection
$connection = DriverManager::getConnection([
    'url' => $dbUrl
]);

echo "✓ Database connected\n";

// Check if user exists
$sql = "SELECT id FROM user WHERE email = ?";
$stmt = $connection->prepare($sql);
$result = $stmt->executeQuery(['nayrouzdaikhi@gmail.com']);
$user = $result->fetchOne();

if ($user) {
    echo "✓ User nayrouzdaikhi@gmail.com already exists (ID: {$user['id']})\n";
    exit(0);
}

// Create password hash (simple bcrypt)
$password = password_hash('TempPassword123!', PASSWORD_BCRYPT, ['cost' => 13]);

// Insert user
$insertSql = "INSERT INTO user (email, password, first_name, last_name, roles, status, created_at, updated_at) 
              VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";

$stmt = $connection->prepare($insertSql);
$stmt->executeStatement([
    'nayrouzdaikhi@gmail.com',
    $password,
    'Nayrouz',
    'Daikhi',
    json_encode(['ROLE_USER']),
    'active'
]);

echo "✓ User nayrouzdaikhi@gmail.com added successfully!\n";
echo "  Email: nayrouzdaikhi@gmail.com\n";
echo "  Name: Nayrouz Daikhi\n";
echo "  Status: active\n";
echo "  Password: Test password - user can reset via 'Forgot password' link\n";
