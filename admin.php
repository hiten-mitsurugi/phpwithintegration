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
    // Redirect to an access denied page or homepage if the user is not an admin
    header("Location: access_denied.php");
    exit();
}

$user_name = $user['first_name'] . ' ' . $user['last_name']; // User's full name from the DB

// Pagination logic
$per_page = 7; // Number of orders per page
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $per_page;

// Query to fetch seller performance data from the materialized view with pagination
$query_performance = "SELECT seller_id, seller_name, quantity_sold, total_sales, net_profit
                      FROM seller_performances_summary
                      ORDER BY seller_id
                      LIMIT $per_page OFFSET $offset";

$result_performance = pg_query($connect, $query_performance);

if (!$result_performance) {
    die("Error fetching performance data: " . pg_last_error($connect));
}

// Query to get the total number of records for pagination
$query_count = "SELECT COUNT(*) FROM seller_performances_summary";
$result_count = pg_query($connect, $query_count);
$total_records = pg_fetch_result($result_count, 0, 0);
$total_pages = ceil($total_records / $per_page);

// Query to calculate the total net profit of the admin (sum of all seller's net profits)
$query_total_profit = "SELECT SUM(net_profit) FROM seller_performances_summary";
$result_total_profit = pg_query($connect, $query_total_profit);

if (!$result_total_profit) {
    die("Error calculating total net profit: " . pg_last_error($connect));
}

$total_net_profit = pg_fetch_result($result_total_profit, 0, 0);

// Query to calculate the total sales of the admin (sum of all seller's total sales)
$query_total_sales = "SELECT SUM(total_sales) FROM seller_performances_summary";
$result_total_sales = pg_query($connect, $query_total_sales);

if (!$result_total_sales) {
    die("Error calculating total sales: " . pg_last_error($connect));
}

$total_sales = pg_fetch_result($result_total_sales, 0, 0);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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
        <section id="user-info" class="bg-white shadow-md p-6 rounded-lg mb-8">
            <div class="mt-4 text-xl font-semibold text-gray-800">
                <h2 class="text-xl font-semibold text-black mb-4">Hello <?php echo htmlspecialchars($user_name); ?>!
                </h2>
                <p class="text-gray-600">Total Sales: <strong
                        class="text-blue-500">$<?php echo number_format($total_sales, 2); ?></strong></p>
                <p class="text-gray-600">Total Admin Profit: <strong
                        class="text-blue-500">$<?php echo number_format($total_net_profit, 2); ?></strong></p>
            </div>
        </section>

        <div class="mt-8 bg-white p-6 rounded-lg shadow-lg">
            <table class="w-full bg-white border border-gray-200 rounded-lg shadow-md">
                <thead>
                    <tr>
                        <th class="py-3 px-6 text-left text-sm font-medium text-gray-600 border-b">Seller Name</th>
                        <th class="py-3 px-6 text-left text-sm font-medium text-gray-600 border-b">Quantity Sold</th>
                        <th class="py-3 px-6 text-left text-sm font-medium text-gray-600 border-b">Total Sales</th>
                        <th class="py-3 px-6 text-left text-sm font-medium text-gray-600 border-b">Admin Profit</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($performance_data = pg_fetch_assoc($result_performance)): ?>
                        <tr>
                            <td class="py-4 px-6 border-b text-sm text-gray-700">
                                <?php echo htmlspecialchars($performance_data['seller_name']); ?>
                            </td>
                            <td class="py-4 px-6 border-b text-sm text-gray-700">
                                <?php echo htmlspecialchars($performance_data['quantity_sold']); ?>
                            </td>
                            <td class="py-4 px-6 border-b text-sm text-gray-700">
                                <?php echo htmlspecialchars($performance_data['total_sales']); ?>
                            </td>
                            <td class="py-4 px-6 border-b text-sm text-gray-700">
                                <?php echo htmlspecialchars($performance_data['net_profit']); ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="flex justify-between items-center mt-4">
                <div>
                    <!-- Previous Button -->
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>"
                            class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 font-semibold rounded-md hover:bg-gray-400">
                            <i class="fas fa-chevron-left"></i> Previous
                        </a>
                    <?php endif; ?>
                </div>
                <div class="space-x-2">
                    <!-- Page Numbers -->
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>"
                            class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 font-semibold rounded-md hover:bg-gray-300">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
                <div>
                    <!-- Next Button -->
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?>"
                            class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 font-semibold rounded-md hover:bg-gray-400">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</body>

</html>