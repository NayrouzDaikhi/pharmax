<?php

namespace App\Controller;

use App\Service\TranslateService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/translate')]
class TranslateController extends AbstractController
{
    public function __construct(private TranslateService $translateService)
    {
    }

    /**
     * Endpoint API pour traduire un texte
     * POST /api/translate
     * 
     * Body JSON:
     * {
     *     "text": "Bonjour, je rencontre un problème",
     *     "targetLang": "en"
     * }
     */
    #[Route('', name: 'api_translate', methods: ['POST'])]
    public function translate(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validation des données
        if (!isset($data['text']) || empty($data['text'])) {
            return $this->json(['error' => 'Le champ "text" est obligatoire'], 400);
        }

        $text = $data['text'];
        $targetLang = $data['targetLang'] ?? 'en';

        try {
            $translated = $this->translateService->translateText($text, $targetLang);

            return $this->json([
                'success' => true,
                'original' => $text,
                'translated' => $translated,
                'targetLanguage' => $targetLang
            ], 200);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la traduction: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Endpoint API pour traduire vers plusieurs langues
     * POST /api/translate/multi
     * 
     * Body JSON:
     * {
     *     "text": "Bonjour, je rencontre un problème",
     *     "targetLangs": ["en", "es", "de"]
     * }
     */
    #[Route('/multi', name: 'api_translate_multi', methods: ['POST'])]
    public function translateMultiple(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validation des données
        if (!isset($data['text']) || empty($data['text'])) {
            return $this->json(['error' => 'Le champ "text" est obligatoire'], 400);
        }

        $text = $data['text'];
        $targetLangs = $data['targetLangs'] ?? ['en', 'fr', 'es'];

        try {
            $translations = $this->translateService->translateToMultipleLangs($text, $targetLangs);

            return $this->json([
                'success' => true,
                'original' => $text,
                'translations' => $translations
            ], 200);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la traduction: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Endpoint simple pour traduire un texte (GET)
     * GET /api/translate/text?text=...&targetLang=en
     */
    #[Route('/text', name: 'api_translate_text', methods: ['GET'])]
    public function translateSimple(Request $request): JsonResponse
    {
        $text = $request->query->get('text');
        $targetLang = $request->query->get('targetLang', 'en');

        if (empty($text)) {
            return $this->json(['error' => 'Le paramètre "text" est obligatoire'], 400);
        }

        try {
            $translated = $this->translateService->translateText($text, $targetLang);

            return $this->json([
                'success' => true,
                'original' => $text,
                'translated' => $translated,
                'targetLanguage' => $targetLang
            ], 200);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la traduction: ' . $e->getMessage()
            ], 500);
        }
    }
}
