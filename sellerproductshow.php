<?php
session_start();
$host = 'localhost'; // Database host
$dbname = 'project'; // Database name
$username = 'seller_user'; // Database username
$password = 'Password123!'; // Database password

// Check if the logout button is clicked
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit();
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Get logged-in user ID
$user_id = $_SESSION['user_id'];

// Database connection using PDO
try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit();
}

// Fetch the user’s name from the database
$query = "SELECT role_name, first_name, last_name 
          FROM seller_user_info 
          WHERE user_id = :user_id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);


if ($user['role_name'] != 'seller') {
    // Redirect to an access denied page or homepage if the user is not an admin
    header("Location: access_denied.php");
    exit();
}

$user_name = $user['first_name'] . ' ' . $user['last_name']; 

// Fetch the specific product details
if (isset($_GET['id'])) {
    $product_id = $_GET['id'];

    // Query to fetch the product details
    $query_product = "
        SELECT p.product_id, p.product_name, p.description, p.quantity, 
               p.capital_price, p.selling_price, p.image_url, p.created_at
        FROM products p
        WHERE p.product_id = :product_id AND p.seller_id = :user_id"; // Ensure only the seller's products are fetched

    // Prepare the query and bind the parameter
    $stmt_product = $pdo->prepare($query_product);
    $stmt_product->bindParam(':product_id', $product_id, PDO::PARAM_INT);
    $stmt_product->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt_product->execute();

    // Fetch the product details
    $product_details = $stmt_product->fetch(PDO::FETCH_ASSOC);

    // Check if the query returned data
    if (!$product_details) {
        die("Error fetching product details or no permission to view this product.");
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
                    <button type="submit" name="logout" class="w-full text-left px-4 py-2 text-red-500 hover:bg-gray-700">Logout</button>
                </form>
            </li>
        </ul>
    </div>

    <!-- Main Content Area -->
    <div class="flex-1 p-8">
        <h1 class="text-3xl font-semibold text-gray-800">Product Details</h1>

        <!-- Back Button -->
        <a href="sellerproductlist.php" class="mt-4 inline-block">
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
