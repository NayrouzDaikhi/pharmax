<?php
// Comprehensive API tests

$baseUrl = 'http://127.0.0.1:8000/api/commentaires';

function testAPI($testName, $payload) {
    global $baseUrl;
    
    $options = [
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json' . "\r\n",
            'content' => json_encode($payload),
            'timeout' => 5
        ]
    ];
    
    $context = stream_context_create($options);
    try {
        $response = file_get_contents($baseUrl, false, $context);
        $data = json_decode($response, true);
        
        echo "\n✅ Test: $testName\n";
        echo "   Payload: " . json_encode($payload) . "\n";
        echo "   Response: " . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
        return true;
    } catch (Exception $e) {
        echo "\n❌ Test Failed: $testName\n";
        echo "   Error: " . $e->getMessage() . "\n";
        return false;
    }
}

echo "========================================\n";
echo "   PHARMAX API TEST SUITE\n";
echo "   Comment Creation Endpoint\n";
echo "========================================\n";

// Test 1: Valid comment
testAPI("Valid Comment", [
    'contenu' => 'This is a great article! Very informative and helpful.',
    'article_id' => 1
]);

// Test 2: Another valid comment
testAPI("Positive Comment", [
    'contenu' => 'Excellent work! I really enjoyed reading this.',
    'article_id' => 1
]);

// Test 3: Missing content
testAPI("Missing Content (Error Test)", [
    'article_id' => 1
]);

// Test 4: Missing article ID
testAPI("Missing Article ID (Error Test)", [
    'contenu' => 'This comment has no article'
]);

// Test 5: Non-existent article
testAPI("Non-existent Article ID (Error Test)", [
    'contenu' => 'Comment for non-existent article',
    'article_id' => 99999
]);

echo "\n========================================\n";
echo "   API TESTS COMPLETED\n";
echo "========================================\n";
?>
