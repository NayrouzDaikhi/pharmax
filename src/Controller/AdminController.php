<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Repository\ProduitRepository;
use App\Repository\CategorieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
final class AdminController extends AbstractController
{
    #[Route('', name: 'app_admin_index', methods: ['GET'])]
    public function index(ArticleRepository $articleRepository, ProduitRepository $produitRepository, CategorieRepository $categorieRepository): Response
    {
        $articles = $articleRepository->findAll();
        $produits = $produitRepository->findAll();
        $categories = $categorieRepository->findAll();

        $statsArticles = [
            'total' => count($articles),
            'published' => count(array_filter($articles, fn($a) => $a->getId() !== null)),
        ];

        $statsProduits = [
            'total' => count($produits),
            'enStock' => count(array_filter($produits, fn($p) => $p->isStatut())),
            'horsStock' => count(array_filter($produits, fn($p) => !$p->isStatut())),
            'prixMoyen' => count($produits) > 0 ? array_sum(array_map(fn($p) => $p->getPrix(), $produits)) / count($produits) : 0,
        ];

        return $this->render('admin/index.html.twig', [
            'articles' => $articles,
            'produits' => $produits,
            'categories' => $categories,
            'statsArticles' => $statsArticles,
            'statsProduits' => $statsProduits,
        ]);
    }
}
