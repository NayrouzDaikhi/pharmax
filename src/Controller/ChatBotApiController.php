<?php

namespace App\Controller;

use App\Service\ChatBotService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[Route('/api/chatbot', name: 'api_chatbot')]
class ChatBotApiController extends AbstractController
{
    public function __construct(
        private ChatBotService $chatBotService,
        private ValidatorInterface $validator,
    ) {}

    /**
     * Endpoint POST pour poser une question au chatbot
     * POST /api/chatbot/ask
     */
    #[Route('/ask', name: 'chatbot_ask', methods: ['POST'])]
    public function ask(Request $request): JsonResponse
    {
        try {
            // Get raw content and log it
            $rawContent = $request->getContent();
            error_log('ChatBot Ask - Raw Content: ' . $rawContent);
            error_log('ChatBot Ask - Content Length: ' . strlen($rawContent));
            
            // Décoder le JSON
            $data = json_decode($rawContent, true);
            error_log('ChatBot Ask - Decoded Data: ' . json_encode($data));

            if ($data === null) {
                error_log('ChatBot Ask - JSON decode failed');
                return $this->json([
                    'success' => false,
                    'error' => 'Format JSON invalide',
                ], 400);
            }

            // Récupérer la question
            $question = $data['question'] ?? null;
            $articleId = isset($data['article_id']) ? (int)$data['article_id'] : null;
            $articleTitle = $data['article_title'] ?? null;

            // Validation rapide
            if (empty($question) || !is_string($question)) {
                return $this->json([
                    'success' => false,
                    'error' => 'La question est requise et doit être une chaîne',
                ], 400);
            }

            $questionLength = mb_strlen($question);
            if ($questionLength < 3 || $questionLength > 1000) {
                return $this->json([
                    'success' => false,
                    'error' => "La question doit contenir entre 3 et 1000 caractères (actuellement $questionLength)",
                ], 400);
            }

            // Vérifier que la clé API est configurée
            if (!$this->chatBotService->isApiKeyConfigured()) {
                return $this->json([
                    'success' => false,
                    'error' => 'Service d\'IA Ollama non configuré. Assurez-vous qu\'Ollama est en cours d\'exécution sur localhost:11434.',
                ], 503);
            }

            // Obtenir la réponse avec contexte article optionnel
            $result = $this->chatBotService->answerQuestion($question, $articleId, $articleTitle);

            $statusCode = $result['success'] ? 200 : 400;

            return $this->json($result, $statusCode);

        } catch (\Exception $e) {
            // Log l'erreur complète
            error_log('ChatBot Error: ' . $e->getMessage() . ' | ' . $e->getFile() . ':' . $e->getLine());
            error_log('Stack trace: ' . $e->getTraceAsString());
            
            return $this->json([
                'success' => false,
                'error' => 'Erreur serveur: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Endpoint GET pour tester la connexion au chatbot
     * GET /api/chatbot/health
     */
    #[Route('/health', name: 'chatbot_health', methods: ['GET'])]
    public function health(): JsonResponse
    {
        return $this->json([
            'status' => 'ok',
            'api_configured' => $this->chatBotService->isApiKeyConfigured(),
            'message' => 'ChatBot API is running',
            'timestamp' => date('Y-m-d H:i:s'),
        ]);
    }
    
    /**
     * Endpoint GET pour déboguer (test simple)
     * GET /api/chatbot/debug
     */
    #[Route('/debug', name: 'chatbot_debug', methods: ['GET'])]
    public function debug(): JsonResponse
    {
        return $this->json([
            'debug' => true,
            'message' => 'Debug endpoint working',
            'server' => $_SERVER['SERVER_NAME'] ?? 'unknown',
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
        ]);
    }
}
