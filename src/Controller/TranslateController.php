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

            $translatedText = $this->translateService->translateText($text, $targetLang);

            return new JsonResponse([
                'success' => true,
                'original' => $text,
                'translated' => $translatedText,
                'lang' => $targetLang
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
