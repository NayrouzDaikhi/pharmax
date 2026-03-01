<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/api/ollama', name: 'api_ollama_')]
class OllamaDebugController extends AbstractController
{
    #[Route('/debug', name: 'debug', methods: ['GET'])]
    public function debug(HttpClientInterface $client): JsonResponse
    {
        $debug = [];

        // Step 1: Can we reach Ollama?
        try {
            $response = $client->request('GET', 'http://localhost:11434/api/tags', [
                'timeout' => 10
            ]);
            $debug['ollama_connection'] = $response->getStatusCode() === 200 ? 'OK' : 'FAILED_' . $response->getStatusCode();
            $models_data = $response->toArray();
            $debug['models'] = array_column($models_data['models'] ?? [], 'name');
        } catch (\Exception $e) {
            $debug['ollama_connection'] = 'ERROR: ' . $e->getMessage();
            $debug['models'] = [];
        }

        // Step 2: Test simple generate
        if (!empty($debug['models'])) {
            try {
                $debug['test_generate'] = 'Attempting...';
                $response = $client->request('POST', 'http://localhost:11434/api/generate', [
                    'json' => [
                        'model' => 'mistral',
                        'prompt' => 'Say hello',
                        'stream' => false
                    ],
                    'timeout' => 120
                ]);
                
                if ($response->getStatusCode() === 200) {
                    $data = $response->toArray();
                    $debug['test_generate'] = 'SUCCESS: ' . substr($data['response'] ?? '', 0, 50);
                } else {
                    $debug['test_generate'] = 'FAILED: HTTP ' . $response->getStatusCode();
                }
            } catch (\Exception $e) {
                $debug['test_generate'] = 'ERROR: ' . $e->getMessage();
            }
        }

        return $this->json($debug);
    }
}
