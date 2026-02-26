<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Repository\ProduitRepository;
use App\Repository\CommentaireRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DataListController extends AbstractController
{
    #[Route('/data-lists', name: 'app_data_lists', methods: ['GET'])]
    public function index(
        ArticleRepository $articleRepository,
        ProduitRepository $produitRepository,
        CommentaireRepository $commentaireRepository
    ): Response {
        $articles = $articleRepository->findAll();
        $produits = $produitRepository->findAll();
        $commentaires = $commentaireRepository->findAll();

        return $this->render('data_lists/index.html.twig', [
            'articles' => $articles,
            'produits' => $produits,
            'commentaires' => $commentaires,
        ]);
    }
}
