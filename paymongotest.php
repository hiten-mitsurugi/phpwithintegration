<?php
require_once 'vendor/autoload.php'; // Autoload Composer dependencies

use Paymongo\PaymongoClient;
use Paymongo\Services\PaymentMethod;
use Paymongo\Services\PaymentIntent;

// Set your PayMongo secret key
$paymongo = new PaymongoClient('sk_test_Tg1Jhc7YxQPifJ4ctMCCHiUX'); // Replace with your secret key

// Create payment method (using the appropriate service)
if ($_POST['payment_method'] == 'gcash') {
    $payment_method = PaymentMethod::create([
        'type' => 'gcash',
    ]);
} elseif ($_POST['payment_method'] == 'paymaya') {
    $payment_method = PaymentMethod::create([
        'type' => 'paymaya',
    ]);
}

// Create the payment intent
$payment_intent = PaymentIntent::create([
    'amount' => $_POST['total_cost'] * 100, // Convert to cents
    'currency' => 'PHP',
    'payment_method' => $payment_method->id,
    'description' => 'Order payment',
]);

// Output the payment intent ID or redirect as needed
echo "Payment intent created: " . $payment_intent->id;
?>
