<?php
session_start();
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
$sql = "SELECT first_name, last_name FROM users WHERE user_id = :user_id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    $user_name = $user['first_name'] . ' ' . $user['last_name']; // Concatenate first and last name
} else {
    $user_name = "Guest"; // Default value if user is not found
}

// Initialize success flag and error message
$success = false;
$error_message = "";

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = $_POST['product_name'];
    $description = $_POST['description'];
    $quantity = $_POST['quantity'];
    $capital_price = $_POST['capital_price'];
    $selling_price = $_POST['selling_price'];

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image_name = basename($_FILES['image']['name']);
        $image_target = 'assets/product/' . $image_name;

        // Move uploaded file to the correct directory
        if (move_uploaded_file($_FILES['image']['tmp_name'], $image_target)) {
            $image_url = $image_name; // Store the image name (not full path) in the database
        } else {
            $error_message = "Error uploading image.";
        }
    } else {
        $image_url = ''; // Default value if no image is uploaded
    }

    // Insert product into the database
    if (empty($error_message)) {
        $sql = "INSERT INTO products (seller_id, product_name, description, quantity, capital_price, selling_price, image_url)
                VALUES (:seller_id, :product_name, :description, :quantity, :capital_price, :selling_price, :image_url)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':seller_id', $user_id);
        $stmt->bindParam(':product_name', $product_name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $stmt->bindParam(':capital_price', $capital_price, PDO::PARAM_STR);
        $stmt->bindParam(':selling_price', $selling_price, PDO::PARAM_STR);
        $stmt->bindParam(':image_url', $image_url);

        if ($stmt->execute()) {
            $success = true;
        } else {
            $error_message = "Error creating product. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Product</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
</head>

<body class="flex h-screen bg-gray-100">

    <!-- Sidebar -->

    <!-- Main Content Area -->
    <div class="w-64 bg-gray-800 text-white flex-shrink-0">
        <div class="p-6">
            <h2 class="text-2xl font-semibold text-gray-100">Admin Dashboard</h2>
            <p class="text-gray-400 mt-1">Welcome, <?php echo htmlspecialchars($user_name); ?>!</p>
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
                        class="w-full text-left px-6 py-3 text-red-500 hover:bg-gray-700 rounded-md">Logout</button>
                </form>
            </li>
        </ul>
    </div>

    <!-- Main Content Area -->
    <div class="flex-1 p-2 overflow-y-auto">
        <div class="max-w-5xl mx-auto bg-white p-6 rounded-lg shadow-md">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-3xl font-semibold text-gray-800">Create a New Product</h1>
                <a href="adminproductlist.php"
                    class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded-lg transition duration-300 ease-in-out">
                    Back to Product List
                </a>
            </div>

            <?php if ($success): ?>
                <div class="bg-green-500 text-white p-4 rounded-md mb-4">
                    Product created successfully!
                </div>
            <?php elseif ($error_message): ?>
                <div class="bg-red-500 text-white p-4 rounded-md mb-4">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <form action="admincreateproduct.php" method="POST" enctype="multipart/form-data" class="grid grid-cols-2 gap-6">
                <!-- Product Name -->
                <div>
                    <label for="product_name" class="block text-sm font-medium text-gray-700">Product Name:</label>
                    <input type="text" name="product_name" id="product_name" required
                        class="w-full px-4 py-2 mt-1 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description:</label>
                    <textarea name="description" id="description" rows="4" required
                        class="w-full px-4 py-2 mt-1 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none"></textarea>
                </div>

                <!-- Quantity -->
                <div>
                    <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity:</label>
                    <input type="number" name="quantity" id="quantity" required
                        class="w-full px-4 py-2 mt-1 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <!-- Capital Price -->
                <div>
                    <label for="capital_price" class="block text-sm font-medium text-gray-700">Capital Price:</label>
                    <input type="number" name="capital_price" id="capital_price" required step="0.01"
                        class="w-full px-4 py-2 mt-1 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <!-- Selling Price -->
                <div>
                    <label for="selling_price" class="block text-sm font-medium text-gray-700">Selling Price:</label>
                    <input type="number" name="selling_price" id="selling_price" required step="0.01"
                        class="w-full px-4 py-2 mt-1 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <!-- Image Upload -->
                <div>
                    <label for="image" class="block text-sm font-medium text-gray-700">Product Image:</label>
                    <input type="file" name="image" id="image" accept="image/*"
                        class="w-full px-4 py-2 mt-1 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <!-- Submit Button -->
                <div class="col-span-2 mt-6">
                    <button type="submit"
                        class="w-full bg-indigo-600 text-white py-3 px-4 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">Create Product</button>
                </div>
            </form>
        </div>
    </div>

</body>

</html>
