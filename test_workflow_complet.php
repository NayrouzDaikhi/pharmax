<?php

require_once 'vendor/autoload.php';

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;
use App\Repository\ProduitRepository;
use App\Entity\Commande;
use App\Entity\LigneCommande;
use Doctrine\ORM\EntityManagerInterface;

// Load Symfony container
$kernel = new \App\Kernel($_ENV['APP_ENV'] ?? 'dev', $_ENV['APP_DEBUG'] ?? true);
$kernel->boot();
$container = $kernel->getContainer();

echo "=== TEST WORKFLOW COMPLET ===\n\n";

// 1. Récupérer les produits
$produitRepo = $container->get('App\Repository\ProduitRepository');
$produits = $produitRepo->findAll();

echo "1. PRODUITS DISPONIBLES:\n";
if (empty($produits)) {
    echo "   ❌ Aucun produit trouvé! D'abord créer des produits.\n\n";
} else {
    foreach ($produits as $p) {
        echo "   - ID: {$p->getId()}, Nom: {$p->getNom()}, Prix: {$p->getPrix()}, Statut: " . ($p->isStatut() ? 'Actif' : 'Inactif') . "\n";
    }
    echo "\n";
}

// 2. Simuler l'ajout au panier
echo "2. SIMULATION AJOUT AU PANIER:\n";
$panier = [];
for ($i = 0; $i < min(2, count($produits)); $i++) {
    $produit = $produits[$i];
    if ($produit->isStatut()) { // Only if active
        if (isset($panier[$produit->getId()])) {
            $panier[$produit->getId()]['quantite']++;
        } else {
            $panier[$produit->getId()] = [
                'id' => $produit->getId(),
                'nom' => $produit->getNom(),
                'prix' => $produit->getPrix(),
                'image' => $produit->getImage(),
                'quantite' => 1,
            ];
        }
        echo "   ✓ Ajouté: {$produit->getNom()} (Prix: {$produit->getPrix()}, Quantité: 1)\n";
    }
}

if (empty($panier)) {
    echo "   ❌ Panier vide! Aucun produit actif.\n\n";
} else {
    echo "\n";
    
    // 3. Calculer le total
    echo "3. RÉSUMÉ PANIER:\n";
    $total = 0;
    foreach ($panier as $prodId => $item) {
        $sousTotal = $item['prix'] * $item['quantite'];
        $total += $sousTotal;
        echo "   - {$item['nom']}: {$item['prix']} TND × {$item['quantite']} = {$sousTotal} TND\n";
    }
    echo "   TOTAL: {$total} TND\n\n";
    
    // 4. Crée la commande
    echo "4. CRÉATION COMMANDE:\n";
    $em = $container->get('doctrine.orm.entity_manager');
    
    $commande = new Commande();
    $commande->setProduits($panier);
    $commande->setTotales($total);
    
    foreach ($panier as $item) {
        $ligne = new LigneCommande();
        $ligne->setNom($item['nom'])
              ->setPrix((float)$item['prix'])
              ->setQuantite((int)$item['quantite'])
              ->setSousTotal((float)($item['prix'] * $item['quantite']));
        
        $commande->addLigne($ligne);
    }
    
    try {
        $em->persist($commande);
        $em->flush();
        echo "   ✓ Commande créée avec succès!\n";
        echo "   ID: {$commande->getId()}\n";
        echo "   Totales: {$commande->getTotales()} TND\n";
        echo "   Statut: {$commande->getStatut()}\n";
        echo "   Lignes: " . count($commande->getLignes()) . "\n\n";
        
        // 5. Vérifier en BD
        echo "5. VÉRIFICATION BD:\n";
        $count = $em->getConnection()->executeQuery('SELECT COUNT(*) as count FROM commandes')->fetchAssociative()['count'];
        echo "   ✓ Total commandes en BD: {$count}\n";
        
        $lastCommande = $em->getRepository(Commande::class)->find($commande->getId());
        if ($lastCommande) {
            echo "   ✓ Commande récupérée: ID {$lastCommande->getId()}, Totales: {$lastCommande->getTotales()}\n";
        }
        
        // 6. Vérifier les lignes
        $lignesCount = $em->getConnection()->executeQuery('SELECT COUNT(*) as count FROM ligne_commandes WHERE commande_id = ?', [$commande->getId()])->fetchAssociative()['count'];
        echo "   ✓ Lignes créées: {$lignesCount}\n\n";
        
        // 7. Afficher le lien pour voir la facture
        echo "6. FACTURE URL:\n";
        echo "   http://127.0.0.1:8000/commandes/frontend/{$commande->getId()}\n";
        echo "   http://127.0.0.1:8000/commandes/{$commande->getId()}/pdf\n\n";
        
        echo "✅ WORKFLOW COMPLET RÉUSSI!\n";
        
    } catch (\Exception $e) {
        echo "   ❌ Erreur: " . $e->getMessage() . "\n\n";
    }
}

$kernel->shutdown();
