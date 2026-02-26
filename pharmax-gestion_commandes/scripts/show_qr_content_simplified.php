<?php

require 'vendor/autoload.php';

use App\Entity\Commande;
use Symfony\Component\Dotenv\Dotenv;

// Load environment variables
if (file_exists('.env')) {
    (new Dotenv())->loadEnv('.env');
}

$env = $_SERVER['APP_ENV'] ?? 'dev';
$debug = $_SERVER['APP_DEBUG'] ?? false;

// Create a kernel
$kernel = new App\Kernel($env, (bool)$debug);
$kernel->boot();

$container = $kernel->getContainer();
$entityManager = $container->get('doctrine')->getManager();

// Get the latest commande
$commande = $entityManager->getRepository(Commande::class)->findOneBy([], ['id' => 'DESC']);

if (!$commande) {
    echo "Aucune commande trouvée.\n";
    exit(1);
}

// Show what's in the QR code
$qrData = sprintf(
    "PHARMAX COMMANDE\nID: #%d\nTotal: %.2f TND\nDate: %s\nStatut: %s\nClient: %s\nURL: https://pharmax.local/commandes/%d",
    $commande->getId(),
    $commande->getTotales(),
    $commande->getCreatedAt()?->format('d/m/Y H:i') ?? 'N/A',
    strtoupper($commande->getStatut()),
    $commande->getUtilisateur()?->getEmail() ?? 'Anonyme',
    $commande->getId()
);

echo "=== CONTENU DU QR CODE (Commande #" . $commande->getId() . ") ===\n\n";
echo $qrData;
echo "\n\n";
echo "=== TAILLE ===\n";
echo strlen($qrData) . " caractères\n";
echo "\nCe contenu QR est maintenant scanneable!\n";
