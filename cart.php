<?php
/**
 * cart.php
 * Displays combined shopping cart items from:
 * 1. PHP Session ('cart' key, stores [isbn => quantity]) - Items from user.php (AI Recs)
 * 2. Cookie ('popShoppingCart' key, stores [id1, id2, ...]) - Items from index.php (Popular)
 */
date_default_timezone_set('Asia/Kolkata'); // Set timezone if needed
session_start(); // Start session to access $_SESSION['cart']

// Include necessary functions (getBookInfo) - Assuming they are in user.php or a separate utils file
// If in user.php, be careful about re-declaring functions if included multiple times.
// Best practice: Move shared functions to a separate file (e.g., 'includes/functions.php')
require_once 'user.php'; // Contains getBookInfo, recommendBooks (though not needed here), etc.
                        // Also contains $googleBooksApiKey, $defaultBookImage, $defaultCurrency
                        // Ensure user.php doesn't output HTML before session_start() here.

// --- Helper Function to Load book_index.json in PHP ---
$bookIndexCache = null; // Cache for book index within this request

function loadBookIndexPHP(): ?array {
    global $bookIndexCache;
    if ($bookIndexCache !== null) {
        return $bookIndexCache;
    }

    $jsonPath = './book_index.json'; // Adjust path if needed
    if (!file_exists($jsonPath)) {
        error_log("Error: book_index.json not found at path: " . $jsonPath);
        return null;
    }
    $jsonString = @file_get_contents($jsonPath);
    if ($jsonString === false) {
        error_log("Error: Failed to read book_index.json.");
        return null;
    }
    $data = json_decode($jsonString, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Error: Failed to decode book_index.json. JSON Error: " . json_last_error_msg());
        return null;
    }
    $bookIndexCache = $data; // Cache the result
    return $data;
}

// --- Process Cart Actions (Remove from Session) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $bookId = filter_input(INPUT_POST, 'bookId', FILTER_SANITIZE_STRING);
    if ($bookId) {
        switch ($_POST['action']) {
            case 'removeFromSessionCart':
                if (isset($_SESSION['cart'][$bookId])) {
                    unset($_SESSION['cart'][$bookId]);
                    // Redirect back to cart page using GET to prevent resubmission
                    header("Location: cart.php");
                    exit;
                }
                break;
            // Add other actions like 'updateQuantity' if needed
        }
    }
}


// --- Retrieve Cart Data ---
$sessionCartItems = [];
$cookieCartItems = [];
$combinedCartItems = []; // Will hold final items for display
$totalPrice = 0.0;
$currency = $defaultCurrency; // Use default currency

// 1. Process Session Cart (ISBN => Quantity)
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $isbn => $quantity) {
        if (empty($isbn) || $quantity <= 0) continue; // Skip invalid entries

        $bookInfo = getBookInfo($isbn); // Use function from user.php (uses Google Books API)

        if ($bookInfo) {
             // Normalize price for calculation
             $numericPrice = 0.0;
             if (isset($bookInfo['price']) && is_numeric(str_replace(',', '', $bookInfo['price']))) {
                 $numericPrice = floatval(str_replace(',', '', $bookInfo['price']));
             } elseif ($bookInfo['price'] === 'Free' || $bookInfo['price'] === '0.00') {
                  $numericPrice = 0.0;
             }

             // Use the first valid currency found
             if (!empty($bookInfo['currencyCode'])) {
                $currency = $bookInfo['currencyCode'];
             }

            $combinedCartItems[] = [
                'id'           => $isbn, // Use ISBN as the unique ID here
                'title'        => $bookInfo['title'],
                'authors'      => $bookInfo['authors'],
                'image'        => $bookInfo['image'],
                'price'        => $bookInfo['price'], // Display price (string)
                'numeric_price'=> $numericPrice,      // Numeric price for calculations
                'currencyCode' => $bookInfo['currencyCode'],
                'quantity'     => $quantity,
                'source'       => 'session' // Mark the source
            ];
        } else {
            // Handle case where book info failed for a session item
            $combinedCartItems[] = [
                'id'           => $isbn,
                'title'        => 'Book Info Unavailable',
                'authors'      => 'N/A',
                'image'        => $defaultBookImage,
                'price'        => 'N/A',
                'numeric_price'=> 0.0,
                'currencyCode' => $currency,
                'quantity'     => $quantity,
                'source'       => 'session',
                'error'        => true
            ];
            error_log("Cart.php: Failed to get info for session item ISBN: $isbn");
        }
    }
}

