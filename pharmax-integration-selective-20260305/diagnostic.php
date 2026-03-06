#!/usr/bin/env php
<?php
/**
 * Pharmax Chatbot - Diagnostic Check Script
 * Checks if all components are properly configured
 */

echo "\n╔════════════════════════════════════════════════════════════════╗\n";
echo "║         PHARMAX CHATBOT - DIAGNOSTIC CHECK                    ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// Check 1: Verify Symfony Cache
echo "[1/6] Checking Symfony Cache...\n";
$cacheDir = __DIR__ . '/var/cache/dev';
if (is_dir($cacheDir)) {
    echo "    ✅ Cache directory exists\n";
} else {
    echo "    ⚠️  Cache directory needs to be created\n";
}

// Check 2: Verify OllamaService exists
echo "\n[2/6] Checking OllamaService...\n";
$ollamaService = __DIR__ . '/src/Service/OllamaService.php';
if (file_exists($ollamaService)) {
    echo "    ✅ OllamaService.php exists\n";
} else {
    echo "    ❌ OllamaService.php not found\n";
}

// Check 3: Verify ChatBotService uses Ollama
echo "\n[3/6] Checking ChatBotService...\n";
$chatbotService = __DIR__ . '/src/Service/ChatBotService.php';
if (file_exists($chatbotService)) {
    $content = file_get_contents($chatbotService);
    if (strpos($content, 'OllamaService') !== false) {
        echo "    ✅ ChatBotService uses OllamaService\n";
    } else {
        echo "    ❌ ChatBotService does not use OllamaService\n";
    }
} else {
    echo "    ❌ ChatBotService.php not found\n";
}

// Check 4: Verify routes
echo "\n[4/6] Checking Routes...\n";
$chatbotController = __DIR__ . '/src/Controller/ChatBotController.php';
if (file_exists($chatbotController)) {
    $content = file_get_contents($chatbotController);
    if (strpos($content, 'chatbot_index') !== false || strpos($content, "name: 'index'") !== false) {
        echo "    ✅ ChatBot controller routes defined\n";
    } else {
        echo "    ⚠️  ChatBot controller route name unclear\n";
    }
} else {
    echo "    ❌ ChatBotController.php not found\n";
}

// Check 5: Verify HuggingFace Moderation still works
echo "\n[5/6] Checking Comment Moderation...\n";
$moderationService = __DIR__ . '/src/Service/CommentModerationService.php';
if (file_exists($moderationService)) {
    $content = file_get_contents($moderationService);
    if (strpos($content, 'huggingface') !== false || strpos($content, 'huggingFace') !== false) {
        echo "    ✅ CommentModerationService uses HuggingFace\n";
    }
} else {
    echo "    ❌ CommentModerationService.php not found\n";
}

// Check 6: Test Ollama connection
echo "\n[6/6] Checking Ollama Service...\n";
$ch = curl_init('http://localhost:11434/api/tags');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 2);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);

$result = @curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    $data = json_decode($result, true);
    if (!empty($data['models'])) {
        echo "    ✅ Ollama is RUNNING\n";
        echo "    ✅ Models available: " . count($data['models']) . "\n";
        foreach ($data['models'] as $model) {
            echo "       - " . $model['name'] . "\n";
        }
    } else {
        echo "    ⚠️  Ollama running but no models found\n";
        echo "    💡 Run: ollama pull mistral\n";
    }
} else {
    echo "    ❌ Ollama NOT RUNNING on localhost:11434\n";
    echo "    💡 Start Ollama: ollama serve\n";
}

echo "\n╔════════════════════════════════════════════════════════════════╗\n";
echo "║         DIAGNOSTIC COMPLETE                                   ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// Recommendation
echo "📋 NEXT STEPS:\n\n";
echo "1️⃣  If Ollama is NOT running:\n";
echo "   • Download from: https://ollama.com/download\n";
echo "   • Run: ollama pull mistral\n";
echo "   • Start: ollama serve\n\n";

echo "2️⃣  Test the chatbot:\n";
echo "   • Visit: http://127.0.0.1:8000/chatbot\n\n";

echo "3️⃣  Check logs if something fails:\n";
echo "   • Symfony dev logs: var/log/dev.log\n";
echo "   • Check Ollama is running: curl http://localhost:11434/api/tags\n\n";
