<?php
/**
 * Quick connectivity test without waiting for Gemini
 */

$url = 'http://127.0.0.1:8000/api/chatbot/health';

$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'timeout' => 3
    ]
]);

echo "Testing API Health Endpoint...\n";
echo "URL: $url\n\n";

$response = @file_get_contents($url, false, $context);

if (isset($http_response_header)) {
    echo "Response Headers:\n";
    foreach ($http_response_header as $h) {
        echo "  " . $h . "\n";
    }
    echo "\n";
}

if ($response !== false) {
    echo "Response Body:\n";
    echo $response . "\n\n";
    
    $decoded = json_decode($response, true);
    if ($decoded && isset($decoded['status'])) {
        echo "✅ API is WORKING!\n";
        echo "API Status: " . $decoded['status'] . "\n";
        echo "API Configured: " . ($decoded['api_configured'] ? 'YES' : 'NO') . "\n";
    }
} else {
    echo "❌ Failed to connect\n";
}
?>
