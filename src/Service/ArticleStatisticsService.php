<?php

namespace App\Service;

use App\Repository\ArticleRepository;
use App\Repository\CommentaireRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service to provide statistical data for articles and comments
 * Used by Chart.js for statistics visualization
 */
class ArticleStatisticsService
{
    public function __construct(
        private ArticleRepository $articleRepository,
        private CommentaireRepository $commentaireRepository,
        private EntityManagerInterface $entityManager,
    ) {}

    /**
     * Get statistics for dashboard
     */
    public function getDashboardStats(): array
    {
        return [
            'total_articles' => $this->getTotalArticles(),
            'total_comments' => $this->getTotalComments(),
            'total_likes' => $this->getTotalLikes(),
            'comments_by_status' => $this->getCommentsByStatus(),
            'articles_by_date' => $this->getArticlesByDate(),
            'comments_by_date' => $this->getCommentsByDate(),
            'top_articles' => $this->getTopArticles(),
            'top_commented_articles' => $this->getTopCommentedArticles(),
        ];
    }

    /**
     * Get total number of articles
     */
    public function getTotalArticles(): int
    {
        return count($this->articleRepository->findAll());
    }

    /**
     * Get total number of comments
     */
    public function getTotalComments(): int
    {
        return count($this->commentaireRepository->findAll());
    }

    /**
     * Get total likes across all articles
     */
    public function getTotalLikes(): int
    {
        $query = $this->entityManager->createQuery(
            'SELECT SUM(a.likes) as total FROM App\Entity\Article a'
        );
        $result = $query->getOneOrNullResult();
        return $result['total'] ?? 0;
    }

    /**
     * Get comments breakdown by status
     */
    public function getCommentsByStatus(): array
    {
        $allComments = $this->commentaireRepository->findAll();
        
        $stats = [
            'valide' => 0,
            'en_attente' => 0,
            'bloque' => 0,
        ];

        foreach ($allComments as $comment) {
            $status = strtolower($comment->getStatut());
            if (isset($stats[$status])) {
                $stats[$status]++;
            }
        }

        return $stats;
    }

    /**
     * Get articles count by date (last 30 days)
     */
    public function getArticlesByDate(): array
    {
        $articles = $this->articleRepository->findAll();
        $stats = [];

        foreach ($articles as $article) {
            if ($article->getDateCreation()) {
                $date = $article->getDateCreation()->format('Y-m-d');
                $stats[$date] = ($stats[$date] ?? 0) + 1;
            }
        }

        krsort($stats);
        return array_slice($stats, 0, 30);
    }

    /**
     * Get comments count by date (last 30 days)
     */
    public function getCommentsByDate(): array
    {
        $comments = $this->commentaireRepository->findAll();
        $stats = [];

        foreach ($comments as $comment) {
            if ($comment->getDatePublication()) {
                $date = $comment->getDatePublication()->format('Y-m-d');
                $stats[$date] = ($stats[$date] ?? 0) + 1;
            }
        }

        krsort($stats);
        return array_slice($stats, 0, 30);
    }

    /**
     * Get top articles by likes
     */
    public function getTopArticles(int $limit = 5): array
    {
        $articles = $this->articleRepository->findAll();
        
        usort($articles, function($a, $b) {
            return $b->getLikes() <=> $a->getLikes();
        });

        return array_slice($articles, 0, $limit);
    }

    /**
     * Get top articles by comment count
     */
    public function getTopCommentedArticles(int $limit = 5): array
    {
        $articles = $this->articleRepository->findAll();
        
        usort($articles, function($a, $b) {
            return count($b->getCommentaires()) <=> count($a->getCommentaires());
        });

        return array_slice($articles, 0, $limit);
    }

    /**
     * Get chart data for comments by status
     */
    public function getCommentsStatusChartData(): array
    {
        $stats = $this->getCommentsByStatus();

        return [
            'labels' => ['Validés', 'En attente', 'Bloqués'],
            'data' => [
                $stats['valide'],
                $stats['en_attente'],
                $stats['bloque'],
            ],
            'colors' => ['#28a745', '#ffc107', '#dc3545'],
        ];
    }

    /**
     * Get chart data for articles by date
     */
    public function getArticlesDateChartData(): array
    {
        $stats = $this->getArticlesByDate();

        return [
            'labels' => array_keys($stats),
            'data' => array_values($stats),
        ];
    }

    /**
     * Get chart data for comments by date
     */
    public function getCommentsDateChartData(): array
    {
        $stats = $this->getCommentsByDate();

        return [
            'labels' => array_keys($stats),
            'data' => array_values($stats),
        ];
    }
}
