<?php

namespace App\Controller\Api;

use App\Service\ChatBotService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/chatbot', name: 'api_chatbot_')]
class ChatBotApiController extends AbstractController
{
    public function __construct(
        private ChatBotService $chatBotService,
    ) {}

    /**
     * Helper to add CORS headers to response
     */
    private function addCorsHeaders(JsonResponse $response): JsonResponse
    {
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        return $response;
    }

    /**
     * Handle preflight requests
     */
    #[Route('/ask', name: 'ask_options', methods: ['OPTIONS'])]
    #[Route('/health', name: 'health_options', methods: ['OPTIONS'])]
    public function options(): Response
    {
        $response = new Response();
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        return $response;
    }

    /**
     * Répondre à une question
     * POST /api/chatbot/ask
     */
    #[Route('/ask', name: 'ask', methods: ['POST'])]
    public function ask(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!isset($data['question'])) {
                return $this->addCorsHeaders($this->json([
                    'success' => false,
                    'error' => 'Question field is required',
                    'answer' => null
                ], Response::HTTP_BAD_REQUEST));
            }

            $question = trim($data['question']);

            // Validate question length
            if (strlen($question) < 3) {
                return $this->addCorsHeaders($this->json([
                    'success' => false,
                    'error' => 'Question must be at least 3 characters long',
                    'answer' => null
                ], Response::HTTP_BAD_REQUEST));
            }

            if (strlen($question) > 1000) {
                return $this->addCorsHeaders($this->json([
                    'success' => false,
                    'error' => 'Question must be no longer than 1000 characters',
                    'answer' => null
                ], Response::HTTP_BAD_REQUEST));
            }

            // Get article ID if provided
            $articleId = $data['articleId'] ?? null;
            $articleTitle = $data['articleTitle'] ?? null;

            // Get answer from ChatBotService
            $response = $this->chatBotService->answerQuestion($question, $articleId, $articleTitle);

            if (!$response['success']) {
                return $this->addCorsHeaders($this->json($response, Response::HTTP_INTERNAL_SERVER_ERROR));
            }

            return $this->addCorsHeaders($this->json($response));

        } catch (\Exception $e) {
            return $this->addCorsHeaders($this->json([
                'success' => false,
                'error' => 'Server error: ' . $e->getMessage(),
                'answer' => null
            ], Response::HTTP_INTERNAL_SERVER_ERROR));
        }
    }

    /**
     * Health check endpoint
     * GET /api/chatbot/health
     */
    #[Route('/health', name: 'health', methods: ['GET'])]
    public function health(): JsonResponse
    {
        return $this->addCorsHeaders($this->json([
            'status' => 'ok',
            'api_configured' => $this->chatBotService->isApiKeyConfigured(),
            'message' => 'ChatBot API is running'
        ]));
    }
}
