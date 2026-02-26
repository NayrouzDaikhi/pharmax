#!/usr/bin/env php
<?php
// Check archived comments in the database

require 'vendor/autoload_runtime.php';

use Symfony\Component\Dotenv\Dotenv;

(new Dotenv())->loadEnv('.env');

$_SERVER += $_ENV;
$_SERVER['APP_ENV'] ??= $_ENV['APP_ENV'] ?? 'dev';
$_SERVER['APP_DEBUG'] ??= $_ENV['APP_DEBUG'] ?? '1';

return function (array $context) {
    return function () {
        // This needs to be run through Symfony bootstrap
    };
};
?>