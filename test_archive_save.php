<?php
// Test script to verify if blocked comments are saved to archive table

require 'vendor/autoload.php';

// Load Symfony environment
use Symfony\Component\Dotenv\Dotenv;
(new Dotenv())->bootEnv('.env');

// Get database connection
$dsn = 'sqlite:///' . getcwd() . '/var/data.db';
// Parse the DATABASE_URL from .env
$databaseUrl = $_ENV['DATABASE_URL'] ?? 'sqlite:///%kernel.project_dir%/var/data.db';
$databasePath = str_replace(['sqlite:///', '%kernel.project_dir%'], ['', getcwd()], $databaseUrl);

echo "Database: $databasePath\n\n";

// Use SQLite directly
$db = new PDO('sqlite:' . $databasePath);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== Before Test ===\n";
$result = $db->query("SELECT COUNT(*) as count FROM archive_de_commentaire")->fetch();
$countBefore = $result['count'];
echo "Total archived comments before: " . $countBefore . "\n\n";

// Send API request
echo "=== Sending Blocked Comment (contains 'stupid') ===\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8000/api/commentaires');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'contenu' => 'This is stupid and very bad',
    'article_id' => 1,
    'user_name' => 'Test User ' . date('H:i:s'),
    'user_email' => 'test@example.com'
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status: " . $httpCode . "\n";
echo "Response: " . $response . "\n\n";

// Check database after request
sleep(1); // Wait a bit for database to process
echo "=== After Test ===\n";
$result = $db->query("SELECT COUNT(*) as count FROM archive_de_commentaire")->fetch();
$countAfter = $result['count'];
echo "Total archived comments after: " . $countAfter . "\n";
echo "Difference: " . ($countAfter - $countBefore) . "\n\n";

if ($countAfter > $countBefore) {
    echo "✅ SUCCESS! Comment was saved to archive table!\n\n";
    echo "Last 3 archived comments:\n";
    $results = $db->query("SELECT * FROM archive_de_commentaire ORDER BY archived_at DESC LIMIT 3");
    $i = 1;
    foreach ($results as $row) {
        echo ($i++) . ". ID: " . $row['id'] . 
             ", User: " . $row['user_name'] . 
             ", Content: " . substr($row['contenu'], 0, 40) . "..." .
             ", Reason: " . $row['reason'] . 
             ", Archived: " . $row['archived_at'] . "\n";
    }
} else {
    echo "❌ PROBLEM: Comment was NOT saved to archive table!\n";
    echo "This means the API blocking is working, but the database save is not.\n";
}
