<?php

namespace App\Controller;

use App\Service\GoogleTranslationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/translate')]
class TranslateController extends AbstractController
{
    public function __construct(private GoogleTranslationService $googleTranslateService)
    {
    }

    #[Route('', name: 'api_translate', methods: ['POST'])]
    public function translate(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!isset($data['text']) || !isset($data['lang'])) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Missing required fields: text and lang'
                ], 400);
            }

            $text = $data['text'];
            $targetLang = $data['lang'];

            // Use Google Translation Service (same as frontend)
            $translatedText = $this->googleTranslateService->translate($text, $targetLang);

            if ($translatedText === null) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Translation service failed',
                    'translated' => $text // Return original if translation fails
                ]);
            }

            return new JsonResponse([
                'success' => true,
                'original' => $text,
                'translated' => $translatedText,
                'lang' => $targetLang
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage(),
                'translated' => $text ?? 'Error'
            ], 500);
        }
    }
}