// 2. Process Cookie Cart (Array of book_index.json IDs)
$cookieCartIds = [];
if (isset($_COOKIE['popShoppingCart'])) {
    $decodedCookie = json_decode($_COOKIE['popShoppingCart'], true);
    if (is_array($decodedCookie)) {
        $cookieCartIds = array_filter($decodedCookie, 'is_string'); // Ensure IDs are strings/valid
    } else {
        error_log("Cart.php: Failed to decode popShoppingCart cookie or it's not an array.");
         // Optionally clear the corrupted cookie
         // setcookie('popShoppingCart', '', time() - 3600, '/');
    }
}

if (!empty($cookieCartIds)) {
    $bookIndex = loadBookIndexPHP(); // Load local book data

    if ($bookIndex) {
        foreach ($cookieCartIds as $bookId) {
             if (empty($bookId)) continue;

            // Find the book in the local index
            $foundBook = null;
            foreach ($bookIndex as $book) {
                if (isset($book['id']) && $book['id'] === $bookId) {
                    $foundBook = $book;
                    break;
                }
            }

            if ($foundBook) {
                 // --- Duplicate Check (Optional but recommended) ---
                 // Check if this book (identified by ISBN if available, otherwise ID)
                 // is ALREADY in the $combinedCartItems from the session.
                 $isDuplicate = false;
                 $checkIsbn = $foundBook['isbn'] ?? null; // Assuming 'isbn' field exists in book_index.json

                 if ($checkIsbn) {
                     foreach ($combinedCartItems as $existingItem) {
                         if ($existingItem['source'] === 'session' && $existingItem['id'] === $checkIsbn) {
                             $isDuplicate = true;
                             // Optional: Increment quantity of the session item instead?
                             // $existingItem['quantity']++; // Needs careful handling if array keys change
                             break;
                         }
                     }
                 } else {
                      // If no ISBN, we can't reliably match with session items (which use ISBN)
                      // So, we treat it as non-duplicate based on ID vs ISBN difference.
                 }


                 // If not a duplicate found in the session items:
                 if (!$isDuplicate) {
                      // Normalize price
                      $numericPrice = 0.0;
                      $displayPrice = 'N/A';
                      if (isset($foundBook['price']) && is_string($foundBook['price'])) {
                          $cleanedPrice = str_replace(['₹', ','], '', $foundBook['price']);
                          if (is_numeric($cleanedPrice)) {
                               $numericPrice = floatval($cleanedPrice);
                               $displayPrice = '₹' . number_format($numericPrice, 2); // Format consistently
                          } elseif (strtolower(trim($foundBook['price'])) == 'free') {
                              $numericPrice = 0.0;
                              $displayPrice = 'Free';
                          } else {
                               $displayPrice = $foundBook['price']; // Keep original string if not numeric
                          }
                      }

                    $combinedCartItems[] = [
                        'id'           => $bookId, // Use the book_index ID here
                        'title'        => $foundBook['title'] ?? 'No Title',
                        'authors'      => $foundBook['author'] ?? 'Unknown Author', // Note 'author' vs 'authors'
                        'image'        => $foundBook['img']['book_cover_1'] ?? $defaultBookImage, // Adjust path/structure as needed
                        'price'        => $displayPrice, // Display price string
                        'numeric_price'=> $numericPrice,  // Numeric for calculation
                        'currencyCode' => 'INR', // Assume INR for cookie items, or get from index if available
                        'quantity'     => 1, // Cookie cart doesn't store quantity > 1
                        'source'       => 'cookie' // Mark the source
                    ];
                 } else {
                     error_log("Cart.php: Skipped adding cookie item ID $bookId (ISBN: $checkIsbn) as it was already found in session cart.");
                 }

            } else {
                // Handle case where ID from cookie is not found in book_index.json
                 $combinedCartItems[] = [
                    'id'           => $bookId,
                    'title'        => 'Book Data Unavailable',
                    'authors'      => 'N/A',
                    'image'        => $defaultBookImage,
                    'price'        => 'N/A',
                    'numeric_price'=> 0.0,
                    'currencyCode' => $currency,
                    'quantity'     => 1,
                    'source'       => 'cookie',
                    'error'        => true
                ];
                error_log("Cart.php: Book ID $bookId from cookie not found in book_index.json.");
            }
        }
    } else {
        error_log("Cart.php: Could not load book_index.json to process cookie cart items.");
        // Optionally display an error message to the user
    }
}

// 3. Calculate Total Price from Combined List
foreach ($combinedCartItems as $item) {
    if (isset($item['numeric_price']) && isset($item['quantity'])) {
        $totalPrice += $item['numeric_price'] * $item['quantity'];
    }
}
// Determine final currency symbol
$currencySymbol = ($currency === 'INR' || empty($currency)) ? '₹' : htmlspecialchars($currency);


