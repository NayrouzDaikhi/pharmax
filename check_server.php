<?php
/**
 * Simple connectivity test
 */
$url = 'http://127.0.0.1:8000/api/chatbot/health';
$response = @file_get_contents($url);

if ($response) {
    echo "✅ Server is RUNNING\n";
    $data = json_decode($response, true);
    echo "API Status: " . ($data['status'] ?? 'unknown') . "\n";
} else {
    echo "❌ Server is NOT RUNNING\n";
    echo "Please start the server with: symfony server:start --no-tls --allow-http\n";
}
?>
