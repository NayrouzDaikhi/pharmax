<?php
/**
 * COMPREHENSIVE GEMINI API DIAGNOSTIC
 * Tests all possible Gemini endpoints and configurations
 */

$keys = [
    'AIzaSyCHx4_KxWzBuMb0aO0KPrnde4LkkH4gNhw',
    'AIzaSyBwGH6ARsWYZDgrN4ikBBiQXvJWHO5t2Cs',
];

echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║      🔍 COMPREHENSIVE GEMINI API DIAGNOSTIC                    ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// Test endpoints
$endpoints = [
    // Google AI Studio API (free tier)
    'https://generativelanguage.googleapis.com/v1/models/gemini-pro:generateContent' => 'Google AI Studio - Gemini Pro',
    'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-pro:generateContent' => 'v1beta - Gemini 1.5 Pro',
    'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent' => 'v1beta - Gemini 1.5 Flash',
    'https://generativelanguage.googleapis.com/v1/models/gemini-1.0-pro:generateContent' => 'v1 - Gemini 1.0 Pro',
    'https://generativelanguage.googleapis.com/v1/models/text-bison-001:generateContent' => 'v1 - Text Bison',
    'https://generativelanguage.googleapis.com/v1alpha/models/gemini-pro:generateContent' => 'v1alpha - Gemini Pro',
];

$payload = [
    'contents' => [
        ['parts' => [['text' => 'Hello, say hi back']]]
    ],
    'generationConfig' => [
        'temperature' => 0.7,
        'maxOutputTokens' => 50
    ]
];

$jsonPayload = json_encode($payload);

foreach ($keys as $keyIndex => $apiKey) {
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "Testing with API Key #" . ($keyIndex + 1) . ": " . substr($apiKey, 0, 10) . "...\n";
    echo "═══════════════════════════════════════════════════════════════\n\n";
    
    $workingEndpoint = null;
    
    foreach ($endpoints as $url => $description) {
        $fullUrl = $url . '?key=' . $apiKey;
        
        echo "Testing: $description\n";
        echo "  URL: " . str_replace('?key=' . $apiKey, '', $fullUrl) . "\n";
        
        // Make request
        $ch = curl_init($fullUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => $jsonPayload,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        // Parse response
        $data = json_decode($response, true);
        
        if ($httpCode === 200) {
            echo "  Status: ✅ 200 OK - SUCCESS!\n";
            if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                echo "  Response: \"" . substr($data['candidates'][0]['content']['parts'][0]['text'], 0, 60) . "...\"\n";
                $workingEndpoint = $url;
            }
        } else if ($httpCode === 400) {
            $errorMsg = $data['error']['message'] ?? 'Unknown error';
            echo "  Status: ❌ 400 Bad Request\n";
            echo "  Error: " . substr($errorMsg, 0, 80) . "\n";
        } else if ($httpCode === 401) {
            echo "  Status: ❌ 401 Unauthorized - Invalid API Key\n";
        } else if ($httpCode === 403) {
            $errorMsg = $data['error']['message'] ?? 'Permission denied';
            echo "  Status: ❌ 403 Forbidden\n";
            echo "  Error: " . substr($errorMsg, 0, 80) . "\n";
        } else if ($httpCode === 404) {
            $errorMsg = $data['error']['message'] ?? 'Not found';
            echo "  Status: ❌ 404 Not Found\n";
            if (strpos($errorMsg, 'model') !== false) {
                echo "  Error: Model not available for this project\n";
            }
        } else if ($httpCode === 429) {
            echo "  Status: ❌ 429 Rate Limited\n";
        } else if ($httpCode === 500) {
            echo "  Status: ❌ 500 Server Error\n";
        } else {
            echo "  Status: ❌ HTTP $httpCode\n";
        }
        
        if ($curlError) {
            echo "  cURL Error: $curlError\n";
        }
        
        echo "\n";
    }
    
    if ($workingEndpoint) {
        echo "✅ FOUND WORKING ENDPOINT for Key #" . ($keyIndex + 1) . "!\n";
        echo "Update ChatBotService.php with:\n";
        echo "  URL: $workingEndpoint\n\n";
        break;
    }
}

echo "═══════════════════════════════════════════════════════════════\n";
echo "📋 DIAGNOSIS & SOLUTION:\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "If ALL endpoints return 404:\n";
echo "  ⚠️  Your API key's project doesn't have Generative AI API enabled\n\n";
echo "SOLUTION:\n";
echo "  1. Go to: https://console.cloud.google.com/apis/library\n";
echo "  2. Search for: \"Generative AI API\" or \"generativelanguage.googleapis.com\"\n";
echo "  3. Click on it and press \"ENABLE\"\n";
echo "  4. Wait 2-3 minutes for API to activate\n";
echo "  5. Re-run this test\n\n";

echo "If you get 401 (Unauthorized):\n";
echo "  ⚠️  API key is invalid or expired\n";
echo "  Get new key: https://aistudio.google.com/app/apikey\n\n";

echo "If you get 403 (Forbidden):\n";
echo "  ⚠️  Project exists but needs billing setup\n";
echo "  Add payment method in Google Cloud Console\n\n";

echo "═══════════════════════════════════════════════════════════════\n";
