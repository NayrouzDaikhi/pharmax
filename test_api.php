<?php
// Simple API test script

$url = 'http://127.0.0.1:8000/api/commentaires';
$payload = json_encode([
    'contenu' => 'This is a test comment to verify the API works!',
    'article_id' => 1
]);

$options = [
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json' . "\r\n",
        'content' => $payload,
        'timeout' => 5
    ]
];

$context = stream_context_create($options);
try {
    $response = file_get_contents($url, false, $context);
    echo "✅ API Response:\n";
    echo json_encode(json_decode($response), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
