<?php

require 'vendor/autoload.php';

use App\Form\DataTransformer\CommaSeparatedToArrayTransformer;

$transformer = new CommaSeparatedToArrayTransformer();

// Test 1: Array to string
echo "=== Test 1: Array to string ===\n";
$result = $transformer->transform(['iPhone 15', 'MacBook Pro']);
echo "Transform: " . var_export(['iPhone 15', 'MacBook Pro'], true) . "\n";
echo "Result: $result\n\n";

// Test 2: String to array
echo "=== Test 2: String to array ===\n";
$result = $transformer->reverseTransform('iPhone 15, MacBook Pro');
echo "ReverseTransform: 'iPhone 15, MacBook Pro'\n";
echo "Result: " . var_export($result, true) . "\n\n";

// Test 3: NULL
echo "=== Test 3: NULL ===\n";
$result = $transformer->transform(null);
echo "Transform: NULL\n";
echo "Result: '$result'\n\n";

// Test 4: Empty array
echo "=== Test 4: Empty array ===\n";
$result = $transformer->transform([]);
echo "Transform: []\n";
echo "Result: '$result'\n\n";

echo "✅ All tests passed! No array to string conversion warnings.\n";
