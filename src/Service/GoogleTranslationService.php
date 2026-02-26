<?php

namespace App\Service;

use Stichoza\GoogleTranslate\GoogleTranslate;

class GoogleTranslationService
{
    private const LANGUAGE_MAP = [
        'ar' => 'ar',
        'arabe' => 'ar',
        'arabic' => 'ar',
        'en' => 'en',
        'english' => 'en',
        'fr' => 'fr',
        'french' => 'fr',
        'es' => 'es',
        'spanish' => 'es',
        'de' => 'de',
        'german' => 'de',
        'it' => 'it',
        'italian' => 'it',
        'ja' => 'ja',
        'japanese' => 'ja',
        'zh' => 'zh',
        'chinese' => 'zh',
        'pt' => 'pt',
        'portuguese' => 'pt',
    ];

    public function detectLanguageRequest(string $text): ?string
    {
        $lower = strtolower($text);
        
        // Check for language keywords
        foreach (self::LANGUAGE_MAP as $keyword => $langCode) {
            if (strpos($lower, $keyword) !== false) {
                return $langCode;
            }
        }
        
        return null;
    }

    public function translate(string $text, string $targetLang = 'en'): ?string
    {
        try {
            // Normalize language code
            $targetLang = self::LANGUAGE_MAP[strtolower($targetLang)] ?? $targetLang;
            
            $tr = new GoogleTranslate();
            $tr->setTarget($targetLang);

            return $tr->translate($text);
        } catch (\Throwable $e) {
            error_log('[Translation] Error translating to ' . $targetLang . ': ' . $e->getMessage());
            return null;
        }
    }

    public function translateArticleContent(string $title, string $content, string $targetLang = 'ar'): array
    {
        try {
            $translatedTitle = $this->translate($title, $targetLang);
            $translatedContent = $this->translate($content, $targetLang);

            return [
                'original_title' => $title,
                'translated_title' => $translatedTitle ?? $title,
                'original_content' => $content,
                'translated_content' => $translatedContent ?? $content,
                'language' => $targetLang,
                'success' => $translatedTitle !== null && $translatedContent !== null,
            ];
        } catch (\Throwable $e) {
            error_log('[Translation] Error in translateArticleContent: ' . $e->getMessage());
            return [
                'original_title' => $title,
                'translated_title' => $title,
                'original_content' => $content,
                'translated_content' => $content,
                'language' => $targetLang,
                'success' => false,
            ];
        }
    }

    public function translateText(string $text, string $targetLang = 'ar'): string
    {
        $translated = $this->translate($text, $targetLang);
        return $translated ?? $text;
    }
}
