<?php
session_start();
include './connect.php'; // Database connection script

// Capture form data
$username = $_POST['username'];
$password = $_POST['password'];

// Query the users table or view for the username and role
$query = $pdo->prepare("
    SELECT 
        user_id, 
        role_name, 
        password
    FROM user_roles_view
    WHERE username = :username
    LIMIT 1
");
$query->execute([ ':username' => $username ]);
$user = $query->fetch(PDO::FETCH_ASSOC);

if ($user) {
    // Verify the password (assuming it's hashed)
    if (password_verify($password, $user['password'])) {
        // Password is correct, store user data in the session
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['role_name'] = $user['role_name'];

        // Set PostgreSQL connection credentials based on user role
        switch ($user['role_name']) {
            case 'admin':
                // Connect using admin_user credentials
                $dbConnection = new PDO('pgsql:host=localhost;dbname=project', 'admin_user', 'Password123!');
                break;
            case 'seller':
                // Connect using seller_user credentials
                $dbConnection = new PDO('pgsql:host=localhost;dbname=project', 'seller_user', 'Password123!');
                break;
            case 'customer':
                // Connect using customer_user credentials
                $dbConnection = new PDO('pgsql:host=localhost;dbname=project', 'customer_user', 'Password123!');
                break;
            default:
                echo "Invalid role.";
                exit;
        }

        // If successful, redirect to the appropriate page based on user role
        switch ($user['role_name']) {    
            case 'admin':
                header('Location: admin.php');
                exit;
            case 'seller':
                header('Location: seller.php'); // Adjust as needed
                exit;
            case 'customer':
                header('Location: shop.php');
                exit;
            default:
                echo "Invalid role.";
                exit;
        }

    } else {
        // Incorrect password
        echo "Invalid username or password.";
    }
} else {
    // User not found
    echo "Invalid username or password.";
}
?>
