<?php

namespace App\Service;

class TranslateService
{
    const LANGUAGE_CODES = [
        'en' => 'en',
        'es' => 'es',
        'de' => 'de',
        'it' => 'it',
        'pt' => 'pt',
        'fr' => 'fr',
        'ja' => 'ja',
        'zh' => 'zh-CN',
        'ar' => 'ar',
        'ru' => 'ru',
    ];

    /**
     * Traduit un texte vers une langue donnée en utilisant MyMemory API
     * 
     * @param string $text Le texte à traduire
     * @param string $targetLang La langue cible (par défaut 'en' pour l'anglais)
     * @return string Le texte traduit
     */
    public function translateText(string $text, string $targetLang = 'en'): string
    {
        try {
            // Valider la langue
            if (!array_key_exists($targetLang, self::LANGUAGE_CODES)) {
                return $text;
            }

            $langCode = self::LANGUAGE_CODES[$targetLang];
            
            // Utiliser MyMemory API (gratuit, pas de clé requise)
            $url = sprintf(
                'https://api.mymemory.translated.net/get?q=%s&langpair=fr|%s',
                urlencode($text),
                urlencode($langCode)
            );

            $context = stream_context_create([
                'http' => [
                    'timeout' => 5,
                    'method' => 'GET',
                    'header' => 'User-Agent: Pharmax-Translator/1.0'
                ]
            ]);

            $response = file_get_contents($url, false, $context);
            
            if ($response === false) {
                return $text; // Retourner l'original si l'API échoue
            }

            $data = json_decode($response, true);
            
            // Vérifier la réponse
            if ($data && isset($data['responseData']['translatedText'])) {
                return $data['responseData']['translatedText'];
            }

            return $text;
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
    public function translateToMultipleLangs(string $text, array $targetLangs = ['en', 'es', 'de']): array
    {
        $translations = [];
        foreach ($targetLangs as $lang) {
            $translations[$lang] = $this->translateText($text, $lang);
        }
        return $translations;
    }
}
