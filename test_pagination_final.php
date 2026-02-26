<?php

/**
 * Test Final Pagination Implementation
 * Vérifie que la pagination est correctement implémentée
 */

echo "================================\n";
echo "Test Final Pagination Setup\n";
echo "================================\n\n";

$checks = [];

// 1. Vérifier que le template contient les bonnes variables
echo "[1/5] Vérification du template Twig...\n";
$templateFile = __DIR__ . '/templates/admin/reclamation/index.html.twig';
if (file_exists($templateFile)) {
    $templateContent = file_get_contents($templateFile);
    $hasVariables = strpos($templateContent, "{% set currentPage") !== false &&
                    strpos($templateContent, "{% set itemsPerPage") !== false &&
                    strpos($templateContent, "{% set totalItems") !== false &&
                    strpos($templateContent, "{% set totalPages = (totalItems / itemsPerPage)|ceil %}") !== false;
    
    $checks[1] = [
        'description' => 'Template contient variables Twig calculées',
        'passed' => $hasVariables
    ];
    echo "   " . ($hasVariables ? "✓" : "✗") . " Variables Twig définies\n";
} else {
    $checks[1] = ['description' => 'Template trouvé', 'passed' => false];
    echo "   ✗ Template non trouvé\n";
}
echo "\n";

// 2. Vérifier qu'il n'y a pas de références à lastPageNumber
echo "[2/5] Vérification des propriétés obsolètes...\n";
if (file_exists($templateFile)) {
    $templateContent = file_get_contents($templateFile);
    $noObsoleteProps = strpos($templateContent, "lastPageNumber") === false &&
                       strpos($templateContent, "hasPreviousPage") === false &&
                       strpos($templateContent, "hasNextPage") === false;
    
    $checks[2] = [
        'description' => 'Pas de propriétés obsolètes',
        'passed' => $noObsoleteProps
    ];
    echo "   " . ($noObsoleteProps ? "✓" : "✗") . " Aucune propriété obsolète\n";
} else {
    $checks[2] = ['description' => 'Propriétés vérifiées', 'passed' => false];
}
echo "\n";

// 3. Vérifier le contrôleur est bien modifié
echo "[3/5] Vérification du contrôleur...\n";
$controllerFile = __DIR__ . '/src/Controller/AdminReclamationController.php';
if (file_exists($controllerFile)) {
    $controllerContent = file_get_contents($controllerFile);
    $hasPaginator = strpos($controllerContent, "PaginatorInterface") !== false &&
                    strpos($controllerContent, "paginator->paginate") !== false;
    
    $checks[3] = [
        'description' => 'Contrôleur contient PaginatorInterface',
        'passed' => $hasPaginator
    ];
    echo "   " . ($hasPaginator ? "✓" : "✗") . " PaginatorInterface injecté et utilisé\n";
} else {
    $checks[3] = ['description' => 'Contrôleur trouvé', 'passed' => false];
    echo "   ✗ Contrôleur non trouvé\n";
}
echo "\n";

// 4. Vérifier la configuration KnpPaginator
echo "[4/5] Vérification de la configuration KnpPaginator...\n";
$configFile = __DIR__ . '/config/packages/knp_paginator.yaml';
if (file_exists($configFile)) {
    $configContent = file_get_contents($configFile);
    $hasConfig = strpos($configContent, "knp_paginator:") !== false &&
                 strpos($configContent, "page_range:") !== false &&
                 strpos($configContent, "template:") !== false;
    
    $checks[4] = [
        'description' => 'Configuration KnpPaginator valide',
        'passed' => $hasConfig
    ];
    echo "   " . ($hasConfig ? "✓" : "✗") . " Configuration présente\n";
} else {
    $checks[4] = ['description' => 'Configuration trouvée', 'passed' => false];
    echo "   ✗ Fichier de configuration non trouvé\n";
}
echo "\n";

// 5. Vérifier que bundle est installé
echo "[5/5] Vérification de l'installation du bundle...\n";
$composerFile = __DIR__ . '/composer.json';
if (file_exists($composerFile)) {
    $composer = json_decode(file_get_contents($composerFile), true);
    $hasPaginator = isset($composer['require']['knplabs/knp-paginator-bundle']);
    
    $checks[5] = [
        'description' => 'KnpPaginatorBundle dans composer.json',
        'passed' => $hasPaginator
    ];
    echo "   " . ($hasPaginator ? "✓" : "✗") . " knplabs/knp-paginator-bundle installé\n";
} else {
    $checks[5] = ['description' => 'Composer.json trouvé', 'passed' => false];
}
echo "\n";

// Résumé
echo "================================\n";
$totalPassed = array_sum(array_map(fn($c) => $c['passed'] ? 1 : 0, $checks));
echo "Résultats: $totalPassed/5 vérifications réussies\n";
echo "================================\n\n";

if ($totalPassed == 5) {
    echo "✓ Pagination est correctement implémentée!\n";
    echo "\nÉtapes suivantes:\n";
    echo "1. Accédez à: http://localhost:8000/admin/reclamation\n";
    echo "2. Vérifiez les contrôles de pagination au bas de la liste\n";
    echo "3. Testez les liens Précédent/Suivant\n";
    echo "4. Vérifiez que les filtres/recherche sont conservés lors du changement de page\n";
} else {
    echo "✗ Certaines vérifications ont échoué\n";
    echo "Vérifiez les résultats ci-dessus\n";
}
echo "\n";

exit($totalPassed == 5 ? 0 : 1);
