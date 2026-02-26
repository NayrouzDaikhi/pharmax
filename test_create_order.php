<?php
// test_create_order.php - Create a test order and verify it

use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Entity\Produit;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Dotenv\Dotenv;

// Load environment variables
$dotenv = new Dotenv();
$dotenv->loadEnv('.env');

require_once 'vendor/autoload.php';

use Symfony\Component\HttpKernel\Kernel;

// Create a simple kernel
class TestKernel extends Kernel
{
    public function registerBundles(): iterable
    {
        return [
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Symfony\Bundle\DoctrineBundle\DoctrineBundle(),
        ];
    }

    public function registerContainerConfiguration(\Symfony\Component\Config\Loader\LoaderInterface $loader)
    {
    }
}

$env = $_ENV['APP_ENV'] ?? 'dev';
$debug = $_ENV['APP_DEBUG'] ?? true;

// Use the real kernel
require_once 'src/Kernel.php';
$kernel = new \App\Kernel($env, $debug);
$kernel->boot();

$em = $kernel->getContainer()->get('doctrine.orm.entity_manager');
$produitRepo = $em->getRepository(\App\Entity\Produit::class);

echo "\n=== CRÉATION COMMANDE DE TEST ===\n\n";

// Get products
$produits = $produitRepo->findAll();
echo "1️⃣ Produits trouvés: " . count($produits) . "\n";

if (count($produits) >= 2) {
    // Create order
    $commande = new Commande();
    
    $total = 0;
    for ($i = 0; $i < min(2, count($produits)); $i++) {
        $p = $produits[$i];
        $qty = $i === 0 ? 2 : 1;  // First product: qty 2, second: qty 1
        $sousTotal = $p->getPrix() * $qty;
        $total += $sousTotal;
        
        $ligne = new LigneCommande();
        $ligne->setNom($p->getNom())
              ->setPrix($p->getPrix())
              ->setQuantite($qty)
              ->setSousTotal($sousTotal);
        
        echo "   - " . $p->getNom() . " x" . $qty . " = " . $sousTotal . " TND\n";
        
        $commande->addLigne($ligne);
    }
    
    // Set command properties
    $panier = [];
    foreach ($commande->getLignes() as $ligne) {
        $panier[] = [
            'nom' => $ligne->getNom(),
            'prix' => $ligne->getPrix(),
            'quantite' => $ligne->getQuantite(),
        ];
    }
    
    $commande->setProduits($panier);
    $commande->setTotales($total);
    $commande->setStatut('en_attente');
    
    echo "\n2️⃣ Création commande:\n";
    echo "   Total: " . $total . " TND\n";
    
    // Persist
    $em->persist($commande);
    $em->flush();
    
    echo "   ✅ Sauvegardé avec ID: " . $commande->getId() . "\n";
    
    // Verify
    echo "\n3️⃣ Vérification en BD:\n";
    $cmd = $em->find(\App\Entity\Commande::class, $commande->getId());
    if ($cmd) {
        echo "   ✅ Commande trouvée\n";
        echo "   ID: " . $cmd->getId() . "\n";
        echo "   Totales: " . $cmd->getTotales() . "\n";
        echo "   Lignes: " . count($cmd->getLignes()) . "\n";
        echo "   Statut: " . $cmd->getStatut() . "\n";
        
        echo "\n4️⃣ URLs pour tester:\n";
        echo "   Page produit: /produits/\n";
        echo "   Ajouter panier: /panier/ajouter/{id}\n";
        echo "   Voir panier: /panier/\n";
        echo "   Créer commande: POST /panier/commander\n";
        echo "   Voir commande: /commandes/frontend/" . $commande->getId() . "\n";
        echo "   PDF facture: /commandes/" . $commande->getId() . "/pdf\n";
        echo "   Admin: /admin/commandes\n";
    }
    
} else {
    echo "❌ Pas assez de produits\n";
}

echo "\n";
$kernel->shutdown();
