<?php
session_start();

// Include PayMongo API SDK and database connection
require 'paymongo_sdk.php'; 
include './customerconnect.php';

// PayMongo Secret API key
$paymongo_api_key = 'sk_test_Tg1Jhc7YxQPifJ4ctMCCHiUX';

// Initialize PayMongo API client
$paymongo = new PayMongo($paymongo_api_key);

// Assuming the payment confirmation is passed as a status or payment response directly
$payment_status = $_GET['payment_status']; // This could be a query parameter or a response from PayMongo

// Retrieve order and payment details (assuming a session-based order_id)
$order_id = $_SESSION['order_id'];  // The current order ID stored in session

if ($payment_status === 'succeeded') {
    // Update the order status to 'paid' in the orders table using order_id
    $update_order_query = "UPDATE orders 
                           SET status_id = (SELECT status_id FROM order_status WHERE status_name = 'paid'), 
                               payment_method_id = 4 -- Assuming 4 is the ID for 'Pending' or appropriate method
                           WHERE order_id = $1";
    $result = pg_query_params($connect, $update_order_query, array($order_id));
    
    if ($result) {
        // Optionally, update inventory, send confirmation email, etc.
        echo "Payment successful! Your order is confirmed.";
    } else {
        echo "Error updating order status.";
    }
} else {
    echo "Payment failed or still pending.";
}
?>
