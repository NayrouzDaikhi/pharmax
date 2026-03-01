<?php
// Script to clean duplicate emails in database

require 'vendor/autoload.php';
require 'config/bootstrap.php';

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

$em = $container->get(EntityManagerInterface::class);
$conn = $em->getConnection();

// Find duplicate emails
$sql = "SELECT email, COUNT(*) as count FROM user GROUP BY email HAVING count > 1";
$stmt = $conn->executeQuery($sql);
$duplicates = $stmt->fetchAllAssociative();

if (empty($duplicates)) {
    echo "No duplicate emails found.\n";
    exit(0);
}

echo "Found " . count($duplicates) . " duplicate emails:\n";
foreach ($duplicates as $row) {
    echo "- {$row['email']}: {$row['count']} users\n";
    
    // Get all users with this email
    $users = $em->getRepository(User::class)->findBy(['email' => $row['email']]);
    
    if (count($users) > 1) {
        echo "  Keeping user ID: " . $users[0]->getId() . "\n";
        
        // Remove other users (keeping the first one)
        for ($i = 1; $i < count($users); $i++) {
            echo "  Removing user ID: " . $users[$i]->getId() . "\n";
            $em->remove($users[$i]);
        }
    }
}

$em->flush();
echo "\nDuplicate cleanup completed!\n";
