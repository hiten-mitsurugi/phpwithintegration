<?php


include './connects.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role_name'])) {
    die("Unauthorized access. Please log in.");
}

// Set PostgreSQL connection credentials based on user role
try {
    switch ($_SESSION['role_name']) {
        case 'admin':
            $dbConnection = new PDO('pgsql:host=localhost;dbname=project', 'admin_user', 'Password123!');
            break;
        case 'seller':
            $dbConnection = new PDO('pgsql:host=localhost;dbname=project', 'seller_user', 'Password123!');
            break;
        case 'customer':
            $dbConnection = new PDO('pgsql:host=localhost;dbname=project', 'customer_user', 'Password123!');
            break;
        default:
            die("Invalid role.");
    }
    $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Add error mode
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Confirm connection is working
if (!$dbConnection) {
    die("Failed to initialize database connection.");
}
?>