?>
<!DOCTYPE html> <!-- Changed doctype to html -->
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart | Udhhan</title>
    <!-- External CSS -->
    <link rel="stylesheet" href="./css/style.css" />
    <!-- Font Awesome {animation}  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css"/>
     <!-- Swiper -->
    <link rel="stylesheet" href="./css/swiper-bundle.min.css">
    <link rel="icon" type="image/svg"  href="./img/bookfavicon.svg" />
    <style>
      /* Styles from original cart.html */
      body { font-family: sans-serif; background-color: #f4f4f4; }
      #cart-items-container { /* Renamed container */
            max-width: 900px; /* Adjusted width */
            margin: 20px auto; /* Added top/bottom margin */
            padding: 0; /* Remove padding if items have their own */
            background-color: transparent; /* Let items have background */
      }
      .cart-item {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            background: white;
            transition: all 0.3s ease;
      }
      .cart-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
      }
      .cart-item img {
            height: 100px; /* Fixed height */
            width: 70px; /* Fixed width */
            object-fit: cover; /* Ensure image covers area */
            margin-right: 20px;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
      }
       .cart-item-details {
            flex-grow: 1;
      }
       .cart-item-details h3 {
            margin-top: 0;
            margin-bottom: 5px;
            font-size: 1.1rem; /* Slightly smaller */
            color: #333;
      }
       .cart-item-details p {
            margin: 3px 0;
            color: #555;
            font-size: 0.9rem;
      }
       .cart-item-details .price {
            font-weight: bold;
            color: #e63946;
            font-size: 1rem;
      }
       .cart-item-actions form,
       .cart-item-actions button { /* Style buttons consistently */
            display: inline-block; /* Allow side-by-side if needed */
            margin-top: 8px;
      }
       .remove-btn {
            background: #dc3545; /* Red */
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.85rem;
            transition: background-color 0.2s ease;
      }
       .remove-btn:hover {
            background-color: #c82333; /* Darker red */
      }
        .remove-btn-js { /* Slightly different style for JS button if needed */
             background: #6c757d; /* Gray */
        }
        .remove-btn-js:hover {
             background: #5a6268;
        }

        .btn { /* General button styles */
            cursor: pointer;
            transition: all 0.3s ease;
            padding: 0.6rem 1.5rem;
            font-size: 1rem;
            border-radius: 5px;
            border: none;
            font-weight: 500;
        }
         .btn-checkout { /* Specific checkout button */
             background-color: #28a745; /* Green */
             color: white;
             text-decoration: none; /* For link */
             display: inline-block; /* For link */
        }
         .btn-checkout:hover {
             background-color: #218838; /* Darker green */
        }

        .cart-total-section {
            max-width: 900px;
            margin: 30px auto; /* Spacing */
            padding: 20px;
            background: #e9ecef; /* Light background */
            border-radius: 8px;
            text-align: right; /* Align text right */
        }
        .cart-total-section p {
             margin: 5px 0;
             font-size: 1.1rem;
             color: #333;
        }
       .cart-total-section .total-price {
             font-size: 1.4rem;
             font-weight: bold;
             color: #212529;
        }
        .cart-total-section .btn-checkout {
             margin-top: 15px;
        }

        .page-header {
            text-align: center;
            padding: 25px 0; /* Reduced padding */
            background-color: #e3f2fd; /* Lighter blue */
            margin-bottom: 20px; /* Reduced margin */
            border-bottom: 1px solid #dee2e6;
        }
        .page-header h1 {
            font-size: 2rem; /* Reduced size */
            margin: 0;
            color: #01579b; /* Darker blue */
            font-weight: 600;
        }
        .empty-cart-message {
            text-align: center;
            font-size: 1.1rem;
            color: #666;
            margin-top: 40px;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
         }

         /* Header/Footer Styles (Assuming style.css covers these, but adding basic placeholders) */
         .header { background-color: #fff; padding: 10px 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
         .header-1 { max-width: 1200px; margin: 0 auto; padding: 0 15px; display: flex; justify-content: space-between; align-items: center; }
         .header-1 .logo { text-decoration: none; color: #333; font-size: 1.5rem; font-weight: bold; }
         .header-1 .icons a { margin-left: 15px; color: #555; font-size: 1.2rem; text-decoration: none; }
         .header-1 .icons a:hover { color: #007bff; }
         /* Add other header/footer styles as needed */
         hr { border: 0; height: 1px; background-color: #ddd; margin: 40px 0; }

    </style>
</head>
<body>

     <!-- Header Start (Same as original HTML) -->
     <header class="header">
        <div class="header-1">
          <a href="./index.php" class="logo"><i class="fas fa-book"></i> Udhhan- The flight of education</a>
          <div class="icons">
            <a href="./fav.html" class="fas fa-heart-circle-check" aria-label="Favorites"></a>
            <a href="./cart.php" class="fas fa-shopping-cart" aria-label="Shopping Cart"></a>
            <a id="login-btn" class="fa-solid fa-user" href="./login.php" aria-label="Login"></a>
          </div>
        </div>
        <!-- Removed header-2 and bottom-navbar for simplicity, add back if needed -->
      </header>
      <!-- Header End -->

    <div class="page-header">
      <h1>Your Shopping Cart</h1>
    </div>

    <!-- Cart Items Container -->
    <div id="cart-items-container">
        <?php if (!empty($combinedCartItems)): ?>
            <?php foreach ($combinedCartItems as $item): ?>
                <div class="cart-item" data-item-id="<?php echo htmlspecialchars($item['id']); ?>" data-source="<?php echo htmlspecialchars($item['source']); ?>">
                    <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                    <div class="cart-item-details">
                        <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                        <?php if (!isset($item['error'])): ?>
                            <p>By: <?php echo htmlspecialchars($item['authors']); ?></p>
                            <p class="price">Price: <?php echo htmlspecialchars($item['price']); ?> <?php // echo !empty($item['currencyCode']) ? htmlspecialchars($item['currencyCode']) : 'INR'; ?></p>
                            <p>Quantity: <?php echo htmlspecialchars($item['quantity']); ?></p>
                        <?php else: ?>
                            <p style="color: red;">Details unavailable.</p>
                        <?php endif; ?>
                         <p><small>Source: <?php echo htmlspecialchars(ucfirst($item['source'])); ?></small></p>
                    </div>
                    <div class="cart-item-actions">
                        <?php if ($item['source'] === 'session'): ?>
                            <!-- Form to remove item from PHP Session -->
                            <form method="post" action="cart.php" style="display: inline;">
                                <input type="hidden" name="action" value="removeFromSessionCart">
                                <input type="hidden" name="bookId" value="<?php echo htmlspecialchars($item['id']); // Session items use ISBN as ID ?>">
                                <button type="submit" class="remove-btn"><i class="fas fa-trash-alt"></i> Remove</button>
                            </form>
                        <?php elseif ($item['source'] === 'cookie'): ?>
                            <!-- Button to call JavaScript function removePopCartItem -->
                            <button type="button" class="remove-btn remove-btn-js" onclick="removePopCartItem('<?php echo htmlspecialchars($item['id']); // Cookie items use book_index ID ?>')">
                                <i class="fas fa-trash-alt"></i> Remove
                            </button>
                        <?php endif; ?>
                        <!-- Add quantity update controls here if needed -->
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="empty-cart-message">Your shopping cart is currently empty.</p>
        <?php endif; ?>
    </div><!-- End Cart Items Container -->

    <!-- Cart Total and Checkout Section -->
    <?php if (!empty($combinedCartItems)): ?>
    <div class="cart-total-section">
        <p class="total-price">Total: <?php echo $currencySymbol; ?><?php echo number_format($totalPrice, 2); ?></p>
        <p>Total Items: <?php echo count($combinedCartItems); ?></p> <!-- Simple item count, could sum quantities -->
        <!-- Checkout Button (ensure checkout.php can also read both session and cookie) -->
        <a href="./checkout.php" class="btn btn-checkout" id="proceed-to-checkout-php">
           <i class="fas fa-check-circle"></i> Proceed to Checkout
        </a>
    </div>
    <?php endif; ?>

    <br><br><hr>
    <!-- Footer (Add your standard footer include/HTML here) -->
    <footer>
        <div style="text-align: center; padding: 20px; color: #555;">
            Copyright © <?php echo date('Y'); ?> Udhhan | All rights reserved.
        </div>
    </footer>


    <!-- Include necessary JavaScript -->
    <!-- jQuery might be needed by script.js -->
     <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.min.js"></script>
    <!-- Swiper (if used on this page) -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>

    <!-- IMPORTANT: cart-favorites.js is included for removePopCartItem function -->
    <script src="./js/cart-favorites.js"></script>

    <!-- Other JS files (ensure they don't conflict or try to re-render the cart) -->
    <script src="./js/search.js"></script>
    <script src="./js/popup.js"></script>
    <script src="./js/script.js"></script>

    <script>
    // Add a flag to the container so displayCart() in cart-favorites.js knows not to run
    const cartContainer = document.getElementById('cart-items-container');
    if (cartContainer) {
        cartContainer.dataset.renderedBy = 'php';
    }

    // The DOMContentLoaded listener in cart-favorites.js handles the checkout button listener
    // and potentially the favorites display if that element exists.
    </script>

</body>
</html>