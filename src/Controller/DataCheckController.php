<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Repository\CategorieRepository;
use App\Repository\CommentaireRepository;
use App\Repository\ProduitRepository;
use App\Repository\ReclamationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class DataCheckController extends AbstractController
{
    #[Route('/api/data-check')]
    public function dataCheck(
        ArticleRepository $articleRepository,
        ProduitRepository $produitRepository,
        CategorieRepository $categorieRepository,
        ReclamationRepository $reclamationRepository,
        CommentaireRepository $commentaireRepository,
    ): JsonResponse {
        return new JsonResponse([
            'status' => 'ok',
            'data' => [
                'articles' => $articleRepository->count([]),
                'products' => $produitRepository->count([]),
                'categories' => $categorieRepository->count([]),
                'reclamations' => $reclamationRepository->count([]),
                'comments' => $commentaireRepository->count([]),
            ],
            'sample_products' => array_map(function ($p) {
                return [
                    'id' => $p->getId(),
                    'name' => $p->getNom(),
                    'price' => $p->getPrix(),
                    'category' => $p->getCategorie()?->getNom(),
                ];
            }, array_slice($produitRepository->findAll(), 0, 3)),
        ]);
    }
}
