<?php
/**
 * Test Gemini API con el modelo correcto
 */

$apiKey = 'AIzaSyCHx4_KxWzBuMb0aO0KPrnde4LkkH4gNhw';
$url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-pro:generateContent?key=' . $apiKey;

$prompt = "Tu eres un asistente de farmacia. Responde brevemente: ¿Cuáles son los beneficios de las vitaminas?";

$data = json_encode([
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

echo "Testing Gemini 1.5 Pro API\n";
echo "URL: " . $url . "\n";
echo "Payload size: " . strlen($data) . " bytes\n\n";

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => $data,
        'timeout' => 30
    ],
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
    ]
]);

echo "Sending request...\n";
$response = @file_get_contents($url, false, $context);

if ($response === false) {
    echo "❌ Failed to get response\n";
    if (isset($http_response_header)) {
        echo "Headers: " . implode("\n", $http_response_header) . "\n";
    }
} else {
    echo "✅ Got response\n\n";
    
    if (isset($http_response_header)) {
        echo "Status: " . $http_response_header[0] . "\n\n";
    }
    
    $decoded = json_decode($response, true);
    
    if ($decoded) {
        if (isset($decoded['error'])) {
            echo "❌ API Error: " . json_encode($decoded['error'], JSON_PRETTY_PRINT) . "\n";
        } else if (isset($decoded['candidates'][0]['content']['parts'][0]['text'])) {
            echo "✅ Success!\n\n";
            echo "Response:\n";
            echo $decoded['candidates'][0]['content']['parts'][0]['text'] . "\n";
        } else {
            echo "⚠️ Unexpected format:\n";
            echo json_encode($decoded, JSON_PRETTY_PRINT) . "\n";
        }
    } else {
        echo "❌ Invalid JSON response:\n";
        echo $response . "\n";
    }
}
?>
