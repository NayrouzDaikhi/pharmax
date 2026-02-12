<?php
// Test script to verify product review functionality

require_once 'vendor/autoload.php';
require_once 'config/bootstrap.php';

use App\Entity\Produit;
use App\Entity\Commentaire;
use Doctrine\ORM\EntityManager;

// Get entity manager
$em = $container->get('doctrine.orm.default_entity_manager');

// 1. Check if products exist
$produitRepo = $em->getRepository(Produit::class);
$produits = $produitRepo->findAll();

echo "=== PRODUCT REVIEW SYSTEM TEST ===\n\n";
echo "1. PRODUCTS FOUND: " . count($produits) . "\n";

if (count($produits) > 0) {
    foreach ($produits as $produit) {
        echo "\n   - Product: " . $produit->getNom();
        echo " (ID: " . $produit->getId() . ")\n";
        echo "     Stock: " . ($produit->isStatut() ? 'En Stock' : 'Indisponible') . "\n";
        echo "     Avis: " . count($produit->getAvis()) . "\n";
    }
}

// 2. Check if comments exist in database
$commentRepo = $em->getRepository(Commentaire::class);
$allComments = $commentRepo->findAll();
$productComments = $commentRepo->findBy(['produit' => null, 'article' => null], [], 10000);

echo "\n\n2. DATABASE COMMENTS STATUS:\n";
echo "   Total Comments: " . count($allComments) . "\n";
echo "   Article Comments: " . $commentRepo->count(['article' => null]) . "\n";

// 3. Check database schema
echo "\n\n3. DATABASE SCHEMA CHECK:\n";
try {
    $connection = $em->getConnection();
    $schemaManager = $connection->createSchemaManager();
    $tables = $schemaManager->listTableNames();
    $commentaireTable = $schemaManager->introspectTable('commentaire');
    
    echo "   commentaire table exists: YES\n";
    
    $columns = $commentaireTable->getColumns();
    echo "   Columns in commentaire table:\n";
    foreach ($columns as $column) {
        echo "     - " . $column->getName() . " (" . $column->getType() . ")\n";
    }
    
    echo "\n   Foreign Keys:\n";
    $fks = $commentaireTable->getForeignKeys();
    foreach ($fks as $fk) {
        echo "     - " . $fk->getLocalTableName() . "." . implode(", ", $fk->getLocalColumns()) 
             . " -> " . $fk->getForeignTableName() . "." . implode(", ", $fk->getForeignColumns()) . "\n";
    }
} catch (\Exception $e) {
    echo "   Error checking schema: " . $e->getMessage() . "\n";
}

echo "\n\n=== TEST COMPLETE ===\n";
