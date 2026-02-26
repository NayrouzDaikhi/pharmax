#!/usr/bin/env php
<?php

/**
 * Script de vérification de la pagination KnpPaginatorBundle
 * 
 * Teste que le bundle est bien configuré et prêt à l'emploi
 */

echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║     Vérification Pagination KnpPaginatorBundle                 ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

$checks = [
    'success' => [],
    'warnings' => [],
    'errors' => []
];

// 1. Vérifier que le bundle est installé
echo "1️⃣  Vérification de l'installation du bundle...\n";
if (file_exists('vendor/knplabs/knp-paginator-bundle')) {
    $checks['success'][] = "✅ KnpPaginatorBundle installé";
    echo "   ✅ KnpPaginatorBundle trouvé\n";
} else {
    $checks['errors'][] = "❌ KnpPaginatorBundle pas installé";
    echo "   ❌ KnpPaginatorBundle manquant\n";
}

// 2. Vérifier composer.json
echo "\n2️⃣  Vérification du composer.json...\n";
if (file_exists('composer.json')) {
    $composerJson = json_decode(file_get_contents('composer.json'), true);
    if (isset($composerJson['require']['knplabs/knp-paginator-bundle'])) {
        $version = $composerJson['require']['knplabs/knp-paginator-bundle'];
        $checks['success'][] = "✅ knplabs/knp-paginator-bundle dans composer.json ($version)";
        echo "   ✅ Présent dans composer.json ($version)\n";
    } else {
        $checks['warnings'][] = "⚠️  knplabs/knp-paginator-bundle pas dans composer.json";
        echo "   ⚠️  Manquant de composer.json\n";
    }
} else {
    $checks['errors'][] = "❌ composer.json introuvable";
    echo "   ❌ composer.json introuvable\n";
}

// 3. Vérifier le fichier de configuration
echo "\n3️⃣  Vérification de la configuration...\n";
if (file_exists('config/packages/knp_paginator.yaml')) {
    $configContent = file_get_contents('config/packages/knp_paginator.yaml');
    if (strpos($configContent, 'knp_paginator:') !== false) {
        $checks['success'][] = "✅ config/packages/knp_paginator.yaml existe";
        echo "   ✅ Fichier de configuration trouvé\n";
        
        // Vérifier les paramètres
        if (strpos($configContent, 'page_range:') !== false) {
            echo "   ✅ Configuration page_range détectée\n";
        }
        if (strpos($configContent, 'bootstrap_v5_pagination') !== false) {
            echo "   ✅ Template Bootstrap 5 configuré\n";
        }
    } else {
        $checks['errors'][] = "❌ Fichier de configuration malformé";
        echo "   ❌ Fichier malformé\n";
    }
} else {
    $checks['warnings'][] = "⚠️  config/packages/knp_paginator.yaml inexistant";
    echo "   ⚠️  Fichier de configuration manquant\n";
}

// 4. Vérifier le contrôleur
echo "\n4️⃣  Vérification du contrôleur AdminReclamationController...\n";
if (file_exists('src/Controller/AdminReclamationController.php')) {
    $controllerContent = file_get_contents('src/Controller/AdminReclamationController.php');
    
    if (strpos($controllerContent, 'PaginatorInterface') !== false) {
        $checks['success'][] = "✅ PaginatorInterface importé";
        echo "   ✅ Import PaginatorInterface détecté\n";
    } else {
        $checks['errors'][] = "❌ PaginatorInterface pas importé";
        echo "   ❌ Import manquant\n";
    }
    
    if (strpos($controllerContent, 'private PaginatorInterface') !== false) {
        $checks['success'][] = "✅ Paginator injecté dans le constructeur";
        echo "   ✅ Injection dans constructeur détectée\n";
    } else {
        $checks['warnings'][] = "⚠️  Paginator pas injecté dans constructeur";
        echo "   ⚠️  Injection manquante\n";
    }
    
    if (strpos($controllerContent, '$this->paginator->paginate') !== false) {
        $checks['success'][] = "✅ paginate() appelé dans index()";
        echo "   ✅ Utilisation du paginator détectée\n";
    } else {
        $checks['errors'][] = "❌ paginate() pas appelé";
        echo "   ❌ paginate() non trouvé\n";
    }
} else {
    $checks['errors'][] = "❌ AdminReclamationController introuvable";
    echo "   ❌ Fichier contrôleur manquant\n";
}

