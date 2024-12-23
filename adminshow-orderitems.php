<?php
session_start();

// Database connection details
$host = 'localhost'; // Database host
$dbname = 'project'; // Database name
$username = 'admin_user'; // Database username
$password = 'Password123!'; // Database password

// Check if the logout button is clicked
if (isset($_POST['logout'])) {
    session_unset(); 
    session_destroy(); 
    header("Location: index.php");
    exit();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit();
}

$query = "SELECT r.role_name, u.first_name, u.last_name 
          FROM users u
          JOIN roles r ON u.role_id = r.role_id
          WHERE u.user_id = :user_id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user['role_name'] != 'admin') {
    header("Location: access_denied.php");
    exit();
}

$user_name = $user['first_name'] . ' ' . $user['last_name']; 

if (isset($_GET['id'])) {
    $order_id = $_GET['id'];

    // Pagination setup
    $items_per_page = 3; // Number of items per page
    $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($current_page - 1) * $items_per_page;

    // Query to fetch the order details with LIMIT and OFFSET
    $query_order_items = "
        SELECT oi.order_item_id, oi.quantity, oi.price, p.product_name, p.description, p.image_url
        FROM order_items oi
        JOIN products p ON oi.product_id = p.product_id
        WHERE oi.order_id = :order_id
        LIMIT :limit OFFSET :offset";

    $stmt_order_items = $pdo->prepare($query_order_items);
    $stmt_order_items->bindParam(':order_id', $order_id, PDO::PARAM_INT);
    $stmt_order_items->bindParam(':limit', $items_per_page, PDO::PARAM_INT);
    $stmt_order_items->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt_order_items->execute();

    $order_items = $stmt_order_items->fetchAll(PDO::FETCH_ASSOC);

    // Get total number of items to calculate number of pages
    $query_total_items = "
        SELECT COUNT(*) FROM order_items WHERE order_id = :order_id";
    $stmt_total_items = $pdo->prepare($query_total_items);
    $stmt_total_items->bindParam(':order_id', $order_id, PDO::PARAM_INT);
    $stmt_total_items->execute();

    $total_items = $stmt_total_items->fetchColumn();
    $total_pages = ceil($total_items / $items_per_page);
} else {
    die("No order selected.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details</title>
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
            <form method="post">
                <button type="submit" name="logout" class="w-full text-left px-4 py-2 text-red-500 hover:bg-gray-700">Logout</button>
            </form>
        </li>
    </ul>
</div>

<!-- Main Content Area -->
<div class="flex-1 p-8">
    <h1 class="text-3xl font-semibold text-gray-800">Order Items Details</h1>

    <!-- Back Button -->
    <a href="adminorderlist.php" class="mt-4 inline-block">
        <button class="px-6 py-3 text-white bg-blue-600 rounded-lg shadow-md hover:bg-blue-700 transform hover:scale-105 transition-all duration-300 ease-in-out">
            Back to Order List
        </button>
    </a>

    <!-- Order Details -->
    <div class="mt-8 bg-white p-6 rounded-lg shadow-lg">
        <h2 class="mt-8 text-xl font-semibold text-gray-700">Order Items</h2>
        <div class="mt-4 space-y-4">
            <?php foreach ($order_items as $item): ?>
                <div class="flex items-center bg-gray-50 p-4 rounded-lg shadow-sm">
                    <img src="assets/product/<?php echo htmlspecialchars($item['image_url']); ?>" alt="Product Image" class="w-24 h-24 object-cover border">
                    <div class="ml-4">
                        <p class="text-gray-800 font-semibold"><?php echo htmlspecialchars($item['product_name']); ?></p>
                        <p class="text-gray-600"><?php echo htmlspecialchars($item['description']); ?></p>
                        <p class="text-gray-600">Quantity: <?php echo htmlspecialchars($item['quantity']); ?></p>
                        <p class="text-gray-600">Price: <?php echo htmlspecialchars(number_format($item['price'], 2)); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination Controls -->
        <div class="mt-6 flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <?php if ($current_page > 1): ?>
                    <a href="?id=<?php echo $order_id; ?>&page=<?php echo $current_page - 1; ?>" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">Previous</a>
                <?php endif; ?>

                <?php if ($current_page < $total_pages): ?>
                    <a href="?id=<?php echo $order_id; ?>&page=<?php echo $current_page + 1; ?>" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">Next</a>
                <?php endif; ?>
            </div>
            <span class="text-gray-700">Page <?php echo $current_page; ?> of <?php echo $total_pages; ?></span>
        </div>
    </div>
</div>

</body>
</html>
