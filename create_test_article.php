<?php
// Create a test article for testing the API

require 'vendor/autoload_runtime.php';

use Symfony\Component\Dotenv\Dotenv;

(new Dotenv())->loadEnv('.env');

$_SERVER += $_ENV;
$_SERVER['APP_ENV'] ??= $_ENV['APP_ENV'] ?? 'dev';
$_SERVER['APP_DEBUG'] ??= $_ENV['APP_DEBUG'] ?? '1';

return function (array $context) {
    return new class {
        public static function run() {
            // Bootstrap Symfony kernel
            require __DIR__.'/src/Kernel.php';
            $kernel = new App\Kernel('dev', true);
            $kernel->boot();
            
            $container = $kernel->getContainer();
            $em = $container->get('doctrine.orm.entity_manager');
            
            // Create a test article
            $article = new \App\Entity\Article();
            $article->setTitre('Test Article for API');
            $article->setContenu('This is a test content for testing the API endpoint');
            $article->setSlug('test-article-api');
            $article->setDateCreation(new \DateTime());
            
            $em->persist($article);
            $em->flush();
            
            echo "✅ Article created with ID: " . $article->getId() . "\n";
        }
    };
};

?>