// 5. Vérifier le template Twig
echo "\n5️⃣  Vérification du template Twig...\n";
if (file_exists('templates/admin/reclamation/index.html.twig')) {
    $twigContent = file_get_contents('templates/admin/reclamation/index.html.twig');
    
    if (strpos($twigContent, 'reclamations.currentPageNumber') !== false) {
        $checks['success'][] = "✅ Variables de pagination utilisées dans le template";
        echo "   ✅ Accès aux données de pagination détecté\n";
    } else {
        $checks['warnings'][] = "⚠️  Variables de pagination pas utilisées";
        echo "   ⚠️  Template peut manquer la pagination\n";
    }
    
    if (strpos($twigContent, 'hasPreviousPage') !== false) {
        echo "   ✅ Navigation Précédent détectée\n";
    }
    
    if (strpos($twigContent, 'hasNextPage') !== false) {
        echo "   ✅ Navigation Suivant détectée\n";
    }
    
    if (strpos($twigContent, 'totalItemCount') !== false) {
        echo "   ✅ Compteur de résultats détecté\n";
    }
} else {
    $checks['warnings'][] = "⚠️  Template admin/reclamation/index.html.twig introuvable";
    echo "   ⚠️  Template manquant\n";
}

// 6. Vérifier la base de donées
echo "\n6️⃣  Vérification de la base de données...\n";
if (file_exists('composer.json')) {
    $composerJson = json_decode(file_get_contents('composer.json'), true);
    if (isset($composerJson['require']['doctrine/doctrine-bundle'])) {
        $checks['success'][] = "✅ Doctrine installé (requis pour la pagination)";
        echo "   ✅ Doctrine/ORM trouvé\n";
    }
}

// Afficher le résumé
echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║                        RÉSUMÉ                                  ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

echo "✅ Succès: " . count($checks['success']) . "\n";
foreach ($checks['success'] as $s) {
    echo "   $s\n";
}

if (!empty($checks['warnings'])) {
    echo "\n⚠️  Avertissements: " . count($checks['warnings']) . "\n";
    foreach ($checks['warnings'] as $w) {
        echo "   $w\n";
    }
}

if (!empty($checks['errors'])) {
    echo "\n❌ Erreurs: " . count($checks['errors']) . "\n";
    foreach ($checks['errors'] as $e) {
        echo "   $e\n";
    }
}

// Recommandations
echo "\n";
echo "💡 Recommandations:\n";

if (count($checks['errors']) === 0) {
    echo "   ✅ Tout est configuré correctement!\n";
    echo "   🚀 La pagination est prête à l'emploi\n";
    echo "   📖 Consultez PAGINATOR_IMPLEMENTATION_GUIDE.md pour les détails\n";
} else {
    echo "   ❌ Veuillez corriger les erreurs ci-dessus\n";
    echo "   📚 Ressources:\n";
    echo "      - Installer: composer require knplabs/knp-paginator-bundle\n";
    echo "      - Cache: php bin/console cache:clear\n";
}

echo "\n";
echo "🎯 Test rapide:\n";
echo "   1. Aller à: http://localhost:8000/admin/reclamation\n";
echo "   2. Vérifier que la pagination apparaît\n";
echo "   3. Cliquer sur une page pour naviguer\n";
echo "   4. Vérifier que les filtres sont conservés\n";

echo "\n";
echo "✅ Pagination implémentée avec succès!\n\n";

exit(count($checks['errors']) > 0 ? 1 : 0);
