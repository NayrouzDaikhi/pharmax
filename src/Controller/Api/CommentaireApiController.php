<?php

namespace App\Controller\Api;

use App\Entity\Commentaire;
use App\Entity\CommentaireArchive;
use App\Repository\ArticleRepository;
use App\Repository\CommentaireRepository;
use App\Repository\CommentaireArchiveRepository;
use App\Service\CommentModerationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class CommentaireApiController extends AbstractController
{
    #[Route('/commentaires', name: 'api_commentaire_create', methods: ['POST'])]
    public function create(
        Request $request,
        ArticleRepository $articleRepository,
        EntityManagerInterface $entityManager,
        CommentModerationService $moderationService
    ): JsonResponse
    {
        // 1️⃣ Read JSON data
        $data = json_decode($request->getContent(), true);

        if (!isset($data['contenu'], $data['article_id'])) {
            return $this->json(['error' => 'Invalid data'], 400);
        }

        // 2️⃣ Find article
        $article = $articleRepository->find($data['article_id']);
        if (!$article) {
            return $this->json(['error' => 'Article not found'], 404);
        }

        // 3️⃣ AI moderation
        $isToxic = $moderationService->analyze($data['contenu']);

        // 4️⃣ Handle blocked vs valid comments
        if ($isToxic) {
            // ❌ Comment is inappropriate - save to archive
            $archive = new CommentaireArchive();
            $archive->setContenu($data['contenu']);
            $archive->setArticle($article);
            $archive->setDatePublication(new \DateTime());
            $archive->setUserName($data['user_name'] ?? 'Anonymous');
            $archive->setUserEmail($data['user_email'] ?? null);
            $archive->setReason('inappropriate');
            
            $entityManager->persist($archive);
            $entityManager->flush();

            // Return warning to user
            return $this->json([
                'success' => false,
                'warning' => '⚠️ Your comment was detected as inappropriate and has not been posted. Please review our community guidelines and avoid posting offensive or harmful content.',
                'status' => 'BLOQUE',
                'message' => 'Comment blocked due to inappropriate content'
            ], 403);
        } else {
            // ✅ Comment is appropriate - save normally
            $commentaire = new Commentaire();
            $commentaire->setContenu($data['contenu']);
            $commentaire->setArticle($article);
            $commentaire->setDatePublication(new \DateTime());
            $commentaire->setStatut('VALIDE');

            $entityManager->persist($commentaire);
            $entityManager->flush();

            // Return success
            return $this->json([
                'success' => true,
                'message' => 'Comment posted successfully',
                'status' => 'VALIDE',
                'comment_id' => $commentaire->getId()
            ], 201);
        }
    }

    #[Route('/stats', name: 'api_stats', methods: ['GET'])]
    public function getStats(
        CommentaireRepository $commentaireRepository,
        CommentaireArchiveRepository $archiveRepository,
        ArticleRepository $articleRepository
    ): JsonResponse
    {
        $allCommentaires = $commentaireRepository->findAll();
        $archivedCommentaires = $archiveRepository->findAll();
        $articles = $articleRepository->findAll();

        // Calculate statistics
        $totalArticles = count($articles);
        $totalCommentaires = count($allCommentaires);
        $totalArchived = count($archivedCommentaires);

        // Distribution by status
        $statsByStatus = [
            'valide' => 0,
            'bloque' => 0,
            'en_attente' => 0
        ];

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
            $status = $commentaire->getStatut();
            if (isset($statsByStatus[$status])) {
                $statsByStatus[$status]++;
            }
        }

        return $this->json([
            'totalArticles' => $totalArticles,
            'totalCommentaires' => $totalCommentaires,
            'totalArchived' => $totalArchived,
            'statsByStatus' => $statsByStatus,
            'mostCommentedArticleId' => $mostCommentedArticleId,
            'mostCommentedCount' => $maxComments
        ]);
    }
}
