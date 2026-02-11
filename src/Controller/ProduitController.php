<?php

namespace App\Controller;

use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/produits')]
class ProduitController extends AbstractController
{
    #[Route('/', name: 'app_produit_index')]
    public function index(ProduitRepository $produitRepository): Response
    {
        $produits = $produitRepository->findAll();

        return $this->render('frontend/produit/index.html.twig', [
            'produits' => $produits,
        ]);
    }

    #[Route('/{id}', name: 'app_produit_show')]
    public function show(int $id, ProduitRepository $produitRepository): Response
    {
        $produit = $produitRepository->find($id);

        if (!$produit) {
            throw $this->createNotFoundException('Produit non trouvé');
        }

        return $this->render('frontend/produit/show.html.twig', [
            'produit' => $produit,
        ]);
    }
}
