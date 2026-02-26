<?php
/**
 * Quick test with timeout
 */

$url = 'http://127.0.0.1:8000/api/chatbot/ask';
$data = json_encode(['question' => 'Hi']);

$fp = @fsockopen("127.0.0.1", 8000, $errno, $errstr, 5);

if (!$fp) {
    echo "Connection failed: $errstr ($errno)\n";
    exit(1);
}

$post_data = json_encode(['question' => 'Hi']);
$length = strlen($post_data);

$out = "POST /api/chatbot/ask HTTP/1.1\r\n";
$out .= "Host: 127.0.0.1:8000\r\n";
$out .= "Content-Type: application/json\r\n";
$out .= "Content-Length: $length\r\n";
$out .= "Connection: Close\r\n\r\n";
$out .= $post_data;

fwrite($fp, $out);

$response = "";
while (!feof($fp)) {
    $response .= fgets($fp, 128);
}
fclose($fp);

// Parse response
$parts = explode("\r\n\r\n", $response, 2);
$headers = $parts[0];
$body = isset($parts[1]) ? $parts[1] : '';

echo "Headers:\n" . substr($headers, 0, 200) . "\n";
echo "\nBody:\n" . $body . "\n";

$decoded = json_decode($body, true);
if ($decoded) {
    echo "\n✅ Valid JSON:\n";
    if (isset($decoded['success'])) {
        echo "Success: " . ($decoded['success'] ? 'YES' : 'NO') . "\n";
        if (isset($decoded['error'])) {
            echo "Error: " . $decoded['error'] . "\n";
        }
        if (isset($decoded['answer'])) {
            echo "Answer: " . substr($decoded['answer'], 0, 200) . "...\n";
        }
    }
} else {
    echo "\n❌ Not JSON\n";
}
?>
