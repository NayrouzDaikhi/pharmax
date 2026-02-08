<?php

// Test blocked comment saving in archive table

$ch = curl_init();
$data = json_encode([
    'contenu' => 'This is stupid and bad',
    'article_id' => 1,
    'user_name' => 'Test User',
    'user_email' => 'test@example.com'
]);

curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8000/api/commentaires');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$result = curl_exec($ch);
$info = curl_getinfo($ch);
curl_close($ch);

echo "=== TEST: Blocked Comment Storage ===\n";
echo "HTTP Status: " . $info['http_code'] . "\n";
echo "Response:\n";
echo $result . "\n\n";

// Now check the database
require 'vendor/autoload.php';
require 'config/bootstrap.php';

use App\Repository\CommentaireArchiveRepository;
$container = require 'var/cache/dev/ContainerPreMY8zBU5\App_Kernel_Dev.container.php';
$entityManager = $container->get('doctrine.orm.entity_manager');

/** @var CommentaireArchiveRepository $archiveRepo */
$archiveRepo = $entityManager->getRepository('App:CommentaireArchive');

$archived = $archiveRepo->findAll();
echo "=== ARCHIVE TABLE CHECK ===\n";
echo "Total archived comments: " . count($archived) . "\n";

if (count($archived) > 0) {
    echo "\nLast 3 archived comments:\n";
    $lastThree = array_slice($archived, -3);
    foreach ($lastThree as $i => $comment) {
        echo ($i+1) . ". ID: " . $comment->getId() . 
             ", Content: " . substr($comment->getContenu(), 0, 50) . "..." .
             ", Reason: " . $comment->getReason() . 
             ", Archived At: " . $comment->getArchivedAt()?->format('Y-m-d H:i:s') . "\n";
    }
}
