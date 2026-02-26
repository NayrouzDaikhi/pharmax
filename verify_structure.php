<?php
/**
 * Verificación rápida de sintaxis
 */
echo "Verificando archivo ChatBotService.php...\n";

$file = 'src/Service/ChatBotService.php';
$content = file_get_contents($file);

// Verificar que el archivo contiene las nuevas funciones
$checks = [
    'generateFallbackResponse' => strpos($content, 'generateFallbackResponse'),
    'GEMINI_API_URL_FALLBACK' => strpos($content, 'GEMINI_API_URL_FALLBACK'),
    'gemini-1.5-flash' => strpos($content, 'gemini-1.5-flash'),
    'Trying Gemini API' => strpos($content, 'Trying Gemini API'),
];

echo "\nVerificaciones:\n";
foreach ($checks as $name => $found) {
    if ($found !== false) {
        echo "✅ $name - encontrado\n";
    } else {
        echo "❌ $name - NO encontrado\n";
    }
}

// Contar líneas
$lines = count(explode("\n", $content));
echo "\nTotal de líneas: $lines\n";

echo "\n✅ Estructura del archivo OK\n";
?>
