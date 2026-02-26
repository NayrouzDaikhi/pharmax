<?php
/**
 * Quick test of API endpoints
 */

$tests = [
    [
        'name' => 'Health Check',
        'url' => 'http://127.0.0.1:8000/api/chatbot/health',
        'method' => 'GET',
        'data' => null
    ],
    [
        'name' => 'Debug',
        'url' => 'http://127.0.0.1:8000/api/chatbot/debug',
        'method' => 'GET',
        'data' => null
    ],
    [
        'name' => 'Ask Question',
        'url' => 'http://127.0.0.1:8000/api/chatbot/ask',
        'method' => 'POST',
        'data' => json_encode(['question' => 'What is this site?'])
    ]
];

foreach ($tests as $test) {
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "Test: {$test['name']}\n";
    echo "URL: {$test['url']}\n";
    echo "Method: {$test['method']}\n";
    
    $context = stream_context_create([
        'http' => [
            'method' => $test['method'],
            'header' => $test['data'] ? 'Content-Type: application/json' : '',
            'content' => $test['data'],
            'timeout' => 5
        ]
    ]);
    
    $response = @file_get_contents($test['url'], false, $context);
    
    if ($response === false) {
        echo "Status: ERROR - Could not connect\n";
        if (isset($http_response_header)) {
            echo "Headers: " . implode(", ", $http_response_header) . "\n";
        }
    } else {
        // Extract status code from headers
        $status = 'Unknown';
        if (isset($http_response_header)) {
            foreach ($http_response_header as $h) {
                if (strpos($h, 'HTTP') === 0) {
                    $status = $h;
                    break;
                }
            }
        }
        echo "Status: $status\n";
        
        // Check if JSON
        $decoded = json_decode($response, true);
        if ($decoded) {
            echo "✅ Valid JSON Response:\n";
            echo json_encode($decoded, JSON_PRETTY_PRINT) . "\n";
        } else {
            echo "❌ Not JSON. First 300 chars:\n";
            echo substr($response, 0, 300) . "\n";
        }
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "Test Complete\n";
?>
