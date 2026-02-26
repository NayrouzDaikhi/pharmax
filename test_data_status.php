<?php
$_SERVER['APP_ENV'] = 'dev';
$_SERVER['APP_DEBUG'] = 1;

$container = require __DIR__.'/config/bootstrap.php';
$em = $container->get('Doctrine\ORM\EntityManagerInterface');
$conn = $em->getConnection();

$cats = $conn->executeQuery('SELECT COUNT(*) as cnt FROM categorie')->fetchOne();
$prods = $conn->executeQuery('SELECT COUNT(*) as cnt FROM produit')->fetchOne();
$arts = $conn->executeQuery('SELECT COUNT(*) as cnt FROM article')->fetchOne();
$recl = $conn->executeQuery('SELECT COUNT(*) as cnt FROM reclamation')->fetchOne();
$comm = $conn->executeQuery('SELECT COUNT(*) as cnt FROM commentaire')->fetchOne();

echo "Database Status:\n";
echo "Categories: $cats\n";
echo "Products: $prods\n";
echo "Articles: $arts\n";
echo "Reclamations: $recl\n";
echo "Comments: $comm\n";

if ($prods > 0) {
    echo "\nSample Product:\n";
    $p = $conn->executeQuery('SELECT id, nom, prix FROM produit LIMIT 1')->fetchAssociative();
    echo "ID: " . $p['id'] . " | Name: " . $p['nom'] . " | Price: " . $p['prix'] . "\n";
}
?>
