<?php
// Test Doctrine persistence identical to PanierController flow
require 'vendor/autoload.php';
require 'config/bootstrap.php';

use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->loadEnv('.env');

use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Entity\Livraison;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\StringInput;

// Get container and EntityManager
$app = require 'config/bootstrap.php';
$kernel = new App\Kernel($_ENV['APP_ENV'], $_ENV['APP_DEBUG']);
$kernel->boot();
$container = $kernel->getContainer();
$em = $container->get('doctrine.orm.entity_manager');
$userRepo = $em->getRepository('App:User');

// Get user ID 3
$user = $userRepo->find(3);
if (!$user) {
    echo "❌ User 3 not found\n";
    exit(1);
}

echo "✓ User found: " . $user->getEmail() . "\n";

// Create Commande exactly like PanierController does
$commande = new Commande();
$commande->setUtilisateur($user);

// Add a ligne commande
$ligne = new LigneCommande();
$ligne->setNom('Test Product')
    ->setPrix(99.99)
    ->setQuantite(1)
    ->setSousTotal(99.99);

$commande->addLigne($ligne);
$commande->setProduits([
    [
        'id' => 1,
        'nom' => 'Test Product',
        'prix' => 99.99,
        'quantite' => 1
    ]
]);
$commande->setTotales(99.99);
$commande->setStatut('en_attente');

// Create Livraison
$livraison = new Livraison();
$livraison->setFirstName($user->getFirstName() ?? 'Test')
    ->setLastName($user->getLastName() ?? 'User')
    ->setEmail($user->getEmail())
    ->setAdresse('Test Address')
    ->setTel('1234567890')
    ->setCommande($commande);

// Try to persist
echo "\n=== Attempting to persist ===\n";
try {
    $em->persist($commande);
    $em->persist($livraison);
    
    echo "Before flush:\n";
    echo "- Commande ID: " . ($commande->getId() ?? 'NULL') . "\n";
    
    // This is the critical point
    $em->flush();
    
    echo "After flush:\n";
    echo "- Commande ID: " . ($commande->getId() ?? 'NULL') . "\n";
    echo "✓ Flush successful!\n";
    
    // Verify in database
    $commandeId = $commande->getId();
    $pdo = new PDO(
        'mysql:host=127.0.0.1;dbname=pharm',
        'root',
        ''
    );
    $result = $pdo->query("SELECT id FROM commandes WHERE id = $commandeId");
    $row = $result->fetch(PDO::FETCH_ASSOC);
    
    if ($row) {
        echo "✓ Database verification: Command $commandeId found!\n";
    } else {
        echo "❌ Database verification: Command $commandeId NOT found!\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Class: " . get_class($e) . "\n";
    if (method_exists($e, 'getPrevious')) {
        $prev = $e->getPrevious();
        if ($prev) {
            echo "Previous: " . $prev->getMessage() . "\n";
        }
    }
}
