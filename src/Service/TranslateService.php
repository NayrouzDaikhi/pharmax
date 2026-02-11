<?php

namespace App\Service;

use Stichoza\GoogleTranslate\GoogleTranslate;

class TranslateService
{
    private $translator;

    public function __construct()
    {
        // Par défaut, traduit vers l'anglais
        $this->translator = new GoogleTranslate('en');
    }

    /**
     * Traduit un texte vers une langue donnée
     * 
     * @param string $text Le texte à traduire
     * @param string $targetLang La langue cible (par défaut 'en' pour l'anglais)
     * @return string Le texte traduit
     */
    public function translateText(string $text, string $targetLang = 'en'): string
    {
        try {
            $this->translator->setTarget($targetLang);
            return $this->translator->translate($text);
        } catch (\Exception $e) {
            // En cas d'erreur, retourner le texte original
            return $text;
        }
    }

    /**
     * Traduit vers plusieurs langues à la fois
     * 
     * @param string $text Le texte à traduire
     * @param array $targetLangs Les langues cibles
     * @return array Les traductions
     */
    public function translateToMultipleLangs(string $text, array $targetLangs = ['en', 'fr', 'es']): array
    {
        $translations = [];
        foreach ($targetLangs as $lang) {
            $translations[$lang] = $this->translateText($text, $lang);
        }
        return $translations;
    }
}
