<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to the login page if not logged in
    header("Location: index.php");
    exit();
}

// Logout functionality
if (isset($_POST['logout'])) {
    session_unset(); // Remove all session variables
    session_destroy(); // Destroy the session

    // Redirect to the login page
    header("Location: index.php");
    exit();
}

include './customerconnect.php';

// Get the user ID from the session
$user_id = $_SESSION['user_id'];

// Query to retrieve the user's role_id
$query_role = "SELECT role_id FROM users WHERE user_id = $1";
$result_role = pg_query_params($connect, $query_role, array($user_id));

if (!$result_role) {
    die("Error fetching user role: " . pg_last_error());
}

$user_role = pg_fetch_assoc($result_role);

// Check if the user's role_id is not 3 (customer)
if ($user_role['role_id'] != 3) {
    // Redirect to access denied page if role_id is not 3
    header("Location: access_denied.php");
    exit();
}

// Query to retrieve cart items for the logged-in user
$query = "SELECT p.product_name, p.image_url, p.selling_price, c.quantity
          FROM cart_items c
          INNER JOIN products p ON c.product_id = p.product_id
          WHERE c.user_id = $1";

$result = pg_query_params($connect, $query, array($user_id)); // Use the existing connection

if (!$result) {
    die("Error in SQL query: " . pg_last_error());
}

$cart_items = array();

// Fetch data from the result set
while ($row = pg_fetch_assoc($result)) {
    $cart_items[] = $row;
}

// Free result set
pg_free_result($result);

// Define the shipping fee
$shippingFee = 300;

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 text-gray-800">

    <!-- Header -->
    <header class="bg-black text-white p-4 flex justify-between items-center">

        <h1 class="text-2xl font-bold">Payment</h1>
        <form method="post">
            <button type="submit" name="logout" class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded">Logout</a>
        </form>
    </header>

    <!-- Container (Centering the form and making it narrower) -->
    <div class="container mx-auto p-6 bg-white shadow-lg rounded mt-8 max-w-2xl">


        <!-- Cart Table -->
        <table class="w-full table-auto border-collapse border border-gray-200 mb-6">
            <thead class="bg-black text-white">
                <tr>
                    <th class="p-3 border border-gray-200">Image</th>
                    <th class="p-3 border border-gray-200">Product Name</th>
                    <th class="p-3 border border-gray-200">Price</th>
                    <th class="p-3 border border-gray-200">Quantity</th>
                    <th class="p-3 border border-gray-200">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $totalCost = 0;
                if (!empty($cart_items)) {
                    foreach ($cart_items as $item) {
                        $subtotal = $item['quantity'] * $item['selling_price'];
                        $totalCost += $subtotal;
                        echo "<tr class='hover:bg-gray-50'>";
                        echo "<td class='p-3 border border-gray-200 text-center'>
                                <img src='assets/product/{$item['image_url']}' alt='{$item['product_name']}' class='w-16 h-16 object-cover rounded'>
                              </td>";
                        echo "<td class='p-3 border border-gray-200 text-center'>{$item['product_name']}</td>";
                        echo "<td class='p-3 border border-gray-200 text-center'>₱{$item['selling_price']}</td>";
                        echo "<td class='p-3 border border-gray-200 text-center'>{$item['quantity']}</td>";
                        echo "<td class='p-3 border border-gray-200 text-center'>₱{$subtotal}</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5' class='text-center p-4'>Cart is empty</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <!-- Shipping Fee & Total Cost -->
        <?php $totalCost += $shippingFee; ?>
        <div class="flex justify-between text-lg font-semibold mb-6">
            <p>Shipping Fee: <span class="text-red-600">₱<?php echo $shippingFee; ?></span></p>
            <p>Total Cost: <span class="text-red-600">₱<?php echo $totalCost; ?></span></p>
        </div>

        <!-- Payment Form (Centered and Narrowed) -->
        <form id="payment-form" action="process_payment.php" method="post" class="space-y-4">
            <input type="hidden" name="total_cost" value="<?php echo $totalCost; ?>">

            <div class="flex flex-col">
                <label for="payment-method" class="text-gray-600">Payment Method:</label>
                <select id="payment-method" name="payment_method" class="border border-gray-300 rounded p-2 mt-1">
                    <option value="1">G-cash</option>
                    <option value="2">PayMaya</option>
                    <option value="3">Bank Transfer</option>
                </select>
            </div>

            <!-- Buttons -->
            <div class="flex space-x-4">
                <button type="submit" name="confirm-btn"
                    class="bg-red-600 text-white hover:bg-teal-400 px-6 py-2 rounded shadow w-full">Confirm</button>
                <button type="button" id="cancel-btn"
                    class="bg-gray-600 text-white hover:bg-gray-700 px-6 py-2 rounded shadow w-full">Cancel</button>
            </div>
        </form>


    </div>

    <!-- JavaScript -->
    <script>

        document.addEventListener('DOMContentLoaded', function () {
            // Handle cancel button click
            const cancelButton = document.getElementById('cancel-btn');
            cancelButton.addEventListener('click', function () {
                // Send an AJAX request to cancel the order and clear the cart
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "cancel_cart.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function () {
                    if (xhr.readyState === XMLHttpRequest.DONE) {
                        if (xhr.status === 200) {
                            // Redirect to the shop page after cancellation
                            window.location.href = 'shop.php';
                        } else {
                            // Handle any errors
                            console.error("Error cancelling order: " + xhr.responseText);
                        }
                    }
                };
                xhr.send();
            });

        });
    </script>

</body>

</html>