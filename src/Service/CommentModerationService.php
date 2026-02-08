<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class CommentModerationService
{
    private HttpClientInterface $client;
    private string $apiKey;

    // Simple blacklist (fallback)
    private array $badWords = [
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
        'offensive'
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
        foreach ($this->badWords as $word) {
            if (str_contains($lowerText, $word)) {
                return true; // BLOQUE immediately
            }
        }

        // 🟡 2️⃣ AI CHECK (BEST EFFORT)
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
                    'timeout' => 30,
                    'max_duration' => 30,
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
}
