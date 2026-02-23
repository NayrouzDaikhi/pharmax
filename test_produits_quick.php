<?php
require_once dirname(__FILE__).'/config/bootstrap.php';

use App\Entity\Produit;
use Doctrine\ORM\EntityManager;

global $container;
$em = $container->get(EntityManager::class);
$produitRepo = $em->getRepository(Produit::class);

$allProduits = $produitRepo->findAll();

echo "=== Test des Produits ===\n\n";
echo "Total: " . count($allProduits) . " produits\n\n";

foreach ($allProduits as $p) {
    echo "ID: " . $p->getId() . "\n";
    echo "Nom: " . $p->getNom() . "\n";
    echo "Prix: " . $p->getPrix() . "DT\n";
    echo "Quantité: " . $p->getQuantite() . "\n";
    echo "Catégorie: " . ($p->getCategorie() ? $p->getCategorie()->getName() : "Aucune") . "\n";
    echo "Image: " . ($p->getImage() ? "✓" : "✗") . "\n";
    echo "---\n\n";
}

// Test findByFilters
echo "\n=== Test findByFilters ===\n";
$filtered = $produitRepo->findByFilters('', '', 'createdAt', 'DESC');
echo "Produits filtrés: " . count($filtered) . "\n";
