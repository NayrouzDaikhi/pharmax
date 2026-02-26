<?php
// Direct test of Gemini API to diagnose the issue
$apiKey = 'AIzaSyCHx4_KxWzBuMb0aO0KPrnde4LkkH4gNhw';

echo "================================================================\n";
echo "🔍 GEMINI API DEBUG TEST\n";
echo "================================================================\n\n";

// Test 1: Check if it's a valid key format
echo "1️⃣ API Key Check:\n";
echo "   Key Length: " . strlen($apiKey) . " chars\n";
echo "   Key Format: " . (strpos($apiKey, 'AIza') === 0 ? "✅ Valid Google API key format" : "❌ Invalid format") . "\n\n";

// Test 2: Try Flash API
echo "2️⃣ Testing Gemini 1.5 Flash Model:\n";
$url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $apiKey;
echo "   URL: " . substr($url, 0, 80) . "...\n";

$payload = [
    'contents' => [
        ['parts' => [['text' => 'hello, say hi back briefly']]]
    ],
    'generationConfig' => [
        'temperature' => 0.7,
        'maxOutputTokens' => 100
    ]
];

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HEADER => true,
]);

$response = curl_exec($ch);
$info = curl_getinfo($ch);
curl_close($ch);

// Split headers and body
list($headerSection, $body) = explode("\r\n\r\n", $response, 2);
$httpCode = $info['http_code'];

echo "   HTTP Code: $httpCode\n";

if ($httpCode === 200) {
    echo "   ✅ SUCCESS!\n";
    $data = json_decode($body, true);
    if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
        echo "   Response: " . $data['candidates'][0]['content']['parts'][0]['text'] . "\n";
    }
} else {
    echo "   ❌ Failed\n";
    $data = json_decode($body, true);
    if (isset($data['error'])) {
        echo "   Error: " . json_encode($data['error'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    } else {
        echo "   Response: " . substr($body, 0, 300) . "\n";
    }
}

echo "\n3️⃣ Possible Issues:\n";
if ($httpCode === 404) {
    echo "   ⚠️  404 Not Found:\n";
    echo "      - Model endpoint might be incorrect\n";
    echo "      - API might have changed\n";
    echo "      - Check if Generative AI API is enabled in Google Cloud\n";
} else if ($httpCode === 401) {
    echo "   ⚠️  401 Unauthorized:\n";
    echo "      - API key is invalid or expired\n";
    echo "      - Get a new key from: https://aistudio.google.com/app/apikey\n";
} else if ($httpCode === 403) {
    echo "   ⚠️  403 Forbidden:\n";
    echo "      - Project lacks permissions\n";
    echo "      - Enable Generative AI API in Google Cloud Console\n";
    echo "      - Enable billing for your Google Cloud project\n";
} else if ($httpCode === 429) {
    echo "   ⚠️  429 Too Many Requests:\n";
    echo "      - Rate limit exceeded\n";
    echo "      - Wait a moment and try again\n";
}

echo "\n4️⃣ Solution:\n";
echo "   Go to: https://aistudio.google.com/app/apikey\n";
echo "   Create a new API key and update .env\n";
echo "\n================================================================\n";
