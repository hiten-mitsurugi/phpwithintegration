<?php
session_start();

// Check if the user is already logged in
if (isset($_SESSION['user_id'])) {
    // Check the role_name in the session
    if (isset($_SESSION['role_name'])) {
        switch ($_SESSION['role_name']) {
            case 'seller':
                header("Location: seller.php"); // Redirect to seller page
                exit();
            case 'admin':
                header("Location: admin.php"); // Redirect to admin page
                exit();
            default:
                header("Location: shop.php"); // Redirect to the default dashboard or another page
                exit();
        }
    } else {
        // If role_name is not set in the session, redirect to a default page
        header("Location: shop.php");
        exit();
    }
}

// Add your login logic here (e.g., check username and password from the database)
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Form</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="left"></div>
    <div class="right">
        <div class="wrapper">
            <!-- Login Form -->
            <form action="login.php" method="post"> <!-- Ensure login.php handles the form submission -->
                <h2>Login</h2>
                <!-- Username Field -->
                <div class="input-field">
                    <input type="text" name="username" id="username" placeholder="Username" required>
                 
                </div>
                <!-- Password Field -->
                <div class="input-field">
                    <input type="password" name="password" id="password" placeholder="Password" required>
          
                </div>
                <!-- Remember Me and Forgot Password -->
                <div class="forget">
                    <label for="remember">
                        <input type="checkbox" id="remember">
                        <p>Remember me</p>
                    </label>
                    <a href="#">Forgot password?</a>
                </div>
                <!-- Submit Button -->
                <button type="submit">Log in</button>
                <!-- Register Link -->
                <div class="register">
                    <p>Don't have an account? <a href="register.php">Register</a></p>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
