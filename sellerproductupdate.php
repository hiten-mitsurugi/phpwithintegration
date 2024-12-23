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

// Query to fetch the user's role and name from the 'seller_user_info'
$query = "SELECT role_name, first_name, last_name 
          FROM seller_user_info 
          WHERE user_id = $1";
$result = pg_query_params($connect, $query, array($user_id));

if (!$result) {
    die("Error fetching user data: " . pg_last_error($connect));
}

$user = pg_fetch_assoc($result);

// Check if the user is a seller
if ($user['role_name'] != 'seller') {
    // Redirect to an access denied page or homepage if the user is not a seller
    header("Location: access_denied.php");
    exit();
}

$user_name = $user['first_name'] . ' ' . $user['last_name'];

// Fetch product details
$product_id = $_GET['id'] ?? null;

if (!$product_id) {
    echo "Error: No product ID provided.";
    exit();
}

$query_product = "SELECT product_id, seller_id, product_name, description, selling_price, capital_price, quantity, image_url
                  FROM products WHERE product_id = $1";
$result_product = pg_query_params($connect, $query_product, array($product_id));

if (!$result_product) {
    echo "Error fetching product details: " . pg_last_error($connect);
    exit();
}

$product_details = pg_fetch_assoc($result_product);

if (!$product_details) {
    echo "Error: Product not found.";
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = $_POST['product_name'];
    $description = $_POST['description'];
    $quantity = $_POST['quantity'];
    $capital_price = $_POST['capital_price'];
    $selling_price = $_POST['selling_price'];

    $image_url = $product_details['image_url']; // Default to the existing image

    // Handle file upload
    if (!empty($_FILES['image_url']['name'])) {
        $upload_dir = 'assets/product/';
        $uploaded_file = $upload_dir . basename($_FILES['image_url']['name']);
        if (move_uploaded_file($_FILES['image_url']['tmp_name'], $uploaded_file)) {
            $image_url = basename($_FILES['image_url']['name']); // Save the file name only
        } else {
            echo "Error uploading the file.";
            exit();
        }
    }

    // Update query
    $update_product_query = "
        UPDATE products
        SET 
            product_name = $1,
            description = $2,
            quantity = $3,
            capital_price = $4,
            selling_price = $5,
            image_url = $6
        WHERE product_id = $7";

    $result_update = pg_query_params($connect, $update_product_query, array(
        $product_name, $description, $quantity, $capital_price, $selling_price, $image_url, $product_id
    ));

    if ($result_update) {
        $_SESSION['update_success'] = true;
        header("Location: sellerproductlist.php");
        exit();
    } else {
        echo "Error updating the product: " . pg_last_error($connect);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Update</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
</head>

<body class="flex h-screen bg-gray-100">

    <!-- Sidebar -->
    <div class="w-64 bg-gray-800 text-white flex-shrink-0">
        <div class="p-6">
            <h2 class="text-2xl font-semibold text-gray-100">Seller Dashboard</h2>
            <p class="text-gray-400 mt-1">Welcome, <?php echo htmlspecialchars($user['first_name']); ?>!</p>
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
                    <button type="submit" name="logout" class="w-full text-left px-6 py-3 text-red-500 hover:bg-gray-700 rounded-md">Logout</button>
                </form>
            </li>
        </ul>
    </div>

    <!-- Main Content Area -->
    <div class="flex-1 p-6 bg-white overflow-y-auto">
        <div class="max-w-4xl mx-auto bg-gray-50 p-6 rounded-lg shadow-lg">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-semibold text-gray-800">Update Product</h1>
                <a href="sellerproductlist.php" class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded-lg transition duration-300 ease-in-out">
                    Back to Product List
                </a>
            </div>

            <!-- Form Container -->
            <div class="flex justify-center">
                <div class="w-full max-w-2xl space-y-4">
                    <?php if ($product_details): ?>
                        <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
                            <div>
                                <label for="product_name" class="block text-sm font-medium text-gray-700">Product Name:</label>
                                <input type="text" name="product_name" value="<?php echo htmlspecialchars($product_details['product_name']); ?>" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md">
                            </div>                        
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700">Description:</label>
                                <textarea name="description" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md"><?php echo htmlspecialchars($product_details['description']); ?></textarea>
                            </div>

                            <div>
                                <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity:</label>
                                <input type="number" name="quantity" value="<?php echo htmlspecialchars($product_details['quantity']); ?>" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md">
                            </div>

                            <div>
                                <label for="capital_price" class="block text-sm font-medium text-gray-700">Capital Price:</label>
                                <input type="number" step="0.01" name="capital_price" value="<?php echo htmlspecialchars($product_details['capital_price']); ?>" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md">
                            </div>

                            <div>
                                <label for="selling_price" class="block text-sm font-medium text-gray-700">Selling Price:</label>
                                <input type="number" step="0.01" name="selling_price" value="<?php echo htmlspecialchars($product_details['selling_price']); ?>" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md">
                            </div>

                            <div>
                                <label for="image_url" class="block text-sm font-medium text-gray-700">Upload New Image:</label>
                                <input type="file" name="image_url" accept="image/*" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md">
                            </div>

                            <div>
                                <img src="assets/product/<?php echo htmlspecialchars($product_details['image_url']); ?>" alt="Product Image" class="w-32 h-32 object-cover border mt-2">
                            </div>

                            <button type="submit" class="w-full py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none">Update Product</button>
                        </form>

                    <?php else: ?>
                        <p>No product details available. Please make sure you've provided a valid product ID.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</body>

</html>
