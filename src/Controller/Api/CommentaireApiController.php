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
        try {
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
                    'warning' => 'Votre commentaire contient un langage inapproprié et n\'a pas pu être publié. Veuillez consulter nos règles de communauté et éviter de poster du contenu offensant ou nuisible.',
                    'status' => 'BLOQUE',
                    'message' => 'Commentaire bloqué pour contenu inapproprié'
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
                    'message' => 'Votre commentaire a été publié avec succès',
                    'status' => 'VALIDE',
                    'comment_id' => $commentaire->getId()
                ], 201);
            }
        } catch (\Throwable $e) {
            // Log the error
            error_log('[API ERROR] ' . $e->getMessage() . ' - ' . $e->getTraceAsString());
            
            // Return error response
            return $this->json([
                'success' => false,
                'error' => 'Une erreur serveur est survenue: ' . $e->getMessage(),
                'message' => 'Error processing comment'
            ], 500);
        }
    }

    #[Route('/statistics', name: 'api_statistics', methods: ['GET'])]
    public function getStatistics(CommentaireRepository $commentaireRepository, CommentaireArchiveRepository $archiveRepository): JsonResponse
    {
        // Get all active comments
        $allCommentaires = $commentaireRepository->findAll();
        
        // Get all archived comments
        $allArchived = $archiveRepository->findAll();
        
        $statsByStatus = [
            'valide' => 0,
            'bloque' => 0,
            'en_attente' => 0
        ];
        
        // Count active comments by status
        foreach ($allCommentaires as $commentaire) {
            $status = strtolower($commentaire->getStatut());
            if (isset($statsByStatus[$status])) {
                $statsByStatus[$status]++;
            }
        }
        
        // Count archived comments as 'bloque'
        $statsByStatus['bloque'] += count($allArchived);
        
        $totalComments = count($allCommentaires) + count($allArchived);
        
        return $this->json([
            'success' => true,
            'statistics' => [
                'approved' => $statsByStatus['valide'],
                'pending' => $statsByStatus['en_attente'],
                'blocked' => $statsByStatus['bloque'],
                'archived' => count($allArchived),
                'total' => $totalComments
            ]
        ]);
    }

    #[Route('/commentaires/delete-multiple', name: 'api_delete_multiple_commentaires', methods: ['POST'])]
    public function deleteMultiple(
        Request $request,
        CommentaireRepository $commentaireRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['ids']) || !is_array($data['ids'])) {
            return $this->json(['success' => false, 'message' => 'Invalid data'], 400);
        }
        
        $ids = array_map('intval', $data['ids']);
        $deletedCount = 0;
        
        foreach ($ids as $id) {
            $commentaire = $commentaireRepository->find($id);
            if ($commentaire) {
                $entityManager->remove($commentaire);
                $deletedCount++;
            }
        }
        
        if ($deletedCount > 0) {
            $entityManager->flush();
        }
        
        return $this->json([
            'success' => true,
            'message' => $deletedCount . ' comment(s) deleted successfully',
            'deleted_count' => $deletedCount
        ]);
    }

    #[Route('/commentaire-archives/delete-multiple', name: 'api_delete_multiple_archives', methods: ['POST'])]
    public function deleteMultipleArchives(
        Request $request,
        CommentaireArchiveRepository $archiveRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['ids']) || !is_array($data['ids'])) {
            return $this->json(['success' => false, 'message' => 'Invalid data'], 400);
        }
        
        $ids = array_map('intval', $data['ids']);
        $deletedCount = 0;
        
        foreach ($ids as $id) {
            $archive = $archiveRepository->find($id);
            if ($archive) {
                $entityManager->remove($archive);
                $deletedCount++;
            }
        }
        
        if ($deletedCount > 0) {
            $entityManager->flush();
        }
        
        return $this->json([
            'success' => true,
            'message' => $deletedCount . ' archive(s) deleted successfully',
            'deleted_count' => $deletedCount
        ]);
    }

    #[Route('/commentaires/{id}/delete', name: 'api_delete_single_commentaire', methods: ['POST'])]
    public function deleteSingle(
        Commentaire $commentaire,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        try {
            $entityManager->remove($commentaire);
            $entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Comment deleted successfully'
            ]);
        } catch (\Throwable $e) {
            return $this->json([
                'success' => false,
                'message' => 'Failed to delete comment: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/commentaire-archives/{id}/delete', name: 'api_delete_single_archive', methods: ['POST'])]
    public function deleteSingleArchive(
        CommentaireArchive $archive,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        try {
            $entityManager->remove($archive);
            $entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Archived comment deleted successfully'
            ]);
        } catch (\Throwable $e) {
            return $this->json([
                'success' => false,
                'message' => 'Failed to delete archived comment: ' . $e->getMessage()
            ], 500);
        }
    }
}