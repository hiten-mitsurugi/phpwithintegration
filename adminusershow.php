<?php
session_start();
$host = 'localhost'; // Database host
$dbname = 'project'; // Database name
$username = 'admin_user'; // Database username
$password = 'Password123!'; // Database password

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

// Fetch user information (example)
$user_id = $_SESSION['user_id'];

// Database connection using PDO (assuming the connection details are correct)
try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit();
}

// Query to fetch the user's role and name from the joined tables
$query = "SELECT r.role_name, u.first_name, u.last_name 
          FROM users u
          JOIN roles r ON u.role_id = r.role_id
          WHERE u.user_id = :user_id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();

// Check if the query is successful
if (!$stmt) {
    die("Error fetching user data: " . $pdo->errorInfo());
}

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user['role_name'] != 'admin') {
    // Redirect to an access denied page or homepage if the user is not an admin
    header("Location: access_denied.php");
    exit();
}

$user_name = $user['first_name'] . ' ' . $user['last_name']; // User's name from the DB

// Fetch the specific user details
// Fetch the specific user details
if (isset($_GET['id'])) {
    $user_id_to_show = $_GET['id'];

    // Query to fetch the user's details (with address)
    $query_user = "
        SELECT u.first_name, u.last_name, u.email, u.contact_number, 
               a.region, a.province, a.city, a.barangay
        FROM users u
        JOIN addresses a ON u.address_id = a.address_id
        WHERE u.user_id = :user_id";

    // Prepare the query and bind the parameter
    $stmt_user = $pdo->prepare($query_user);
    $stmt_user->bindParam(':user_id', $user_id_to_show, PDO::PARAM_INT);
    $stmt_user->execute();

    // Fetch the user details
    $user_details = $stmt_user->fetch(PDO::FETCH_ASSOC);

    // Check if the query returned data
    if (!$user_details) {
        die("Error fetching user details.");
    }
} else {
    die("No user selected.");
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
</head>

<body class="flex h-screen bg-gray-100">

    <!-- Sidebar -->
    <div class="w-64 bg-gray-800 text-white">
        <div class="p-4">
            <h2 class="text-2xl font-bold">Admin Dashboard</h2>
            <p class="text-gray-400">Welcome, <?php echo htmlspecialchars($user_name); ?>!</p>
        </div>
        <ul class="mt-6 space-y-2">
        <li><a href="admin.php" class="block px-4 py-2 hover:bg-gray-700">Dashboard</a></li>
        <li><a href="adminuserlist.php" class="block px-4 py-2 hover:bg-gray-700">Users</a></li>
            <li><a href="adminproductlist.php" class="block px-4 py-2 hover:bg-gray-700">Products</a></li>
            <li><a href="adminorderlist.php" class="block px-4 py-2 hover:bg-gray-700">Orders</a></li>
            <li><a href="admin-top-products.php" class="block px-4 py-2 hover:bg-gray-700">Top 5 Products</a></li> 
            <li><a href="admin-logs.php" class="block px-4 py-2 hover:bg-gray-700">Logs</a></li> 
            <li><a href="admin-shipped.php" class="block px-4 py-2 hover:bg-gray-700">Orders for Shipping</a></li>
            <li><a href="admin-personal-sales.php" class="block px-4 py-2 hover:bg-gray-700">Personal Sales</a></li> 
            <li>
                <!-- Logout Form -->
                <form method="post">
                    <button type="submit" name="logout"
                        class="w-full text-left px-4 py-2 text-red-500 hover:bg-gray-700">Logout</button>
                </form>
            </li>
        </ul>
    </div>

    <!-- Main Content Area -->
    <div class="flex-1 p-8">
        <h1 class="text-3xl font-semibold text-gray-800">User Details</h1>

        <!-- Back Button -->
        <a href="adminuserlist.php" class="mt-4 inline-block">
            <button
                class="px-6 py-3 text-white bg-blue-600 rounded-lg shadow-md hover:bg-blue-700 transform hover:scale-105 transition-all duration-300 ease-in-out">
                Back to User List
            </button>
        </a>

        <!-- User Details -->
        <!-- User Details -->
        <div class="mt-8 bg-white p-6 rounded-lg shadow-lg">
            <h2 class="text-xl font-semibold text-gray-700">User Information</h2>
            <div class="mt-4">
                <p class="text-gray-600"><strong>Name:</strong>
                    <?php echo isset($user_details['first_name']) ? htmlspecialchars($user_details['first_name'] . ' ' . $user_details['last_name']) : 'N/A'; ?>
                </p>
                <p class="text-gray-600"><strong>Email:</strong>
                    <?php echo isset($user_details['email']) ? htmlspecialchars($user_details['email']) : 'N/A'; ?></p>
                <p class="text-gray-600"><strong>Contact:</strong>
                    <?php echo isset($user_details['contact_number']) ? htmlspecialchars($user_details['contact_number']) : 'N/A'; ?>
                </p>
                <p class="text-gray-600"><strong>Region:</strong>
                    <?php echo isset($user_details['region']) ? htmlspecialchars($user_details['region']) : 'N/A'; ?>
                </p>
                <p class="text-gray-600"><strong>Province:</strong>
                    <?php echo isset($user_details['province']) ? htmlspecialchars($user_details['province']) : 'N/A'; ?>
                </p>
                <p class="text-gray-600"><strong>City:</strong>
                    <?php echo isset($user_details['city']) ? htmlspecialchars($user_details['city']) : 'N/A'; ?></p>
                <p class="text-gray-600"><strong>Barangay:</strong>
                    <?php echo isset($user_details['barangay']) ? htmlspecialchars($user_details['barangay']) : 'N/A'; ?>
                </p>
            </div>
        </div>

    </div>

</body>

</html>