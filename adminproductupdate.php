<?php
session_start();

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

// Database connection
$host = 'localhost';
$dbname = 'project';
$username = 'admin_user';
$password = 'Password123!';
try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit();
}

// Fetch user info and check admin access
$user_id = $_SESSION['user_id'];
$query = "SELECT u.user_id, u.first_name, u.last_name, r.role_name
          FROM users u
          LEFT JOIN roles r ON u.role_id = r.role_id
          WHERE u.user_id = :user_id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user['role_name'] !== 'admin') {
    header("Location: access_denied.php");
    exit();
}

// Fetch product details
$product_id = $_GET['id'] ?? null;

if (!$product_id) {
    echo "Error: No product ID provided.";
    exit();
}

$query_product = "SELECT product_id, seller_id, product_name, description, selling_price, capital_price, quantity, image_url, status_id
                  FROM products WHERE product_id = :product_id";
$stmt = $pdo->prepare($query_product);
$stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
$stmt->execute();
$product_details = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product_details) {
    echo "Error: Product not found.";
    exit();
}

// Fetch status options
$query_status = "SELECT status_id, status_name FROM product_status ORDER BY status_id";
$stmt_status = $pdo->prepare($query_status);
$stmt_status->execute();
$status_options = $stmt_status->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = $_POST['product_name'];
    $description = $_POST['description'];
    $quantity = $_POST['quantity'];
    $capital_price = $_POST['capital_price'];
    $selling_price = $_POST['selling_price'];
    $status_id = $_POST['status_id']; // Get the new status_id

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
            product_name = :product_name,
            description = :description,
            quantity = :quantity,
            capital_price = :capital_price,
            selling_price = :selling_price,
            image_url = :image_url,
            status_id = :status_id
        WHERE product_id = :product_id";

    $stmt = $pdo->prepare($update_product_query);
    $stmt->bindParam(':product_name', $product_name);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
    $stmt->bindParam(':capital_price', $capital_price);
    $stmt->bindParam(':selling_price', $selling_price);
    $stmt->bindParam(':image_url', $image_url);
    $stmt->bindParam(':status_id', $status_id, PDO::PARAM_INT); // Bind the new status_id
    $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $_SESSION['update_success'] = true;
        header("Location: adminproductlist.php");
        exit();
    } else {
        echo "Error updating the product.";
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
            <h2 class="text-2xl font-semibold text-gray-100">Admin Dashboard</h2>
            <p class="text-gray-400 mt-1">Welcome, <?php echo htmlspecialchars($user['first_name']); ?>!</p>
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
                <a href="adminproductlist.php" class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded-lg transition duration-300 ease-in-out">
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
                                <label for="status" class="block text-sm font-medium text-gray-700">Status:</label>
                                <select name="status_id" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md">
                                    <?php foreach ($status_options as $status): ?>
                                        <option value="<?php echo $status['status_id']; ?>" <?php echo $status['status_id'] == $product_details['status_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($status['status_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label for="image_url" class="block text-sm font-medium text-gray-700">Product Image:</label>
                                <input type="file" name="image_url" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md">
                            </div>

                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md transition duration-300 ease-in-out">
                                Update Product
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
