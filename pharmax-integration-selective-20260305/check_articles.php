<?php
// Quick check of articles in database
require 'vendor/autoload.php';

$dotenv = new Symfony\Component\Dotenv\Dotenv();
$dotenv->load('.env');

try {
    $pdo = new PDO(
        'mysql:host=' . $_ENV['DATABASE_HOST'] . ';dbname=' . $_ENV['DATABASE_NAME'],
        $_ENV['DATABASE_USER'],
        $_ENV['DATABASE_PASSWORD']
    );
    
    $result = $pdo->query('SELECT id, titre, LENGTH(contenu) as content_length FROM article');
    
    echo "Articles in database:\n";
    echo "====================\n";
    
    foreach ($result as $row) {
        echo "ID: " . $row['id'] . "\n";
        echo "Title: " . $row['titre'] . "\n";
        echo "Content Length: " . $row['content_length'] . " bytes\n";
        echo "---\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
