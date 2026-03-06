#!/usr/bin/env php
<?php
/**
 * Script d'Extraction Complète des Données
 * PHARMAX - Articles et Produits
 */

echo "\n╔════════════════════════════════════════════════════╗\n";
echo "║  PHARMAX - Extraction Complète des Données         ║\n";
echo "║  Articles & Produits Créés                         ║\n";
echo "╚════════════════════════════════════════════════════╝\n\n";

// Configuration
$dbPath = __DIR__ . '/var/data_dev.db';

if (!file_exists($dbPath)) {
    echo "⚠️  Base de données SQLite non trouvée à: $dbPath\n";
    echo "Essayons avec MySQL via Symfony...\n\n";
} else {
    echo "✅ Base de données trouvée: $dbPath\n\n";
}

// Essayons de se connecter directement via SQLite si disponible
try {
    $db = new PDO("sqlite:$dbPath");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "📰 EXTRACTION - ARTICLES\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    $stmt = $db->query('SELECT * FROM article ORDER BY date_creation DESC');
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($articles) > 0) {
        echo "Total d'articles: " . count($articles) . "\n\n";

        foreach ($articles as $idx => $article) {
            echo str_repeat("─", 50) . "\n";
            echo "Article #" . ($idx + 1) . "\n";
            echo str_repeat("─", 50) . "\n";
            echo "ID:              " . $article['id'] . "\n";
            echo "Titre:           " . $article['titre'] . "\n";
            echo "Likes:           " . $article['likes'] . "\n";
            echo "Créé:            " . $article['date_creation'] . "\n";
            echo "Modifié:         " . $article['date_modification'] . "\n";
            echo "Contenu (FR):    " . (strlen($article['contenu']) > 80 ?
                substr($article['contenu'], 0, 80) . "..." : $article['contenu']) . "\n";
            if (!empty($article['contenuEn'])) {
                echo "Contenu (EN):    " . (strlen($article['contenuEn']) > 80 ?
                    substr($article['contenuEn'], 0, 80) . "..." : $article['contenuEn']) . "\n";
            }
            echo "Image:           " . ($article['image'] ? "✅ " . $article['image'] : "❌ Pas d'image") . "\n";
            echo "\n";
        }
    } else {
        echo "⚠️  Aucun article trouvé\n\n";
    }

    // PRODUITS
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "💊 EXTRACTION - PRODUITS\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    $stmt = $db->query('SELECT * FROM produit ORDER BY created_at DESC');
    $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($produits) > 0) {
        echo "Total de produits: " . count($produits) . "\n\n";

        foreach ($produits as $idx => $produit) {
            echo str_repeat("─", 50) . "\n";
            echo "Produit #" . ($idx + 1) . "\n";
            echo str_repeat("─", 50) . "\n";
            echo "ID:              " . $produit['id'] . "\n";
            echo "Nom:             " . $produit['nom'] . "\n";
            echo "Prix:            " . $produit['prix'] . "€\n";
            echo "Quantité:        " . $produit['quantite'] . " unités\n";
            echo "Statut:          " . ($produit['statut'] ? "✅ En stock" : "❌ Rupture") . "\n";
            echo "Expiration:      " . $produit['dateExpiration'] . "\n";
            echo "Catégorie ID:    " . $produit['categorie_id'] . "\n";
            echo "Créé:            " . $produit['created_at'] . "\n";
            echo "Description:     " . (strlen($produit['description']) > 80 ?
                substr($produit['description'], 0, 80) . "..." : $produit['description']) . "\n";
            echo "Image:           " . ($produit['image'] ? "✅ " . $produit['image'] : "❌ Pas d'image") . "\n";
            echo "\n";
        }
    } else {
        echo "⚠️  Aucun produit trouvé\n\n";
    }

    // CATÉGORIES
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "📂 EXTRACTION - CATÉGORIES\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    $stmt = $db->query('SELECT * FROM categorie ORDER BY created_at DESC');
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($categories) > 0) {
        echo "Total de catégories: " . count($categories) . "\n\n";

        foreach ($categories as $idx => $cat) {
            echo str_repeat("─", 50) . "\n";
            echo "Catégorie #" . ($idx + 1) . "\n";
            echo str_repeat("─", 50) . "\n";
            echo "ID:              " . $cat['id'] . "\n";
            echo "Nom:             " . $cat['nom'] . "\n";
            echo "Description:     " . $cat['description'] . "\n";
            echo "Créé:            " . $cat['created_at'] . "\n";
            echo "Image:           " . ($cat['image'] ? "✅ " . $cat['image'] : "❌ Pas d'image") . "\n";
            echo "\n";
        }
    } else {
        echo "⚠️  Aucune catégorie trouvée\n\n";
    }

    // COMMENTAIRES
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "💬 EXTRACTION - COMMENTAIRES\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    $stmt = $db->query('SELECT * FROM commentaire ORDER BY datePublication DESC LIMIT 10');
    $commentaires = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($commentaires) > 0) {
        echo "Total de commentaires (affichage: 10 premiers): " . count($commentaires) . "\n\n";

        foreach ($commentaires as $idx => $com) {
            echo "Commentaire #" . ($idx + 1) . "\n";
            echo "  Article ID: " . $com['article_id'] . "\n";
            echo "  Statut: " . ($com['statut'] ?? 'valide') . "\n";
            echo "  Contenu: " . (strlen($com['contenu']) > 60 ?
                substr($com['contenu'], 0, 60) . "..." : $com['contenu']) . "\n";
            echo "\n";
        }

        // Total par statut
        $stmt = $db->query('SELECT statut, COUNT(*) as count FROM commentaire GROUP BY statut');
        $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Statistiques statuts:\n";
        foreach ($stats as $stat) {
            echo "  " . ($stat['statut'] ?? 'valide') . ": " . $stat['count'] . "\n";
        }
    } else {
        echo "⚠️  Aucun commentaire trouvé\n\n";
    }

    // STATISTIQUES GLOBALES
    echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "📊 STATISTIQUES GLOBALES\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    $totalArticles = count($articles);
    $totalProduits = count($produits);
    $totalCategories = count($categories);
    $totalCommentaires = count($commentaires);

    echo "Articles:       " . $totalArticles . "\n";
    echo "Produits:       " . $totalProduits . "\n";
    echo "Catégories:     " . $totalCategories . "\n";
    echo "Commentaires:   " . $totalCommentaires . "\n";

    if ($totalProduits > 0) {
        // Calculs
        $prixTotal = 0;
        $enStock = 0;
        foreach ($produits as $p) {
            $prixTotal += $p['prix'];
            if ($p['statut']) $enStock++;
        }
        $prixMoyen = $prixTotal / $totalProduits;

        echo "\n" . str_repeat("─", 50) . "\n";
        echo "Prix moyen des produits:     " . number_format($prixMoyen, 2, ',', ' ') . "€\n";
        echo "Produits en stock:           " . $enStock . "/" . $totalProduits . "\n";
        echo "Valeur stock totale:         " . number_format($prixTotal, 2, ',', ' ') . "€\n";
    }

    if ($totalArticles > 0) {
        $totalLikes = 0;
        foreach ($articles as $a) {
            $totalLikes += $a['likes'];
        }
        echo "\nTotal likes (articles):      " . $totalLikes . "\n";
        echo "Likes moyens par article:    " . number_format($totalLikes / $totalArticles, 1, ',', ' ') . "\n";
    }

    echo "\n" . str_repeat("═", 50) . "\n";
    echo "✅ EXTRACTION COMPLÈTE\n";
    echo str_repeat("═", 50) . "\n";

} catch (Exception $e) {
    echo "❌ Erreur Base de Données: " . $e->getMessage() . "\n";
    echo "\n💡 Note: Si vous utilisez MySQL, modifiez la connexion PDO dans ce script.\n";
}

echo "\n";
?>
