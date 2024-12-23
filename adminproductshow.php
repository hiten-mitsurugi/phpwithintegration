<?php
session_start();

// Database connection details
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

// Fetch the specific product details
if (isset($_GET['id'])) {
    $product_id = $_GET['id'];

    // Query to fetch the product details
    $query_product = "
        SELECT p.product_id, p.product_name, p.description, p.quantity, 
               p.capital_price, p.selling_price, p.image_url, p.created_at
        FROM products p
        WHERE p.product_id = :product_id";

    // Prepare the query and bind the parameter
    $stmt_product = $pdo->prepare($query_product);
    $stmt_product->bindParam(':product_id', $product_id, PDO::PARAM_INT);
    $stmt_product->execute();

    // Fetch the product details
    $product_details = $stmt_product->fetch(PDO::FETCH_ASSOC);

    // Check if the query returned data
    if (!$product_details) {
        die("Error fetching product details.");
    }
} else {
    die("No product selected.");
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
                    <button type="submit" name="logout" class="w-full text-left px-4 py-2 text-red-500 hover:bg-gray-700">Logout</button>
                </form>
            </li>
        </ul>
    </div>

    <!-- Main Content Area -->
    <div class="flex-1 p-8">
        <h1 class="text-3xl font-semibold text-gray-800">Product Details</h1>

        <!-- Back Button -->
        <a href="adminproductlist.php" class="mt-4 inline-block">
            <button class="px-6 py-3 text-white bg-blue-600 rounded-lg shadow-md hover:bg-blue-700 transform hover:scale-105 transition-all duration-300 ease-in-out">
                Back to Product List
            </button>
        </a>

        <!-- Product Details -->
        <div class="mt-8 bg-white p-6 rounded-lg shadow-lg">
            <h2 class="text-xl font-semibold text-gray-700">Product Information</h2>
            <div class="mt-4">
                <p class="text-gray-600"><strong>Product Name:</strong>
                    <?php echo isset($product_details['product_name']) ? htmlspecialchars($product_details['product_name']) : 'N/A'; ?>
                </p>
                <p class="text-gray-600"><strong>Description:</strong>
                    <?php echo isset($product_details['description']) ? htmlspecialchars($product_details['description']) : 'N/A'; ?>
                </p>
                <p class="text-gray-600"><strong>Quantity:</strong>
                    <?php echo isset($product_details['quantity']) ? htmlspecialchars($product_details['quantity']) : 'N/A'; ?>
                </p>
                <p class="text-gray-600"><strong>Capital Price:</strong>
                    <?php echo isset($product_details['capital_price']) ? htmlspecialchars(number_format($product_details['capital_price'], 2)) : 'N/A'; ?>
                </p>
                <p class="text-gray-600"><strong>Selling Price:</strong>
                    <?php echo isset($product_details['selling_price']) ? htmlspecialchars(number_format($product_details['selling_price'], 2)) : 'N/A'; ?>
                </p>
                <p class="text-gray-600"><strong>Created At:</strong>
                    <?php echo isset($product_details['created_at']) ? htmlspecialchars(date('Y-m-d H:i:s', strtotime($product_details['created_at']))) : 'N/A'; ?>
                </p>
                <p class="text-gray-600"><strong>Image:</strong><br>
                    <?php if (isset($product_details['image_url']) && !empty($product_details['image_url'])): ?>
                        <img src="assets/product/<?php echo htmlspecialchars($product_details['image_url']); ?>" alt="Product Image" class="w-32 h-32 object-cover border mt-2">
                    <?php else: ?>
                        No image available.
                    <?php endif; ?>
                </p>
            </div>
        </div>

    </div>

</body>

</html>
