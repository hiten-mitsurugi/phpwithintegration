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

// Fetch user information
$user_id = $_SESSION['user_id'];
include './sellerconnect.php'; // Database connection

// Query to fetch the user's role and name
$query = "SELECT role_name, first_name, last_name 
          FROM seller_user_info 
          WHERE user_id = $1";
$result = pg_query_params($connect, $query, array($user_id));

if (!$result) {
    die("Error fetching user data: " . pg_last_error($connect));
}

$user = pg_fetch_assoc($result);

if ($user['role_name'] != 'seller') {
    header("Location: access_denied.php");
    exit();
}

$user_name = $user['first_name'] . ' ' . $user['last_name'];

// Pagination logic
$per_page = 10; // Number of orders per page
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $per_page;

// Query to fetch paginated orders for the seller's products
// Query to fetch paginated orders for the seller's products

$query_orders = "SELECT o.order_id, 
                    o.created_at, 
                     oi.quantity, 
                     oi.price, 
                    p.product_name, 
                    u.first_name || ' ' || u.last_name AS buyer_name, 
                    os.status_name AS order_status  -- Fetch status_name from order_status
                FROM orders o
                JOIN order_items oi ON o.order_id = oi.order_id
                JOIN products p ON oi.product_id = p.product_id
                JOIN users u ON o.user_id = u.user_id
                JOIN order_status os ON o.status_id = os.status_id  
                WHERE p.seller_id = $1
                    AND os.status_name != 'shipped'  -- Exclude orders that are already shipped
                ORDER BY o.created_at DESC
                LIMIT $2 OFFSET $3";

// Execute the query using pg_query_params
$result_orders = pg_query_params($connect, $query_orders, array($user_id, $per_page, $offset));

if (!$result_orders) {
    die("Error fetching orders: " . pg_last_error($connect));
}


// Query to get the total number of orders for pagination
$query_count = "
    SELECT COUNT(DISTINCT o.order_id)
    FROM orders o
    JOIN order_items oi ON o.order_id = oi.order_id
    JOIN products p ON oi.product_id = p.product_id
    WHERE p.seller_id = $1";
$result_count = pg_query_params($connect, $query_count, array($user_id));

if (!$result_count) {
    die("Error fetching order count: " . pg_last_error($connect));
}

$total_orders = pg_fetch_result($result_count, 0, 0);
$total_pages = ceil($total_orders / $per_page); // Calculate total pages
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order List</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
</head>

<body class="flex h-screen bg-gray-100">

    <!-- Sidebar -->
    <div class="w-64 bg-gray-800 text-white">
        <div class="p-4">
            <h2 class="text-2xl font-bold">Seller Dashboard</h2>
            <p class="text-gray-400">Welcome, <?php echo htmlspecialchars($user_name); ?>!</p>
        </div>
        <ul class="mt-6 space-y-2">
        <li><a href="seller.php" class="block px-4 py-2 hover:bg-gray-700">Dashboard</a></li>
            <li><a href="seller-shop.php" class="block px-4 py-2 hover:bg-gray-700">Shop</a></li>
            <li><a href="sellerproductlist.php" class="block px-4 py-2 hover:bg-gray-700">Products</a></li>
            <li><a href="seller-order-list.php" class="block px-4 py-2 hover:bg-gray-700">Orders For Shipping</a></li>
            <li><a href="seller-personal-orders.php" class="block px-4 py-2 hover:bg-gray-700">Sales Summary</a></li>
            <li><a href="seller-sales-summary.php" class="block px-4 py-2 hover:bg-gray-700">Personal Purchase</a></li>
            <li>
                <form method="post">
                    <button type="submit" name="logout"
                        class="w-full text-left px-4 py-2 text-red-500 hover:bg-gray-700">Logout</button>
                </form>
            </li>
        </ul>
    </div>

    <!-- Main Content Area -->
    <div class="flex-1 p-8">
        <h1 class="text-3xl font-semibold text-gray-800">Order List</h1>

        <!-- Order Table -->
        <div class="mt-8 bg-white p-6 rounded-lg shadow-lg">
            <table class="w-full bg-white border border-gray-200 rounded-lg shadow-md">
                <thead>
                    <tr>
                        <th class="py-3 px-6 text-left text-sm font-medium text-gray-600 border-b">Order ID</th>
                        <th class="py-3 px-6 text-left text-sm font-medium text-gray-600 border-b">Product Name</th>
                        <th class="py-3 px-6 text-left text-sm font-medium text-gray-600 border-b">Buyer</th>
                        <th class="py-3 px-6 text-left text-sm font-medium text-gray-600 border-b">Quantity</th>
                        <th class="py-3 px-6 text-left text-sm font-medium text-gray-600 border-b">Total Price</th>
                        <th class="py-3 px-6 text-left text-sm font-medium text-gray-600 border-b">Order Date</th>
                        <th class="py-3 px-6 text-left text-sm font-medium text-gray-600 border-b">Order Status</th>
                        <th class="py-3 px-6 text-left text-sm font-medium text-gray-600 border-b">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = pg_fetch_assoc($result_orders)): ?>
                        <tr>
                            <td class="py-4 px-6 border-b text-sm text-gray-700">
                                <?php echo htmlspecialchars($order['order_id']); ?>
                            </td>
                            <td class="py-4 px-6 border-b text-sm text-gray-700">
                                <?php echo htmlspecialchars($order['product_name']); ?>
                            </td>
                            <td class="py-4 px-6 border-b text-sm text-gray-700">
                                <?php echo htmlspecialchars($order['buyer_name']); ?>
                            </td>
                            <td class="py-4 px-6 border-b text-sm text-gray-700">
                                <?php echo htmlspecialchars($order['quantity']); ?>
                            </td>
                            <td class="py-4 px-6 border-b text-sm text-gray-700">
                                <?php echo number_format($order['price'], 2); ?>
                            </td>
                            <td class="py-4 px-6 border-b text-sm text-gray-700">
                                <?php echo htmlspecialchars($order['created_at']); ?>
                            </td>
                            <td class="py-4 px-6 border-b text-sm text-gray-700">
                                <?php echo htmlspecialchars($order['order_status'] ?? 'Pending'); ?>
                            </td>
                            <td class="py-4 px-6 border-b text-sm text-gray-700 flex space-x-2">
                                <!-- Shipped Button -->
                                <form action="seller-update-os.php" method="post" class="inline-block">
                                    <input type="hidden" name="order_id"
                                        value="<?php echo htmlspecialchars($order['order_id']); ?>">
                                    <button type="submit" class="text-green-500 hover:text-green-700">
                                        Mark as Shipped
                                    </button>
                                </form>
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