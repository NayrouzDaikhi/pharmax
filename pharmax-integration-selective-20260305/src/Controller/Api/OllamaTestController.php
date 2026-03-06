<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/api/ollama', name: 'api_ollama_')]
class OllamaTestController extends AbstractController
{
    #[Route('/test', name: 'test', methods: ['POST', 'GET'])]
    public function test(HttpClientInterface $client, Request $request): JsonResponse
    {
        $question = $request->query->get('q') ?? $request->request->get('question') ?? 'hello';
        
        try {
            $response = $client->request('POST', 'http://localhost:11434/api/generate', [
                'json' => [
                    'model' => 'mistral',
                    'prompt' => $question,
                    'stream' => false,
                ],
                'timeout' => 120
            ]);

            $data = $response->toArray();
            
            return $this->json([
                'success' => true,
                'question' => $question,
                'response' => $data['response'] ?? 'No response',
                'model' => 'mistral',
                'status_code' => $response->getStatusCode()
            ]);
        } catch (\Throwable $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
                'question' => $question
            ], 500);
        }
    }

    #[Route('/status', name: 'status', methods: ['GET'])]
    public function status(HttpClientInterface $client): JsonResponse
    {
        try {
            $response = $client->request('GET', 'http://localhost:11434/api/tags', [
                'timeout' => 5
            ]);

            $data = $response->toArray();
            
            return $this->json([
                'connected' => true,
                'models' => array_column($data['models'] ?? [], 'name'),
                'status_code' => $response->getStatusCode()
            ]);
        } catch (\Throwable $e) {
            return $this->json([
                'connected' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
