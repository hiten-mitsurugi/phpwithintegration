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

// Fetch user information (example)
$user_id = $_SESSION['user_id'];

$host = 'localhost'; // Database host
$dbname = 'project'; // Database name
$username = 'admin_user'; // Database username
$password = 'Password123!'; // Database password

// Database connection using PDO (assuming the connection details are correct)
try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit();
}

// Query to fetch the user's role and name from the joined tables
$query = "SELECT u.user_id, u.first_name, u.last_name, u.email, u.contact_number, 
                 r.role_name, a.region, a.province, a.city, a.barangay
          FROM users u
          LEFT JOIN roles r ON u.role_id = r.role_id
          LEFT JOIN addresses a ON u.address_id = a.address_id
          WHERE u.user_id = :user_id";

$stmt = $pdo->prepare($query);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();

$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if role_name is available and verify if the user is an admin
if ($user['role_name'] != 'admin') {
    // Debugging output
    echo "Access Denied: You are not an admin.";
    header("Location: access_denied.php");
    exit();
}

$user_name = $user['first_name'] . ' ' . $user['last_name']; // User's name from the DB

// Initialize success flag
$success = false;

// Your page content goes here, after these checks


// For update functionality, we need to check if 'id' is provided in the URL to fetch specific user
if (isset($_GET['id'])) {
    $user_id_to_update = $_GET['id']; // User ID for the update page

    // Query to fetch the user's details based on the provided 'id' in the URL
    $query_user = "
        SELECT u.user_id, u.first_name, u.last_name, u.email, u.contact_number, 
               a.region, a.province, a.city, a.barangay, r.role_name
        FROM users u
        JOIN addresses a ON u.address_id = a.address_id
        JOIN roles r ON u.role_id = r.role_id
        WHERE u.user_id = :user_id";

    // Prepare and execute the query
    $stmt_user = $pdo->prepare($query_user);
    $stmt_user->bindParam(':user_id', $user_id_to_update, PDO::PARAM_INT);
    $stmt_user->execute();
    $user_details = $stmt_user->fetch(PDO::FETCH_ASSOC);

    // If user details are not found, display an error
    if (!$user_details) {
        die("Error fetching user details.");
    }

    // If the form is submitted, update the user data
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Retrieve updated data from the form
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $email = $_POST['email'];
        $contact_number = $_POST['contact_number'];
        $region = $_POST['region'];
        $province = $_POST['province'];
        $city = $_POST['city'];
        $barangay = $_POST['barangay'];
        $role_name = $_POST['role_name'];

        // Get role_id based on role_name
        $role_query = "SELECT role_id FROM roles WHERE role_name = :role_name";
        $stmt_role = $pdo->prepare($role_query);
        $stmt_role->bindParam(':role_name', $role_name);
        $stmt_role->execute();
        $role = $stmt_role->fetch(PDO::FETCH_ASSOC);
        $role_id = $role['role_id'];

        // Update the user data
        $update_user_query = "
            UPDATE users
            SET role_id = :role_id,
                first_name = :first_name,
                last_name = :last_name,
                email = :email,
                contact_number = :contact_number
            WHERE user_id = :user_id";

        // Prepare the update statement
        $stmt_user_update = $pdo->prepare($update_user_query);
        $stmt_user_update->bindParam(':role_id', $role_id);
        $stmt_user_update->bindParam(':first_name', $first_name);
        $stmt_user_update->bindParam(':last_name', $last_name);
        $stmt_user_update->bindParam(':email', $email);
        $stmt_user_update->bindParam(':contact_number', $contact_number);
        $stmt_user_update->bindParam(':user_id', $user_id_to_update, PDO::PARAM_INT);

        // Execute the update for the users table
        $stmt_user_update->execute();

        // Update the address data
        $update_address_query = "
            UPDATE addresses
            SET region = :region,
                province = :province,
                city = :city,
                barangay = :barangay
            WHERE address_id = (SELECT address_id FROM users WHERE user_id = :user_id)";

        $stmt_address_update = $pdo->prepare($update_address_query);
        $stmt_address_update->bindParam(':region', $region);
        $stmt_address_update->bindParam(':province', $province);
        $stmt_address_update->bindParam(':city', $city);
        $stmt_address_update->bindParam(':barangay', $barangay);
        $stmt_address_update->bindParam(':user_id', $user_id_to_update, PDO::PARAM_INT);

        // Execute the update for the addresses table
        $stmt_address_update->execute();

        $_SESSION['update_success'] = true;

        // Redirect to adminuserlist.php
        header("Location: adminuserlist.php");
        exit();
    }
} else {
    echo "No user ID provided.";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Update</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    
</head>

<body class="flex h-screen bg-gray-100">

    <!-- Sidebar -->
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
    <!-- Main Content Area -->
    <div class="flex-1 p-6 bg-white overflow-y-auto">
        <div class="max-w-4xl mx-auto bg-gray-50 p-6 rounded-lg shadow-lg">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-semibold text-gray-800">Update User</h1>
                <a href="adminuserlist.php"
                    class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded-lg transition duration-300 ease-in-out">
                    Back to User List
                </a>
            </div>

            <!-- Form Container -->
            <div class="flex justify-center">
                <div class="w-full max-w-2xl space-y-4">
                    <!-- Form -->
                    <form action="" method="POST" class="space-y-4">
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-gray-700">First Name:</label>
                            <input type="text" name="first_name"
                                value="<?php echo htmlspecialchars($user_details['first_name']); ?>" required
                                class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>

                        <div>
                            <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name:</label>
                            <input type="text" name="last_name"
                                value="<?php echo htmlspecialchars($user_details['last_name']); ?>" required
                                class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <div>
                            <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                            <select id="role" name="role_name" required
                                class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="admin" <?php echo ($user_details['role_name'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                                <option value="seller" <?php echo ($user_details['role_name'] == 'seller') ? 'selected' : ''; ?>>Seller</option>
                                <option value="customer" <?php echo ($user_details['role_name'] == 'customer') ? 'selected' : ''; ?>>Customer</option>
                            </select>
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email:</label>
                            <input type="email" name="email"
                                value="<?php echo htmlspecialchars($user_details['email']); ?>" required
                                class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>

                        <div>
                            <label for="contact_number" class="block text-sm font-medium text-gray-700">Contact
                                Number:</label>
                            <input type="text" name="contact_number"
                                value="<?php echo htmlspecialchars($user_details['contact_number']); ?>" required
                                class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>

                        <div>
                            <label for="region" class="block text-sm font-medium text-gray-700">Region:</label>
                            <input type="text" name="region"
                                value="<?php echo htmlspecialchars($user_details['region']); ?>" required
                                class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>

                        <div>
                            <label for="province" class="block text-sm font-medium text-gray-700">Province:</label>
                            <input type="text" name="province"
                                value="<?php echo htmlspecialchars($user_details['province']); ?>" required
                                class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>

                        <div>
                            <label for="city" class="block text-sm font-medium text-gray-700">City:</label>
                            <input type="text" name="city"
                                value="<?php echo htmlspecialchars($user_details['city']); ?>" required
                                class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>

                        <div>
                            <label for="barangay" class="block text-sm font-medium text-gray-700">Barangay:</label>
                            <input type="text" name="barangay"
                                value="<?php echo htmlspecialchars($user_details['barangay']); ?>" required
                                class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>

                        <button type="submit"
                            class="w-full py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            Update
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>


</body>

</html>