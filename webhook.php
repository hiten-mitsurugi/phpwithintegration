<?php
// Retrieve the payload
$payload = file_get_contents('php://input');

// Log the payload for debugging
file_put_contents('webhook.log', $payload, FILE_APPEND);

// Decode JSON payload
$data = json_decode($payload, true);

// Process the webhook data
if ($data && isset($data['data']['type']) && $data['data']['attributes']['type'] === 'payment.paid') {
    // Example: Extract payment ID and update database
    $paymentId = $data['data']['id'];
    $amount = $data['data']['attributes']['amount'];
    $status = $data['data']['attributes']['status'];

    // Connect to the database
    include './customerconnect.php';

    // Update the order status in the database
    $query = "UPDATE orders SET status = $1 WHERE payment_id = $2";
    $result = pg_query_params($connect, $query, array($status, $paymentId));

    if ($result) {
        file_put_contents('webhook.log', "Payment successfully updated.\n", FILE_APPEND);
    } else {
        file_put_contents('webhook.log', "Failed to update payment: " . pg_last_error() . "\n", FILE_APPEND);
    }
}
?>
