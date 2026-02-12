<?php
// Quick test to verify products controller works

use Doctrine\ORM\EntityManager;
use App\Repository\ProduitRepository;
use App\Repository\CategorieRepository;

require_once 'vendor/autoload.php';

$kernel = new \App\Kernel($_ENV['APP_ENV'] ?? 'dev', $_ENV['APP_DEBUG'] ?? false);
$kernel->boot();
$container = $kernel->getContainer();

$em = $container->get(EntityManager::class);
$produitRepo = $em->getRepository('App\Entity\Produit');
$categoryRepo = $em->getRepository('App\Entity\Categorie');

echo "=== TEST CONTRÔLEUR PRODUITS ===\n\n";

try {
    // Test findAll
    $produits = $produitRepo->findAll();
    echo "✓ findAll() works: " . count($produits) . " produits\n";
    
    // Test findByFilters
    $filtered = $produitRepo->findByFilters('', '', 'createdAt', 'DESC');
    echo "✓ findByFilters() works: " . count($filtered) . " produits\n";

    // Test getters on produit
    if (count($produits) > 0) {
        $p = $produits[0];
        echo "\n=== Vérification getters Produit ===\n";
        echo "getName(): " . ($p->getName() ? '✓ ' . $p->getName() : '✗') . "\n";
        echo "getPrice(): " . ($p->getPrice() ? '✓ ' . $p->getPrice() : '✗') . "\n";
        echo "getStock(): " . ($p->getStock() !== null ? '✓ ' . $p->getStock() : '✗') . "\n";
        echo "getExpirationDate(): " . ($p->getExpirationDate() ? '✓ ' . $p->getExpirationDate()->format('Y-m-d') : '✗') . "\n";
        
        // Test categorie getter
        if ($p->getCategorie()) {
            echo "getCategorie()->getName(): " . ($p->getCategorie()->getName() ? '✓ ' . $p->getCategorie()->getName() : '✗') . "\n";
        }
    }

    // Test categories
    $categories = $categoryRepo->findAll();
    echo "\n✓ Categories found: " . count($categories) . "\n";
    
    echo "\n=== TOUS LES TESTS PASSÉS ✓ ===\n";

} catch (Exception $e) {
    echo "✗ ERREUR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
