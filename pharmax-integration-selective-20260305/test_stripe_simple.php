<?php
// Test if Stripe library is working
echo "Checking Stripe setup...\n";

// Check if vendor exists
if (file_exists('vendor/stripe/stripe-php/init.php')) {
    echo "✓ Stripe PHP library found\n";
} else {
    echo "✗ Stripe PHP library NOT found\n";
}

// Check .env configuration
if (file_exists('.env')) {
    $env = file_get_contents('.env');
    if (strpos($env, 'STRIPE_SECRET_KEY') !== false) {
        echo "✓ STRIPE_SECRET_KEY configured in .env\n";
    } else {
        echo "✗ STRIPE_SECRET_KEY NOT in .env\n";
    }
    if (strpos($env, 'STRIPE_PUBLIC_KEY') !== false) {
        echo "✓ STRIPE_PUBLIC_KEY configured in .env\n";
    } else {
        echo "✗ STRIPE_PUBLIC_KEY NOT in .env\n";
    }
}

// Check PaymentController
if (file_exists('src/Controller/PaymentController.php')) {
    echo "✓ PaymentController exists\n";
    $ctrl = file_get_contents('src/Controller/PaymentController.php');
    if (strpos($ctrl, 'createCheckoutSession') !== false) {
        echo "✓ PaymentController calls createCheckoutSession\n";
    }
}

// Check checkout template
if (file_exists('templates/frontend/payment/checkout.html.twig')) {
    echo "✓ Checkout template exists\n";
    $tpl = file_get_contents('templates/frontend/payment/checkout.html.twig');
    if (strpos($tpl, 'csrf_token') !== false) {
        echo "✓ Checkout template includes CSRF token\n";
    } else {
        echo "✗ Checkout template MISSING CSRF token\n";
    }
    if (strpos($tpl, 'app_payment_checkout') !== false) {
        echo "✓ Checkout template form points to app_payment_checkout\n";
    }
} else {
    echo "✗ Checkout template NOT found\n";
}

echo "\nDone!\n";
