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
include './adminconnect.php';

// Query to fetch the user's role and name from the 'user_show_view'
$query = "SELECT r.role_name, u.first_name, u.last_name 
          FROM users u
          JOIN roles r ON u.role_id = r.role_id
          WHERE u.user_id = $1";
$result = pg_query_params($connect, $query, array($user_id));

if (!$result) {
    die("Error fetching user data: " . pg_last_error($connect));
}

$user = pg_fetch_assoc($result);

// Check if the user is an admin
if ($user['role_name'] != 'admin') {
    // Redirect to an access denied page or homepage if the user is not an admin
    header("Location: access_denied.php");
    exit();
}

$user_name = $user['first_name'] . ' ' . $user['last_name']; // User's full name from the DB

// Pagination logic
$per_page = 7; // Number of logs per page
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $per_page;

// Query to fetch the list of audit logs from the 'audit_log' table with pagination
$query_log = "SELECT * FROM audit_log ORDER BY performed_at DESC LIMIT $per_page OFFSET $offset";


$result_logs = pg_query($connect, $query_log);

if (!$result_logs) {
    die("Error fetching audit logs: " . pg_last_error($connect));
}

// Query to get the total number of audit logs for pagination
$query_count = "SELECT COUNT(*) FROM audit_log";
$result_count = pg_query($connect, $query_count);
$total_logs = pg_fetch_result($result_count, 0, 0);
$total_pages = ceil($total_logs / $per_page);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Log</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
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
        <h1 class="text-3xl font-semibold text-gray-800">Audit Log</h1>

        <!-- Audit Log Table -->
        <div class="mt-8 bg-white p-6 rounded-lg shadow-lg">
            <table class="w-full bg-white border border-gray-200 rounded-lg shadow-md">
                <thead>
                    <tr>
                        <!-- Add column headers for all columns -->
                        <th class="py-3 px-6 text-left text-sm font-medium text-gray-600 border-b">ID</th>
                        <th class="py-3 px-6 text-left text-sm font-medium text-gray-600 border-b">User Type</th>
                        <th class="py-3 px-6 text-left text-sm font-medium text-gray-600 border-b">Action Performed</th>
                        <th class="py-3 px-6 text-left text-sm font-medium text-gray-600 border-b">Table Name</th>
                        <th class="py-3 px-6 text-left text-sm font-medium text-gray-600 border-b">Column Name</th>
                        <th class="py-3 px-6 text-left text-sm font-medium text-gray-600 border-b">Permed At</th>
                        <!-- You can add other column headers here depending on the columns in your 'audit_log' table -->
                        <!-- For example, if you have a 'details' column, you would add another th like this: -->
                        <!-- <th class="py-3 px-6 text-left text-sm font-medium text-gray-600 border-b">Details</th> -->
                    </tr>
                </thead>
                <tbody>
                    <?php while ($log_data = pg_fetch_assoc($result_logs)): ?>
                        <tr>
                            <!-- Dynamically display all fetched column values -->
                            <td class="py-4 px-6 border-b text-sm text-gray-700">
                                <?php echo htmlspecialchars($log_data['id']); ?></td>
                            <td class="py-4 px-6 border-b text-sm text-gray-700">
                                <?php echo htmlspecialchars($log_data['user_type']); ?></td>
                            <td class="py-4 px-6 border-b text-sm text-gray-700">
                                <?php echo htmlspecialchars($log_data['action_performed']); ?></td>
                                <td class="py-4 px-6 border-b text-sm text-gray-700">
                                <?php echo htmlspecialchars($log_data['table_name']); ?></td>
                            <td class="py-4 px-6 border-b text-sm text-gray-700">
                                <?php echo htmlspecialchars($log_data['column_name']); ?></td>
                            <td class="py-4 px-6 border-b text-sm text-gray-700">
                                <?php echo htmlspecialchars($log_data['performed_at']); ?></td>
                            <!-- Add any additional data here as per your database -->
                            <!-- For example, if you have a 'details' column, you would display it like this: -->
                            <!-- <td class="py-4 px-6 border-b text-sm text-gray-700"><?php echo htmlspecialchars($log_data['details']); ?></td> -->
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