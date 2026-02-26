<?php

/**
 * Test direct des endpoints API ChatBot
 */

echo "=== PHARMAX CHATBOT API - DIRECT TEST ===\n\n";

// Test 1: Health endpoint
echo "1. Testing Health Endpoint\n";
echo "URL: http://127.0.0.1:8000/api/chatbot/health\n";

try {
    $url = 'http://127.0.0.1:8000/api/chatbot/health';
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 5
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    
    if ($response === false) {
        echo "❌ Error: Could not connect to endpoint\n";
        if (isset($http_response_header)) {
            echo "Headers: " . print_r($http_response_header, true) . "\n";
        }
    } else {
        echo "✅ Response received:\n";
        echo "Headers: " . print_r($http_response_header, true) . "\n";
        echo "Body: " . $response . "\n";
        
        // Try to decode as JSON
        $decoded = json_decode($response, true);
        if ($decoded) {
            echo "\n✅ Valid JSON:\n";
            echo json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        } else {
            echo "\n❌ Not valid JSON. First 200 chars:\n";
            echo mb_substr($response, 0, 200) . "\n";
        }
    }
} catch (\Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("-", 50) . "\n\n";

// Test 2: Debug endpoint
echo "2. Testing Debug Endpoint\n";
echo "URL: http://127.0.0.1:8000/api/chatbot/debug\n";

try {
    $url = 'http://127.0.0.1:8000/api/chatbot/debug';
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 5
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    
    if ($response === false) {
        echo "❌ Error: Could not connect to endpoint\n";
    } else {
        echo "✅ Response received:\n";
        echo "First 200 chars: " . mb_substr($response, 0, 200) . "\n";
        
        $decoded = json_decode($response, true);
        if ($decoded) {
            echo "✅ Valid JSON\n";
        } else {
            echo "❌ Not valid JSON (HTML response)\n";
        }
    }
} catch (\Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("-", 50) . "\n\n";

// Test 3: Ask endpoint
echo "3. Testing Ask Endpoint\n";
echo "URL: http://127.0.0.1:8000/api/chatbot/ask\n";
echo "Method: POST\n";

try {
    $url = 'http://127.0.0.1:8000/api/chatbot/ask';
    
    $data = json_encode([
        'question' => 'What is this site about?',
        'article_id' => null,
        'article_title' => null
    ]);
    
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data)
            ],
            'content' => $data,
            'timeout' => 5
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    
    if ($response === false) {
        echo "❌ Error: Could not connect to endpoint\n";
    } else {
        echo "✅ Response received:\n";
        echo "First 200 chars: " . mb_substr($response, 0, 200) . "\n";
        
        $decoded = json_decode($response, true);
        if ($decoded) {
            echo "✅ Valid JSON\n";
        } else {
            echo "❌ Not valid JSON (HTML response)\n";
        }
    }
} catch (\Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
}

echo "\n=== END TEST ===\n";
?>
