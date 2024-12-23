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
include './customerconnect.php';

// Fetch user information
$query_user = "SELECT * FROM users WHERE user_id = $user_id";
$result_user = pg_query($connect, $query_user);
$user = pg_fetch_assoc($result_user);

// Pagination: Define the number of items per page
$items_per_page = 5;

// Get the current page from URL (defaults to 1 if not set)
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Calculate the offset for the query
$offset = ($page - 1) * $items_per_page;

// Fetch the order items using the get_order_items function with pagination
$query_order_items = "SELECT * FROM get_order_items($user_id) LIMIT $items_per_page OFFSET $offset";
$result_order_items = pg_query($connect, $query_order_items);

if (!$result_order_items) {
    die("Error fetching order items: " . pg_last_error());
}

// Count total order items for pagination calculation
$query_total_items = "SELECT COUNT(*) AS total FROM get_order_items($user_id)";
$result_total_items = pg_query($connect, $query_total_items);
$total_items = pg_fetch_assoc($result_total_items)['total'];

// Map status_id to human-readable status
function getStatus($status_id)
{
    switch ($status_id) {
        case 1:
            return 'Paid';
        case 2:
            return 'Shipped';
        default:
            return 'Unknown';
    }
}

// Calculate total number of pages
$total_pages = ceil($total_items / $items_per_page);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script> <!-- Tailwind CDN -->
</head>

<body class="bg-white text-black">

    <header class="bg-black text-white p-4">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <h1 class="text-2xl font-bold">Welcome, <?php echo htmlspecialchars($user['username']); ?>!</h1>
            <nav>
                <ul class="flex space-x-6">
                    <li><a href="shop.php" class="hover:text-green-400">Shop</a></li>
                    <form method="post">
                        <button type="submit" name="logout"
                            class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded">Logout</a>
                    </form>
                </ul>
            </nav>
        </div>
    </header>

    <main class="max-w-7xl mx-auto p-6">
        <section id="user-info" class="bg-green-100 shadow-md p-6 rounded-lg mb-8">
            <h2 class="text-xl font-semibold text-black mb-4">Your Information</h2>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
        </section>

        <section id="order-items" class="bg-green-100 shadow-md p-6 rounded-lg">
            <h2 class="text-xl font-semibold text-black mb-4">Order Items</h2>
            <?php if (pg_num_rows($result_order_items) > 0): ?>
                <table class="w-full table-auto border-collapse">
                    <thead>
                        <tr class="border-b bg-black text-white">
                            <th class="px-4 py-2 text-left">Image</th>
                            <th class="px-4 py-2 text-left">Product Name</th>
                            <th class="px-4 py-2 text-left">Price</th>
                            <th class="px-4 py-2 text-left">Quantity</th>
                            <th class="px-4 py-2 text-left">Total Price</th>
                            <th class="px-4 py-2 text-left">Order Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($item = pg_fetch_assoc($result_order_items)): ?>
                            <tr class="border-b hover:bg-green-200">
                                <td class="px-4 py-2"><img src="assets/product/<?php echo htmlspecialchars($item['img_url']); ?>" alt="Product Image" class="w-16 h-16 object-cover"></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($item['product_name']); ?></td>
                                <td class="px-4 py-2">$<?php echo number_format($item['product_price'], 2); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($item['quantity']); ?></td>
                                <td class="px-4 py-2">$<?php echo number_format($item['total_price'], 2); ?></td>
                                <td class="px-4 py-2"><?php echo getStatus($item['order_status_id']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="flex justify-between mt-6">
                    <div>
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>" class="bg-black text-white px-4 py-2 rounded hover:bg-green-400">Previous</a>
                        <?php endif; ?>
                    </div>
                    <div>
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?>" class="bg-black text-white px-4 py-2 rounded hover:bg-green-400">Next</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <p class="text-black">No order items found.</p>
            <?php endif; ?>
        </section>

    </main>

</body>

</html>
