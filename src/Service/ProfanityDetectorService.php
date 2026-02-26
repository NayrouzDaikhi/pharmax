<?php

namespace App\Service;

use ConsoleTVs\Profanity\Builder;

class ProfanityDetectorService
{
    public function containsProfanity(string $text): bool
    {
        // Utilise la bibliothèque consoletvs/profanity pour détecter les mots inappropriés
        // Par défaut, elle utilise une liste de mots dans plusieurs langues.
        // Vous pouvez spécifier des langues spécifiques si nécessaire, par exemple :
        // return Builder::blocker($text, languages: ['fr', 'en'])->isDirty();
        return !Builder::blocker($text)->clean();
    }

    public function getProfaneWords(string $text): array
    {
        return Builder::blocker($text)->badWords();
    }
}
