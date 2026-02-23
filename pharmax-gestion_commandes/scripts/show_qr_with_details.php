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
$qrData = "=== PHARMAX COMMANDE ===\n";
$qrData .= sprintf("ID: #%d\n", $commande->getId());
$qrData .= sprintf("Date: %s\n", $commande->getCreatedAt()?->format('d/m/Y H:i') ?? 'N/A');
$qrData .= sprintf("Client: %s\n", $commande->getUtilisateur()?->getEmail() ?? 'Anonyme');
$qrData .= "\n--- PRODUITS ---\n";

// Add product details from lignes
if ($commande->getLignes() && $commande->getLignes()->count() > 0) {
    foreach ($commande->getLignes() as $ligne) {
        $qrData .= sprintf(
            "%s | %.2f TND x%d = %.2f TND\n",
            $ligne->getNom(),
            $ligne->getPrix(),
            $ligne->getQuantite(),
            $ligne->getSousTotal()
        );
    }
}

$qrData .= "\n--- TOTAL ---\n";
$qrData .= sprintf("%.2f TND\n", $commande->getTotales());
$qrData .= sprintf("Statut: %s\n", strtoupper($commande->getStatut()));

echo "=== CONTENU DU QR CODE (Commande #" . $commande->getId() . ") ===\n\n";
echo $qrData;
echo "\n";
echo "=== TAILLE ===\n";
echo strlen($qrData) . " caractères\n";
echo "\nCe contenu QR inclut maintenant les détails complets comme dans la facture!\n";
