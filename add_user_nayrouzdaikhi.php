<?php
// Script to add user nayrouzdaikhi@gmail.com

require 'vendor/autoload.php';
require 'config/bootstrap.php';

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

$container = require __DIR__ . '/config/bootstrap.php';
$entityManager = $container->get('doctrine')->getManager();
$passwordHasher = $container->get(UserPasswordHasherInterface::class);

// Check if user already exists
$existingUser = $entityManager->getRepository(User::class)->findOneBy([
    'email' => 'nayrouzdaikhi@gmail.com'
]);

if ($existingUser) {
    echo "✓ User nayrouzdaikhi@gmail.com already exists (ID: {$existingUser->getId()})\n";
    exit(0);
}

// Create new user
$user = new User();
$user->setEmail('nayrouzdaikhi@gmail.com');
$user->setFirstName('Nayrouz');
$user->setLastName('Daikhi');
$user->setRoles(['ROLE_USER']);
$user->setStatus('active');

// Set a temporary password (user can reset it)
$hashedPassword = $passwordHasher->hashPassword($user, 'TempPassword123!');
$user->setPassword($hashedPassword);

$user->setCreatedAt(new \DateTime());
$user->setUpdatedAt(new \DateTime());

try {
    $entityManager->persist($user);
    $entityManager->flush();
    
    echo "✓ User nayrouzdaikhi@gmail.com added successfully!\n";
    echo "  ID: {$user->getId()}\n";
    echo "  Email: {$user->getEmail()}\n";
    echo "  Name: {$user->getFirstName()} {$user->getLastName()}\n";
    echo "\nUser can now reset their password during login.\n";
} catch (\Exception $e) {
    echo "✗ Error adding user: " . $e->getMessage() . "\n";
    exit(1);
}
