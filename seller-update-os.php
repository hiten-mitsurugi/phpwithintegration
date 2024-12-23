<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

include './sellerconnect.php'; // Database connection

// Check if order_id is passed
if (isset($_POST['order_id'])) {
    $order_id = $_POST['order_id'];
    
    // Update the order status from 'paid' (status_id = 1) to 'shipped' (status_id = 2)
    $query = "UPDATE orders
              SET status_id = 2  -- Change the status to 'shipped' (status_id = 2)
              WHERE order_id = $1 AND status_id = 1";  // Only update if status is 'paid' (status_id = 1)
    
    // Execute the query
    $result = pg_query_params($connect, $query, array($order_id));

    if (!$result) {
        die("Error updating order status: " . pg_last_error($connect));
    }

    // Redirect back to the orders page
    header("Location: seller-order-list.php");
    exit();
} else {
    echo "Invalid order ID.";
}
?>
