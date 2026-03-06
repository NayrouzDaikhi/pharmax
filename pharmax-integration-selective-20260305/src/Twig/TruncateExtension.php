<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TruncateExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('truncate', [$this, 'truncate']),
        ];
    }

    /**
     * Truncate a string to a given length
     * 
     * @param string $text The text to truncate
     * @param int $length The maximum length
     * @param string $suffix The suffix to add if truncated
     * @return string The truncated text
     */
    public function truncate(string $text, int $length = 100, string $suffix = '...'): string
    {
        if (mb_strlen($text) <= $length) {
            return $text;
        }

        return mb_substr($text, 0, $length) . $suffix;
    }
}
