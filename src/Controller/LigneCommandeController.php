<?php

namespace App\Controller;

use App\Entity\LigneCommande;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LigneCommandeController extends AbstractController
{
    #[Route('/ligne-commande/create', name: 'ligne_commande_create')]
    public function create(EntityManagerInterface $em): Response
    {
        // récupérer l'utilisateur connecté
        $User = $this->getUser();

        if (!$User) {
            return new Response("Vous devez être connecté !");
        }

        // créer une nouvelle ligne de commande
        $ligne = new LigneCommande();
        $ligne->setQuantite(3);
        $ligne->setSousTotal(150.0);

        // persist + flush
        $em->persist($ligne);
        $em->flush();

        return new Response("Ligne de commande créée avec succès !");
    }
}
