<?php
// Quick test script for Stripe payment functionality
require 'vendor/autoload.php';
require 'config/bootstrap.php';

use Stripe\Stripe;
use Stripe\Checkout\Session;

// Set API key
Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

try {
    echo "Testing Stripe API connection...\n";
    
    // Create a test session
    $session = Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [
            [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => 'Test Product',
                    ],
                    'unit_amount' => 1000, // €10.00
                ],
                'quantity' => 1,
            ],
        ],
        'mode' => 'payment',
        'success_url' => 'http://localhost:8000/payment/success?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => 'http://localhost:8000/payment/cancel',
        'customer_email' => 'test@example.com',
    ]);
    
    echo "✓ Session created successfully!\n";
    echo "Session ID: " . $session->id . "\n";
    echo "Session URL: " . ($session->url ?? 'NO URL - ERROR') . "\n";
    echo "Session object keys: " . implode(', ', array_keys($session->toArray())) . "\n";
    
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Error type: " . get_class($e) . "\n";
}
