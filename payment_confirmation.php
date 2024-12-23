<?php
require_once 'vendor/autoload.php'; // Autoload Composer dependencies

$user_id = $_SESSION['user_id'];
$payment_method_id = $_POST['payment_method'];  // Get the payment method selected by the user
$total_cost = $_POST['total_cost']; // Total cost including shipping fee
$shipping_fee = 300; // Shipping fee

// Set your PayMongo secret key
\Paymongo\Paymongo::setApiKey('sk_test_Tg1Jhc7YxQPifJ4ctMCCHiUX'); // Replace with your secret key

// Create a payment method (this can vary depending on the payment method selected)
if ($payment_method_id == 1) {
    // Example for GCash (if supported by PayMongo, replace with actual payment details)
    $payment_method = \Paymongo\PaymentMethod::create([
        'type' => 'gcash', // Specify the payment type, 'gcash' or 'paymaya' if available
    ]);
} elseif ($payment_method_id == 2) {
    // Example for PayMaya
    $payment_method = \Paymongo\PaymentMethod::create([
        'type' => 'paymaya', // Specify the payment type
    ]);
} else {
    // Add Bank transfer or other payment method logic if needed
}

// Create the payment intent
$payment_intent = \Paymongo\PaymentIntent::create([
    'amount' => $total_cost * 100, // Convert to cents (₱100.00 = 10000)
    'currency' => 'PHP',
    'payment_method' => $payment_method->id,
    'description' => 'Order payment',
    'capture_mode' => 'AUTOMATIC', // or MANUAL depending on your use case
]);

// Optionally, store the payment intent ID in the database or session
$order_id = $_POST['order_id']; // Get order ID if applicable
$query_update = "UPDATE orders SET payment_intent_id = $1 WHERE order_id = $2";
pg_query_params($connect, $query_update, array($payment_intent->id, $order_id));

// Redirect to PayMongo payment page or confirmation page
header("Location: confirm_payment.php?payment_intent_id=" . $payment_intent->id);
exit();
?>