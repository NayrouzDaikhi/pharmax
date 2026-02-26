<?php

namespace App\Controller\Api;

use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class ArticleApiController extends AbstractController
{
    #[Route('/articles/delete-multiple', name: 'api_delete_multiple_articles', methods: ['POST'])]
    public function deleteMultiple(
        Request $request,
        ArticleRepository $articleRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['ids']) || !is_array($data['ids'])) {
            return $this->json(['success' => false, 'message' => 'Invalid data'], 400);
        }
        
        $ids = array_map('intval', $data['ids']);
        $deletedCount = 0;
        
        foreach ($ids as $id) {
            $article = $articleRepository->find($id);
            if ($article) {
                $entityManager->remove($article);
                $deletedCount++;
            }
        }
        
        if ($deletedCount > 0) {
            $entityManager->flush();
        }
        
        return $this->json([
            'success' => true,
            'message' => $deletedCount . ' article(s) deleted successfully',
            'deleted_count' => $deletedCount
        ]);
    }
}
