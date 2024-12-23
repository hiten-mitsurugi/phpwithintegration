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
include './sellerconnect.php';

// Query to fetch the user's role and name from the 'user_show_view'
$query = $query = "SELECT role_name, first_name, last_name 
                FROM seller_user_info 
                WHERE user_id = $1";
$result = pg_query_params($connect, $query, array($user_id));

if (!$result) {
    die("Error fetching user data: " . pg_last_error($connect));
}

$user = pg_fetch_assoc($result);

// Check if the user is an admin
if ($user['role_name'] != 'seller') {
    // Redirect to an access denied page or homepage if the user is not an admin
    header("Location: access_denied.php");
    exit();
}

$user_name = $user['first_name'] . ' ' . $user['last_name']; // User's full name from the DB

// Pagination logic
$per_page = 3; // Number of products per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $per_page; // Calculate the OFFSET

$seller_id = $user_id; // Assuming the logged-in seller's ID is stored in the session

// Query to fetch paginated products for the specific seller from the view
$query_product = "SELECT product_id, product_name, selling_price, capital_price, description, quantity, image_url
                  FROM seller_products_view
                  WHERE seller_id = $1
                  ORDER BY product_id ASC
                  LIMIT $2 OFFSET $3";

$result_products = pg_query_params($connect, $query_product, array($seller_id, $per_page, $offset));

if (!$result_products) {
    die("Error fetching products: " . pg_last_error($connect));
}

// Query to get the total number of products for pagination (for the specific seller)
$query_count = "SELECT COUNT(*) FROM products WHERE seller_id = $1";
$result_count = pg_query_params($connect, $query_count, array($seller_id));

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
    <title>Product List</title>
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
            <li><a href="seller-personal-orders.php" class="block px-4 py-2 hover:bg-gray-700">Personal Purchase</a></li>
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
        <h1 class="text-3xl font-semibold text-gray-800">Product List</h1>

        <div class="mt-4 mb-6">
            <a href="sellercreateproduct.php"
                class="inline-block px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg shadow-md hover:bg-blue-700 transition duration-300 ease-in-out transform hover:-translate-y-1 hover:scale-105">
                <i class="fas fa-plus-circle mr-2"></i> Add New Product
            </a>
        </div>

        <!-- Product Table -->
        <div class="mt-8 bg-white p-6 rounded-lg shadow-lg">
            <table class="w-full bg-white border border-gray-200 rounded-lg shadow-md">
                <thead>
                    <tr>
                        <th class="py-3 px-6 text-left text-sm font-medium text-gray-600 border-b">Image</th>
                        <th class="py-3 px-6 text-left text-sm font-medium text-gray-600 border-b">Product Name</th>
                        <th class="py-3 px-6 text-left text-sm font-medium text-gray-600 border-b">Selling Price</th>
                        <th class="py-3 px-6 text-left text-sm font-medium text-gray-600 border-b">Capital Price</th>
                        <th class="py-3 px-6 text-left text-sm font-medium text-gray-600 border-b">Quantity</th>
                        <th class="py-3 px-6 text-left text-sm font-medium text-gray-600 border-b">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($product_data = pg_fetch_assoc($result_products)): ?>
                        <tr>
                            <td class="py-4 px-6 border-b text-sm text-gray-700">
                                <img src="assets/product/<?php echo $product_data['image_url']; ?>" alt="Product Image"
                                    style="max-width: 100px; max-height: 100px;">
                            </td>
                            <td class="py-4 px-6 border-b text-sm text-gray-700">
                                <?php echo htmlspecialchars($product_data['product_name']); ?>
                            </td>
                            <td class="py-4 px-6 border-b text-sm text-gray-700">
                                $<?php echo number_format($product_data['selling_price'], 2); ?>
                            </td>
                            <td class="py-4 px-6 border-b text-sm text-gray-700">
                                $<?php echo number_format($product_data['capital_price'], 2); ?>
                            </td>
                            <td class="py-4 px-6 border-b text-sm text-gray-700">
                                <?php echo htmlspecialchars($product_data['quantity']); ?>
                            </td>
                            <td class="py-4 px-6 border-b text-sm">
                                <div class="flex gap-2 justify-center">
                                    <a href="sellerproductshow.php?id=<?php echo htmlspecialchars($product_data['product_id']); ?>"
                                        class="text-blue-500 hover:text-blue-700 transition duration-200"><i
                                            class="fas fa-eye"></i></a>
                                  <a href="sellerproductupdate.php?id=<?php echo htmlspecialchars($product_data['product_id']); ?>"
                                        class="text-green-500 hover:text-green-700 transition duration-200">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="sellerproductdelete.php" method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this product?');">
                                        <input type="hidden" name="product_id"
                                            value="<?php echo htmlspecialchars($product_data['product_id']); ?>">
                                        <button type="submit"
                                            class="text-red-500 hover:text-red-700 transition duration-200"><i
                                                class="fas fa-trash"></i></button>
                                    </form>
                                </div>
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
                            class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 font-semibold rounded-md hover:bg-gray-400 <?php echo $i === $page ? 'bg-blue-500 text-white' : ''; ?>">
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