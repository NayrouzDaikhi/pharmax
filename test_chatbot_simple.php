<?php

// Test simple du ChatBot API

$url = 'http://127.0.0.1:8000/api/chatbot/ask';

$data = [
    'question' => 'resumé cet article',
    'article_id' => 1,
    'article_title' => 'test article'
];

$jsonData = json_encode($data);

echo "=== Testing ChatBot API ===\n\n";
echo "URL: $url\n";
echo "Request Data: " . print_r($data, true) . "\n";
echo "JSON: $jsonData\n\n";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($jsonData)
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Curl Error: " . ($curlError ?: 'None') . "\n\n";

echo "Response:\n";
echo $response . "\n\n";

if ($response) {
    $decoded = json_decode($response, true);
    if ($decoded) {
        echo "Decoded Response:\n";
        print_r($decoded);
    } else {
        echo "Failed to decode response as JSON\n";
        echo "JSON Error: " . json_last_error_msg() . "\n";
    }
}

// Test health endpoint
echo "\n\n=== Testing Health Endpoint ===\n\n";

$healthUrl = 'http://127.0.0.1:8000/api/chatbot/health';

$ch = curl_init($healthUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

$healthResponse = curl_exec($ch);
$healthCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$healthError = curl_error($ch);
curl_close($ch);

echo "URL: $healthUrl\n";
echo "HTTP Code: $healthCode\n";
echo "Curl Error: " . ($healthError ?: 'None') . "\n";
echo "Response: $healthResponse\n\n";

if ($healthResponse) {
    $decoded = json_decode($healthResponse, true);
    if ($decoded) {
        echo "Decoded Response:\n";
        print_r($decoded);
    }
}
