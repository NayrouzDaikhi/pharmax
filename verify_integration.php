#!/usr/bin/env php
<?php
/**
 * Vérification Intégration Produit & Article - PHARMAX
 * Récupère les articles et produits créés
 */

require 'vendor/autoload.php';
require 'config/bootstrap.php';

use App\Repository\ArticleRepository;
use App\Repository\ProduitRepository;
use App\Repository\CategorieRepository;
use App\Repository\CommentaireRepository;

// Bootstrap Symfony container
$kernel = new \App\Kernel($_ENV['APP_ENV'] ?? 'dev', $_ENV['APP_DEBUG'] ?? false);
$kernel->boot();
$container = $kernel->getContainer();

// Get repositories
$articleRepo = $container->get(ArticleRepository::class);
$produitRepo = $container->get(ProduitRepository::class);
$categorieRepo = $container->get(CategorieRepository::class);
$commentaireRepo = $container->get(CommentaireRepository::class);

echo "\n╔══════════════════════════════════════════════════════╗\n";
echo "║  PHARMAX - Vérification Intégration Complète         ║\n";
echo "║  Articles & Produits                                  ║\n";
echo "╚══════════════════════════════════════════════════════╝\n\n";

// 1. ARTICLES
echo "📰 ARTICLES DU BLOG:\n";
echo "─".str_repeat("─", 48)."─\n";
$articles = $articleRepo->findAll();
echo "Total: " . count($articles) . " article(s)\n\n";

if (count($articles) > 0) {
    foreach ($articles as $article) {
        echo "  ID: {$article->getId()}\n";
        echo "  Titre: {$article->getTitre()}\n";
        echo "  Likes: {$article->getLikes()}\n";
        echo "  Créé: " . $article->getDateCreation()->format('Y-m-d H:i:s') . "\n";
        echo "  Commentaires: " . count($article->getCommentaires()) . "\n";
        echo "  ---\n";
    }
} else {
    echo "  ℹ️  Aucun article trouvé\n";
}

// 2. PRODUITS
echo "\n💊 PRODUITS PHARMACEUTIQUES:\n";
echo "─".str_repeat("─", 48)."─\n";
$produits = $produitRepo->findAll();
echo "Total: " . count($produits) . " produit(s)\n\n";

if (count($produits) > 0) {
    foreach ($produits as $produit) {
        echo "  ID: {$produit->getId()}\n";
        echo "  Nom: {$produit->getNom()}\n";
        echo "  Prix: {$produit->getPrix()}€\n";
        echo "  Quantité: {$produit->getQuantite()}\n";
        echo "  Statut: " . ($produit->isStatut() ? "✅ En stock" : "❌ Rupture") . "\n";
        echo "  Catégorie: " . ($produit->getCategorie() ? $produit->getCategorie()->getNom() : "N/A") . "\n";
        echo "  Expiration: " . $produit->getDateExpiration()->format('Y-m-d') . "\n";
        echo "  Créé: " . $produit->getCreatedAt()->format('Y-m-d H:i:s') . "\n";
        echo "  ---\n";
    }
} else {
    echo "  ℹ️  Aucun produit trouvé\n";
}

// 3. CATÉGORIES
echo "\n📂 CATÉGORIES PRODUITS:\n";
echo "─".str_repeat("─", 48)."─\n";
$categories = $categorieRepo->findAll();
echo "Total: " . count($categories) . " catégorie(s)\n\n";

if (count($categories) > 0) {
    foreach ($categories as $cat) {
        echo "  ID: {$cat->getId()}\n";
        echo "  Nom: {$cat->getNom()}\n";
        echo "  Produits: " . count($cat->getProduits()) . "\n";
        echo "  Créé: " . $cat->getCreatedAt()->format('Y-m-d H:i:s') . "\n";
        echo "  ---\n";
    }
} else {
    echo "  ℹ️  Aucune catégorie trouvée\n";
}

// 4. COMMENTAIRES
echo "\n💬 COMMENTAIRES:\n";
echo "─".str_repeat("─", 48)."─\n";
$commentaires = $commentaireRepo->findAll();
echo "Total: " . count($commentaires) . " commentaire(s)\n";

if (count($commentaires) > 0) {
    $statuts = ['valide' => 0, 'en_attente' => 0, 'bloque' => 0];
    foreach ($commentaires as $com) {
        $status = $com->getStatut() ?? 'valide';
        if (isset($statuts[$status])) {
            $statuts[$status]++;
        }
    }
    echo "  Validés: {$statuts['valide']}\n";
    echo "  En attente: {$statuts['en_attente']}\n";
    echo "  Bloqués: {$statuts['bloque']}\n";
} else {
    echo "  ℹ️  Aucun commentaire trouvé\n";
}

// 5. STATISTIQUES DASHBOARD
echo "\n📊 STATISTIQUES DASHBOARD:\n";
echo "─".str_repeat("─", 48)."─\n";

$totalArticles = count($articles);
$totalLikes = array_sum(array_map(fn($a) => $a->getLikes(), $articles));
$totalProduits = count($produits);
$enStock = count(array_filter($produits, fn($p) => $p->isStatut()));
$prixMoyen = $totalProduits > 0 ? array_sum(array_map(fn($p) => $p->getPrix(), $produits)) / $totalProduits : 0;
$totalCommentaires = count($commentaires);

echo "  Articles: $totalArticles\n";
echo "  Total Likes: $totalLikes\n";
echo "  Produits: $totalProduits\n";
echo "  Produits en stock: $enStock\n";
echo "  Prix moyen: " . number_format($prixMoyen, 2, ',', ' ') . "€\n";
echo "  Commentaires: $totalCommentaires\n";

// 6. RÉSUMÉ INTÉGRATION
echo "\n✅ RÉSUMÉ INTÉGRATION:\n";
echo "─".str_repeat("─", 48)."─\n";

$pointsVerifs = [
    'Controllers en place' => file_exists('src/Controller/DashboardController.php') &&
                             file_exists('src/Controller/ProduitController.php') &&
                             file_exists('src/Controller/BlogController.php'),
    'Entités existantes' => file_exists('src/Entity/Produit.php') &&
                           file_exists('src/Entity/Article.php') &&
                           file_exists('src/Entity/Categorie.php'),
    'Repositories disponibles' => file_exists('src/Repository/ProduitRepository.php') &&
                                 file_exists('src/Repository/ArticleRepository.php'),
    'Templates frontend' => file_exists('templates/blog/products.html.twig') &&
                           file_exists('templates/blog/product_detail.html.twig'),
    'Templates admin' => file_exists('templates/produit/index.html.twig') &&
                        file_exists('templates/article/index.html.twig'),
    'Dashboard existe' => file_exists('templates/dashboard/index.html.twig'),
    'Données produits présentes' => $totalProduits > 0,
    'Données articles présentes' => $totalArticles > 0,
];

foreach ($pointsVerifs as $point => $check) {
    echo ($check ? "✅" : "❌") . " $point\n";
}

echo "\n" . str_repeat("═", 50) . "\n";
if (array_reduce($pointsVerifs, fn($carry, $item) => $carry && $item, true)) {
    echo "🎉 INTÉGRATION COMPLÈTE ET VALIDE!\n";
} else {
    echo "⚠️  Certains éléments manquent ou ne sont pas configurés\n";
}
echo str_repeat("═", 50) . "\n\n";

$kernel->shutdown();
