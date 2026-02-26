<?php

require 'vendor/autoload.php';

use App\Service\CommandeQrCodeService;
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

// Create the service directly
$qrCodeService = new CommandeQrCodeService();

// Get the latest commande
$commande = $entityManager->getRepository(\App\Entity\Commande::class)->findOneBy([], ['id' => 'DESC']);

if (!$commande) {
    echo "No commande found.\n";
    exit(1);
}

// Prepare the data that goes into the QR code
$lignes = [];
$totalQuantite = 0;

foreach ($commande->getLignes() as $ligne) {
    $lignes[] = [
        'produit' => $ligne->getNom(),
        'prix_unitaire' => number_format($ligne->getPrix(), 2, '.', ''),
        'quantite' => $ligne->getQuantite(),
        'sous_total' => number_format($ligne->getSousTotal(), 2, '.', ''),
    ];
    $totalQuantite += $ligne->getQuantite();
}

$qrData = [
    'PHARMAX - FACTURE/COMMANDE' => [
        'numero_commande' => '#' . str_pad($commande->getId(), 6, '0', STR_PAD_LEFT),
        'date' => $commande->getCreatedAt()?->format('d/m/Y H:i:s') ?? 'N/A',
        'statut' => strtoupper($commande->getStatut()),
    ],
    'CLIENT' => [
        'email' => $commande->getUtilisateur()?->getEmail() ?? 'Client Anonyme',
    ],
    'PRODUITS' => [
        'nombre_articles' => count($lignes),
        'quantite_totale' => $totalQuantite,
        'articles' => $lignes,
    ],
    'MONTANTS' => [
        'total_ht' => number_format($commande->getTotales(), 2, '.', ''),
        'devise' => 'TND',
        'total_ttc' => number_format($commande->getTotales(), 2, '.', ''),
    ],
    'INFORMATIONS' => [
        'reference' => 'CMD-' . str_pad($commande->getId(), 6, '0', STR_PAD_LEFT) . '-' . $commande->getCreatedAt()?->format('Ymd'),
        'validation_date' => date('d/m/Y'),
    ],
];

echo "=== CONTENU DU QR CODE POUR COMMANDE #" . $commande->getId() . " ===\n\n";
echo json_encode($qrData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
echo "\n\n=== FIN CONTENU QR CODE ===\n";
