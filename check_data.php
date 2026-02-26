#!/usr/bin/env php
<?php
// Simple data loader for Pharmax

use Doctrine\ORM\EntityManagerInterface;

require_once __DIR__.'/config/bootstrap.php';

$container = require_once __DIR__.'/config/bootstrap.php';

try {
    $em = $container->get(EntityManagerInterface::class);
    
    // Show current state
    $prodCount = $em->getConnection()->executeQuery('SELECT COUNT(*) as cnt FROM produit')->fetchOne();
    $artCount = $em->getConnection()->executeQuery('SELECT COUNT(*) as cnt FROM article')->fetchOne();
    $catCount = $em->getConnection()->executeQuery('SELECT COUNT(*) as cnt FROM categorie')->fetchOne();
    
    echo "Current state:\n";
    echo "- Products: $prodCount\n";
    echo "- Articles: $artCount\n";
    echo "- Categories: $catCount\n";
    
    if ($prodCount > 0 && $artCount > 0 && $catCount > 0) {
        echo "\n✓ Data is already imported!\n";
        
        // Show sample product
        $result = $em->getConnection()->executeQuery('SELECT id, nom, prix FROM produit LIMIT 1')->fetchAssociative();
        echo "\nSample product:\n";
        print_r($result);
    } else {
        echo "\n✗ Data import incomplete. Please run: php import_data.php\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
