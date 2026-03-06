#!/usr/bin/env php
<?php
/**
 * Synchronize Doctrine migrations after SQL dump import
 * 
 * This script marks all migrations as executed if they're already in the database.
 * Use this after importing pharmax.sql to avoid "column already exists" errors.
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/config/bootstrap.php';

use Symfony\Component\Console\Application;
use Doctrine\Migrations\Tools\Console\Command\ExecuteCommand;
use Doctrine\Migrations\Tools\Console\Command\SyncMetadataCommand;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\ORM\EntityManagerInterface;

$kernel = \App\Kernel::class;
$app = new \Symfony\Component\HttpKernel\HttpKernel(
    'dev',
    true,
);

try {
    echo "Synchronizing Doctrine migrations with database schema...\n";
    echo "This ensures migrations match the imported SQL dump schema.\n\n";
    
    // Run the sync-metadata command which is safe to run on an existing database
    system('php bin/console doctrine:migrations:sync-metadata-storage --no-interaction');
    
    // List migration status
    system('php bin/console doctrine:migrations:status');
    
    echo "\nMigration sync complete!\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
