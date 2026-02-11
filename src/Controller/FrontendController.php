<?php

namespace App\Controller;

use App\Repository\CommandeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('')]
class FrontendController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('frontend/home.html.twig');
    }

    #[Route('/mes-commandes', name: 'app_frontend_commande_index')]
    public function myCommandes(CommandeRepository $commandeRepository): Response
    {
        // Si vous avez un système d'authentification, filtrer par utilisateur connecté
        // $commandes = $commandeRepository->findByUser($this->getUser());
        
        // Pour maintenant, afficher toutes les commandes
        $commandes = $commandeRepository->findAll();

        return $this->render('frontend/commande/index.html.twig', [
            'commandes' => $commandes,
        ]);
    }
}
