<?php

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class CommaSeparatedToArrayTransformer implements DataTransformerInterface
{
    /**
     * Transforms an array to a comma-separated string for the form field.
     *
     * @param array|null $value
     * @return string
     */
    public function transform($value): string
    {
        if (null === $value) {
            return '';
        }

        if (is_array($value)) {
            return implode(', ', $value);
        }

        return (string) $value;
    }

    /**
     * Transforms a comma-separated string to an array for the entity.
     *
     * @param string|null $value
     * @return array
     */
    public function reverseTransform($value): array
    {
        if (null === $value || '' === trim((string) $value)) {
            return [];
        }

        $items = array_map('trim', explode(',', (string) $value));
        $items = array_filter($items, fn($v) => $v !== '');

        return array_values($items);
    }
}
