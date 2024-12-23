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

$user_name = $user['first_name'] . ' ' . $user['last_name']; // User's full name from the DB

// Query to fetch the top-selling products based on the 'top_selling_products' view
$query_product = "SELECT p.product_id, p.product_name, COALESCE(SUM(oi.quantity), 0) AS total_quantity_sold 
                  FROM products p
                  JOIN order_items oi ON p.product_id = oi.product_id
                  JOIN orders o ON oi.order_id = o.order_id
                  GROUP BY p.product_id, p.product_name
                  ORDER BY total_quantity_sold DESC
                  LIMIT 5";

$result_products = pg_query($connect, $query_product);

if (!$result_products) {
    die("Error fetching top-selling products: " . pg_last_error($connect));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Top Selling Products</title>
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
        <h1 class="text-3xl font-semibold text-gray-800">Top 5 Selling Products</h1>

        <!-- Product Table -->
        <div class="mt-8 bg-white p-6 rounded-lg shadow-lg">
            <table class="w-full bg-white border border-gray-200 rounded-lg shadow-md">
                <thead>
                    <tr>
                        <th class="py-3 px-6 text-left text-sm font-medium text-gray-600 border-b">Product Id</th>
                        <th class="py-3 px-6 text-left text-sm font-medium text-gray-600 border-b">Product Name</th>
                        <th class="py-3 px-6 text-left text-sm font-medium text-gray-600 border-b">Total Quantity Sold</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($product_data = pg_fetch_assoc($result_products)): ?>
                        <tr>
                            <td class="py-4 px-6 border-b text-sm text-gray-700">
                                <?php echo htmlspecialchars($product_data['product_id']); ?></td>
                            <td class="py-4 px-6 border-b text-sm text-gray-700">
                                <?php echo htmlspecialchars($product_data['product_name']); ?></td>
                            <td class="py-4 px-6 border-b text-sm text-gray-700">
                                <?php echo htmlspecialchars($product_data['total_quantity_sold']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>

</html>
