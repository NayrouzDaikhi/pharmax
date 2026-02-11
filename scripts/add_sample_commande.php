<?php

use App\Kernel;
use App\Entity\User;
use App\Entity\Commande;

$loader = require __DIR__ . '/../vendor/autoload.php';
// Load environment variables for CLI scripts
if (file_exists(__DIR__ . '/../.env')) {
    (new Symfony\Component\Dotenv\Dotenv())->bootEnv(__DIR__ . '/../.env');
}

$kernel = new Kernel($_SERVER['APP_ENV'] ?? 'dev', (bool)($_SERVER['APP_DEBUG'] ?? true));
$kernel->boot();
$container = $kernel->getContainer();
$em = $container->get('doctrine')->getManager();

// create or find user
$userRepo = $em->getRepository(User::class);
$user = $userRepo->findOneBy(['email' => 'demo2@example.com']);
if (!$user) {
    $user = new User();
    $user->setEmail('demo2@example.com');
    $user->setPassword('demo');
    $em->persist($user);
    $em->flush();
    echo "Created user id: " . $user->getId() . "\n";
}

// create commande
$commande = new Commande();
$commande->setUtilisateur($user);
$commande->setProduits(["Sample product 1", "Sample product 2"]);
$commande->setTotales(49.95);
$commande->setStatut('en_cours');
$commande->setCreatedAt(new \DateTime('2026-02-10 17:00:00'));

$em->persist($commande);
$em->flush();

echo "Created commande id: " . $commande->getId() . " linked to user id: " . $user->getId() . "\n";
