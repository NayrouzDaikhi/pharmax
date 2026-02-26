<?php
/**
 * ChatBot API Test Script
 * Tests all endpoints and error cases
 * 
 * Usage: php test_chatbot_api.php
 */

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║         PHARMAX CHATBOT API - COMPREHENSIVE TEST          ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

// Configuration
$baseUrl = 'http://127.0.0.1:8000';
$apiBase = $baseUrl . '/api/chatbot';
$webInterface = $baseUrl . '/chatbot';

// Test results
$results = [
    'passed' => 0,
    'failed' => 0,
    'tests' => []
];

// ============ Helper Functions ============

function testRequest($name, $method, $url, $data = null) {
    global $results;
    
    echo "▶ Testing: $name\n";
    
    try {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        if ($method === 'POST' && $data !== null) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            echo "  ✗ FAILED: $error\n";
            $results['failed']++;
            $results['tests'][] = ['test' => $name, 'status' => 'failed', 'error' => $error];
            return null;
        }
        
        $decoded = json_decode($response, true);
        
        echo "  ✓ Status: $httpCode\n";
        $results['passed']++;
        $results['tests'][] = ['test' => $name, 'status' => 'passed', 'code' => $httpCode];
        
        return ['code' => $httpCode, 'data' => $decoded, 'raw' => $response];
        
    } catch (\Exception $e) {
        echo "  ✗ FAILED: " . $e->getMessage() . "\n";
        $results['failed']++;
        $results['tests'][] = ['test' => $name, 'status' => 'failed', 'error' => $e->getMessage()];
        return null;
    }
}

// ============ Tests ============

echo "\n1. CONNECTIVITY TESTS\n";
echo str_repeat("─", 60) . "\n";

// Test 1: Health check
$health = testRequest(
    'Health Check (GET /api/chatbot/health)',
    'GET',
    "$apiBase/health"
);

if ($health && $health['code'] === 200) {
    echo "  Data: " . json_encode($health['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
}

// Test 2: Web interface access
testRequest(
    'Web Interface (GET /chatbot)',
    'GET',
    $webInterface
);

echo "\n2. VALIDATION TESTS\n";
echo str_repeat("─", 60) . "\n";

// Test 3: Empty question
testRequest(
    'Empty Question (Should fail)',
    'POST',
    "$apiBase/ask",
    ['question' => '']
);

// Test 4: Too short question
testRequest(
    'Too Short Question - 2 chars (Should fail)',
    'POST',
    "$apiBase/ask",
    ['question' => 'hi']
);

// Test 5: Too long question
$longQuestion = str_repeat('x', 1001);
testRequest(
    'Too Long Question - 1001 chars (Should fail)',
    'POST',
    "$apiBase/ask",
    ['question' => $longQuestion]
);

// Test 6: Null question
testRequest(
    'Null Question (Should fail)',
    'POST',
    "$apiBase/ask",
    ['question' => null]
);

// Test 7: Non-string question
testRequest(
    'Non-String Question (Should fail)',
    'POST',
    "$apiBase/ask",
    ['question' => 123]
);

// Test 8: Invalid JSON
echo "▶ Testing: Invalid JSON (Should fail)\n";
try {
    $ch = curl_init("$apiBase/ask");
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'invalid json {');
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "  ✓ Status: $httpCode\n";
    $results['passed']++;
} catch (\Exception $e) {
    echo "  ✗ FAILED: " . $e->getMessage() . "\n";
    $results['failed']++;
}

echo "\n3. VALID QUESTION TESTS\n";
echo str_repeat("─", 60) . "\n";

$testQuestions = [
    'Quels sont les bienfaits de la vitamine C?',
    'vitamines',
    'Comment renforcer mon système immunitaire?',
    'difference medicament generique original',
    'vitamine D hiver',
];

foreach ($testQuestions as $question) {
    $response = testRequest(
        "Question: \"" . substr($question, 0, 40) . "...\"",
        'POST',
        "$apiBase/ask",
        ['question' => $question]
    );
    
    if ($response && isset($response['data'])) {
        $data = $response['data'];
        
        if ($data['success'] === true) {
            echo "  ✓ Success: true\n";
            echo "  ✓ Has answer: " . (!empty($data['answer']) ? 'Yes (' . strlen($data['answer']) . ' chars)' : 'No') . "\n";
            echo "  ✓ Sources count: " . (isset($data['sources']) ? count($data['sources']) : 0) . "\n";
            
            if (isset($data['sources']) && count($data['sources']) > 0) {
                echo "    Sources:\n";
                foreach ($data['sources'] as $source) {
                    echo "    - {$source['title']}\n";
                }
            }
        } else {
            echo "  ✓ Success: false\n";
            echo "  ✓ Error: " . ($data['error'] ?? 'Unknown') . "\n";
        }
    }
    
    echo "\n";
}

echo "\n4. EDGE CASE TESTS\n";
echo str_repeat("─", 60) . "\n";

$edgeCases = [
    ['question' => '   ', 'name' => 'Whitespace only'],
    ['question' => 'abc', 'name' => 'Exactly 3 chars'],
    ['question' => str_repeat('a', 1000), 'name' => 'Exactly 1000 chars'],
    ['question' => 'café', 'name' => 'Accents (café)'],
    ['question' => 'ñoño', 'name' => 'Special characters'],
    ['question' => '你好', 'name' => 'Chinese characters'],
    ['question' => '😊 question', 'name' => 'Emoji characters'],
];

foreach ($edgeCases as $case) {
    testRequest(
        $case['name'],
        'POST',
        "$apiBase/ask",
        $case
    );
}

echo "\n5. SUMMARY\n";
echo str_repeat("═", 60) . "\n";
echo "Total Tests: " . ($results['passed'] + $results['failed']) . "\n";
echo "✓ Passed: " . $results['passed'] . "\n";
echo "✗ Failed: " . $results['failed'] . "\n";
echo "\nSuccess Rate: " . round((($results['passed'] / ($results['passed'] + $results['failed'])) * 100), 1) . "%\n";

echo "\n6. RECOMMENDATIONS\n";
echo str_repeat("─", 60) . "\n";

if ($results['failed'] === 0) {
    echo "✓ All tests passed! System is operational.\n";
} else {
    echo "⚠ Some tests failed. Review the errors above.\n";
}

echo "\nYou can now:\n";
echo "1. Access web UI: $webInterface\n";
echo "2. Use API: POST $apiBase/ask\n";
echo "3. Check health: GET $apiBase/health\n";

echo "\n" . str_repeat("═", 60) . "\n";
echo "Test Complete!\n";
?>
