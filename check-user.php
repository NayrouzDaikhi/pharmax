<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=pharmax', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
]);

$stmt = $pdo->prepare('SELECT id, email, first_name, last_name, roles FROM `user` WHERE email = ?');
$stmt->execute(['nayrouzdaikhi@gmail.com']);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    echo "✅ User found:\n";
    echo "   ID: " . $user['id'] . "\n";
    echo "   Email: " . $user['email'] . "\n";
    echo "   Name: " . $user['first_name'] . " " . $user['last_name'] . "\n";
    echo "   Roles: " . $user['roles'] . "\n\n";
    echo "🎉 User is ready to login!\n";
    echo "Password: nayrouz123\n";
} else {
    echo "❌ User not found - need to create it\n";
}
