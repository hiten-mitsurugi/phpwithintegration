<?php
session_start();

// Check if the logout button is clicked
if (isset($_POST['logout'])) {
    // Destroy the session
    session_unset(); // Remove all session variables
    session_destroy(); // Destroy the session

    // Redirect to the login page
    header("Location: index.php");
    exit();
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to the login page if not logged in
    header("Location: index.php");
    exit();
}

// Fetch user information
$user_id = $_SESSION['user_id'];

// Database connection (assuming you already have the connection set up)
include './sellerconnect.php';

// Query to fetch the user's role_id, first_name, and last_name from the 'users' table
$query = "SELECT u.role_id, u.first_name, u.last_name 
          FROM users u
          WHERE u.user_id = $1";
$result = pg_query_params($connect, $query, array($user_id));

if (!$result) {
    die("Error fetching user data: " . pg_last_error($connect));
}

$user = pg_fetch_assoc($result);

// Check if the user has role_id 3 (customer)
if ($user['role_id'] != 2) {
    // Redirect to access denied page if role_id is not 3
    header("Location: access_denied.php");
    exit();
}

// If role_id is 3 (customer), proceed with adding items to the cart
$cart = json_decode(file_get_contents('php://input'), true);

// Start a transaction
pg_query($connect, "BEGIN");

$success = true;

foreach ($cart as $item) {
    $product_id = $item['id'];
    $quantity = $item['quantity'];

    // Insert each item into the cart
    $query = "INSERT INTO cart_items (user_id, product_id, quantity) VALUES ($1, $2, $3)";
    $result = pg_query_params($connect, $query, array($user_id, $product_id, $quantity));

    if (!$result) {
        $success = false;
        break;
    }
}

if ($success) {
    // Commit the transaction if successful
    pg_query($connect, "COMMIT");
    http_response_code(200);
    echo json_encode(array("status" => "success", "message" => "Cart items saved successfully"));
} else {
    // Rollback the transaction if any query fails
    pg_query($connect, "ROLLBACK");
    http_response_code(500);
    echo json_encode(array("status" => "error", "message" => "Failed to save cart items"));
}
?>
