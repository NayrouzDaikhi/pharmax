<?php

namespace App\Service;

class ProfanityDetectorService
{
    /**
     * List of profane words in multiple languages (simplified)
     */
    private array $profaneWords = [
        // English
        'bad', 'damn', 'hell', 'crap', 'ass', 'bastard', 'bitch',
        // French
        'merde', 'con', 'débile', 'connard', 'salaud', 'putain',
        // Arabic (transliteration examples)
        'qahwa', 'kus',
    ];

    public function __construct()
    {
        // Load profane words from environment or default list
    }

    /**
     * Check if text contains profane words
     */
    public function containsProfanity(string $text): bool
    {
        $text = strtolower($text);
        
        foreach ($this->profaneWords as $word) {
            if (strpos($text, strtolower($word)) !== false) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get list of profane words found in text
     */
    public function getProfaneWords(string $text): array
    {
        $text = strtolower($text);
        $found = [];
        
        foreach ($this->profaneWords as $word) {
            if (strpos($text, strtolower($word)) !== false) {
                $found[] = $word;
            }
        }
        
        return $found;
    }

    /**
     * Add custom profane words to the detector
     */
    public function addProfaneWords(array $words): void
    {
        $this->profaneWords = array_merge($this->profaneWords, $words);
    }

    /**
     * Check if text is clean (no profanity)
     */
    public function isClean(string $text): bool
    {
        return !$this->containsProfanity($text);
    }
}
