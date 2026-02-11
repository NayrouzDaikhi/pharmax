<?php

namespace App\Controller;

use App\Entity\Commentaire;
use App\Repository\ArticleRepository;
use App\Repository\CommentaireRepository;
use App\Service\GoogleTranslationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BlogController extends AbstractController
{
    #[Route('/', name: 'app_blog_index', methods: ['GET'])]
    public function index(Request $request, ArticleRepository $articleRepository): Response
    {
        if ($request->headers->get('accept') === 'application/json') {
            return $this->indexJson($request, $articleRepository);
        }

        $searchQuery = $request->query->get('search', '');
        $page = max(1, (int)$request->query->get('page', 1));
        $itemsPerPage = 3;
        
        $articles = $articleRepository->findAll();
        
        // Filter articles by search query
        if (!empty($searchQuery)) {
            $articles = array_filter($articles, function($article) use ($searchQuery) {
                $search = strtolower($searchQuery);
                return strpos(strtolower($article->getTitre()), $search) !== false ||
                       strpos(strtolower($article->getContenu()), $search) !== false;
            });
        }
        
        // Sort articles by newest first
        usort($articles, function($a, $b) {
            $dateA = $a->getDateCreation() ? $a->getDateCreation()->getTimestamp() : 0;
            $dateB = $b->getDateCreation() ? $b->getDateCreation()->getTimestamp() : 0;
            return $dateB <=> $dateA;
        });
        
        // Calculate pagination
        $totalArticles = count($articles);
        $totalPages = ceil($totalArticles / $itemsPerPage);
        $page = min($page, $totalPages);
        $page = max(1, $page);
        
        // Get articles for current page
        $startIndex = ($page - 1) * $itemsPerPage;
        $paginatedArticles = array_slice($articles, $startIndex, $itemsPerPage);
        
        return $this->render('blog/index.html.twig', [
            'articles' => $paginatedArticles,
            'searchQuery' => $searchQuery,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalArticles' => $totalArticles,
            'itemsPerPage' => $itemsPerPage,
        ]);
    }

    private function indexJson(Request $request, ArticleRepository $articleRepository)
    {
        $searchQuery = $request->query->get('search', '');
        $page = max(1, (int)$request->query->get('page', 1));
        $itemsPerPage = 3;
        
        $articles = $articleRepository->findAll();
        
        // Filter articles by search query
        if (!empty($searchQuery)) {
            $articles = array_filter($articles, function($article) use ($searchQuery) {
                $search = strtolower($searchQuery);
                return strpos(strtolower($article->getTitre()), $search) !== false ||
                       strpos(strtolower($article->getContenu()), $search) !== false;
            });
        }
        
        // Sort articles by newest first
        usort($articles, function($a, $b) {
            $dateA = $a->getDateCreation() ? $a->getDateCreation()->getTimestamp() : 0;
            $dateB = $b->getDateCreation() ? $b->getDateCreation()->getTimestamp() : 0;
            return $dateB <=> $dateA;
        });
        
        // Calculate pagination
        $totalArticles = count($articles);
        $totalPages = ceil($totalArticles / $itemsPerPage);
        $page = min($page, $totalPages);
        $page = max(1, $page);
        
        // Get articles for current page
        $startIndex = ($page - 1) * $itemsPerPage;
        $paginatedArticles = array_slice($articles, $startIndex, $itemsPerPage);
        
        $html = $this->renderView('blog/_articles_list.html.twig', [
            'articles' => $paginatedArticles,
        ]);
        
        return new JsonResponse([
            'html' => $html,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalArticles' => $totalArticles,
        ]);
    }

    #[Route('/blog/{id}', name: 'app_blog_show', methods: ['GET'])]
    public function show(int $id, ArticleRepository $articleRepository): Response
    {
        $article = $articleRepository->find($id);

        if (!$article) {
            throw $this->createNotFoundException('Article not found');
        }

        return $this->render('blog/show.html.twig', [
            'article' => $article,
        ]);
    }

    #[Route('/blog/{id}/translate', name: 'app_blog_translate', methods: ['POST'])]
    public function translate(
        int $id,
        ArticleRepository $articleRepository,
        GoogleTranslationService $translationService,
        EntityManagerInterface $entityManager
    ): Response
    {
        $article = $articleRepository->find($id);

        if (!$article) {
            throw $this->createNotFoundException('Article not found');
        }

        $translated = $translationService->translate(
            $article->getContenu(),
            'en'
        );

        if ($translated) {
            $article->setContenuEn($translated);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_blog_show', [
            'id' => $article->getId()
        ]);
    }

    #[Route('/blog/{id}/like', name: 'app_blog_like', methods: ['POST'])]
    public function likeArticle(int $id, ArticleRepository $articleRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $article = $articleRepository->find($id);

        if (!$article) {
            return new JsonResponse(['error' => 'Article not found'], 404);
        }

        $article->incrementLikes();
        $entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'likes' => $article->getLikes(),
        ]);
    }

    #[Route('/blog/{id}/unlike', name: 'app_blog_unlike', methods: ['POST'])]
    public function unlikeArticle(int $id, ArticleRepository $articleRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $article = $articleRepository->find($id);

        if (!$article) {
            return new JsonResponse(['error' => 'Article not found'], 404);
        }

        $article->decrementLikes();
        $entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'likes' => $article->getLikes(),
        ]);
    }
    public function createComment(int $id, Request $request, ArticleRepository $articleRepository, EntityManagerInterface $entityManager): Response
    {
        $article = $articleRepository->find($id);

        if (!$article) {
            throw $this->createNotFoundException('Article not found');
        }

        $contenu = $request->request->get('contenu', '');

        if (empty(trim($contenu))) {
            // Add error handling - redirect back with error
            return $this->redirectToRoute('app_blog_show', ['id' => $id], Response::HTTP_SEE_OTHER);
        }

        // Create new comment
        $commentaire = new Commentaire();
        $commentaire->setContenu($contenu);
        $commentaire->setArticle($article);
        $commentaire->setDatePublication(new \DateTime());
        $commentaire->setStatut('en_attente');

        $entityManager->persist($commentaire);
        $entityManager->flush();

        // Redirect back to the article page
        return $this->redirectToRoute('app_blog_show', ['id' => $id], Response::HTTP_SEE_OTHER);
    }
}
