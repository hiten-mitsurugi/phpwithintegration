<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    // Redirect to the login page if not logged in
    header("Location: index.php");
    exit();
}

// Include your database connection
include './customerconnect.php';

$user_id = $_SESSION['user_id'];
$payment_method = $_POST['payment_method']; // Selected payment method
$total_cost = $_POST['total_cost'];
$shipping_fee = 300; // Shipping fee

// Default payment method ID (pending status)
$payment_method_id = 4; // Default to "pending"

// Map selected payment method to ID
if ($payment_method == 1) {
    $payment_method_id = 1; // G-cash
} elseif ($payment_method == 2) {
    $payment_method_id = 2; // PayMaya
} elseif ($payment_method == 3) {
    $payment_method_id = 3; // Bank Transfer
}

// Debugging: Check the column existence of shipping_charges in the orders table
$result_check_column = pg_query($connect, "SELECT column_name FROM information_schema.columns WHERE table_name = 'orders' AND column_name = 'shipping_charges'");
if (pg_num_rows($result_check_column) == 0) {
    die("Error: Column 'shipping_charges' does not exist in the 'orders' table.");
}

// Insert the order into the orders table
$query = "INSERT INTO orders (user_id, total_amount, shipping_charges, payment_method_id, status_id)
          VALUES ($user_id, $total_cost, $shipping_fee, $payment_method_id, 1) RETURNING order_id"; // Changed orders_id to order_id

$result = pg_query($connect, $query);

if (!$result) {
    die("Error in SQL query: " . pg_last_error());
}

// Fetch the order ID
$order_id = pg_fetch_result($result, 0, 'order_id'); // Changed orders_id to order_id

// Retrieve cart items for the logged-in user
$query_cart = "SELECT c.product_id, c.quantity, p.selling_price, p.seller_id
               FROM cart_items c
               INNER JOIN products p ON c.product_id = p.product_id
               WHERE c.user_id = $user_id";

$cart_result = pg_query($connect, $query_cart);

if (!$cart_result) {
    die("Error fetching cart items: " . pg_last_error());
}

// Insert each cart item into the order_items table
while ($cart_item = pg_fetch_assoc($cart_result)) {
    $product_id = $cart_item['product_id'];
    $quantity = $cart_item['quantity'];
    $price = $cart_item['selling_price'];
    $seller_id = $cart_item['seller_id'];

    // Insert the order item
    $query_order_item = "INSERT INTO order_items (order_id, product_id, quantity, price, seller_id)
                         VALUES ($order_id, $product_id, $quantity, $price, $seller_id)";

    $insert_order_item_result = pg_query($connect, $query_order_item);

    if (!$insert_order_item_result) {
        die("Error inserting order item: " . pg_last_error());
    }
}

// Empty the cart after the order is placed
$query_empty_cart = "DELETE FROM cart_items WHERE user_id = $user_id";
$empty_cart_result = pg_query($connect, $query_empty_cart);

if (!$empty_cart_result) {
    die("Error emptying cart: " . pg_last_error());
}

// Now proceed with the payment redirection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the total cost sent from payment.php
    $amount = $total_cost * 100; // Convert to cents

    // Check if the amount is valid
    if ($amount <= 0) {
        echo json_encode(["error" => "Invalid amount"]);
        exit();
    }

    // Initialize cURL
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.paymongo.com/v1/links",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode([ 
            "data" => [
                "attributes" => [
                    "amount" => $amount,  // Amount in cents
                    "description" => "Payment for order",
                    "remarks" => "Order payment"  // Optional remarks
                ]
            ]
        ]),
        CURLOPT_HTTPHEADER => [
            "accept: application/json",
            "authorization: Basic c2tfdGVzdF82Uk56QnBXZ1BBdnBidU1OTXFvc0ZaRmk6", // Ensure the key is correct
            "content-type: application/json"
        ]
    ]);

    $response = curl_exec($curl);
    $err = curl_errno($curl);

    curl_close($curl);

    if ($err) {
        echo json_encode(["error" => "cURL Error #: $err"]);
    } else {
        $responseData = json_decode($response, true);

        // Check for the correct location of the checkout URL
        if (isset($responseData['data']['attributes']['checkout_url'])) {
            // Redirect to the checkout URL
            header("Location: " . $responseData['data']['attributes']['checkout_url']);
            exit();
        } else {
            // If the checkout URL is not present, return an error
            echo json_encode(["error" => "Failed to create payment link"]);
        }
    }
} else {
    echo json_encode(["error" => "Invalid request method"]);
}
?>
