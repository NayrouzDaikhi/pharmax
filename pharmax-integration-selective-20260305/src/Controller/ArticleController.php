<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use App\Repository\CommentaireRepository;
use App\Repository\CommentaireArchiveRepository;
use App\Service\GoogleTranslationService;
use App\Service\ArticleStatisticsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/admin/article')]
final class ArticleController extends AbstractController
{
    public function __construct(
        private ArticleStatisticsService $statisticsService,
    ) {}

    #[Route(name: 'app_article_index', methods: ['GET'])]
    public function index(
        Request $request, 
        ArticleRepository $articleRepository,
        CommentaireRepository $commentaireRepository, 
        CommentaireArchiveRepository $archiveRepository,
        PaginatorInterface $paginator
    ): Response
    {
        $searchQuery = $request->query->get('search', '');
        $statusFilter = $request->query->get('status', '');
        $sortBy = $request->query->get('sort_by', 'date');
        $sortOrder = $request->query->get('sort_order', 'desc');
        $commentFilter = $request->query->get('comment_filter', '');
        $page = $request->query->getInt('page', 1);
        $perPage = $request->query->getInt('per_page', 20);
        
        // Validate sort parameters
        if (!in_array($sortBy, ['date', 'titre', 'likes', 'comments'])) {
            $sortBy = 'date';
        }
        if (!in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }
        
        // Validate status filter
        if (!in_array($statusFilter, ['', 'draft', 'published'])) {
            $statusFilter = '';
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

        // Filter articles by status (draft/published)
        if (!empty($statusFilter)) {
            $articles = array_filter($articles, function($article) use ($statusFilter) {
                if ($statusFilter === 'draft') {
                    return $article->isDraft();
                } elseif ($statusFilter === 'published') {
                    return !$article->isDraft();
                }
                return true;
            });
        }
        
        // Sort articles
        usort($articles, function($a, $b) use ($sortBy, $sortOrder) {
            $compareValue = 0;
            
            switch ($sortBy) {
                case 'titre':
                    $compareValue = strcasecmp($a->getTitre(), $b->getTitre());
                    break;
                case 'likes':
                    $compareValue = $a->getLikes() <=> $b->getLikes();
                    break;
                case 'comments':
                    $compareValue = count($a->getCommentaires()) <=> count($b->getCommentaires());
                    break;
                case 'date':
                default:
                    $dateA = $a->getDateCreation() ? $a->getDateCreation()->getTimestamp() : 0;
                    $dateB = $b->getDateCreation() ? $b->getDateCreation()->getTimestamp() : 0;
                    $compareValue = $dateA <=> $dateB;
                    break;
            }
            
            if ($sortOrder === 'asc') {
                return $compareValue;
            } else {
                return -$compareValue;
            }
        });

        // Get statistics from service
        $stats = $this->statisticsService->getDashboardStats();
        $commentsStatusChart = $this->statisticsService->getCommentsStatusChartData();
        $articlesDateChart = $this->statisticsService->getArticlesDateChartData();

        // Apply pagination to articles
        $pagination = $paginator->paginate(
            $articles,
            $page,
            $perPage
        );

        $allCommentaires = $commentaireRepository->findAll();
        
        // Separate article comments and product reviews
        $articleCommentaires = array_filter($allCommentaires, function($c) {
            return $c->getArticle() !== null; // Only article comments
        });
        
        $avis = array_filter($allCommentaires, function($c) {
            return $c->getProduit() !== null; // Only product reviews
        });
        
        // Get archived comments
        $allArchived = $archiveRepository->findAll();
        
        // Filter commentaires by status (only article comments)
        $commentaires = $articleCommentaires;
        if (!empty($commentFilter)) {
            $commentaires = array_filter($commentaires, function($commentaire) use ($commentFilter) {
                return strtolower($commentaire->getStatut()) === $commentFilter;
            });
        }
        
        // Calculate statistics
        $totalArticles = count($articles);
        $totalCommentaires = count($articleCommentaires) + count($allArchived);
        $totalAvis = count($avis);
        
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
        // Find article with most likes
        $maxLikes = 0;
        $mostLikedArticleId = null;
        
        foreach ($articles as $article) {
            $commentCount = count($article->getCommentaires());
            if ($commentCount > $maxComments) {
                $maxComments = $commentCount;
                $mostCommentedArticleId = $article->getId();
            }
            
            $likesCount = $article->getLikes();
            if ($likesCount > $maxLikes) {
                $maxLikes = $likesCount;
                $mostLikedArticleId = $article->getId();
            }
        }
        
        foreach ($articleCommentaires as $commentaire) {
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
        
        // Count archived comments as 'bloque'
        $statsByStatus['bloque'] += count($allArchived);
        
        // Add archived comments to date count
        foreach ($allArchived as $archive) {
            if ($archive->getArchivedAt()) {
                $dateKey = $archive->getArchivedAt()->format('Y-m-d');
                if (!isset($commentsByDate[$dateKey])) {
                    $commentsByDate[$dateKey] = 0;
                }
                $commentsByDate[$dateKey]++;
            }
        }
        
        // Sort dates
        ksort($commentsByDate);
        
        return $this->render('article/index.html.twig', [
            'articles' => $pagination,
            'commentaires' => $commentaires,
            'archived_commentaires' => $archiveRepository->findAllWithArticles(),
            'avis' => $avis,
            'totalArticles' => $totalArticles,
            'totalCommentaires' => $totalCommentaires,
            'totalAvis' => $totalAvis,
            'statsByStatus' => $statsByStatus,
            'commentsByDate' => $commentsByDate,
            'mostCommentedArticleId' => $mostCommentedArticleId,
            'mostLikedArticleId' => $mostLikedArticleId,
            'searchQuery' => $searchQuery,
            'statusFilter' => $statusFilter,
            'sortBy' => $sortBy,
            'stats' => $stats,
            'comments_status_chart' => $commentsStatusChart,
            'articles_date_chart' => $articlesDateChart,
            'page' => $page,
            'per_page' => $perPage,
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

            // Check which button was clicked
            if ($form->get('saveDraft')->isClicked()) {
                // Save as draft
                $article->saveDraft();
                $this->addFlash('success', 'Article sauvegardé comme brouillon!');
            } else {
                // Publish the article
                $article->publish();
                $this->addFlash('success', 'Article publié avec succès!');
            }

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
    public function show(Article $article, ArticleRepository $articleRepository): Response
    {
        // Get recommended/recent articles (excluding the current article)
        $recentArticles = $articleRepository->findBy([], ['date_creation' => 'DESC'], 5);
        $recommendedArticles = array_filter($recentArticles, function($a) use ($article) {
            return $a->getId() !== $article->getId();
        });
        // Limit to 4 articles for sidebar
        $recommendedArticles = array_slice($recommendedArticles, 0, 4);
        
        return $this->render('article/show.html.twig', [
            'article' => $article,
            'recommendedArticles' => $recommendedArticles,
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

    #[Route('/{id}/translate', name: 'app_article_translate', methods: ['POST'])]
    public function translate(
        Article $article,
        GoogleTranslationService $translationService,
        EntityManagerInterface $entityManager
    ): Response
    {
        $translated = $translationService->translate(
            $article->getContenu(),
            'en'
        );

        if ($translated) {
            $article->setContenuEn($translated);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_article_show', [
            'id' => $article->getId()
        ]);
    }

    #[Route('/{id}/toggle-publish', name: 'app_article_toggle_publish', methods: ['POST'])]
    public function togglePublish(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('toggle_publish' . $article->getId(), $request->getPayload()->getString('_token'))) {
            if ($article->isDraft()) {
                $article->publish();
                $this->addFlash('success', "L'article « " . $article->getTitre() . " » a été publié");
            } else {
                $article->saveDraft();
                $this->addFlash('info', "L'article « " . $article->getTitre() . " » a été enregistré comme brouillon");
            }
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_article_index');
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
