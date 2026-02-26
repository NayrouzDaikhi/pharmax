<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Repository\ProduitRepository;
use App\Repository\CommentaireRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard', methods: ['GET'])]
    public function index(ArticleRepository $articleRepository, ProduitRepository $produitRepository, CommentaireRepository $commentaireRepository): Response
    {
        $articles = $articleRepository->findAll();
        $produits = $produitRepository->findAll();
        $commentaires = $commentaireRepository->findAll();

        $statsArticles = [
            'total' => count($articles),
            'likes_total' => array_sum(array_map(fn($a) => $a->getLikes(), $articles)),
        ];

        $statsProduits = [
            'total' => count($produits),
            'en_stock' => count(array_filter($produits, fn($p) => $p->isStatut())),
            'prix_moyen' => count($produits) > 0 ? array_sum(array_map(fn($p) => $p->getPrix(), $produits)) / count($produits) : 0,
        ];

        $statsCommentaires = [
            'total' => count($commentaires),
        ];

        return $this->render('dashboard/index.html.twig', [
            'statsArticles' => $statsArticles,
            'statsProduits' => $statsProduits,
            'statsCommentaires' => $statsCommentaires,
            'articles' => array_slice($articles, 0, 5),
            'produits' => array_slice($produits, 0, 5),
        ]);
    }
}
