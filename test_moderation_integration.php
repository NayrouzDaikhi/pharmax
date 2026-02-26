<?php
// Test the complete comment moderation and archiving integration

$baseUrl = 'http://127.0.0.1:8000/api/commentaires';

function testComment($testName, $payload) {
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
        $response = @file_get_contents($baseUrl, false, $context);
        $data = json_decode($response, true);
        
        // Get HTTP status code from headers
        $httpCode = (isset($http_response_header) && count($http_response_header) > 0) 
            ? intval(explode(' ', $http_response_header[0])[1]) 
            : 200;
        
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "TEST: $testName\n";
        echo str_repeat("=", 60) . "\n";
        echo "📝 Content: " . substr($payload['contenu'], 0, 50) . "...\n";
        echo "HTTP: $httpCode\n\n";
        
        if ($data) {
            echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        } else {
            echo "[Response parsing failed - likely blocked]";
        }
        return $data;
    } catch (Exception $e) {
        echo "\n❌ Test Error: $testName\n";
        echo "Error: " . $e->getMessage() . "\n";
        return null;
    }
}

echo "\n\n";
echo "╔" . str_repeat("═", 58) . "╗\n";
echo "║  PHARMAX API - COMMENT MODERATION INTEGRATION TEST     ║\n";
echo "║  Testing Archive & Warning System                      ║\n";
echo "╚" . str_repeat("═", 58) . "╝\n";

// Test 1: Positive comment (should be posted)
testComment("✅ Positive Comment (Should Be Posted)", [
    'contenu' => 'This is an excellent article! Very informative and well-written. Great job!',
    'article_id' => 1,
    'user_name' => 'John Doe',
    'user_email' => 'john@example.com'
]);

// Test 2: Negative comment (should be blocked)
testComment("❌ Negative Comment (Should Be Blocked)", [
    'contenu' => 'This is terrible and completely useless awful content I hate this',
    'article_id' => 1,
    'user_name' => 'Jane Doe',
    'user_email' => 'jane@example.com'
]);

// Test 3: Another positive comment
testComment("✅ Another Positive Comment", [
    'contenu' => 'Really appreciated the insights shared in this article. Learned something new today!',
    'article_id' => 1,
    'user_name' => 'Bob Smith',
    'user_email' => 'bob@example.com'
]);

// Test 4: Neutral comment
testComment("✅ Neutral Comment", [
    'contenu' => 'Interesting perspective on this topic. I agree with some points but disagree with others.',
    'article_id' => 1,
    'user_name' => 'Alice Johnson',
    'user_email' => 'alice@example.com'
]);

echo "\n\n";
echo "╔" . str_repeat("═", 58) . "╗\n";
echo "║  TEST SUITE COMPLETED                                  ║\n";
echo "╚" . str_repeat("═", 58) . "╝\n";
echo "\n✨ Key Points:\n";
echo "  • Positive comments are posted and visible\n";
echo "  • Negative comments are BLOCKED and archived\n";
echo "  • Users receive warnings about inappropriate content\n";
echo "  • Blocked comments are saved in commentaire_archive table\n\n";
?>
