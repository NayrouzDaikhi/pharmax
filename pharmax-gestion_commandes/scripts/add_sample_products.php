<?php

use App\Kernel;
use App\Entity\Produit;
use App\Entity\Categorie;

$loader = require __DIR__ . '/../vendor/autoload.php';
// Load environment variables for CLI scripts
if (file_exists(__DIR__ . '/../.env')) {
    (new Symfony\Component\Dotenv\Dotenv())->bootEnv(__DIR__ . '/../.env');
}

$kernel = new Kernel($_SERVER['APP_ENV'] ?? 'dev', (bool)($_SERVER['APP_DEBUG'] ?? true));
$kernel->boot();
$container = $kernel->getContainer();
$em = $container->get('doctrine')->getManager();

// Create categories if they don't exist
$categoryRepo = $em->getRepository(Categorie::class);
$categories = [];

$categoryData = [
    ['Médicaments', 'Tous les médicaments et traitements pharmaceutiques'],
    ['Vitamines', 'Vitamines et nutriments essentiels'],
    ['Compléments Alimentaires', 'Compléments alimentaires et suppléments nutritionnels'],
    ['Hygiène', 'Produits d\'hygiène et de soins personnels'],
];

foreach ($categoryData as [$name, $desc]) {
    $cat = $categoryRepo->findOneBy(['nom' => $name]);
    if (!$cat) {
        $cat = new Categorie();
        $cat->setNom($name);
        $cat->setDescription($desc);
        $cat->setCreatedAt(new \DateTime());
        $em->persist($cat);
        $em->flush();
        echo "Created category: " . $name . " (id: " . $cat->getId() . ")\n";
    }
    $categories[] = $cat;
}

// Sample products
$products = [
    [
        'nom' => 'Paracétamol 500mg',
        'description' => 'Analgésique et antipyrétique efficace pour soulager la douleur et la fièvre. Boîte de 20 comprimés.',
        'prix' => 3.50,
        'categorie' => $categories[0], // Médicaments
        'dateExpiration' => new \DateTime('2027-06-30'),
    ],
    [
        'nom' => 'Ibuprofène 400mg',
        'description' => 'Anti-inflammatoire et analgésique pour les douleurs musculaires et articulaires. Boîte de 30 comprimés.',
        'prix' => 4.99,
        'categorie' => $categories[0], // Médicaments
        'dateExpiration' => new \DateTime('2027-08-15'),
    ],
    [
        'nom' => 'Vitamine C 1000mg',
        'description' => 'Complément de vitamine C pour renforcer le système immunitaire. Boîte de 60 comprimés.',
        'prix' => 8.99,
        'categorie' => $categories[1], // Vitamines
        'dateExpiration' => new \DateTime('2027-12-31'),
    ],
    [
        'nom' => 'Vitamine D3 2000 UI',
        'description' => 'Vitamine D3 pour la santé osseuse et immunitaire. Flacon de 30 ml.',
        'prix' => 12.99,
        'categorie' => $categories[1], // Vitamines
        'dateExpiration' => new \DateTime('2027-10-30'),
    ],
    [
        'nom' => 'Oméga-3 Fish Oil',
        'description' => 'Oméga-3 naturel pour la santé cardiaque et cérébrale. Boîte de 120 capsules.',
        'prix' => 15.99,
        'categorie' => $categories[2], // Compléments Alimentaires
        'dateExpiration' => new \DateTime('2027-07-15'),
    ],
    [
        'nom' => 'Gel Douche Corp Doux',
        'description' => 'Gel douche doux et hydratant pour tous les types de peau. Flacon de 250 ml.',
        'prix' => 2.99,
        'categorie' => $categories[3], // Hygiène
        'dateExpiration' => new \DateTime('2026-12-31'),
    ],
    [
        'nom' => 'Shampooing Réparateur',
        'description' => 'Shampooing réparateur pour cheveux endommagés et cassants. Flacon de 400 ml.',
        'prix' => 5.99,
        'categorie' => $categories[3], // Hygiène
        'dateExpiration' => new \DateTime('2026-11-30'),
    ],
    [
        'nom' => 'Probiotiques Multi-Souches',
        'description' => 'Probiotiques multi-souches pour la santé digestive. Boîte de 30 gélules.',
        'prix' => 18.99,
        'categorie' => $categories[2], // Compléments Alimentaires
        'dateExpiration' => new \DateTime('2027-09-30'),
    ],
    [
        'nom' => 'Savon Antibactérien',
        'description' => 'Savon antibactérien doux pour les mains et le corps. Savon de 100g.',
        'prix' => 1.50,
        'categorie' => $categories[3], // Hygiène
        'dateExpiration' => new \DateTime('2026-09-30'),
    ],
    [
        'nom' => 'Calcium + Vitamine D',
        'description' => 'Complément de calcium et vitamine D pour les os forts. Boîte de 90 comprimés.',
        'prix' => 9.99,
        'categorie' => $categories[1], // Vitamines
        'dateExpiration' => new \DateTime('2027-05-30'),
    ],
];

$produitRepo = $em->getRepository(Produit::class);

foreach ($products as $data) {
    $existing = $produitRepo->findOneBy(['nom' => $data['nom']]);
    if (!$existing) {
        $producto = new Produit();
        $producto->setNom($data['nom']);
        $producto->setDescription($data['description']);
        $producto->setPrix($data['prix']);
        $producto->setCategorie($data['categorie']);
        $producto->setDateExpiration($data['dateExpiration']);
        $producto->setStatut(true); // Actif
        $producto->setCreatedAt(new \DateTime());
        
        $em->persist($producto);
        $em->flush();
        echo "Created product: " . $data['nom'] . " (id: " . $producto->getId() . ", price: " . $data['prix'] . " DT)\n";
    } else {
        echo "Product already exists: " . $data['nom'] . "\n";
    }
}

echo "\nProducts data added successfully!\n";
