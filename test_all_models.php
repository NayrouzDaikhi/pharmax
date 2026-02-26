<?php
// Test all available Gemini models to find which works
$apiKey = 'AIzaSyBwGH6ARsWYZDgrN4ikBBiQXvJWHO5t2Cs';

$models = [
    'https://generativelanguage.googleapis.com/v1/models/gemini-pro:generateContent',
    'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-pro:generateContent',
    'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent',
    'https://generativelanguage.googleapis.com/v1/models/gemini-1.0-pro:generateContent',
    'https://generativelanguage.googleapis.com/v1/models/text-bison-001:generateContent',
];

echo "================================================================\n";
echo "🔍 TESTING ALL GEMINI MODELS\n";
echo "================================================================\n\n";

$testPrompt = [
    'contents' => [
        ['parts' => [['text' => 'Hello! Respond briefly in one sentence.']]]
    ],
    'generationConfig' => [
        'temperature' => 0.7,
        'maxOutputTokens' => 100
    ]
];

foreach ($models as $url) {
    echo "Testing: " . str_replace('https://generativelanguage.googleapis.com', '', $url) . "\n";
    
    $fullUrl = $url . '?key=' . $apiKey;
    
    $ch = curl_init($fullUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($testPrompt),
        CURLOPT_TIMEOUT => 10,
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "   Status: $httpCode";
    
    if ($httpCode === 200) {
        echo " ✅ SUCCESS!\n";
        $data = json_decode($response, true);
        if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            echo "   Response: " . substr($data['candidates'][0]['content']['parts'][0]['text'], 0, 100) . "\n";
        }
    } else {
        $data = json_decode($response, true);
        if (isset($data['error']['message'])) {
            echo " ❌ Error: " . substr($data['error']['message'], 0, 50) . "...\n";
        } else {
            echo " ❌ Failed\n";
        }
    }
    echo "\n";
}

echo "================================================================\n";
echo "✨ Update src/Service/ChatBotService.php with the working URL\n";
echo "================================================================\n";
