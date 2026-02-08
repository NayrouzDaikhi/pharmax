<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use App\Repository\CommentaireRepository;
use App\Repository\CommentaireArchiveRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/article')]
final class ArticleController extends AbstractController
{
    #[Route(name: 'app_article_index', methods: ['GET'])]
    public function index(Request $request, ArticleRepository $articleRepository, CommentaireRepository $commentaireRepository, CommentaireArchiveRepository $archiveRepository): Response
    {
        $searchQuery = $request->query->get('search', '');
        $sortBy = $request->query->get('sort_by', 'date');
        $sortOrder = $request->query->get('sort_order', 'desc');
        $commentFilter = $request->query->get('comment_filter', '');
        
        // Validate sort parameters
        if (!in_array($sortBy, ['date'])) {
            $sortBy = 'date';
        }
        if (!in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }
        
        // Validate comment filter
        if (!in_array($commentFilter, ['', 'valide', 'en_attente', 'bloque'])) {
            $commentFilter = '';
        }
        
        $articles = $articleRepository->findAll();
        
        // Filter articles by search query
        if (!empty($searchQuery)) {
            $articles = array_filter($articles, function($article) use ($searchQuery) {
                $search = strtolower($searchQuery);
                return strpos(strtolower($article->getTitre()), $search) !== false ||
                       strpos(strtolower($article->getContenu()), $search) !== false;
            });
        }
        
        // Sort articles
        usort($articles, function($a, $b) use ($sortBy, $sortOrder) {
            $dateA = $a->getDateCreation() ? $a->getDateCreation()->getTimestamp() : 0;
            $dateB = $b->getDateCreation() ? $b->getDateCreation()->getTimestamp() : 0;
            
            if ($sortOrder === 'asc') {
                return $dateA <=> $dateB;
            } else {
                return $dateB <=> $dateA;
            }
        });
        
        $allCommentaires = $commentaireRepository->findAll();
        
        // Filter commentaires by status
        $commentaires = $allCommentaires;
        if (!empty($commentFilter)) {
            $commentaires = array_filter($commentaires, function($commentaire) use ($commentFilter) {
                return strtolower($commentaire->getStatut()) === $commentFilter;
            });
        }
        
        // Calculate statistics
        $totalArticles = count($articles);
        $totalCommentaires = count($allCommentaires);
        
        // Distribution by status
        $statsByStatus = [
            'valide' => 0,
            'bloque' => 0,
            'en_attente' => 0
        ];
        
        $commentsByDate = [];
        
        // Find article with most comments
        $maxComments = 0;
        $mostCommentedArticleId = null;
        foreach ($articles as $article) {
            $commentCount = count($article->getCommentaires());
            if ($commentCount > $maxComments) {
                $maxComments = $commentCount;
                $mostCommentedArticleId = $article->getId();
            }
        }
        
        foreach ($allCommentaires as $commentaire) {
            // Count by status (case-insensitive)
            $status = strtolower($commentaire->getStatut());
            if (isset($statsByStatus[$status])) {
                $statsByStatus[$status]++;
            }
            
            // Count by date
            if ($commentaire->getDatePublication()) {
                $dateKey = $commentaire->getDatePublication()->format('Y-m-d');
                if (!isset($commentsByDate[$dateKey])) {
                    $commentsByDate[$dateKey] = 0;
                }
                $commentsByDate[$dateKey]++;
            }
        }
        
        // Sort dates
        ksort($commentsByDate);
        
        return $this->render('article/index.html.twig', [
            'articles' => $articles,
            'commentaires' => $commentaires,
            'archived_commentaires' => $archiveRepository->findAll(),
            'totalArticles' => $totalArticles,
            'totalCommentaires' => $totalCommentaires,
            'statsByStatus' => $statsByStatus,
            'commentsByDate' => $commentsByDate,
            'mostCommentedArticleId' => $mostCommentedArticleId,
            'searchQuery' => $searchQuery,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
            'commentFilter' => $commentFilter,
        ]);
    }

    #[Route('/new', name: 'app_article_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();

                $imageFile->move(
                    $this->getParameter('kernel.project_dir') . '/public/uploads/images',
                    $newFilename
                );

                $article->setImage($newFilename);
            }

            // Set dates automatically
            $article->setDateCreation(new \DateTime('now'));
            $article->setDateModification(new \DateTime('now'));

            $entityManager->persist($article);
            $entityManager->flush();

            return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('article/new.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_article_show', methods: ['GET'])]
    public function show(Article $article): Response
    {
        return $this->render('article/show.html.twig', [
            'article' => $article,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_article_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();

                $imageFile->move(
                    $this->getParameter('kernel.project_dir') . '/public/uploads/images',
                    $newFilename
                );

                $article->setImage($newFilename);
            }

            // Update modification date
            $article->setDateModification(new \DateTime('now'));

            $entityManager->flush();

            return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('article/edit.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_article_delete', methods: ['POST'])]
    public function delete(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $article->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($article);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
    }
}
