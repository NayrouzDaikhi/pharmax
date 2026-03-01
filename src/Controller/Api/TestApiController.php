<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/test', name: 'api_test_')]
class TestApiController extends AbstractController
{
    #[Route('/simple', name: 'simple', methods: ['POST', 'GET'])]
    public function simple(): JsonResponse
    {
        return $this->json([
            'success' => true,
            'message' => 'Test endpoint works!',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
}
