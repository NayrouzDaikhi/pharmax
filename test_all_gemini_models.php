<?php
/**
 * Test directo de modelos de Gemini API
 */

$apiKey = 'AIzaSyCHx4_KxWzBuMb0aO0KPrnde4LkkH4gNhw';

// Probar diferentes modelos
$models = [
    'gemini-1.5-flash',
    'gemini-1.5-pro',
    'gemini-1.0-pro',
    'gemini-pro',
    'gemini-pro-vision',
];

$prompt = "Hola, ¿eres una IA?";

foreach ($models as $model) {
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "Testing Model: $model\n";
    echo str_repeat("=", 60) . "\n";
    
    $url = "https://generativelanguage.googleapis.com/v1beta/models/$model:generateContent?key=$apiKey";
    
    $payload = json_encode([
        'contents' => [
            [
                'parts' => [
                    ['text' => $prompt]
                ]
            ]
        ],
        'generationConfig' => [
            'temperature' => 0.7,
            'maxOutputTokens' => 256,
        ]
    ]);
    
    echo "URL: $url\n";
    echo "Payload size: " . strlen($payload) . " bytes\n\n";
    
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => $payload,
            'timeout' => 15
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
        ]
    ]);
    
    echo "Sending request...\n";
    $response = @file_get_contents($url, false, $context);
    
    if (isset($http_response_header)) {
        $status = $http_response_header[0] ?? 'Unknown';
        echo "HTTP Status: $status\n";
        
        // Buscar el código de error
        if (preg_match('/(\d{3})/', $status, $matches)) {
            $code = $matches[1];
            if ($code === '200') {
                echo "✅ Success!\n";
            } elseif ($code === '404') {
                echo "❌ 404 - Model not found\n";
            } elseif ($code === '401') {
                echo "❌ 401 - Unauthorized (Invalid API Key?)\n";
            } elseif ($code === '403') {
                echo "❌ 403 - Forbidden (No access to this model)\n";
            }
        }
    }
    
    if ($response !== false) {
        $decoded = json_decode($response, true);
        
        if (isset($decoded['error'])) {
            echo "\nError Response:\n";
            echo json_encode($decoded['error'], JSON_PRETTY_PRINT) . "\n";
        } elseif (isset($decoded['candidates'][0]['content']['parts'][0]['text'])) {
            echo "\n✅ Got answer: " . substr($decoded['candidates'][0]['content']['parts'][0]['text'], 0, 100) . "...\n";
        }
    } else {
        echo "❌ No response received\n";
    }
    
    echo "\n";
}

echo str_repeat("=", 60) . "\n";
echo "Test Complete\n";
echo str_repeat("=", 60) . "\n";
?>
