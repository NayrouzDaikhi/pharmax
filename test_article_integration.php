<?php
/**
 * Test del endpoint ask con article_id
 */

$url = 'http://127.0.0.1:8000/api/chatbot/ask';

// Test 1: Con article_id y article_title
$testCases = [
    [
        'name' => 'Con Article ID (debe recuperar artículo 1)',
        'data' => [
            'question' => 'Cuáles son los beneficios?',
            'article_id' => 1,
            'article_title' => 'Article 1'
        ]
    ],
    [
        'name' => 'Sin Article ID (búsqueda por palabras clave)',
        'data' => [
            'question' => 'información sobre medicamentos',
            'article_id' => null,
            'article_title' => null
        ]
    ],
    [
        'name' => 'Con Article ID válido',
        'data' => [
            'question' => '¿qué contiene este artículo?',
            'article_id' => 2,
            'article_title' => 'Article 2'
        ]
    ]
];

foreach ($testCases as $test) {
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "Test: " . $test['name'] . "\n";
    echo str_repeat("=", 60) . "\n";
    
    $jsonData = json_encode($test['data']);
    
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => $jsonData,
            'timeout' => 30  // Esperar 30 segundos para Gemini
        ]
    ]);
    
    echo "Request: " . json_encode($test['data']) . "\n\n";
    echo "Processing... (this may take up to 30 seconds for AI response)\n";
    
    $response = @file_get_contents($url, false, $context);
    
    if (isset($http_response_header)) {
        $statusLine = $http_response_header[0] ?? 'Unknown';
        echo "Status: $statusLine\n";
    }
    
    if ($response !== false) {
        $decoded = json_decode($response, true);
        
        if ($decoded) {
            echo "Success: " . ($decoded['success'] ? 'YES ✓' : 'NO ✗') . "\n";
            
            if ($decoded['success']) {
                echo "\nAnswer:\n" . substr($decoded['answer'], 0, 300) . "...\n";
                
                if (isset($decoded['sources'])) {
                    echo "\nSources:\n";
                    foreach ($decoded['sources'] as $source) {
                        echo "  - " . $source['title'] . " (ID: " . $source['id'] . ")\n";
                    }
                }
            } else {
                echo "Error: " . ($decoded['error'] ?? 'Unknown error') . "\n";
            }
        } else {
            echo "Response: " . substr($response, 0, 200) . "\n";
        }
    } else {
        echo "Failed to connect\n";
    }
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "Test Complete\n";
echo str_repeat("=", 60) . "\n";
?>
