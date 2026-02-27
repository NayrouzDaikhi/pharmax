<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Exception;

class OllamaService
{
    private string $apiUrl;
    private string $model;
    
    public function __construct(
        private HttpClientInterface $httpClient
    ) {
        $this->apiUrl = $_ENV['OLLAMA_API_URL'] ?? 'http://localhost:11434';
        $this->model = $_ENV['OLLAMA_MODEL'] ?? 'mistral';
    }

    /**
     * Generate a chatbot answer using Ollama
     */
    public function generateChatbotAnswer(
        string $question,
        string $context = '',
        ?int $articleId = null,
        ?string $articleTitle = null
    ): string {
        try {
            $prompt = $this->buildPrompt($question, $context, $articleId, $articleTitle);
            
            $response = $this->httpClient->request(
                'POST',
                $this->apiUrl . '/api/generate',
                [
                    'json' => [
                        'model' => $this->model,
                        'prompt' => $prompt,
                        'stream' => false,
                    ],
                    'timeout' => 60,
                ]
            );

            $data = $response->toArray();
            return $data['response'] ?? '';
        } catch (Exception $e) {
            error_log('OllamaService error: ' . $e->getMessage());
            
            // Provide better error messages for common issues
            $msg = $e->getMessage();
            if (strpos($msg, 'Connection refused') !== false) {
                throw new Exception('Connection refused to Ollama at ' . $this->apiUrl . ':11434. Make sure Ollama is running with: ollama serve');
            } elseif (strpos($msg, 'timed out') !== false) {
                throw new Exception('Ollama API request timed out. The server might be busy or the model is still loading.');
            } else {
                throw new Exception('Erreur Ollama: ' . $msg);
            }
        }
    }

    /**
     * Generate an expiration notification message
     */
    public function generateExpirationMessage(string $productName, string $expirationDate): string
    {
        try {
            $prompt = "Génère un message court et amical pour informer un utilisateur que le produit '$productName' expire le $expirationDate. Le message doit être court (max 100 caractères).";
            
            $response = $this->httpClient->request(
                'POST',
                $this->apiUrl . '/api/generate',
                [
                    'json' => [
                        'model' => $this->model,
                        'prompt' => $prompt,
                        'stream' => false,
                    ],
                    'timeout' => 30,
                ]
            );

            $data = $response->toArray();
            return $data['response'] ?? '';
        } catch (Exception $e) {
            error_log('OllamaService expiration message error: ' . $e->getMessage());
            return "Le produit $productName expire le $expirationDate.";
        }
    }

    /**
     * Get Ollama service status
     */
    public function getStatus(): array
    {
        try {
            $response = $this->httpClient->request(
                'GET',
                $this->apiUrl . '/api/tags',
                ['timeout' => 5]
            );

            if ($response->getStatusCode() === 200) {
                $data = $response->toArray();
                return [
                    'status' => 'running',
                    'models' => $data['models'] ?? [],
                    'message' => 'Ollama is running'
                ];
            }
            
            return ['status' => 'error', 'message' => 'Cannot connect to Ollama'];
        } catch (Exception $e) {
            return ['status' => 'offline', 'message' => $e->getMessage()];
        }
    }

    /**
     * Check if Ollama is configured and accessible
     */
    public function isConfigured(): bool
    {
        try {
            $response = $this->httpClient->request(
                'GET',
                $this->apiUrl . '/api/tags',
                ['timeout' => 2]
            );
            
            return $response->getStatusCode() === 200;
        } catch (Exception) {
            return false;
        }
    }

    /**
     * Build the prompt for the chatbot
     */
    private function buildPrompt(
        string $question,
        string $context,
        ?int $articleId,
        ?string $articleTitle
    ): string {
        $prompt = "You are a helpful pharmacy assistant.";
        
        if ($articleId && $articleTitle) {
            $prompt .= " You are answering questions about the article: '$articleTitle'.";
        }
        
        if (!empty($context)) {
            $prompt .= "\n\nContext:\n" . $context;
        }
        
        $prompt .= "\n\nQuestion: " . $question;
        $prompt .= "\n\nAnswer (be concise and helpful):";
        
        return $prompt;
    }
}
