<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/bootstrap.php';

use App\Entity\Reclamation;
use Doctrine\ORM\EntityManagerInterface;

$kernel = new \App\Kernel($_ENV['APP_ENV'], (bool)$_ENV['APP_DEBUG']);
$kernel->boot();

$em = $kernel->getContainer()->get(EntityManagerInterface::class);

// Create test reclamation
$reclamation1 = new Reclamation();
$reclamation1->setTitre("Problème avec la livraison");
$reclamation1->setDescription("J'ai commandé un produit il y a 5 jours mais je n'ai toujours pas reçu ma commande. Veuillez vérifier le statut de ma livraison au plus tôt.");
$reclamation1->setStatut("En attente");

$reclamation2 = new Reclamation();
$reclamation2->setTitre("Produit endommagé");
$reclamation2->setDescription("Le produit que j'ai reçu est endommagé. L'emballage était troué et le contenu à l'intérieur ne fonctionne pas correctement.");
$reclamation2->setStatut("En cours");

$reclamation3 = new Reclamation();
$reclamation3->setTitre("Remboursement non traité");
$reclamation3->setDescription("J'ai demandé un remboursement il y a 2 semaines mais mon argent n'a pas été remboursé. Merci de vérifier.");
$reclamation3->setStatut("Resolu");

$em->persist($reclamation1);
$em->persist($reclamation2);
$em->persist($reclamation3);
$em->flush();

echo "✅ 3 réclamations de test créées avec succès:\n";
echo "- ID {$reclamation1->getId()}: {$reclamation1->getTitre()} (En attente)\n";
echo "- ID {$reclamation2->getId()}: {$reclamation2->getTitre()} (En cours)\n";
echo "- ID {$reclamation3->getId()}: {$reclamation3->getTitre()} (Resolu)\n";
?>
