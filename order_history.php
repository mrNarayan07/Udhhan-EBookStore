<?php
date_default_timezone_set('Asia/Kolkata');
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to the login page if not logged in
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

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
    die("Database error occurred.");
}

// Fetch order history for the logged-in user
try {
    $stmt = $conn->prepare("SELECT order_id, order_date, total_amount, payment_method, item_ids FROM orders WHERE user_id = :user_id ORDER BY order_date DESC");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Error fetching order history: " . $e->getMessage());
    $orders = []; // Initialize as empty array to avoid errors in HTML
    $fetch_error = "Could not retrieve order history. Please try again later.";
}

// Function to retrieve book details from JSON based on item IDs
function getBookDetails($item_ids_json) {
    $book_index_json = file_get_contents('book_index.json');
    $book_index = json_decode($book_index_json, true);
    $item_ids = json_decode($item_ids_json, true);
    $orderDetails = [];

    if ($book_index && is_array($item_ids)) {
        foreach ($item_ids as $item_id) {
            foreach ($book_index as $book) {
                if ($book['id'] === $item_id) {
                    $orderDetails[] = $book;
                    break;
                }
            }
        }
    }
    return $orderDetails;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History | Udhhan</title>
    <link rel="stylesheet" href="./css/style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css"/>
    <link rel="stylesheet" href="./css/swiper-bundle.min.css">
    <link rel="icon" type="image/svg"  href="./img/bookfavicon.svg" />

    <style>
    .order-history-wrapper {
        display: flex;
        flex-direction: row; /* Ensure items are in a row (default) */
        max-width: 900px;
        margin: 30px auto;
        border: 1px solid #ddd;
        border-radius: 8px;
        background-color: #f9f9f9;
        overflow: hidden;
    }

    .order-history-svg {
        flex: 0 0 30%; /* Adjust width as needed */
        /* height: 80%; */
        padding: 20px;
        background-color:rgba(216, 220, 218, 0.51); /* Optional background */
        display: flex;
        justify-content: flex-start; /* Align SVG to the left */
        align-items: left; /* Vertically center the SVG (optional) */
    }

    .order-history-svg img, .order-history-svg svg {
        max-width: 80%; /* Adjust max-width for spacing inside the SVG container */
        height: auto;
        display: block; /* Prevent extra space below inline elements */
    }

    .order-history-content {
        flex: 1;
        padding: 20px;
    }

    .order-history-content h1 {
        text-align: left;
        margin-bottom: 20px;
        color: #333;
    }
    .dash-art {
            background: url(./img/dashboard_art.png);
            background-repeat: repeat;
        }
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .order-history-wrapper {
            flex-direction: column; /* Stack SVG and content on smaller screens */
        }

        .order-history-svg {
            width: 100%;
            flex-basis: auto; /* Allow it to take full width */
            justify-content: center; /* Center SVG on smaller screens for better visual */
            align-items: center; /* Center vertically */
            padding: 20px;
        }

        .order-history-content {
            width: 100%;
        }
    }

    /* Further adjustments for even smaller screens if needed */
    @media (max-width: 480px) {
        .order-history-svg {
            padding: 15px;
        }
    }

    .order-item {
        margin-bottom: 20px;
        padding: 15px;
        border: 1px solid #eee;
        border-radius: 6px;
        background-color: #fff;
    }
    .order-header {
        display: flex;
        justify-content: space-between;
        align-items: right;
        margin-bottom: 10px;
        color: #555;
    }
    .order-details {
        margin-top: 10px;
    }
    .order-details ul {
        list-style: none;
        padding: 0;
    }
    .order-details li {
        margin-bottom: 5px;
        color: #777;
    }
    .order-details li strong {
        font-weight: bold;
        color: #555;
    }
    .book-list {
        margin-top: 10px;
        padding-left: 20px;
    }
    .book-list li {
        margin-bottom: 5px;
        color: #888;
    }
    .book-list li::before {
        content: "- ";
    }
    .no-orders {
        text-align: center;
        color: #777;
    }
    .error-message {
        color: red;
        text-align: center;
        margin-top: 15px;
    }
