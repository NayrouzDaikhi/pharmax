<?php
// Final verification script for Pharmax data import
// Usage: php verify_import.php

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║     PHARMAX DATABASE IMPORT - FINAL VERIFICATION         ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

$_SERVER['APP_ENV'] = 'dev';
$_SERVER['APP_DEBUG'] = 1;

try {
    // Load Symfony container
    $container = require __DIR__ . '/config/bootstrap.php';
    $em = $container->get('Doctrine\ORM\EntityManagerInterface');
    $conn = $em->getConnection();
    
    echo "✓ Database connection established\n\n";
    
    // Test 1: Check table existence
    echo "▶ TESTING TABLE EXISTENCE:\n";
    $tables = ['categorie', 'produit', 'article', 'reclamation', 'reponse', 'commentaire'];
    foreach ($tables as $table) {
        try {
            $result = $conn->executeQuery("SELECT COUNT(*) as cnt FROM $table")->fetchOne();
            echo "  ✓ $table: $result records\n";
        } catch (Exception $e) {
            echo "  ✗ $table: ERROR\n";
        }
    }
    
    // Test 2: Verify categories
    echo "\n▶ CATEGORIES IMPORTED:\n";
    $categories = $conn->executeQuery("SELECT id, nom FROM categorie ORDER BY id")->fetchAllAssociative();
    foreach ($categories as $cat) {
        echo "  {$cat['id']}. {$cat['nom']}\n";
    }
    
    // Test 3: Verify products
    echo "\n▶ PRODUCTS IMPORTED:\n";
    $products = $conn->executeQuery("SELECT id, nom, prix FROM produit ORDER BY id")->fetchAllAssociative();
    foreach ($products as $prod) {
        echo "  {$prod['id']}. {$prod['nom']} - {$prod['prix']} DTN\n";
    }
    
    // Test 4: Verify articles
    echo "\n▶ ARTICLES IMPORTED:\n";
    $articles = $conn->executeQuery("SELECT id, titre FROM article ORDER BY id")->fetchAllAssociative();
    foreach ($articles as $art) {
        echo "  {$art['id']}. {$art['titre']}\n";
    }
    
    // Test 5: Verify reclamations
    echo "\n▶ RECLAMATIONS IMPORTED:\n";
    $reclamations = $conn->executeQuery("SELECT id, titre, statut FROM reclamation ORDER BY id")->fetchAllAssociative();
    foreach ($reclamations as $recl) {
        echo "  {$recl['id']}. {$recl['titre']} [Status: {$recl['statut']}]\n";
    }
    
    // Test 6: Verify comments
    echo "\n▶ COMMENTS IMPORTED:\n";
    $comments = $conn->executeQuery("
        SELECT c.id, c.contenu, p.nom as product_name
        FROM commentaire c
        LEFT JOIN produit p ON c.produit_id = p.id
        ORDER BY c.id
    ")->fetchAllAssociative();
    foreach ($comments as $comm) {
        $text = substr($comm['contenu'], 0, 50) . '...';
        echo "  {$comm['id']}. {$comm['product_name']}: {$text}\n";
    }
    
    // Test 7: Verify foreign key relationships
    echo "\n▶ RELATIONSHIP INTEGRITY CHECK:\n";
    
    $orphaned_products = $conn->executeQuery("
        SELECT COUNT(*) as cnt FROM produit 
        WHERE categorie_id IS NOT NULL 
        AND categorie_id NOT IN (SELECT id FROM categorie)
    ")->fetchOne();
    echo "  ✓ Orphaned products: $orphaned_products (should be 0)\n";
    
    $orphaned_responses = $conn->executeQuery("
        SELECT COUNT(*) as cnt FROM reponse 
        WHERE reclamation_id NOT IN (SELECT id FROM reclamation)
    ")->fetchOne();
    echo "  ✓ Orphaned responses: $orphaned_responses (should be 0)\n";
    
    $orphaned_comments = $conn->executeQuery("
        SELECT COUNT(*) as cnt FROM commentaire 
        WHERE produit_id IS NOT NULL 
        AND produit_id NOT IN (SELECT id FROM produit)
    ")->fetchOne();
    echo "  ✓ Orphaned comments: $orphaned_comments (should be 0)\n";
    
    // Summary
    echo "\n╔════════════════════════════════════════════════════════════╗\n";
    echo "║                    VERIFICATION COMPLETE                 ║\n";
    echo "║                                                            ║\n";
    
    $cat_count = count($categories);
    $prod_count = count($products);
    $art_count = count($articles);
    $recl_count = count($reclamations);
    $comm_count = count($comments);
    
    echo "║  Categories:    $cat_count/4    ✓\n";
    echo "║  Products:      $prod_count/8    ✓\n";
    echo "║  Articles:      $art_count/3    ✓\n";
    echo "║  Reclamations:  $recl_count/3    ✓\n";
    echo "║  Comments:      $comm_count/4    ✓\n";
    echo "║                                                            ║\n";
    
    if ($cat_count == 4 && $prod_count == 8 && $art_count == 3 && $recl_count == 3 && $comm_count == 4) {
        echo "║       ✓ ALL DATA IMPORTED SUCCESSFULLY                   ║\n";
        echo "║         Database is ready for use!                      ║\n";
    } else {
        echo "║       ⚠ INCOMPLETE - Some data is missing               ║\n";
    }
    
    echo "╚════════════════════════════════════════════════════════════╝\n";
    
    exit(0);
    
} catch (Exception $e) {
    echo "╔════════════════════════════════════════════════════════════╗\n";
    echo "║                    VERIFICATION FAILED                   ║\n";
    echo "╚════════════════════════════════════════════════════════════╝\n\n";
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
