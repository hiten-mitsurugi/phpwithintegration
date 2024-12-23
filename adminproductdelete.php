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
include './adminconnect.php';

// Query to fetch the user's role and name from the 'users' table
$query = "SELECT r.role_name, u.first_name, u.last_name 
          FROM users u
          JOIN roles r ON u.role_id = r.role_id
          WHERE u.user_id = $1";
$result = pg_query_params($connect, $query, array($user_id));

if (!$result) {
    die("Error fetching user data: " . pg_last_error($connect));
}

$user = pg_fetch_assoc($result);

// Check if the user is an admin
if ($user['role_name'] != 'admin') {
    // Redirect to an access denied page if the user is not an admin
    header("Location: access_denied.php");
    exit();
}

// Check if the product_id is passed in the POST request
if (!isset($_POST['product_id']) || empty($_POST['product_id'])) {
    echo "Product ID is required.";
    exit();
}

// Get the product_id from the POST request
$product_id = $_POST['product_id'];

// Query to check if the product exists
$query = "SELECT * FROM products WHERE product_id = $1";
$result = pg_query_params($connect, $query, array($product_id));

if (!$result) {
    die("Error fetching product data: " . pg_last_error($connect));
}

$product_data = pg_fetch_assoc($result);

if (!$product_data) {
    echo "Product not found.";
    exit();
}

// Query to delete the product
$query_delete = "DELETE FROM products WHERE product_id = $1";
$result_delete = pg_query_params($connect, $query_delete, array($product_id));

if ($result_delete) {
    // Redirect back to the product list page after deletion
    header("Location: adminproductlist.php?success=Product deleted successfully");
    exit();
} else {
    echo "Error deleting product: " . pg_last_error($connect);
}
?>
