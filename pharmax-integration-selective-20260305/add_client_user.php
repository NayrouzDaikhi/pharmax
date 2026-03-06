<?php
// Script pour insérer un utilisateur client dans la base de données

use App\Entity\User;
use Symfony\Component\Dotenv\Dotenv;

require_once __DIR__.'/vendor/autoload.php';

// Load .env file
if (file_exists(__DIR__.'/.env')) {
    (new Dotenv())->usePutenv()->load(__DIR__.'/.env');
}

// Use SymfonyKernel
require_once __DIR__.'/src/Kernel.php';
use App\Kernel;

$kernel = new Kernel($_ENV['APP_ENV'] ?? 'dev', (bool) ($_ENV['APP_DEBUG'] ?? false));
$kernel->boot();
$entityManager = $kernel->getContainer()->get('doctrine.orm.default_entity_manager');

// Créer un nouvel utilisateur
$user = new User();
$user->setEmail('daikhinayrouz31@gmail.com');
$user->setFirstName('Nayrouz');
$user->setLastName('Daikhi');
$user->setRoles(['ROLE_USER']); // Rôle client
$user->setStatus(User::STATUS_UNBLOCKED);

// Générer un mot de passe temporaire et le hasher
$plainPassword = 'Pharmax2026!Nayrouz'; // Mot de passe initial
$hashedPassword = password_hash($plainPassword, PASSWORD_BCRYPT);
$user->setPassword($hashedPassword);

$user->setCreatedAt(new \DateTime());

try {
    // Persister et flush l'utilisateur
    $entityManager->persist($user);
    $entityManager->flush();
    
    echo "✅ Utilisateur créé avec succès!\n\n";
    echo "=== COORDONNÉES DE CONNEXION ===\n";
    echo "Email: daikhinayrouz31@gmail.com\n";
    echo "Mot de passe: Pharmax2026!Nayrouz\n";
    echo "Rôle: Client (ROLE_USER)\n";
    echo "Statut: Actif\n";
    echo "ID Utilisateur: " . $user->getId() . "\n";
    echo "==============================\n";
    
} catch (\Exception $e) {
    echo "❌ Erreur lors de la création de l'utilisateur:\n";
    echo $e->getMessage() . "\n";
    exit(1);
}

$kernel->shutdown();
exit(0);
