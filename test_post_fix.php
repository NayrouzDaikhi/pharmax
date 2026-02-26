<?php
/**
 * Test del endpoint POST ask después de cambiar el modelo de Gemini
 */

echo "Testing ChatBot API with Gemini 1.5 Pro\n";
echo str_repeat("=", 60) . "\n\n";

$testCases = [
    [
        'name' => 'Con Article ID (Gemini 1.5 Pro)',
        'data' => [
            'question' => 'Cuáles son los beneficios?',
            'article_id' => 1,
            'article_title' => 'Article 1'
        ]
    ]
];

foreach ($testCases as $test) {
    echo "Test: " . $test['name'] . "\n";
    echo str_repeat("-", 60) . "\n";
    
    $url = 'http://127.0.0.1:8000/api/chatbot/ask';
    $jsonData = json_encode($test['data']);
    
    echo "Sending: " . $jsonData . "\n\n";
    echo "Processing (waiting for Gemini response)...\n\n";
    
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => $jsonData,
            'timeout' => 45  // Esperar más tiempo para Gemini 1.5 Pro
        ]
    ]);
    
    $startTime = microtime(true);
    $response = @file_get_contents($url, false, $context);
    $elapsed = microtime(true) - $startTime;
    
    if (isset($http_response_header)) {
        $statusLine = $http_response_header[0] ?? 'Unknown';
        echo "Status: $statusLine\n";
        echo "Response time: " . number_format($elapsed, 2) . " seconds\n\n";
    }
    
    if ($response !== false) {
        $decoded = json_decode($response, true);
        
        if ($decoded) {
            if ($decoded['success']) {
                echo "✅ SUCCESS\n\n";
                echo "Answer:\n" . $decoded['answer'] . "\n";
                
                if (isset($decoded['sources'])) {
                    echo "\n📚 Sources:\n";
                    foreach ($decoded['sources'] as $source) {
                        echo "  - " . $source['title'] . " (ID: " . $source['id'] . ")\n";
                    }
                }
            } else {
                echo "❌ Error: " . $decoded['error'] . "\n";
            }
        } else {
            echo "❌ Invalid JSON: " . substr($response, 0, 200) . "\n";
        }
    } else {
        echo "❌ Failed to connect to server\n";
    }
    
    echo "\n" . str_repeat("=", 60) . "\n\n";
}
?>
