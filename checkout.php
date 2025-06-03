<?php
date_default_timezone_set('Asia/Kolkata');
session_start();
// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to the login page if not logged in
    header("Location: login.php");
    exit();
}

// Database connection details
$servername = "localhost";
$username = "root";
$passwd = "";
$dbname = "udhhan";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $passwd);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    // For demonstration, you might want to show a user-friendly error
    die("Database error occurred.");
}

// Retrieve cart items from cookie and book details from JSON
$cart_items = [];
$total_price = 0;
if (isset($_COOKIE['popShoppingCart'])) {
    $cart_item_ids = json_decode($_COOKIE['popShoppingCart'], true);
    if (is_array($cart_item_ids)) {
        $book_index_json = file_get_contents('book_index.json');
        $book_index = json_decode($book_index_json, true);

        if ($book_index) {
            foreach ($cart_item_ids as $item_id) {
                foreach ($book_index as $book) {
                    if ($book['id'] === $item_id) {
                        $cart_items[] = $book;
                        $price = floatval(str_replace(['₹', '+', ','], '', $book['price'] ?? '0'));
                        $total_price += $price;
                        break;
                    }
                }
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="./css/style.css" />
     <!-- Font Awesome {animation}  -->
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css"/>
     <!-- <link rel="stylesheet" href="./css/font-awesome.min.css" /> -->
    <!-- Swiper -->
    <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css"/> -->
     <link rel="stylesheet" href="./css/swiper-bundle.min.css">
    <link rel="icon" type="image/svg"  href="./img/bookfavicon.svg" />
    <style>
        .checkout-container {
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #f9f9f9;
        }
        .checkout-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .checkout-item img {
            max-width: 80px;
            height: auto;
            margin-right: 15px;
            border-radius: 5px;
        }
        .payment-options {
            margin-top: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #fff;
        }
        .payment-options h2 {
            margin-top: 0;
            margin-bottom: 15px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input[type="text"],
        .form-group input[type="number"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .payment-button {
            background-color: #386c5c; /* Primary button color */
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1.1em;
        }
        .payment-button:hover {
            background-color: #2e5a4d;
        }
        .order-confirmation {
            margin-top: 30px;
            padding: 20px;
            border: 1px solid #dff0d8;
            border-radius: 8px;
            background-color: #dff0d8;
            color: #3c763d;
            text-align: center;
            font-size: 1.4em; /* Increased font size */
        }
        .total-price {
            font-size: 1.2em;
            font-weight: bold;
            margin-top: 15px;
            text-align: right;
        }
        .error-message {
            color: red;
            margin-top: 5px;
        }
    </style>
</head>
<body>

     <!-- Header Start  -->
     <header class="header">
        <!-- Header 1 Start  -->
        <div class="header-1">
          <a href="./index.php" class="logo"><i class="fas fa-book"></i> Udhhan- The flight of education</a>
        
          <!-- Icons Section -->
          <div class="icons">
           
            <!-- Favorite Icon -->
            <a href="./fav.html" class="fas fa-heart-circle-check" aria-label="Favorites"></a>
        
            <!-- Shopping Cart -->
            <a href="./cart.html" class="fas fa-shopping-cart" aria-label="Shopping Cart"></a>
        
            <!-- Login Icon -->
            <a id="login-btn" class="fa-solid fa-user" href="./login.php" aria-label="Login"></a>
          </div>
        </div>
          </div>
          <!-- Header 1 End -->
    
          <!-- Header 2 Start -->
          <div class="header-2">
            <div class="navbar">
            </div>
          </div>
          <!-- Header 2 End -->

        <!-- Header End -->
    
        <!-- Bottom Navbar Start -->
        <div class="bottom-navbar">
        </div>
        <!-- Bottom Navbar End -->
      </header>
    <div class="checkout-container">
        <h1>Checkout</h1>

        <?php if (empty($cart_items)): ?>
            <p>Your cart is empty. Please add items to your cart before proceeding to checkout.</p>
        <?php else: ?>
            <h2>Items in Your Cart:</h2>
            <?php foreach ($cart_items as $item): ?>
                <div class="checkout-item">
                    <img src="<?php echo $item['img']['book_cover_1'] ?? './img/placeholder.png'; ?>" alt="<?php echo $item['title'] ?? 'Book Cover'; ?>">
                    <div>
                        <h3><?php echo $item['title'] ?? 'No Title'; ?></h3>
                        <p>Author: <?php echo $item['author'] ?? 'Unknown Author'; ?></p>
                        <p>Price: <?php echo $item['price'] ?? 'Unavailable'; ?></p>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="total-price">
                Total: ₹<?php echo number_format($total_price, 2); ?>
            </div>

            <div class="payment-options">
                <h2>Payment Information</h2>
                <form id="payment-form">
                    <div class="form-group">
                        <label for="payment_method">Payment Method:</label>
                        <select id="payment_method">
                            <option value="credit_card">Credit/Debit Card</option>
                            <option value="upi">UPI</option>
                        </select>
                    </div>

                    <div id="credit_card_fields">
                        <h3>Credit/Debit Card Details</h3>
                        <div class="form-group">
                            <label for="card_number">Card Number:</label>
                            <input type="text" id="card_number" placeholder="Enter 16-digit card number">
                            <span id="card_number_error" class="error-message" style="display: none;">Please enter a valid 16-digit card number.</span>
                        </div>
                        <div class="form-group">
                            <label for="expiry_date">Expiry Date:</label>
                            <input type="text" id="expiry_date" placeholder="MM/YY">
                        </div>
                        <div class="form-group">
                            <label for="cvv">CVV:</label>
                            <input type="number" id="cvv" placeholder="CVV">
                        </div>
                    </div>

                    <div id="upi_fields" style="display: none;">
                        <h3>UPI Details</h3>
                        <div class="form-group">
                            <label for="upi_id">UPI ID:</label>
                            <input type="text" id="upi_id" placeholder="Enter your UPI ID">
                        </div>
                    </div>

                    <button type="button" class="payment-button" onclick="processOrder()">Place Order</button>
                </form>
            </div>

            <div id="order-confirmation" style="display: none;">
                <p>Your order has been successfully placed! A confirmation email containing your order details and tracking information has been sent to your registered email address. Thank you for your purchase!</p>
            </div>
        <?php endif; ?>
    </div>
                
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const paymentMethodSelect = document.getElementById('payment_method');
            const creditCardFields = document.getElementById('credit_card_fields');
            const upiFields = document.getElementById('upi_fields');

            paymentMethodSelect.addEventListener('change', function() {
                if (this.value === 'credit_card') {
                    creditCardFields.style.display = 'block';
                    upiFields.style.display = 'none';
                } else if (this.value === 'upi') {
                    creditCardFields.style.display = 'none';
                    upiFields.style.display = 'block';
                }
            });
        });

        function processOrder() {
            const paymentMethod = document.getElementById('payment_method').value;
            let isValid = true;

            if (paymentMethod === 'credit_card') {
                const cardNumberInput = document.getElementById('card_number');
                const cardNumber = cardNumberInput.value.trim();
                const cardNumberError = document.getElementById('card_number_error');

                if (cardNumber.length !== 16 || !/^\d+$/.test(cardNumber)) {
                    cardNumberError.style.display = 'block';
                    isValid = false;
                } else {
                    cardNumberError.style.display = 'none';
                }

                const expiryDate = document.getElementById('expiry_date').value;
                const cvv = document.getElementById('cvv').value;

                // Basic fake validation
                if (expiryDate.length < 5 || cvv.length < 3) {
                    alert('Please enter valid expiry date and CVV.');
                    isValid = false;
                }
            } else if (paymentMethod === 'upi') {
                const upiId = document.getElementById('upi_id').value;
                if (!upiId.includes('@')) {
                    alert('Please enter a valid UPI ID.');
                    isValid = false;
                }
            }

            if (isValid) {
                // Prepare order data
                const cartItemIds = JSON.parse(getCookie('popShoppingCart') || '[]');
                const userId = <?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'null'; ?>;

                if (userId !== null && cartItemIds.length > 0) {
                    // Send order details to the server to be stored in the database
                    fetch('process_order.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            user_id: userId,
                            items: cartItemIds,
                            total_amount: <?php echo $total_price; ?>,
                            payment_method: paymentMethod
                            // Add other relevant details if needed
                        }),
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('payment-form').style.display = 'none';
                            document.getElementById('order-confirmation').style.display = 'block';
                            document.cookie = 'popShoppingCart=; Max-Age=-99999999; path=/;'; // Clear cart
                        } else {
                            alert('Order processing failed. Please try again.');
                            console.error('Order processing failed:', data.error);
                        }
                    })
                    .catch(error => {
                        alert('An error occurred while processing your order.');
                        console.error('Fetch error:', error);
                    });
                } else {
                    alert('Could not process order. Please ensure you are logged in and have items in your cart.');
                }
            }
        }

        // Helper function to get a cookie (if not already defined elsewhere)
        function getCookie(name) {
            const nameEQ = name + "=";
            const ca = document.cookie.split(';');
            for (let i = 0; i < ca.length; i++) {
                let c = ca[i];
                while (c.charAt(0) === ' ') c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) === 0) return decodeURIComponent(c.substring(nameEQ.length, c.length));
            }
            return null;
        }
    </script>

</body>
</html>