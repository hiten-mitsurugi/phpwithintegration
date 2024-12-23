<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to the login page if not logged in
    header("Location: index.php");
    exit();
}

// Logout functionality
if (isset($_POST['logout'])) {
    session_unset(); // Remove all session variables
    session_destroy(); // Destroy the session

    // Redirect to the login page
    header("Location: index.php");
    exit();
}

include './sellerconnect.php.php';

// Get the user ID from the session
$user_id = $_SESSION['user_id'];

// Query to retrieve the user's role_id
$query_role = "SELECT role_id FROM users WHERE user_id = $1";
$result_role = pg_query_params($connect, $query_role, array($user_id));

if (!$result_role) {
    die("Error fetching user role: " . pg_last_error());
}

$user_role = pg_fetch_assoc($result_role);

// Check if the user's role_id is not 3 (customer)
if ($user_role['role_id'] != 2) {
    // Redirect to access denied page if role_id is not 3
    header("Location: access_denied.php");
    exit();
}
pg_query($connect, "SET ROLE customer_user");
// Query to delete cart items for the logged-in user
$delete_query = "DELETE FROM cart_items WHERE user_id = $1";
$result_delete = pg_query_params($connect, $delete_query, array($user_id));

if (!$result_delete) {
    die("Error deleting cart items: " . pg_last_error());
}

// Redirect to the shop page after cancellation
header('Location: seller-shop.php');
exit();
?>
