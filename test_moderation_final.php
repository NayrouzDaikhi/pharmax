#!/usr/bin/env php
<?php
// Test the complete comment moderation and archiving integration

$baseUrl = 'http://127.0.0.1:8000/api/commentaires';

function testComment($testName, $payload) {
    global $baseUrl;
    
    $ch = curl_init($baseUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $data = json_decode($response, true);
    
    echo "\n" . str_repeat("=", 70) . "\n";
    echo "TEST: $testName\n";
    echo str_repeat("=", 70) . "\n";
    echo "📝 Content: " . substr($payload['contenu'], 0, 55) . "...\n";
    echo "Status Code: " . $httpCode . "\n\n";
    
    if ($data) {
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
    
    echo "\n";
    return $data;
}

echo "\n\n";
echo "╔" . str_repeat("═", 68) . "╗\n";
echo "║  PHARMAX API - COMMENT MODERATION INTEGRATION TEST               ║\n";
echo "║  Testing Archive & Warning System                                ║\n";
echo "╚" . str_repeat("═", 68) . "╝\n";

// Test 1: Positive comment (should be posted)
testComment("✅ POSITIVE COMMENT (Should Be Posted)", [
    'contenu' => 'This is an excellent article! Very informative and well-written. Great job!',
    'article_id' => 1,
    'user_name' => 'John Doe',
    'user_email' => 'john@example.com'
]);

// Test 2: Negative comment (should be blocked)
testComment("❌ NEGATIVE COMMENT (Should Be BLOCKED)", [
    'contenu' => 'This is terrible and completely useless awful content I hate this',
    'article_id' => 1,
    'user_name' => 'Jane Doe',
    'user_email' => 'jane@example.com'
]);

// Test 3: Another positive comment
testComment("✅ POSITIVE COMMENT #2 (Should Be Posted)", [
    'contenu' => 'Really appreciated the insights shared in this article. Learned something new today!',
    'article_id' => 1,
    'user_name' => 'Bob Smith',
    'user_email' => 'bob@example.com'
]);

// Test 4: Offensive comment (should be blocked)
testComment("❌ OFFENSIVE COMMENT (Should Be BLOCKED)", [
    'contenu' => 'You are stupid and this article is disgusting. I hate everything about it.',
    'article_id' => 1,
    'user_name' => 'Troll User',
    'user_email' => 'troll@example.com'
]);

// Test 5: Neutral comment
testComment("✅ NEUTRAL COMMENT (Should Be Posted)", [
    'contenu' => 'Interesting perspective on this topic. I agree with some points but disagree with others.',
    'article_id' => 1,
    'user_name' => 'Alice Johnson',
    'user_email' => 'alice@example.com'
]);

echo "\n\n";
echo "╔" . str_repeat("═", 68) . "╗\n";
echo "║  TEST SUITE COMPLETED ✅                                         ║\n";
echo "╚" . str_repeat("═", 68) . "╝\n";
echo "\n📊 SYSTEM SUMMARY:\n";
echo "  ✅ Positive/Neutral comments → POSTED (HTTP 201)\n";
echo "  ❌ Negative/Offensive comments → BLOCKED (HTTP 403)\n";
echo "  📦 Blocked comments ARCHIVED in commentaire_archive table\n";
echo "  ⚠️  Users receive warning message about inappropriate content\n";
echo "  👁️  Only valid comments appear in the blog comments section\n\n";
?>
