#!/usr/bin/env php
<?php

require_once dirname(__FILE__) . '/vendor/autoload.php';
require_once dirname(__FILE__) . '/config/bootstrap.php';

use App\Entity\User;
use DateTime;

try {
    $kernel = new \App\Kernel('dev', true);
    $kernel->boot();
    
    $container = $kernel->getContainer();
    
    $em = $container->get('doctrine.orm.entity_manager');
    $hasher = $container->get('security.password_hasher');
    
    // Check if user already exists
    $existingUser = $em->getRepository(User::class)->findOneBy(['email' => 'nayrouzdaikhi@gmail.com']);
    
    if ($existingUser) {
        echo "⚠️  User already exists: nayrouzdaikhi@gmail.com\n";
        exit(0);
    }
    
    // Create new user
    $user = new User();
    $user->setEmail('nayrouzdaikhi@gmail.com');
    $user->setFirstName('Nayrouzdaikhi');
    $user->setLastName('Admin');
    
    // Hash password
    $hashedPassword = $hasher->hashPassword($user, 'nayrouz123');
    $user->setPassword($hashedPassword);
    
    // Set roles and status
    $user->setRoles(['ROLE_SUPER_ADMIN', 'ROLE_USER']);
    $user->setStatus('UNBLOCKED');
    
    // Set timestamps
    $now = new DateTime();
    $user->setCreatedAt($now);
    $user->setUpdatedAt($now);
    
    // Don't set face recognition - it's optional
    // $user->setDataFaceApi(null); - this is the default
    
    // Save to database
    $em->persist($user);
    $em->flush();
    
    echo "✅ Admin user created successfully!\n";
    echo "   Email: nayrouzdaikhi@gmail.com\n";
    echo "   Password: nayrouz123\n";
    echo "   Roles: ROLE_SUPER_ADMIN, ROLE_USER\n";
    echo "   Face Recognition: Not required (optional)\n";
    echo "\n";
    echo "You can now login with this account.\n";
    
    exit(0);
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
