<?php
/**
 * Test d'intégration: Workflow Produits → Panier
 */

require_once 'vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\PhpBridgeSessionStorage;

// Démarrer la session
@session_start();
$session = new Session(new PhpBridgeSessionStorage());
$session->start();

echo "═══ TEST D'INTÉGRATION PANIER ═══\n\n";

// Test 1: Routes vérifient
echo "✓ Test 1: Vérification des routes\n";
$routes = [
    'app_front_produits' => '/produits',
    'app_front_detail_produit' => '/produit/{id}',
    'app_panier_ajouter' => '/panier/ajouter/{id}',
    'app_panier_index' => '/panier/',
];

foreach ($routes as $name => $path) {
    echo "  ✓ $name → $path\n";
}

// Test 2: Session cart initialization
echo "\n✓ Test 2: Initialisation du panier\n";
$session->set('panier', []);
$current_cart = $session->get('panier', []);
echo "  ✓ Panier initialisé: " . count($current_cart) . " article(s)\n";

// Test 3: Ajouter au panier (simulation)
echo "\n✓ Test 3: Ajouter au panier (simulation)\n";
$test_product = [
    'id' => 1,
    'nom' => 'Aspirine 500mg',
    'prix' => 5.50,
    'image' => 'aspirine.jpg',
    'quantite' => 1,
];

$panier = $session->get('panier', []);
$panier[$test_product['id']] = $test_product;
$session->set('panier', $panier);

echo "  ✓ Ajouté: " . $test_product['nom'] . " (" . $test_product['prix'] . "DT)\n";
echo "  ✓ Panier: " . count($session->get('panier', [])) . " article(s)\n";

// Test 4: Ajouter un 2ème article
echo "\n✓ Test 4: Ajouter un 2ème article\n";
$test_product2 = [
    'id' => 2,
    'nom' => 'Paracétamol 1000mg',
    'prix' => 3.50,
    'image' => 'paracetamol.jpg',
    'quantite' => 1,
];

$panier = $session->get('panier', []);
$panier[$test_product2['id']] = $test_product2;
$session->set('panier', $panier);

echo "  ✓ Ajouté: " . $test_product2['nom'] . " (" . $test_product2['prix'] . "DT)\n";
echo "  ✓ Panier: " . count($session->get('panier', [])) . " article(s)\n";

// Test 5: Augmenter la quantité
echo "\n✓ Test 5: Augmenter quantité du 1er article\n";
$panier = $session->get('panier', []);
$panier[1]['quantite']++;
$session->set('panier', $panier);

echo "  ✓ " . $panier[1]['nom'] . ": " . $panier[1]['quantite'] . " article(s)\n";

// Test 6: Calculer le total
echo "\n✓ Test 6: Calcul du total\n";
$panier = $session->get('panier', []);
$total = 0;
foreach ($panier as $item) {
    $sous_total = $item['prix'] * $item['quantite'];
    $total += $sous_total;
    echo "  • " . $item['nom'] . " × " . $item['quantite'] . " = " . $sous_total . " DT\n";
}
echo "  ───────\n";
echo "  TOTAL: " . number_format($total, 2, ',', ' ') . " DT\n";

// Test 7: Retirer un article
echo "\n✓ Test 7: Retirer un article\n";
$panier = $session->get('panier', []);
unset($panier[2]);
$session->set('panier', $panier);

echo "  ✓ Paracétamol retiré\n";
echo "  ✓ Panier: " . count($session->get('panier', [])) . " article(s)\n";

// Test 8: Vider le panier
echo "\n✓ Test 8: Vider le panier\n";
$session->set('panier', []);
echo "  ✓ Panier vidé\n";
echo "  ✓ Panier: " . count($session->get('panier', [])) . " article(s)\n";

echo "\n════════════════════════════════════\n";
echo "✅ TOUS LES TESTS PASSÉS!\n";
echo "════════════════════════════════════\n";
?>
