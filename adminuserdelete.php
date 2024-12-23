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

// Query to fetch the user's role and name from the 'user_show_view'
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
    // Redirect to an access denied page or homepage if the user is not an admin
    header("Location: access_denied.php");
    exit();
}

// Check if the user_id is passed in the POST request
if (!isset($_POST['user_id']) || empty($_POST['user_id'])) {
    echo "User ID is required.";
    exit();
}

// Get the user_id from the POST request
$user_id_to_delete = $_POST['user_id'];

// Query to check if the user exists
$query_check = "SELECT * FROM users WHERE user_id = $1";
$result_check = pg_query_params($connect, $query_check, array($user_id_to_delete));

if (!$result_check) {
    die("Error checking user data: " . pg_last_error($connect));
}

$user_data = pg_fetch_assoc($result_check);

if (!$user_data) {
    echo "User not found.";
    exit();
}

// Query to delete the user
$query_delete = "DELETE FROM users WHERE user_id = $1";
$result_delete = pg_query_params($connect, $query_delete, array($user_id_to_delete));

if ($result_delete) {
    // Redirect back to the user list page after deletion
    header("Location: adminuserlist.php?success=User deleted successfully");
    exit();
} else {
    echo "Error deleting user: " . pg_last_error($connect);
}
?>
