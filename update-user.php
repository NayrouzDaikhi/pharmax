<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=pharmax', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
]);

// Update the user with correct data
$password = 'nayrouz123';
$passwordHash = password_hash($password, PASSWORD_BCRYPT);

$sql = <<<SQL
UPDATE `user` SET 
    `first_name` = 'Nayrouzdaikhi',
    `last_name` = 'Admin',
    `roles` = '["ROLE_SUPER_ADMIN", "ROLE_USER"]',
    `password` = ?,
    `status` = 'UNBLOCKED',
    `updated_at` = NOW()
WHERE email = 'nayrouzdaikhi@gmail.com'
SQL;

$stmt = $pdo->prepare($sql);
$stmt->execute([$passwordHash]);

echo "✅ User updated successfully!\n\n";
echo "Login Information:\n";
echo "  Email: nayrouzdaikhi@gmail.com\n";
echo "  Password: nayrouz123\n";
echo "  Roles: ROLE_SUPER_ADMIN, ROLE_USER\n";
echo "  Face Recognition: Not required (optional)\n";
echo "\n";
echo "You can now login and access the admin panel.\n";
