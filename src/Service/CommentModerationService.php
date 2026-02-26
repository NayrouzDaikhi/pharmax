<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class CommentModerationService
{
    private HttpClientInterface $client;
    private string $apiKey;

    // Simple blacklist (fallback)
    private array $badWords = [
        // English words
        'fuck',
        'shit',
        'bitch',
        'asshole',
        'idiot',
        'stupid',
        'bastard',
        'hate',
        'terrible',
        'awful',
        'useless',
        'dumb',
        'worst',
        'disgusting',
        'offensive',
        // French words
        'connard',
        'connasse',
        'débile',
        'con',
        'salaud',
        'salope',
        'crétin',
        'imbécile',
        'putain',
        'foutre',
        'merde',
        'très con',
        'très débile',
        'très nul',
        'nul',
        'horrible',
        'dégueulasse',
        'ignoble',
        'immonde',
        'abominable',
        'haïr',
        'déteste',
        'détestable',
        'pire',
        'pourri',
        'pourrave',
        'craignos',
        'chelou',
        'chelou pas possible',
        'ouf',
        't\'es pas normal',
        't\'es fou',
        'es un fou',
        'c\'est de la merde',
        'quelle merde',
        'vraiment nul',
        'archi nul',
        'super nul',
    ];

    public function __construct(HttpClientInterface $client, string $huggingFaceApiKey)
    {
        $this->client = $client;
        $this->apiKey = $huggingFaceApiKey;
    }

    public function analyze(string $text): bool
    {
        // 🔴 1️⃣ FAST RULE-BASED CHECK (ALWAYS WORKS)
        $lowerText = strtolower($text);
        $normalizedText = $this->removeAccents($lowerText);
        
        foreach ($this->badWords as $word) {
            $normalizedWord = $this->removeAccents(strtolower($word));
            // Use word boundaries to avoid matching substrings (e.g., 'con' in 'contenue')
            // \b ensures the word is bounded by non-word characters or string boundaries
            $pattern = '/\b' . preg_quote($normalizedWord, '/') . '\b/i';
            if (preg_match($pattern, $normalizedText)) {
                return true; // BLOQUE immediately
            }
        }

        // 🟡 2️⃣ AI CHECK (BEST EFFORT)
        // Skip AI check if API key is not configured
        if (strpos($this->apiKey, 'your_') !== false || empty($this->apiKey)) {
            return false;
        }

        try {
            $response = $this->client->request(
                'POST',
                'https://api-inference.huggingface.co/models/unitary/toxic-bert',
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->apiKey,
                        'Content-Type' => 'application/json',
                    ],
                    'json' => [
                        'inputs' => $text,
                    ],
                    'timeout' => 5,
                    'max_duration' => 5,
                ]
            );

            $data = $response->toArray(false);

            if (!isset($data[0]) || !is_array($data[0])) {
                return false;
            }

            $blockedLabels = [
                'toxic',
                'severe_toxic',
                'obscene',
                'threat',
                'insult',
                'identity_hate'
            ];

            foreach ($data[0] as $result) {
                if (
                    isset($result['label'], $result['score']) &&
                    in_array($result['label'], $blockedLabels, true) &&
                    $result['score'] > 0.4
                ) {
                    return true;
                }
            }

            return false;
        } catch (\Throwable $e) {
            // 🔵 FAIL SAFE
            error_log('[AI MODERATION FAILED] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove accents from a string (é -> e, à -> a, etc.)
     */
    private function removeAccents(string $text): string
    {
        $replacements = [
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'à' => 'a', 'â' => 'a', 'ä' => 'a', 'á' => 'a',
            'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'ô' => 'o', 'ö' => 'o', 'ó' => 'o',
            'ì' => 'i', 'î' => 'i', 'ï' => 'i',
            'ç' => 'c',
            'ñ' => 'n',
        ];
        
        return strtr($text, $replacements);
    }
}
