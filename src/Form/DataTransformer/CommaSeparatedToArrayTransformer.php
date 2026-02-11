<?php

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class CommaSeparatedToArrayTransformer implements DataTransformerInterface
{
    /**
     * Transforms an array to a comma-separated string for the form field.
     *
     * @param mixed $value
     * @return string
     */
    public function transform($value): string
    {
        if (null === $value) {
            return '';
        }

        // If it's already a string, return as-is
        if (is_string($value)) {
            return $value;
        }

        if (is_array($value)) {
            if (empty($value)) {
                return '';
            }

            // Check if it's an array of objects/arrays (new format)
            if (isset($value[0]) && (is_array($value[0]) || is_object($value[0]))) {
                $names = [];
                foreach ($value as $item) {
                    if (is_array($item) && isset($item['nom'])) {
                        $names[] = $item['nom'];
                    } elseif (is_object($item) && property_exists($item, 'nom')) {
                        $names[] = $item->nom;
                    } elseif (is_string($item)) {
                        $names[] = $item;
                    }
                }
                return implode(', ', $names);
            }

            // Old format: array of strings
            $stringValues = [];
            foreach ($value as $item) {
                if (is_string($item)) {
                    $stringValues[] = $item;
                }
            }
            return implode(', ', $stringValues);
        }

        return '';
    }

    /**
     * Transforms a comma-separated string to an array for the entity.
     *
     * @param mixed $value
     * @return array
     */
    public function reverseTransform($value): array
    {
        // If already an array, return as-is
        if (is_array($value)) {
            return array_filter($value, fn($v) => $v !== '' && $v !== null);
        }

        if (null === $value || '' === trim((string) $value)) {
            return [];
        }

        $items = array_map('trim', explode(',', (string) $value));
        $items = array_filter($items, fn($v) => $v !== '');

        return array_values($items);
    }
}

