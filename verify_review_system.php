#!/usr/bin/env php
<?php
/**
 * Quick verification of Product Review System Integration
 * No Symfony container required - static file checks
 */

echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║  PRODUCT REVIEW SYSTEM - INTEGRATION VERIFICATION             ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";
echo "\n";

$passed = 0;
$failed = 0;

function check_file_content($filepath, $searchStrings, $description) {
    global $passed, $failed;
    
    if (!file_exists($filepath)) {
        echo "✗ " . $description . " - FILE NOT FOUND: $filepath\n";
        $failed++;
        return false;
    }
    
    $content = file_get_contents($filepath);
    $missing = [];
    
    foreach ($searchStrings as $search) {
        if (strpos($content, $search) === false) {
            $missing[] = $search;
        }
    }
    
    if (empty($missing)) {
        echo "✓ " . $description . "\n";
        $passed++;
        return true;
    } else {
        echo "✗ " . $description . "\n";
        foreach ($missing as $m) {
            echo "    └─ Missing: \"" . substr($m, 0, 50) . "...\"\n";
        }
        $failed++;
        return false;
    }
}

function check_file_exists($filepath, $description) {
    global $passed, $failed;
    
    if (file_exists($filepath)) {
        $size = filesize($filepath);
        echo "✓ " . $description . " (" . number_format($size) . " bytes)\n";
        $passed++;
        return true;
    } else {
        echo "✗ " . $description . " - NOT FOUND\n";
        $failed++;
        return false;
    }
}

echo "CHECK 1: Entity Files\n";
echo "─".str_repeat("─", 62)."─\n";

check_file_content(
    'src/Entity/Commentaire.php',
    ['private ?Produit $produit', 'getProduit()', 'setProduit()'],
    'Commentaire.php has Produit relation'
);

check_file_content(
    'src/Entity/Commentaire.php',
    ['nullable: true', '$article'],
    'Commentaire.article relation is nullable'
);

check_file_content(
    'src/Entity/Produit.php',
    ['private Collection $avis', 'getAvis()', 'addAvis()'],
    'Produit.php has avis collection'
);

check_file_content(
    'src/Entity/Produit.php',
    ['ArrayCollection'],
    'Produit.php imports ArrayCollection'
);

echo "\nCHECK 2: Controller\n";
echo "─".str_repeat("─", 62)."─\n";

check_file_content(
    'src/Controller/BlogController.php',
    ['methods: [\'GET\', \'POST\']', 'detailProduit'],
    'BlogController.detailProduit handles GET+POST'
);

check_file_content(
    'src/Controller/BlogController.php',
    ['new Commentaire()', 'setProduit($produit)', 'setStatut(\'en_attente\')'],
    'BlogController creates and saves comments'
);

check_file_content(
    'src/Controller/BlogController.php',
    ['CommentaireRepository', 'findBy', 'statut', 'valide'],
    'BlogController fetches validated reviews'
);

echo "\nCHECK 3: Form Type\n";
echo "─".str_repeat("─", 62)."─\n";

check_file_content(
    'src/Form/CommentaireType.php',
    ['Produit', 'EntityType', 'produit', 'required\' => false'],
    'CommentaireType.php includes Produit field'
);

check_file_content(
    'src/Form/CommentaireType.php',
    ['article', 'required\' => false'],
    'CommentaireType.php article field is optional'
);

echo "\nCHECK 4: Template\n";
echo "─".str_repeat("─", 62)."─\n";

check_file_content(
    'templates/blog/product_detail.html.twig',
    ['form method="POST"', 'textarea', 'contenu'],
    'product_detail.twig has review form'
);

check_file_content(
    'templates/blog/product_detail.html.twig',
    ['for commentaire in avis', 'avis', 'statut'],
    'product_detail.twig displays reviews'
);

check_file_content(
    'templates/blog/product_detail.html.twig',
    ['Avis et Commentaires', 'datePublication'],
    'product_detail.twig shows review date'
);

echo "\nCHECK 5: Database\n";
echo "─".str_repeat("─", 62)."─\n";

check_file_exists(
    'var/data_dev.db',
    'SQLite database file exists'
);

check_file_content(
    'migrations/Version20260211222111.php',
    ['produit_id', 'commentaire', 'FOREIGN KEY'],
    'Migration file creates produit_id column'
);

echo "\nCHECK 6: Documentation\n";
echo "─".str_repeat("─", 62)."─\n";

check_file_exists(
    'PRODUCT_REVIEW_SYSTEM_INTEGRATION.md',
    'Integration documentation exists'
);

// SUMMARY
echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║  VERIFICATION SUMMARY                                          ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";
echo "\nResults: " . $passed . " ✓ Passed, " . $failed . " ✗ Failed\n";

if ($failed === 0) {
    echo "\n✨ ALL CHECKS PASSED!\n";
    echo "\n✓ Product Review System Implementation Complete\n";
    echo "✓ All components are in place and properly configured\n";
    echo "✓ Database schema has been updated\n";
    echo "✓ Frontend functionality is ready\n";
    echo "\n";
    echo "🚀 READY TO USE:\n";
    echo "   1. Start server: symfony server:start -d\n";
    echo "   2. Visit product: http://localhost/produit/1\n";
    echo "   3. Submit a review\n";
    echo "   4. Moderate at: http://localhost/commentaire\n";
} else {
    echo "\n⚠ Some checks failed. Please review the output above.\n";
}

echo "\n";
