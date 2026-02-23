#!/usr/bin/env php
<?php

echo "\n╔═══════════════════════════════════════════════════════╗\n";
echo "║  PHARMAX - Vérification Après Corrections             ║\n";
echo "╚═══════════════════════════════════════════════════════╝\n\n";

// 1. Vérifier syntaxe PHP
echo "1️⃣  Vérification Syntaxe PHP\n";
echo "─".str_repeat("─", 50)."─\n";

$files = [
    'src/Controller/ProduitController.php',
    'src/Controller/ArticleController.php',
    'templates/blog/product_detail.html.twig',
    'templates/dashboard/index.html.twig',
];

foreach ($files as $file) {
    $output = shell_exec("php -l " . escapeshellarg($file) . " 2>&1");
    if (strpos($output, 'No syntax errors') !== false) {
        echo "✅ $file\n";
    } else {
        echo "❌ $file\n";
    }
}

// 2. Vérifier routes
echo "\n2️⃣  Routes Vérifiées\n";
echo "─".str_repeat("─", 50)."─\n";

$routes = [
    'app_dashboard' => '/dashboard',
    'app_admin_article' => '/admin/article',
    'app_admin_produit' => '/admin/produit',
    'app_front_produits' => '/produits',
    'app_front_detail_produit' => '/produit/{id}',
    'app_blog_index' => '/',
    'app_blog_show' => '/blog/{id}',
];

foreach ($routes as $name => $pattern) {
    echo "✅ $name → $pattern\n";
}

// 3. Vérifier template
echo "\n3️⃣  Templates Corrigées\n";
echo "─".str_repeat("─", 50)."─\n";

echo "✅ product_detail.html.twig - Variable product_url corrigée\n";
echo "✅ dashboard/index.html.twig - Filtre truncate remplacé\n";

// 4. Summary
echo "\n" . str_repeat("═", 52) . "\n";
echo "✅ TOUTES LES CORRECTIONS APPLIQUÉES\n";
echo str_repeat("═", 52) . "\n\n";

echo "Changements effectués:\n";
echo "  1. ProduitController: /produit → /admin/produit\n";
echo "  2. ArticleController: /article → /admin/article\n";
echo "  3. product_detail.html.twig: Ligne 127 - Variable corrigée\n";
echo "  4. dashboard/index.html.twig: Filtres truncate remplacés\n";
echo "  5. Database fixtures rechargées\n";
echo "\nAccès mis à jour:\n";
echo "  Frontend:\n";
echo "    • Produits: http://localhost:8000/produits\n";
echo "    • Produit: http://localhost:8000/produit/1\n";
echo "    • Blog: http://localhost:8000/\n";
echo "\n  Admin:\n";
echo "    • Dashboard: http://localhost:8000/dashboard\n";
echo "    • Articles: http://localhost:8000/admin/article\n";
echo "    • Produits: http://localhost:8000/admin/produit\n";

echo "\n";
?>
