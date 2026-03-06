<?php

namespace App\DataFixtures;

use App\Entity\Categorie;
use App\Entity\Produit;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProduitFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Créer des catégories
        $categories = [];
        
        $cat1 = new Categorie();
        $cat1->setNom('Médicaments');
        $cat1->setDescription('Tous nos médicaments disponibles');
        $cat1->setCreatedAt(new \DateTime());
        $manager->persist($cat1);
        $categories[] = $cat1;
        
        $cat2 = new Categorie();
        $cat2->setNom('Vitamines');
        $cat2->setDescription('Vitamines et suppléments');
        $cat2->setCreatedAt(new \DateTime());
        $manager->persist($cat2);
        $categories[] = $cat2;
        
        $cat3 = new Categorie();
        $cat3->setNom('Hygiène');
        $cat3->setDescription('Produits d\'hygiène');
        $cat3->setCreatedAt(new \DateTime());
        $manager->persist($cat3);
        $categories[] = $cat3;
        
        // Créer des produits
        $produit1 = new Produit();
        $produit1->setNom('Paracétamol 500mg');
        $produit1->setDescription('Paracétamol 500mg - Efficace contre la fièvre et la douleur. Réduit les symptômes du rhume et de la grippe. Dosage recommandé: 1-2 comprimés toutes les 4-6 heures.');
        $produit1->setPrix(5.99);
        $produit1->setQuantite(100);
        $produit1->setStatut(true);
        $produit1->setDateExpiration(new \DateTime('2027-12-31'));
        $produit1->setCreatedAt(new \DateTime());
        $produit1->setCategorie($categories[0]);
        $manager->persist($produit1);
        
        $produit2 = new Produit();
        $produit2->setNom('Vitamine C 1000mg');
        $produit2->setDescription('Complément vitaminique C pour renforcer l\'immunité. Aide votre système immunitaire à combattre les infections. Dose quotidienne recommandée: 1 comprimé par jour.');
        $produit2->setPrix(12.50);
        $produit2->setQuantite(50);
        $produit2->setStatut(true);
        $produit2->setDateExpiration(new \DateTime('2026-06-30'));
        $produit2->setCreatedAt(new \DateTime());
        $produit2->setCategorie($categories[1]);
        $manager->persist($produit2);
        
        $produit3 = new Produit();
        $produit3->setNom('Savon Antibactérien');
        $produit3->setDescription('Savon antibactérien haute efficacité. Tue 99.9% des bactéries. Idéal pour le nettoyage quotidien des mains et du corps.');
        $produit3->setPrix(3.99);
        $produit3->setQuantite(200);
        $produit3->setStatut(true);
        $produit3->setDateExpiration(new \DateTime('2026-12-31'));
        $produit3->setCreatedAt(new \DateTime());
        $produit3->setCategorie($categories[2]);
        $manager->persist($produit3);
        
        $manager->flush();
    }
}
