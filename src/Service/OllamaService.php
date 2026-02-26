<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Exception;

class OllamaService
{
    private const OLLAMA_API = 'http://localhost:11434/api/generate';
    private const MODEL = 'mistral'; // Default model - can be changed to neural-chat, orca-mini, etc.
    
    // Alternative models available
    private const AVAILABLE_MODELS = [
        'mistral',
        'neural-chat',
        'orca-mini',
        'llama2',
        'dolphin-mixtral',
    ];

    public function __construct(
        private HttpClientInterface $httpClient,
    ) {}

    /**
     * Generate text using Ollama - General purpose method
     */
    public function generate(string $prompt, array $options = []): string
    {
        try {
            return $this->callOllamaAPI($prompt, $options);
        } catch (Exception $e) {
            error_log('[Ollama] Generation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate expiration message (replaces Gemini's generateExpirationMessage)
     */
    public function generateExpirationMessage(string $productName, int $days): string
    {
        try {
            $prompt = "Génère un message professionnel pour informer qu'un produit nommé '$productName' expire dans $days jours. Ajoute des recommandations d'action.";
            
            return $this->callOllamaAPI($prompt, [
                'temperature' => 0.7,
                'top_p' => 0.9,
                'top_k' => 40,
            ]);
        } catch (Exception $e) {
            error_log('[Ollama] Failed to generate expiration message: ' . $e->getMessage());
            // Fallback message
            return $this->generateFallbackMessage($productName, $days);
        }
    }

    /**
     * Generate chatbot answer from question and context
     */
    public function generateChatbotAnswer(string $question, string $context, ?int $articleId = null, ?string $articleTitle = null): string
    {
        try {
            // Build detailed prompt with article context
            $prompt = $this->buildChatbotPrompt($question, $context, $articleId, $articleTitle);
            
            return $this->callOllamaAPI($prompt, [
                'temperature' => 0.7,
                'top_p' => 0.95,
                'top_k' => 40,
            ]);
        } catch (Exception $e) {
            error_log('[Ollama] Failed to generate chatbot answer: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Call the Ollama API
     */
    private function callOllamaAPI(string $prompt, array $options = []): string
    {
        try {
            error_log('[Ollama] Calling API with model: ' . self::MODEL);

            // Check if model is available before attempting to use it
            $availableModels = $this->getAvailableModels();
            error_log('[Ollama] Available models: ' . json_encode(array_column($availableModels, 'name')));
            
            // Check if requested model is available
            $modelFound = false;
            foreach ($availableModels as $model) {
                if (isset($model['name']) && strpos($model['name'], self::MODEL) === 0) {
                    $modelFound = true;
                    break;
                }
            }
            
            if (!$modelFound) {
                $modelList = implode(', ', array_column($availableModels, 'name'));
                $errorMsg = 'Model "' . self::MODEL . '" is not downloaded yet. ';
                if (!empty($modelList)) {
                    $errorMsg .= 'Available models: ' . $modelList . '. ';
                }
                $errorMsg .= 'Please wait for the model to download or run: ollama pull ' . self::MODEL;
                throw new Exception($errorMsg);
            }

            // Default generation parameters
            $params = array_merge([
                'temperature' => 0.7,
                'top_p' => 0.9,
                'top_k' => 40,
                'repeat_penalty' => 1.1,
            ], $options);

            // Call Ollama API with streaming disabled for simpler response handling
            $response = $this->httpClient->request('POST', self::OLLAMA_API, [
                'json' => [
                    'model' => self::MODEL,
                    'prompt' => $prompt,
                    'stream' => false,
                    'temperature' => $params['temperature'],
                    'top_p' => $params['top_p'],
                    'top_k' => $params['top_k'],
                    'repeat_penalty' => $params['repeat_penalty'],
                ],
                'timeout' => 60, // Ollama can be slow on first response
            ]);

            $statusCode = $response->getStatusCode();
            error_log('[Ollama] Response status: ' . $statusCode);

            if ($statusCode !== 200) {
                throw new Exception('Ollama API returned status ' . $statusCode);
            }

            $data = $response->toArray();
            
            if (!isset($data['response'])) {
                error_log('[Ollama] Invalid response format: ' . json_encode($data));
                throw new Exception('Invalid response format from Ollama');
            }

            $response_text = trim($data['response']);
            error_log('[Ollama] Successfully got response (length: ' . strlen($response_text) . ')');
            
            return $response_text;

        } catch (Exception $e) {
            error_log('[Ollama] API Error: ' . $e->getMessage());
            throw new Exception('Ollama API unavailable: ' . $e->getMessage());
        }
    }

    /**
     * Build prompt for chatbot (matches Gemini format but optimized for Ollama)
     */
    private function buildChatbotPrompt(string $question, string $context, ?int $articleId = null, ?string $articleTitle = null): string
    {
        $articleContext = '';
        if ($articleId && $articleTitle) {
            $articleContext = "\n\n⭐ ARTICLE PRINCIPAL CONSULTÉ:\n"
                . "ID: $articleId\n"
                . "Titre: $articleTitle\n";
        }

        return <<<PROMPT
Tu es un assistant IA pour le site Pharmax. Tu dois répondre aux questions des utilisateurs en utilisant UNIQUEMENT les informations fournies ci-dessous. Si une question n'est pas couverte par ces articles, dis-le clairement.

📄 ARTICLES ET RESSOURCES DISPONIBLES:
{$context}
{$articleContext}

INSTRUCTIONS DE RÉPONSE:
✅ Sois professionnel et courtois
✅ Fournir des réponses claires et concises (max 300 mots)
✅ Cite l'article source si approprié
✅ Si la réponse n'est pas trouvée, recommande de contacter support@pharmax.com
❌ Ne pas inventer d'informations
❌ Ne pas sortir du contexte

QUESTION DU CLIENT:
"{$question}"

RÉPONSE PERSONNALISÉE:
PROMPT;
    }

    /**
     * Fallback message for expiration (same as Gemini)
     */
    private function generateFallbackMessage(string $productName, int $days): string
    {
        return sprintf(
            "⚠️ Notification d'expiration\n\n"
            . "Le produit '%s' expire dans %d jours.\n\n"
            . "Actions recommandées:\n"
            . "- Examiner le stock immédiatement\n"
            . "- Établir un plan de liquidation\n"
            . "- Informer les clients\n"
            . "- Vérifier la conformité réglementaire",
            $productName,
            $days
        );
    }

    /**
     * Check if Ollama is configured and accessible
     */
    public function isConfigured(): bool
    {
        try {
            $response = $this->httpClient->request('GET', 'http://localhost:11434/api/tags', [
                'timeout' => 5,
            ]);
            return $response->getStatusCode() === 200;
        } catch (Exception $e) {
            error_log('[Ollama] Configuration check failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get available models from Ollama
     */
    public function getAvailableModels(): array
    {
        try {
            $response = $this->httpClient->request('GET', 'http://localhost:11434/api/tags', [
                'timeout' => 5,
            ]);

            $data = $response->toArray();
            return $data['models'] ?? [];
        } catch (Exception $e) {
            error_log('[Ollama] Failed to get models: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Check connection status
     */
    public function getStatus(): array
    {
        try {
            $response = $this->httpClient->request('GET', 'http://localhost:11434/api/tags', [
                'timeout' => 5,
            ]);

            $status = $response->getStatusCode() === 200;
            $models = $response->toArray();

            return [
                'status' => $status ? 'online' : 'offline',
                'models' => $models['models'] ?? [],
                'configured' => true,
            ];
        } catch (Exception $e) {
            return [
                'status' => 'offline',
                'models' => [],
                'configured' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
