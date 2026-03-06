<?php

require_once dirname(__FILE__) . '/vendor/autoload.php';

use Doctrine\DBAL\Connection;

$kernel = new \App\Kernel('dev', true);
$kernel->boot();

$container = $kernel->getContainer();
$connection = $container->get(Connection::class);

try {
    echo "Checking if data_face_api column exists...\n";
    
    $sm = $connection->createSchemaManager();
    $columns = $sm->listTableColumns('user');
    
    if (isset($columns['data_face_api'])) {
        echo "✅ Column 'data_face_api' already exists.\n";
    } else {
        echo "Adding column 'data_face_api'...\n";
        
       // Use raw SQL for MariaDB compatibility
        $connection->executeStatement('ALTER TABLE `user` ADD COLUMN data_face_api LONGTEXT DEFAULT NULL');
        
        echo "✅ Column 'data_face_api' added successfully.\n";
    }
    
    exit(0);
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