</style>
</head>
<body>

    <header class="header">
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
        <!-- Header 2 Start -->
            <div class="header-2">
                <div class="navbar">
                </div>
            </div>
            <!-- Header 2 End -->

            <!-- Header End -->
        
            <!-- Bottom Navbar Start -->
            <div class="bottom-navbar">            </div>
            <!-- Bottom Navbar End -->
      </header>
        <section class="dash-art">
        <section class="display-order">
    <div class="order-history-wrapper">
        <div class="order-history-svg">
             <img src="./img/reading_book.svg" alt="Reading Book SVG">
            </div>
        <div class="order-history-content">
            <h1>Your Order History</h1>

            <?php if (isset($fetch_error)): ?>
                <p class="error-message"><?php echo $fetch_error; ?></p>
            <?php elseif (empty($orders)): ?>
                <p class="no-orders">You haven't placed any orders yet.</p>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <div class="order-item">
                        <div class="order-header">
                            <div>
                                <strong>Order ID:</strong> <?php echo $order['order_id']; ?>
                            </div>
                            <div>
                                <strong>Order Date:</strong> <?php echo date('Y-m-d H:i:s', strtotime($order['order_date'])); ?>
                            </div>
                        </div>
                        <div class="order-details">
                            <ul>
                                <li><strong>Total Amount:</strong> ₹<?php echo number_format($order['total_amount'], 2); ?></li>
                                <li><strong>Payment Method:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></li>
                                <li><strong>Items:</strong>
                                    <ul class="book-list">
                                        <?php
                                            $ordered_books = getBookDetails($order['item_ids']);
                                            if (!empty($ordered_books)):
                                                foreach ($ordered_books as $book):
                                        ?>
                                                    <li><?php echo htmlspecialchars($book['title'] ?? 'Unknown Book'); ?></li>
                                        <?php
                                                endforeach;
                                            else:
                                        ?>
                                                    <li>No item details found for this order.</li>
                                        <?php endif; ?>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
        </section>
        </section>
    <footer class="footer" id="footer">
        <div class="main-content">
            <div class="left box">
                <h2>About us</h2>
                <div class="content">
                    <p>
                        In the heart of India, where wisdom has been passed down for generations, lies a story of
                        perseverance and education. Inspired by the teachings of great scholars, our mission is to
                        make knowledge accessible to all. Like the legendary Nalanda University, which attracted
                        learners from all over the world, our bookstore aims to provide resources that ignite young
                        minds and fuel their thirst for learning.
                    </p>
                    <p>
                        From the sacred scriptures of the Vedas to modern innovations in AI and technology,
                        education remains the strongest pillar of progress. We are committed to bringing you
                        books that enlighten, inspire, and empower the next generation of leaders.
                    </p>

                    <div class="social">
                        <a href="#footer"><span class="fab fa-facebook-f"></span></a>
                        <a href="#footer"><span class="fab fa-twitter"></span></a>
                        <a href="#footer"><span class="fab fa-instagram"></span></a>
                        <a href="#footer"><span class="fa-brands fa-youtube"></span></a>
                    </div>
                </div>
            </div>
            <div class="center box">
                <h2>Address</h2>
                <div class="content">
                    <div class="place">
                        <span class="fas fa-map-marker-alt"></span>
                        <span class="text">Uttar Pradesh, India</span>
                    </div>
                    <div class="phone">
                        <span class="fas fa-phone-alt"></span>
                        <span class="text">+91 6600669900</span>
                    </div>
                    <div class="email">
                        <span class="fas fa-envelope"></span>
                        <span class="text" style="text-transform: lowercase;">udhhan-india@edu.com</span>
                    </div>
                </div>
            </div>
            <div class="right box">
                <h2>Contact us</h2>
                <div class="content">
                    <form action="">
                        <div class="email">
                            <div class="text">Email</div>
                            <input type="email"placeholder="Email address..." required />
                        </div>
                        <div class="msg">
                            <div class="text">Message</div>
                            <input type="text" placeholder="Your message..." required /></input>
                        </div>
                        <div class="btn">
                            <button type="submit">Send</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="bottom">
            <span class="credit"
                >Copyright <span class="far fa-copyright"></span> 2025</span>
            <span> <a href="./index.html">Guys at UU</a> | All rights reserved.</span>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.min.js"></script>
</body>
</html>