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

// Create the service directly (no need to get from container)
$qrCodeService = new CommandeQrCodeService();

// Get the latest commande
$commande = $entityManager->getRepository(\App\Entity\Commande::class)->findOneBy([], ['id' => 'DESC']);

if (!$commande) {
    echo "No commande found.\n";
    exit(1);
}

echo "Testing QR Code generation for Commande #" . $commande->getId() . "\n";

try {
    $qrCode = $qrCodeService->generateQrCodeDataUrl($commande);
    
    // Check if the data URL is valid
    if (strpos($qrCode, 'data:image/') === 0) {
        echo "✓ QR Code generated successfully!\n";
        echo "  Data URL length: " . strlen($qrCode) . " characters\n";
        echo "  Data URL prefix: " . substr($qrCode, 0, 50) . "...\n";
        echo "  Commande ID: " . $commande->getId() . "\n";
        echo "  Total: " . $commande->getTotales() . " TND\n";
        echo "  Status: " . $commande->getStatut() . "\n";
    } else {
        echo "✗ QR Code invalid format. Got: " . substr($qrCode, 0, 50) . "\n";
        echo "  Full length: " . strlen($qrCode) . "\n";
        exit(1);
    }
} catch (\Throwable $e) {
    echo "✗ Error generating QR Code: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n✓ All tests passed!\n";
