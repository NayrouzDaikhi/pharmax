<?php
/**
 * Test Ask endpoint and capture the error response
 */

$url = 'http://127.0.0.1:8000/api/chatbot/ask';
$data = json_encode(['question' => 'What is this site?']);

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => $data,
        'timeout' => 5
    ]
]);

echo "Testing POST /api/chatbot/ask\n";
echo "Data: " . $data . "\n";
echo str_repeat("-", 50) . "\n";

$response = @file_get_contents($url, false, $context);

// Get status from header
if (isset($http_response_header)) {
    foreach ($http_response_header as $h) {
        if (strpos($h, 'HTTP') === 0) {
            echo "HTTP Status: " . $h . "\n";
            break;
        }
    }
}

if ($response) {
    echo "Response Body:\n";
    echo $response . "\n";
    
    $decoded = json_decode($response, true);
    if ($decoded) {
        echo "\nParsed JSON:\n";
        echo json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    }
} else {
    echo "No response body\n";
}
?>
