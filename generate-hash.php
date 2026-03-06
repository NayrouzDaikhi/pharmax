<?php
// Quick hash generator for password: nayrouz123
require_once 'vendor/autoload.php';

use Symfony\Component\PasswordHasher\Hasher\NativePasswordHasher;

$hasher = new NativePasswordHasher();
$hash = $hasher->hash('nayrouz123');
echo $hash;
