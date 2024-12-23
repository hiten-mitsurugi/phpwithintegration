<?php
session_start();

// Check if the logout button is clicked
if (isset($_POST['logout'])) {
    session_unset(); // Remove all session variables
    session_destroy(); // Destroy the session
    header("Location: index.php");
    exit();
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id']; // Fetch the logged-in user's ID

// Database connection (assuming you already have the connection set up)
include './adminconnect.php';

// Fetch the user's role and name
$query = "SELECT role_name, first_name, last_name FROM seller_user_info WHERE user_id = $1";
$result = pg_query_params($connect, $query, array($user_id));

if (!$result) {
    die("Error fetching user data: " . pg_last_error($connect));
}

$user = pg_fetch_assoc($result);


if ($user['role_name'] != 'admin') {
    header("Location: access_denied.php");
    exit();
}

$user_name = $user['first_name'] . ' ' . $user['last_name']; // User's name from the DB
$net_profit_query = "
    SELECT COALESCE(SUM(net_profit), 0) AS total_net_profit
    FROM get_product_sales_summary_for_seller($1);
";
$net_profit_result = pg_query_params($connect, $net_profit_query, array($user_id));

if (!$net_profit_result) {
    die("Error fetching net profit: " . pg_last_error($connect));
}

$net_profit_row = pg_fetch_assoc($net_profit_result);
$total_net_profit = $net_profit_row['total_net_profit'] ?? 0.00; 
// Fetch financial summary
$financials_query = "
    SELECT 
        COALESCE(SUM(oi.price * oi.quantity), 0) AS total_sales,
        COALESCE(SUM((oi.price - p.capital_price) * oi.quantity), 0) AS total_gross_profit,
        COALESCE(SUM(p.capital_price * oi.quantity), 0) AS total_capital
    FROM order_items oi
    INNER JOIN products p ON oi.product_id = p.product_id
    WHERE oi.seller_id = $1;
";
$financials_result = pg_query_params($connect, $financials_query, array($user_id));

if (!$financials_result) {
    die("Error fetching financial data: " . pg_last_error($connect));
}

$financials_row = pg_fetch_assoc($financials_result);
$total_sales = $financials_row['total_sales'] ?? 0.00;
$total_gross_profit = $financials_row['total_gross_profit'] ?? 0.00;
$total_capital = $financials_row['total_capital'] ?? 0.00; // Corrected variable for total capital


// Pagination logic
$per_page = 10; // Number of products per page
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $per_page;

// Query to fetch paginated sales summary for the seller's products
$query_sales_summary = "
    SELECT product_id, 
           product_name, 
           total_quantity_sold, 
           total_revenue, 
           gross_profit, 
           net_profit
    FROM get_product_sales_summary_for_seller($1)
    LIMIT $2 OFFSET $3";
$result_sales_summary = pg_query_params($connect, $query_sales_summary, array($user_id, $per_page, $offset));

if (!$result_sales_summary) {
    die("Error fetching product sales summary: " . pg_last_error($connect));
}

// Query to get the total number of products for pagination
$query_count = "
    SELECT COUNT(DISTINCT p.product_id)
    FROM products p
    LEFT JOIN order_items oi ON p.product_id = oi.product_id
    LEFT JOIN orders o ON oi.order_id = o.order_id
    WHERE p.seller_id = $1"; 
$result_count = pg_query_params($connect, $query_count, array($user_id));

if (!$result_count) {
    die("Error fetching product count: " . pg_last_error($connect));
}

$total_products = pg_fetch_result($result_count, 0, 0);
$total_pages = ceil($total_products / $per_page); // Calculate total pages
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personal Sales</title>
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
                    <button type="submit" name="logout" class="w-full text-left px-4 py-2 text-red-500 hover:bg-gray-700">Logout</button>
                </form>
            </li>
        </ul>
    </div>

    <!-- Main Content Area -->
    <div class="flex-1 p-8">
        <section id="user-info" class="bg-white shadow-md p-6 rounded-lg mb-8">
            <h2 class="text-xl font-semibold text-black mb-4">Hello <?php echo htmlspecialchars($user_name); ?>!</h2>
            <p class="text-gray-600">Total Sales: <strong class="text-blue-500">$<?php echo number_format($total_sales, 2); ?></strong></p>
            <p class="text-gray-600">Total Capital: <strong class="text-purple-500">$<?php echo number_format($total_capital, 2); ?></strong></p>
            <p class="text-gray-600">Total Gross Profit: <strong class="text-yellow-500">$<?php echo number_format($total_gross_profit, 2); ?></strong></p>
            <p class="text-gray-600">Total Net Profit: <strong class="text-green-500">$<?php echo number_format($total_net_profit, 2); ?></strong></p>
        </section>

        <!-- Sales Summary Table -->
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <h1 class="text-3xl font-semibold text-gray-800 mb-4">Product Sales Summary</h1>
            <table class="w-full bg-white border border-gray-200 rounded-lg shadow-md">
                <thead>
                    <tr>
                        <th class="py-3 px-6 text-left text-sm font-medium text-gray-600 border-b">Product ID</th>
                        <th class="py-3 px-6 text-left text-sm font-medium text-gray-600 border-b">Product Name</th>
                        <th class="py-3 px-6 text-left text-sm font-medium text-gray-600 border-b">Quantity Sold</th>
                        <th class="py-3 px-6 text-left text-sm font-medium text-gray-600 border-b">Total Revenue</th>
                        <th class="py-3 px-6 text-left text-sm font-medium text-gray-600 border-b">Gross Profit</th>
                        <th class="py-3 px-6 text-left text-sm font-medium text-gray-600 border-b">Net Profit</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($product = pg_fetch_assoc($result_sales_summary)): ?>
                        <tr>
                            <td class="py-4 px-6 border-b text-sm text-gray-700">
                                <?php echo htmlspecialchars($product['product_id']); ?>
                            </td>
                            <td class="py-4 px-6 border-b text-sm text-gray-700">
                                <?php echo htmlspecialchars($product['product_name']); ?>
                            </td>
                            <td class="py-4 px-6 border-b text-sm text-gray-700">
                                <?php echo htmlspecialchars($product['total_quantity_sold']); ?>
                            </td>
                            <td class="py-4 px-6 border-b text-sm text-gray-700">
                                <?php echo number_format($product['total_revenue'], 2); ?>
                            </td>
                            <td class="py-4 px-6 border-b text-sm text-gray-700">
                                <?php echo number_format($product['gross_profit'], 2); ?>
                            </td>
                            <td class="py-4 px-6 border-b text-sm text-gray-700">
                                <?php echo number_format($product['net_profit'], 2); ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="mt-4 flex justify-center">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>"
                        class="px-4 py-2 mx-1 rounded-lg <?php echo $i == $page ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-800'; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        </div>
    </div>
</body>

</html>
