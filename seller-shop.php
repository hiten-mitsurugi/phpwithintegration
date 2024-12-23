<?php
session_start();
$host = 'localhost'; // Database host
$dbname = 'project'; // Database name
$username = 'seller_user'; // Database username
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

// Database connection using PDO
try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit();
}

// Fetch user role_id and check access
$user_id = $_SESSION['user_id'];

try {
    // Query to fetch the role_id directly from the users table
    $query = "SELECT role_id, first_name, last_name FROM users WHERE user_id = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    // Fetch the user's data
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // User not found in the database
        die("Error: User not found.");
    }

    // Check if role_id is NOT 3 (customer role)
    if ($user['role_id'] != 2) {
        // Redirect to the access denied page
        header("Location: access_denied.php");
        exit();
    }

    // User is a customer, allow access
    $user_name = $user['first_name'] . ' ' . $user['last_name'];

} catch (PDOException $e) {
    die("Query failed: " . $e->getMessage());
}

// Retrieve active products
try {
    $query_products = "
        SELECT * 
        FROM active_products_view 
        WHERE status_id = :status_id 
        ORDER BY product_name ASC";

    $stmt_products = $pdo->prepare($query_products);
    $stmt_products->execute([':status_id' => 1]);
    $products = $stmt_products->fetchAll(PDO::FETCH_ASSOC);

    if (!$products) {
        echo "No active products found.";
    }
} catch (PDOException $e) {
    die("Query failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./Styles/Shop.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <title>SHOPPING</title>
</head>

<section class="main-home">
    <!-- Video Background -->
    <video autoplay muted loop id="video-bg">
        <source src="./assets/bg.mp4" type="video/mp4">
        Your browser does not support the video tag.
    </video>

    <header>
        <h1>Elite Footprints</h1>
        <!-- Nav Icon -->
        <div class="nav-icon">
            <a href="seller.php"><i class='bx bxs-detail'></i></a>
            <div class="icon-cart">
                <a href="#"><i class='bx bx-cart'></i></a>
                <span>0</span>
            </div>
        </div>
    </header>

    <div class="main-text">
        <h5>Elite Footprints Collection</h5>
        <h1>Elevate Your Style with Our Exquisite Collection</h1>
        <p>Discover the Perfect Pair for Every Trend</p>
        <a href="#trending" class="main-btn">Shop now <i class='bx bx-shopping-bag'></i></a>
    </div>
</section>

<div class="trending" id="trending">
    <h5>Trendings</h5>
</div>

<!-- List of Products -->
<div class="listProduct">
    <?php
    // Loop through each product with status_id = 1 and display its information
    if ($products) {
        foreach ($products as $row) {
            ?>
            <div class="item">
                <img src="assets/product/<?php echo htmlspecialchars($row['image_url']); ?>" alt="">
                <div class="detailscontainer">
                    <h2><?php echo htmlspecialchars($row['product_name']); ?></h2>
                    <h5>$<?php echo htmlspecialchars($row['selling_price']); ?></h5>
                </div>
                <button class="addCart" data-product-id="<?php echo htmlspecialchars($row['product_id']); ?>">Add To
                    Cart</button>
            </div>
            <?php
        }
    } else {
        echo "<p>No active products found.</p>";
    }
    ?>
</div>



<div class="cartTab" id="cartTab">
    <button class="close">
        <i class='iconClose bx bx-left-arrow-alt'></i>
        <h1>Shopping Cart</h1>
    </button>
    <div class="listCart">
        <!-- Cart items will be dynamically added here -->
    </div>
    <div class="totalCartPrice">Total: $0.00</div>
    <div class="btn">
        <button class="checkOut">CHECK OUT</button>
    </div>
</div>



<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<script>




    document.querySelector(".icon-cart").addEventListener("click", function () {
        document.querySelector(".cartTab").style.width = "450px";
    })



    document.querySelector(".iconClose").addEventListener("click", function () {
        document.querySelector(".cartTab").style.width = "0px";
    })



    document.addEventListener('DOMContentLoaded', function () {




        const cartIcon = document.querySelector('.icon-cart');
        const cartTab = document.querySelector('.cartTab');
        const closeButton = document.querySelector('.close');

        const checkoutButton = document.querySelector('.checkOut');


        checkoutButton.addEventListener('click', function () {
            const cart = JSON.parse(localStorage.getItem('cart')) || [];
            fetch('seller-checkout.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(cart)
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        localStorage.removeItem('cart');
                        updateCartUI();
                        window.location.href = 'seller-payment.php';
                    } else {
                        console.error('Error during checkout:', data.message);
                        alert('Error during checkout. Please try again.');
                    }
                })
                .catch(error => {
                    console.error('Error during checkout:', error);
                    alert('Error during checkout. Please try again.');
                });
        });



        const addToCartButtons = document.querySelectorAll('.addCart');

        addToCartButtons.forEach(button => {
            button.addEventListener('click', function () {
                const productId = this.getAttribute('data-product-id');
                const productName = this.parentNode.querySelector('h2').textContent;
                const productPrice = parseFloat(this.parentNode.querySelector('h5').textContent.replace('$', ''));
                const productImage = this.parentNode.querySelector('img').src; // Get the product image URL

                Toastify({
                    text: "Added To Cart Sucessfull",
                    duration: 2000,
                    newWindow: false,
                    close: false,
                    gravity: "top", // `top` or `bottom`
                    position: "right", // `left`, `center` or `right`
                    stopOnFocus: false, // Prevents dismissing of toast on hover
                    style: {
                        background: "linear-gradient(to right, #00CE89,#00CE89)",
                    },
                    onClick: function () { } // Callback after click
                }).showToast();


                // Retrieve existing cart data from local storage
                let cart = JSON.parse(localStorage.getItem('cart')) || [];

                // Check if the product is already in the cart
                const existingProductIndex = cart.findIndex(item => item.id === productId);

                if (existingProductIndex !== -1) {
                    // Product already exists in the cart, update quantity
                    cart[existingProductIndex].quantity++;
                } else {
                    // Product doesn't exist in the cart, add it
                    cart.push({
                        id: productId,
                        name: productName,
                        price: productPrice,
                        quantity: 1,
                        image_url: productImage
                    });
                }

                // Save updated cart data to local storage
                localStorage.setItem('cart', JSON.stringify(cart));

                // Update the UI
                updateCartUI();

            });
        });

        // Function to update the cart UI
        function updateCartUI() {
            const listCart = document.querySelector('.listCart');
            listCart.innerHTML = ''; // Clear the cart list

            // Retrieve cart data from local storage
            const cart = JSON.parse(localStorage.getItem('cart')) || [];
            console.log(cart);
            // Update the span to display the total number of items in the cart
            const cartCount = document.querySelector('.icon-cart span');
            cartCount.textContent = cart.reduce((total, item) => total + item.quantity, 0);

            // Populate the cart items
            cart.forEach(item => {
                const cartItem = document.createElement('div');
                cartItem.classList.add('item');

                // Create image element for the product image
                const image = document.createElement('img');
                image.src = item.image_url; // Set the image source
                image.alt = item.name; // Set the alt attribute



                const productName = document.createElement('span');
                productName.textContent = item.name; // Set the product name
                productName.classList.add('nameHeader'); // Add 'decrement' cl
                // Create buttons for incrementing and decrementing quantity


                const itemContainer = document.createElement('div');
                itemContainer.classList.add('item-container');
                // Create span for product name

                const decrementButton = document.createElement('button');
                decrementButton.textContent = '-'; // Set button text content
                decrementButton.classList.add('decrement'); // Add 'decrement' class to the button
                decrementButton.dataset.itemId = item.id; // Set 'data-item-id' attribute
                itemContainer.appendChild(decrementButton)

                // Create span for product quantity
                const productQuantity = document.createElement('span');
                productQuantity.textContent = item.quantity;
                productQuantity.classList.add('quantityheader'); // Add 'd// Set the product quantity
                itemContainer.appendChild(productQuantity)


                const incrementButton = document.createElement('button');
                incrementButton.textContent = '+';
                incrementButton.classList.add('increment');
                incrementButton.dataset.itemId = item.id;
                itemContainer.appendChild(incrementButton)


                // Create span for product price
                const productPrice = document.createElement('span');
                productPrice.textContent = '₱' + (item.price * item.quantity).toFixed(2); // Calculate and set the product price
                productPrice.classList.add('priceheader');
                // Create button to remove item from cart
                const removeButton = document.createElement('button');
                removeButton.classList.add('remove');
                removeButton.dataset.productId = item.id; // Set 'data-product-id' attribute

                // Create an <i> element for the icon
                const iconElement = document.createElement('i');
                iconElement.classList.add('bx', 'bxs-trash'); // Add classes for the icon (assuming these are for icon styling)

                // Append the <i> element to the button's text content
                removeButton.appendChild(iconElement);



                // Append elements to cart item
                cartItem.appendChild(image);
                cartItem.appendChild(productName);
                cartItem.appendChild(itemContainer);
                cartItem.appendChild(productPrice);
                cartItem.appendChild(removeButton);

                // Append cart item to list
                listCart.appendChild(cartItem);
            });

            // Update total price
            updateTotalPrice();
        }

        // Function to remove item from cart
        document.addEventListener('click', function (event) {
            if (event.target.classList.contains('remove')) {
                const productId = event.target.getAttribute('data-product-id');

                // Retrieve cart data from local storage
                let cart = JSON.parse(localStorage.getItem('cart')) || [];

                // Remove the item from cart
                cart = cart.filter(item => item.id !== productId);

                // Save updated cart data to local storage
                localStorage.setItem('cart', JSON.stringify(cart));

                // Update the UI
                updateCartUI();
            } else if (event.target.classList.contains('increment')) {
                const itemId = event.target.dataset.itemId;
                handleQuantityChange(itemId, 1);
            } else if (event.target.classList.contains('decrement')) {
                const itemId = event.target.dataset.itemId;
                handleQuantityChange(itemId, -1);
            }
        });

        // Function to handle incrementing and decrementing quantity
        function handleQuantityChange(itemId, change) {
            // Retrieve cart data from local storage
            let cart = JSON.parse(localStorage.getItem('cart')) || [];

            // Find the item in the cart
            const itemIndex = cart.findIndex(item => item.id === itemId);

            if (itemIndex !== -1) {
                // Update the quantity
                cart[itemIndex].quantity += change;

                // Ensure quantity doesn't go below 1
                if (cart[itemIndex].quantity < 1) {
                    cart[itemIndex].quantity = 1;
                }

                // Save updated cart data to local storage
                localStorage.setItem('cart', JSON.stringify(cart));

                // Update the UI
                updateCartUI();
            }
        }

        // Function to calculate and display total price
        function updateTotalPrice() {
            const cart = JSON.parse(localStorage.getItem('cart')) || [];
            let totalPrice = 0;
            cart.forEach(item => {
                totalPrice += item.price * item.quantity;
            });
            document.querySelector('.totalCartPrice').textContent = 'Total: ₱' + totalPrice.toFixed(2); // Replace $ with ₱ for peso sign
        }

        // Load cart data from local storage when the page is loaded
        updateCartUI();

    });

    "use strict";
      
      !function() {
        var t = window.driftt = window.drift = window.driftt || [];
        if (!t.init) {
          if (t.invoked) return void (window.console && console.error && console.error("Drift snippet included twice."));
          t.invoked = !0, t.methods = [ "identify", "config", "track", "reset", "debug", "show", "ping", "page", "hide", "off", "on" ], 
          t.factory = function(e) {
            return function() {
              var n = Array.prototype.slice.call(arguments);
              return n.unshift(e), t.push(n), t;
            };
          }, t.methods.forEach(function(e) {
            t[e] = t.factory(e);
          }), t.load = function(t) {
            var e = 3e5, n = Math.ceil(new Date() / e) * e, o = document.createElement("script");
            o.type = "text/javascript", o.async = !0, o.crossorigin = "anonymous", o.src = "https://js.driftt.com/include/" + n + "/" + t + ".js";
            var i = document.getElementsByTagName("script")[0];
            i.parentNode.insertBefore(o, i);
          };
        }
      }();
      drift.SNIPPET_VERSION = '0.3.1';
      drift.load('vxapcad9d8f7');
      
      // Set static name for chat
      drift.on('ready', function(api) {
        drift.identify('elite-footprints-id', { // Use a unique identifier
          name: 'Elite Footprints' // The name that will display in chat
        });
      });

</script>




</body>

</html>