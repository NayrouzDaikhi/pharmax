<?php

namespace App\Service;

use Stichoza\GoogleTranslate\GoogleTranslate;

class GoogleTranslationService
{
    public function translate(string $text, string $targetLang = 'en'): ?string
    {
        try {
            $tr = new GoogleTranslate();
            $tr->setTarget($targetLang);

            return $tr->translate($text);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
