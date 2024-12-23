<?php
session_start();
$host = 'localhost'; // Database host
$dbname = 'project'; // Database name
$username = 'admin_user'; // Database username
$password = 'Password123!'; // Database password

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

// Database connection using PDO (assuming the connection details are correct)
try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit();
}

// Query to fetch the user's role and name from the joined tables
$query = "SELECT r.role_name, u.first_name, u.last_name 
          FROM users u
          JOIN roles r ON u.role_id = r.role_id
          WHERE u.user_id = :user_id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();

// Check if the query is successful
if (!$stmt) {
    die("Error fetching user data: " . $pdo->errorInfo());
}

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user['role_name'] != 'admin') {
    // Redirect to an access denied page or homepage if the user is not an admin
    header("Location: access_denied.php");
    exit();
}

$user_name = $user['first_name'] . ' ' . $user['last_name']; // User's name from the DB

// Initialize success flag and error message
$success = false;
$error_message = "";

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hash the password
    $contact_number = $_POST['contact_number'];
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $last_name = $_POST['last_name'];
    $region = $_POST['region'];
    $province = $_POST['province'];
    $city = $_POST['city'];
    $barangay = $_POST['barangay'];
    $gender = $_POST['gender'];
    $role_id = 2; // 'seller' role ID

    // Insert the address into the addresses table
    $address_query = "INSERT INTO addresses (region, province, city, barangay)
                      VALUES (:region, :province, :city, :barangay)";
    $address_stmt = $pdo->prepare($address_query);
    $address_stmt->bindParam(':region', $region);
    $address_stmt->bindParam(':province', $province);
    $address_stmt->bindParam(':city', $city);
    $address_stmt->bindParam(':barangay', $barangay);

    // Execute the address query
    if ($address_stmt->execute()) {
        // Get the inserted address_id
        $address_id = $pdo->lastInsertId();

        // Prepare SQL query to insert the seller
        $sql = "INSERT INTO users (username, role_id, email, password, contact_number, first_name, middle_name, last_name, address_id, gender)
                VALUES (:username, :role_id, :email, :password, :contact_number, :first_name, :middle_name, :last_name, :address_id, :gender)";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':role_id', $role_id);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':contact_number', $contact_number);
        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':middle_name', $middle_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':address_id', $address_id);
        $stmt->bindParam(':gender', $gender);

        // Execute the query
        if ($stmt->execute()) {
            $success = true; // Seller added successfully
        } else {
            $error_message = "Error creating seller. Please try again.";
        }
    } else {
        $error_message = "Error inserting address. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Seller</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <script>
        // Function to display an alert message
        function showAlert(message, isSuccess) {
            const alertDiv = document.createElement('div');
            alertDiv.className = isSuccess ? 'bg-green-500 text-white p-4 rounded-md' : 'bg-red-500 text-white p-4 rounded-md';
            alertDiv.textContent = message;
            document.body.insertBefore(alertDiv, document.body.firstChild);
            setTimeout(() => alertDiv.remove(), 5000); // Remove the alert after 5 seconds
        }
    </script>
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
                <h1 class="text-3xl font-semibold text-gray-800">Create a New Seller</h1>
                <a href="adminuserlist.php"
                    class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded-lg transition duration-300 ease-in-out">
                    Back to User List
                </a>
            </div>


            <?php
            // Display success or error message
            if ($success) {
                header("Location: adminuserlist.php"); // Redirect to a success page or the same page without POST data
                exit();
            }
            ?>

            <form action="admincreateseller.php" method="POST" class="grid grid-cols-2 gap-6">
                <!-- Form Fields as before -->
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700">Username:</label>
                    <input type="text" name="username" id="username" required
                        class="w-full px-4 py-2 mt-1 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <!-- Email Field -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email:</label>
                    <input type="email" name="email" id="email" required
                        class="w-full px-4 py-2 mt-1 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <!-- Password Field -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password:</label>
                    <input type="password" name="password" id="password" required
                        class="w-full px-4 py-2 mt-1 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
                <!-- Contact Number Field -->
                <div>
                    <label for="contact_number" class="block text-sm font-medium text-gray-700">Contact Number:</label>
                    <input type="text" name="contact_number" id="contact_number"
                        class="w-full px-4 py-2 mt-1 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <!-- First Name Field -->
                <div>
                    <label for="first_name" class="block text-sm font-medium text-gray-700">First Name:</label>
                    <input type="text" name="first_name" id="first_name"
                        class="w-full px-4 py-2 mt-1 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <!-- Middle Name Field -->
                <div>
                    <label for="middle_name" class="block text-sm font-medium text-gray-700">Middle Name:</label>
                    <input type="text" name="middle_name" id="middle_name"
                        class="w-full px-4 py-2 mt-1 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <!-- Last Name Field -->
                <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name:</label>
                    <input type="text" name="last_name" id="last_name"
                        class="w-full px-4 py-2 mt-1 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <!-- Address Section -->
                <div class="col-span-2">
                    <h3 class="text-xl font-semibold text-gray-700 mb-4">Address</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="region" class="block text-sm font-medium text-gray-700">Region:</label>
                            <input type="text" name="region" id="region" required
                                class="w-full px-4 py-2 mt-1 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        </div>

                        <div>
                            <label for="province" class="block text-sm font-medium text-gray-700">Province:</label>
                            <input type="text" name="province" id="province" required
                                class="w-full px-4 py-2 mt-1 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        </div>

                        <div>
                            <label for="city" class="block text-sm font-medium text-gray-700">City:</label>
                            <input type="text" name="city" id="city" required
                                class="w-full px-4 py-2 mt-1 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        </div>

                        <div>
                            <label for="barangay" class="block text-sm font-medium text-gray-700">Barangay:</label>
                            <input type="text" name="barangay" id="barangay" required
                                class="w-full px-4 py-2 mt-1 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        </div>
                    </div>
                </div>

                <!-- Gender Field -->
                <div>
                    <label for="gender" class="block text-sm font-medium text-gray-700">Gender:</label>
                    <select name="gender" id="gender"
                        class="w-full px-4 py-2 mt-1 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <!-- Submit Button -->
                <div class="col-span-2 mt-6">
                    <button type="submit"
                        class="w-full bg-indigo-600 text-white py-3 px-4 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">Create
                        Seller</button>
                </div>
            </form>
        </div>
    </div>
    </div>

</body>

</html>