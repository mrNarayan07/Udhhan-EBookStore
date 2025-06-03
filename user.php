<?php
/**
 * AI Book Recommendation System - User Interface (user.php)
 *
 * Enhanced with basic API call caching and slightly refined recommendations.
 * Cart management uses PHP Sessions.
 */
date_default_timezone_set('Asia/Kolkata');
// Start a session to manage the shopping cart.
session_start();

// ** Configuration **
$googleBooksApiKey = 'AIzaSyDgEbogiuQpRjxQKJXYDF1TIKPXWc8vjKI'; // ** IMPORTANT: Still exposed, consider moving for production **
$defaultBookImage = './img/404Image2.png';
$defaultCurrency = 'INR';

// --- Global Cache for Book Info during this request ---
$bookInfoCache = [];

/**
 * Enhanced recommendation logic with broader coverage and more specific suggestions.
 * Returns unique ISBNs.
 *
 * @param int $age The user's age.
 * @param bool $hasReadBefore Whether the user has read a book before.
 * @param array $interests An array of the user's interests.
 * @return array An array of unique book ISBNs (International Standard Book Numbers).
 */
function recommendBooks(int $age, bool $hasReadBefore, array $interests): array
{
    $recommendations = [];
    $interests = array_map('strtolower', $interests); // Normalize interests to lowercase

    // --- Beginner Readers ---
    if (!$hasReadBefore) {
        if ($age <= 6) {
            $recommendations[] = '9780439023528'; // The Very Hungry Caterpillar
            $recommendations[] = '9780399242348'; // Goodnight Moon
        } elseif ($age <= 10) {
             $recommendations[] = '9781406358627'; // Paddington
             $recommendations[] = '9780064400558'; // Charlotte's Web
        } else {
            // Older beginners might appreciate simpler narratives
            $recommendations[] = '9780545582933'; // Wonder
            $recommendations[] = '9780143105815'; // The Little Prince
        }
        if (in_array('storybook', $interests)) { // Specific interest override
            $recommendations[] = '9780439023528'; // The Very Hungry Caterpillar
            $recommendations[] = '9780060256654'; // Where the Wild Things Are
        }
         return array_unique(array_slice($recommendations, 0, 6)); // Limit recommendations for beginners
    }

    // --- Teen Readers (13-19) ---
    if ($age >= 13 && $age <= 19) {
        if (in_array('science fiction', $interests)) {
            $recommendations[] = '9780345391803'; // The Hitchhiker's Guide to the Galaxy
            $recommendations[] = '9780804139021'; // Ready Player One
            $recommendations[] = '9780385732550'; // The Hunger Games
        }
        if (in_array('fantasy', $interests)) {
            $recommendations[] = '9780547928227'; // The Hobbit
            $recommendations[] = '9780553593716'; // A Game of Thrones (Mature Teen)
            $recommendations[] = '9780439554930'; // Harry Potter and the Sorcerer's Stone
            $recommendations[] = '9781423140607'; // Percy Jackson: The Lightning Thief
        }
        if (in_array('horror', $interests)) {
            $recommendations[] = '9780307743657'; // Carrie
            $recommendations[] = '9780451208463'; // The Haunting of Hill House
            $recommendations[] = '9780765377698'; // Miss Peregrine's Home for Peculiar Children
        }
        if (in_array('romance', $interests)) {
            $recommendations[] = '9780141439518'; // Pride and Prejudice
            $recommendations[] = '9780553802789'; // The Notebook
            $recommendations[] = '9780142407332'; // The Fault in Our Stars
        }
        if (in_array('philosophy', $interests)) {
            $recommendations[] = '9780140449210'; // The Republic (Plato) - Accessible Intro
            $recommendations[] = '9780486270698'; // Sophie's World
        }
        if (in_array('history', $interests)) {
            $recommendations[] = '9780062334167'; // Sapiens: A Brief History of Humankind
            $recommendations[] = '9780316017923'; // The Diary of a Young Girl (Anne Frank)
        }
         if (in_array('mystery', $interests)) { // Added Genre
            $recommendations[] = '9780307474278'; // The Girl with the Dragon Tattoo (Mature Teen)
            $recommendations[] = '9780062073481'; // Gone Girl (Mature Teen)
            $recommendations[] = '9780316098755'; // The Da Vinci Code
        }
    }
    // --- Adult Readers (> 19) ---
    elseif ($age > 19) {
        if (in_array('psychology', $interests)) {
            $recommendations[] = '9780374275631'; // Thinking, Fast and Slow
            $recommendations[] = '9780807014271'; // Man's Search for Meaning
            $recommendations[] = '9780062315081'; // Emotional Intelligence 2.0
        }
        if (in_array('philosophy', $interests)) {
            $recommendations[] = '9780199540245'; // Meditations (Marcus Aurelius)
            $recommendations[] = '9780140449241'; // Thus Spoke Zarathustra
            $recommendations[] = '9780679725303'; // Existentialism Is a Humanism
        }
        if (in_array('computers', $interests) || in_array('programming', $interests)) {
            $recommendations[] = '9780735619678'; // Code Complete 2
            $recommendations[] = '9780132350884'; // Clean Code
            $recommendations[] = '9780321765723'; // The Pragmatic Programmer
        }
        if (in_array('finance', $interests) || in_array('business', $interests)) {
            $recommendations[] = '9781612680194'; // Rich Dad Poor Dad
            $recommendations[] = '9780470903425'; // The Intelligent Investor
            $recommendations[] = '9780062373357'; // Thinking in Systems: A Primer
        }
        if (in_array('war', $interests)) {
            $recommendations[] = '9780199553467'; // The Art of War
            $recommendations[] = '9780547921860'; // The Things They Carried
            $recommendations[] = '9780743272996'; // Black Hawk Down
        }
        if (in_array('politics', $interests)) {
            $recommendations[] = '9780451524935'; // 1984
            $recommendations[] = '9780451526533'; // Animal Farm
            $recommendations[] = '9780553804363'; // The Prince (Machiavelli)
        }
        if (in_array('motivation', $interests) || in_array('self improvement', $interests)) {
            $recommendations[] = '9780743272453'; // The 7 Habits of Highly Effective People
            $recommendations[] = '9781592405179'; // Daring Greatly
            $recommendations[] = '9780316253580'; // Atomic Habits
            $recommendations[] = '9780743267915'; // How to Win Friends and Influence People
        }
        if (in_array('history', $interests)) {
            $recommendations[] = '9780062334167'; // Sapiens: A Brief History of Humankind
            $recommendations[] = '9780307277359'; // The Guns of August
            $recommendations[] = '9780393356134'; // A People's History of the United States
        }
         if (in_array('romance', $interests)) {
            $recommendations[] = '9780141439518'; // Pride and Prejudice
            $recommendations[] = '9780553802789'; // The Notebook
            $recommendations[] = '9780316734773'; // The Love Hypothesis
            $recommendations[] = '9781501110344'; // Me Before You
        }
        if (in_array('religion', $interests) || in_array('spirituality', $interests)) {
            // Note: Providing specific religious texts can be sensitive. Offer broader/philosophical options too.
            $recommendations[] = '9780060608832'; // The Holy Bible: NIV (Example)
            $recommendations[] = '9780199535340'; // The Qur'an: A Very Short Introduction
            $recommendations[] = '9780807015018'; // Siddhartha
            $recommendations[] = '9780140449197'; // Bhagavad Gita (Penguin Classics)
        }
        if (in_array('science fiction', $interests)) {
            $recommendations[] = '9780451524935'; // 1984
            $recommendations[] = '9780575086413'; // Dune
            $recommendations[] = '9780316029184'; // Foundation (Asimov)
            $recommendations[] = '9780061120084'; // Brave New World (Often grouped with Sci-Fi/Dystopian)
        }
         if (in_array('mystery', $interests) || in_array('thriller', $interests)) {
            $recommendations[] = '9780307474278'; // The Girl with the Dragon Tattoo
            $recommendations[] = '9780062073481'; // Gone Girl
            $recommendations[] = '9780385534630'; // The Silent Patient
            $recommendations[] = '9780316098755'; // The Da Vinci Code
        }
    }
    // --- Default/Fallback Recommendations (if no specific criteria met) ---
    else {
        $recommendations[] = '9780374275631'; // Thinking, Fast and Slow (General Interest)
        $recommendations[] = '9780061120084'; // To Kill a Mockingbird (Classic Literature)
        $recommendations[] = '9780143105815'; // The Little Prince (All Ages Classic)
        $recommendations[] = '9780743273565'; // The Alchemist (Inspirational)
    }

    // Ensure uniqueness and limit the total number of recommendations
    return array_slice(array_unique($recommendations), 0, 9); // Show up to 9 unique recommendations
}


/**
 * Retrieves book information from the Google Books API based on the ISBN.
 * Uses a simple in-memory cache ($bookInfoCache) to avoid redundant API calls during a single request.
 * Implements error handling and response checking.
 *
 * @param string $isbn The International Standard Book Number of the book.
 * @return array|null An array containing book details, or null if retrieval fails or book not found.
 */
function getBookInfo(string $isbn): ?array
{
    global $googleBooksApiKey, $defaultBookImage, $defaultCurrency, $bookInfoCache;

    // Check cache first
    if (isset($bookInfoCache[$isbn])) {
        return $bookInfoCache[$isbn];
    }

    // Basic validation
    if (empty($isbn)) {
        error_log("getBookInfo called with empty ISBN.");
        return null;
    }

    if (empty($googleBooksApiKey) || $googleBooksApiKey === 'YOUR_GOOGLE_BOOKS_API_KEY') {
        // Display error only once per request if needed, or just log it.
        static $apiKeyErrorDisplayed = false;
        if (!$apiKeyErrorDisplayed) {
             echo "<div class='error'>ERROR: Google Books API key is missing or not configured. Please update the \$googleBooksApiKey in the code. The system will not function correctly without it.</div>";
             $apiKeyErrorDisplayed = true;
        }
        error_log("Google Books API key is missing or not configured.");
        return null; // Prevent API call without key
    }

    $url = "https://www.googleapis.com/books/v1/volumes?q=isbn:" . urlencode($isbn) . "&key=" . $googleBooksApiKey;

    // Use cURL for better error handling and control (if available)
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); // 10 second timeout
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // Verify SSL cert
        curl_setopt($ch, CURLOPT_USERAGENT, 'UdhhanBookRecommender/1.0'); // Set a user agent
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false || $httpCode >= 400) {
            error_log("cURL Error fetching ISBN $isbn: $curlError | HTTP Code: $httpCode | URL: $url");
             $bookInfoCache[$isbn] = null; // Cache the failure
            return null;
        }
    } else {
        // Fallback to file_get_contents (less robust error handling)
        $context = stream_context_create(['http' => ['timeout' => 10, 'user_agent' => 'UdhhanBookRecommender/1.0']]);
        $response = @file_get_contents($url, false, $context); // Use @ to suppress warnings

        if ($response === false) {
            error_log("file_get_contents failed for ISBN: $isbn. Network error or API endpoint issue. URL: $url");
             $bookInfoCache[$isbn] = null; // Cache the failure
            return null;
        }
    }

    $data = json_decode($response, true);

    if ($data === null) {
        error_log("Error decoding JSON response from Google Books API for ISBN: $isbn. Response length: " . strlen($response));
         $bookInfoCache[$isbn] = null; // Cache the failure
        return null;
    }

    if (isset($data['error'])) {
        error_log("Google Books API error for ISBN: $isbn. Error: " . print_r($data['error'], true));
         $bookInfoCache[$isbn] = null; // Cache the failure
        return null;
    }

    if (isset($data['totalItems']) && $data['totalItems'] > 0 && isset($data['items'][0])) {
         // Find the item that most closely matches the queried ISBN
        $bestMatch = null;
        foreach ($data['items'] as $item) {
            if (isset($item['volumeInfo']['industryIdentifiers'])) {
                foreach ($item['volumeInfo']['industryIdentifiers'] as $identifier) {
                    // Normalize ISBNs (remove hyphens) for comparison
                    $normalizedApiIsbn = str_replace('-', '', $identifier['identifier']);
                    $normalizedQueryIsbn = str_replace('-', '', $isbn);
                    if ($identifier['type'] === 'ISBN_13' && $normalizedApiIsbn === $normalizedQueryIsbn) {
                        $bestMatch = $item;
                        break 2; // Found exact ISBN-13 match
                    }
                    if ($identifier['type'] === 'ISBN_10' && $normalizedApiIsbn === $normalizedQueryIsbn) {
                        $bestMatch = $item;
                        // Don't break yet, prefer ISBN-13 if available later
                    }
                }
            }
        }
         // If no exact match, fall back to the first item (less reliable)
        if ($bestMatch === null) {
             $bestMatch = $data['items'][0];
             error_log("No exact ISBN match for $isbn found in API response, using first result.");
        }


        $volumeInfo = $bestMatch['volumeInfo'] ?? [];
        $saleInfo = $bestMatch['saleInfo'] ?? [];

        $bookDetails = [
            'title'       => $volumeInfo['title'] ?? 'Title Not Found',
            'authors'     => isset($volumeInfo['authors']) ? implode(', ', $volumeInfo['authors']) : 'Author Not Found',
            'description' => $volumeInfo['description'] ?? 'No Description Available',
            'image'       => ($volumeInfo['imageLinks']['thumbnail'] ?? $volumeInfo['imageLinks']['smallThumbnail'] ?? $defaultBookImage), // Prefer thumbnail, fallback smallThumbnail
            'price'       => 'Not Available', // Default price
            'currencyCode'=> $defaultCurrency, // Default currency
            'bookId'      => $isbn, // Use the original queried ISBN as bookId
            'googleBooksId' => $bestMatch['id'] ?? null, // Store Google's internal ID if needed
            'previewLink' => $volumeInfo['previewLink'] ?? null, // Link to Google Books preview
        ];

        // Determine best available price
        if (isset($saleInfo['listPrice']['amount'])) {
            $bookDetails['price'] = number_format($saleInfo['listPrice']['amount'], 2);
            $bookDetails['currencyCode'] = $saleInfo['listPrice']['currencyCode'] ?? $defaultCurrency;
        } elseif (isset($saleInfo['retailPrice']['amount'])) {
            // Use retail price as fallback if list price isn't available
             $bookDetails['price'] = number_format($saleInfo['retailPrice']['amount'], 2);
             $bookDetails['currencyCode'] = $saleInfo['retailPrice']['currencyCode'] ?? $defaultCurrency;
        } elseif ($saleInfo['saleability'] === 'FOR_SALE') {
             $bookDetails['price'] = 'Price Unavailable'; // It's for sale, but price isn't listed
        } elseif ($saleInfo['saleability'] === 'FREE') {
             $bookDetails['price'] = '0.00'; // It's free
             $bookDetails['currencyCode'] = ''; // No currency for free items
        } elseif ($saleInfo['saleability'] === 'NOT_FOR_SALE') {
            $bookDetails['price'] = 'Not For Sale';
        }


        $bookInfoCache[$isbn] = $bookDetails; // Store successful result in cache
        return $bookDetails;

    } else {
        error_log("No book found on Google Books API for ISBN: $isbn. TotalItems: " . ($data['totalItems'] ?? 'N/A'));
        $bookInfoCache[$isbn] = null; // Cache the failure (book not found)
        return null;
    }
}

/**
 * Adds a book to the shopping cart (stored in the session).
 * The cart stores items as [isbn => quantity].
 *
 * @param string $bookId The ISBN of the book to add.
 */
function addToCart(string $bookId): void
{
    if (empty($bookId)) return; // Don't add empty IDs

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    // Ensure bookId exists as key before incrementing
    if (!isset($_SESSION['cart'][$bookId])) {
        $_SESSION['cart'][$bookId] = 0;
    }
    $_SESSION['cart'][$bookId]++; // Increment quantity
    // Optional: Add a success message or redirect
    // $_SESSION['message'] = "Book added to cart!";
}

/**
 * Displays the contents of the shopping cart stored in the PHP session.
 * Uses the getBookInfo function (and its cache) to retrieve details.
 *
 * @return array An array of book details in the cart, including quantity, or an empty array if cart is empty.
 */
function viewCart(): array
{
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return [];
    }

    $cartItems = [];
    foreach ($_SESSION['cart'] as $bookId => $quantity) {
        $bookInfo = getBookInfo($bookId); // Will use cache if already fetched
        if ($bookInfo) {
            // Important: Ensure price is treated as a number for calculation
            $priceValue = ($bookInfo['price'] !== 'Not Available' && $bookInfo['price'] !== 'Not For Sale' && $bookInfo['price'] !== 'Price Unavailable')
                          ? floatval(str_replace(',', '', $bookInfo['price'])) // Remove commas for float conversion
                          : 0.0;

            $cartItems[] = array_merge($bookInfo, [
                'quantity' => $quantity,
                'numeric_price' => $priceValue // Add a field for easier calculation
            ]);
        } else {
            // Handle case where book info couldn't be retrieved for an item in cart
            $cartItems[] = [
                'title' => 'Book Information Unavailable',
                'authors' => 'N/A',
                'image' => $GLOBALS['defaultBookImage'],
                'price' => 'N/A',
                'currencyCode' => $GLOBALS['defaultCurrency'],
                'bookId' => $bookId,
                'quantity' => $quantity,
                'numeric_price' => 0.0,
                'error' => true // Flag to indicate missing info
            ];
            error_log("Could not retrieve info for book $bookId present in the cart session.");
        }
    }
    return $cartItems;
}

/**
 * Removes a book (or reduces quantity) from the shopping cart session.
 * Currently removes the entire entry regardless of quantity.
 *
 * @param string $bookId The ISBN of the book to remove.
 */
function removeFromCart(string $bookId): void
{
    if (isset($_SESSION['cart'][$bookId])) {
        unset($_SESSION['cart'][$bookId]);
        // Optional: Add a success message or redirect
        // $_SESSION['message'] = "Book removed from cart!";

        // To decrease quantity instead of removing:
        /*
        if ($_SESSION['cart'][$bookId] > 1) {
            $_SESSION['cart'][$bookId]--;
        } else {
            unset($_SESSION['cart'][$bookId]);
        }
        */
    }
}


// --- Request Processing ---

$age = '';
$hasReadBefore = null; // Use null to check if form was submitted
$interests = [];
$recommendations = [];
$cartItems = []; // Initialize cart items array

// Process form submission for recommendations
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['getRecommendations'])) {
    $age = filter_input(INPUT_POST, 'age', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $hasReadBeforeInput = filter_input(INPUT_POST, 'hasReadBefore', FILTER_SANITIZE_STRING);
    $interests = filter_input(INPUT_POST, 'interests', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY) ?? [];

    if ($age !== false && $age !== null && $hasReadBeforeInput !== null) {
         $hasReadBefore = ($hasReadBeforeInput === 'yes');
         $recommendations = recommendBooks($age, $hasReadBefore, $interests);
         // Pre-fetch book info for recommendations to populate cache
         foreach ($recommendations as $isbn) {
             getBookInfo($isbn); // Fetch and cache
         }
    } else {
        // Handle invalid input if necessary
        echo "<div class='error'>Please provide a valid age and select reading history.</div>";
        // Reset age/hasReadBefore to avoid inconsistent state in the form redisplay
        $age = '';
        $hasReadBefore = null;
    }
     // Preserve form values for redisplay
     $_SESSION['form_input'] = ['age' => $age, 'hasReadBefore' => $hasReadBeforeInput, 'interests' => $interests];

} elseif (isset($_SESSION['form_input'])) {
    // Restore form values if page reloads after cart action
    $age = $_SESSION['form_input']['age'];
    $hasReadBeforeInput = $_SESSION['form_input']['hasReadBefore'];
    $hasReadBefore = ($hasReadBeforeInput === 'yes');
    $interests = $_SESSION['form_input']['interests'];
     // Re-run recommendations if needed (optional, could also store recommendations in session)
     if ($age && $hasReadBefore !== null) {
         $recommendations = recommendBooks($age, $hasReadBefore, $interests);
          // Pre-fetch book info again if not storing recommendations
         foreach ($recommendations as $isbn) {
             getBookInfo($isbn);
         }
     }
}


// Process cart actions (Add/Remove) - should happen after recommendation logic potentially populates cache
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $bookId = filter_input(INPUT_POST, 'bookId', FILTER_SANITIZE_STRING);
    if ($bookId) {
        switch ($_POST['action']) {
            case 'addToCart':
                addToCart($bookId);
                 // Redirect back to the same page using GET to prevent form resubmission on refresh
                 header("Location: " . $_SERVER['PHP_SELF']);
                 exit;
                break;
            case 'removeFromCart':
                removeFromCart($bookId);
                 // Redirect back to the same page using GET
                 header("Location: " . $_SERVER['PHP_SELF']);
                 exit;
                break;
        }
    }
    // Clear form input session if cart action occurred without recommendation submit
    // unset($_SESSION['form_input']); // Decide if you want form to persist after cart actions
}

// Always get current cart state AFTER potential modifications
$cartItems = viewCart();

// Clear form input session data after using it, if desired
// unset($_SESSION['form_input']);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Book Recommender</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css"/>
    <!-- Assuming swiper CSS is needed elsewhere, keeping it -->
    <link rel="stylesheet" href="./css/swiper-bundle.min.css">
    <link rel="icon" type="image/svg"  href="./img/bookfavicon.svg" />
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(to right, #27548A, #4F1C51);
            /* Add some padding to body to avoid content touching edges */
            padding: 1.5rem; /* equivalent to p-6 */
        }
        .error {
            color: #B91C1C; /* Darker Red */
            font-weight: bold;
            margin-bottom: 1rem;
            padding: 0.75rem 1rem; /* py-3 px-4 */
            border: 1px solid #F87171; /* Lighter Red Border */
            background-color: #FECACA; /* Light Red Background */
            border-radius: 0.375rem; /* rounded-md */
            color: #7f1d1d; /* Text color for contrast */
        }
        .aibtn {
            background: linear-gradient(to right, #74512D, #4E1F00);
            transition: background 400ms ease; /* Corrected transition property */
            display: inline-block; /* Ensure button behaves like a block/inline-block for styling */
            text-align: center;
        }
        .aibtn:hover {
            background: linear-gradient(to right, #8E6E4B, #6D3B1F); /* Slightly lighter hover */
        }
        .line-clamp-3 {
           overflow: hidden;
           display: -webkit-box;
           -webkit-line-clamp: 3;
           -webkit-box-orient: vertical;
        }
         /* Basic Header Styling (replace with your actual header CSS if needed) */
        .header { background: none !important; padding-bottom: 1rem;}
        .header-1 { display: flex; justify-content: space-between; align-items: center; }
        .header-1 .logo { color: white; text-decoration: none; font-size: 1.5rem; font-weight: bold;}
        .header-1 .icons a { color: white; margin-left: 1rem; font-size: 1.2rem; text-decoration: none; }
        .header-1 .icons a:hover { color: #FDE047; /* yellow-300 */ }

         /* Footer Styling */
         .footer { color: #D1D5DB; /* gray-300 */ margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid #4B5563; /* gray-600 */ }
         .footer .main-content { display: flex; justify-content: space-around; flex-wrap: wrap; gap: 1rem; margin-bottom: 1rem;}
         .footer .box h2 { font-size: 1.25rem; /* text-xl */ font-weight: 600; margin-bottom: 0.5rem; color: white; }
         .footer .content div { margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem; }
         .footer .content span.fas, .footer .content span.fab { width: 20px; text-align: center; }
         .footer .social a { color: #D1D5DB; margin-right: 0.75rem; font-size: 1.25rem; }
         .footer .social a:hover { color: white; }
         .footer .bottom { margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #4B5563; /* gray-600 */ font-size: 0.875rem; /* text-sm */ }
         .footer .bottom a { color: #FDE047; text-decoration: none; }
         .footer .bottom a:hover { text-decoration: underline; }

          /* Cart Item specific styles */
        .cart-item-details { flex-grow: 1; }
        .cart-item-remove-form { flex-shrink: 0; }
    </style>
</head>
<body class="text-white"> <!-- Removed p-6 from body, added above -->
    <header class="header">
        <div class="header-1">
            <a href="./index.php" class="logo"><i class="fas fa-book"></i> Udhhan - The flight of education</a>
            <div class="icons text-right">
                <a href="./fav.html" class="fas fa-heart-circle-check" aria-label="Favorites"></a>
                <!-- Link to cart.html - this page will need PHP/AJAX to show session cart -->
                <a href="./cart.html" class="fas fa-shopping-cart" aria-label="Shopping Cart">
                   <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                        <span class="cart-count" style="background-color: red; color: white; border-radius: 50%; padding: 2px 6px; font-size: 0.7rem; vertical-align: top; margin-left: -5px;"><?php echo array_sum($_SESSION['cart']); ?></span>
                   <?php endif; ?>
                </a>
                <a id="login-btn" class="fa-solid fa-user" href="./login.php" aria-label="Login"></a>
            </div>
        </div>
    </header>
    <!-- Removed br tags, using margin/padding instead -->

    <div class="container mx-auto rounded-lg shadow-lg bg-white/10 backdrop-blur-md p-8 mt-6">
        <h1 class="text-3xl font-semibold mb-6 text-center text-white">AI Book Recommendation</h1>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="mb-8 rounded-lg shadow-sm bg-white/20 p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                    <label for="age" class="block text-gray-200 text-sm font-bold mb-2">Your Age:</label>
                    <input type="number" id="age" name="age" value="<?php echo htmlspecialchars($age ?: ''); ?>" required min="1" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-gray-200 text-sm font-bold mb-2">Have you read a book before?</label>
                    <div class="flex items-center mt-2 space-x-4">
                        <label for="yes" class="flex items-center cursor-pointer">
                            <input type="radio" id="yes" name="hasReadBefore" value="yes" <?php if ($hasReadBefore === true) echo 'checked'; ?> required class="mr-2 text-blue-500 focus:ring-blue-400">
                            <span class="text-gray-200">Yes</span>
                        </label>
                        <label for="no" class="flex items-center cursor-pointer">
                            <input type="radio" id="no" name="hasReadBefore" value="no" <?php if ($hasReadBefore === false) echo 'checked'; ?> required class="mr-2 text-pink-500 focus:ring-pink-400">
                            <span class="text-gray-200">No</span>
                        </label>
                    </div>
                </div>
            </div>


            <div class="mb-6">
                <label for="interests" class="block text-gray-200 text-sm font-bold mb-2">Your Interests (select all that apply):</label>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                    <?php
                    // Expanded and alphabetized list
                    $allInterests = [
                        'business', 'computers', 'fantasy', 'finance', 'history', 'horror',
                         'motivation', 'mystery', 'philosophy', 'politics', 'programming',
                        'psychology', 'religion', 'romance', 'science fiction',
                        'self improvement', 'spirituality', 'storybook', 'thriller', 'war'
                    ];
                    sort($allInterests); // Keep them sorted
                    foreach ($allInterests as $interest) {
                        $interestId = str_replace(' ', '_', $interest); // Create valid ID
                        ?>
                        <div class="flex items-center bg-white/10 p-2 rounded">
                            <input type="checkbox" id="<?php echo $interestId; ?>" name="interests[]" value="<?php echo $interest; ?>" <?php if (in_array($interest, $interests)) echo 'checked'; ?> class="mr-2 rounded text-purple-500 focus:ring-purple-400">
                            <label for="<?php echo $interestId; ?>" class="text-gray-200 text-sm cursor-pointer"><?php echo ucfirst($interest); ?></label>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <button type="submit" name="getRecommendations" value="true" class="aibtn text-white font-bold py-2 px-6 rounded-full shadow-md focus:outline-none focus:shadow-outline transition duration-300 ease-in-out">
                <i class="fas fa-magic mr-2"></i> Get Recommendations
            </button>
        </form>

        <?php
        // Display recommendations only if they were generated
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['getRecommendations']) || isset($_SESSION['form_input'])) {
            if (!empty($recommendations)) {
                echo "<h2 class='text-2xl font-semibold mb-4 text-white'>Recommended Books For You:</h2>";
                echo "<div class='grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8'>"; // Added mb-8

                foreach ($recommendations as $bookIsbn) {
                    // Retrieve from cache (populated earlier or by getBookInfo call inside viewCart)
                    $bookInfo = $bookInfoCache[$bookIsbn] ?? getBookInfo($bookIsbn); // Fallback just in case

                    if ($bookInfo) {
                        echo "<div class='bg-white/20 rounded-lg shadow-md p-4 flex flex-col justify-between transition duration-300 ease-in-out hover:shadow-xl'>";
                        echo "<div>"; // Container for content before button
                        echo "<h3 class='text-lg font-semibold mb-2 text-white'>" . htmlspecialchars($bookInfo['title']) . "</h3>";
                        echo "<p class='text-gray-300 text-sm mb-2'>By: " . htmlspecialchars($bookInfo['authors']) . "</p>";
                        echo "<div class='text-center mb-4'>"; // Center the image
                        echo "<img src='" . htmlspecialchars($bookInfo['image']) . "' alt='" . htmlspecialchars($bookInfo['title']) . "' class='inline-block max-w-full h-auto rounded mb-2' style='max-height: 180px;'>";
                        echo "</div>";
                        // Add preview link if available
                         if (!empty($bookInfo['previewLink'])) {
                            echo "<a href='" . htmlspecialchars($bookInfo['previewLink']) . "' target='_blank' class='text-sm text-blue-300 hover:text-blue-100 mb-2 inline-block'><i class='fas fa-eye mr-1'></i> Preview on Google Books</a>";
                        }
                        echo "<p class='text-gray-200 text-sm mb-3 line-clamp-3'>" . htmlspecialchars($bookInfo['description']) . "</p>";
                         // Display price clearly
                        $priceDisplay = ($bookInfo['price'] === 'Not Available' || $bookInfo['price'] === 'Not For Sale' || $bookInfo['price'] === 'Price Unavailable')
                                        ? $bookInfo['price']
                                        : (!empty($bookInfo['currencyCode']) ? htmlspecialchars($bookInfo['currencyCode']) . ' ' : '₹') . htmlspecialchars($bookInfo['price']);
                         if ($bookInfo['price'] === '0.00') $priceDisplay = 'Free';

                         echo "<p class='text-yellow-300 font-bold text-md mb-3'>Price: " . $priceDisplay . "</p>";
                         echo "</div>"; // End content container

                        // Add to Cart Form
                        echo "<form method='post' action='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "' class='mt-auto'>"; // Push button to bottom
                        echo "<input type='hidden' name='action' value='addToCart'>";
                        echo "<input type='hidden' name='bookId' value='" . htmlspecialchars($bookIsbn) . "'>";
                        echo "<button type='submit' class='aibtn w-full text-white font-bold py-2 px-4 rounded-full shadow-md focus:outline-none focus:shadow-outline transition duration-300 ease-in-out'><i class='fas fa-cart-plus mr-2'></i> Add to Cart</button>";
                        echo "</form>";
                        echo "</div>"; // End card
                    } else {
                        // Display a placeholder if book info failed
                        echo "<div class='bg-white/20 rounded-lg shadow-md p-4 opacity-70'>";
                        echo "<p class='text-gray-400'>Failed to load details for ISBN: " . htmlspecialchars($bookIsbn) . ". It might be invalid or unavailable.</p>";
                        echo "</div>";
                    }
                }
                echo "</div>"; // End grid
            } elseif ($age !== '' && $hasReadBefore !== null) {
                // Only show "no books found" if the form was actually submitted with valid inputs
                 echo "<p class='text-gray-300 italic text-center my-6'>We couldn't find specific recommendations for your current selection. Try broadening your interests!</p>";
            }
        }

        // --- Display the Shopping Cart (using data from PHP Session) ---
        if (!empty($cartItems)) {
            echo "<h2 class='text-2xl font-semibold mt-10 mb-4 text-white'>Your Shopping Cart:</h2>"; // Increased mt-10
            echo "<div class='bg-white/20 rounded-lg shadow-md p-6'>";
            echo "<ul class='space-y-4'>";
            $totalPrice = 0;
            $cartCurrency = $defaultCurrency; // Default

            foreach ($cartItems as $item) {
                // Determine currency - use first valid one found, otherwise default
                if (!empty($item['currencyCode']) && $item['currencyCode'] != $defaultCurrency) {
                    $cartCurrency = $item['currencyCode']; // Use the specific currency if available and not default
                } elseif ($item['numeric_price'] > 0 && empty($cartCurrency)) {
                     // If price exists but currency unknown, default to INR (or your chosen default)
                     $cartCurrency = $defaultCurrency;
                }

                 $itemTotalPrice = $item['numeric_price'] * $item['quantity'];
                 $totalPrice += $itemTotalPrice;

                 $priceDisplay = ($item['price'] === 'Not Available' || $item['price'] === 'Not For Sale' || $item['price'] === 'Price Unavailable' || $item['price'] === 'N/A')
                                        ? $item['price'] // Display status text
                                        : (!empty($item['currencyCode']) ? htmlspecialchars($item['currencyCode']) . ' ' : '₹') . htmlspecialchars($item['price']);
                 if ($item['price'] === '0.00') $priceDisplay = 'Free';


                echo "<li class='flex flex-col sm:flex-row items-center justify-between border-b border-gray-400 pb-3'>"; // Added flex-col for smaller screens
                echo "<div class='flex items-center w-full sm:w-auto mb-3 sm:mb-0'>"; // Image and basic info
                echo "<img src='" . htmlspecialchars($item['image']) . "' alt='" . htmlspecialchars($item['title']) . "' class='w-16 h-auto rounded mr-4 shadow'>";
                echo "<div class='cart-item-details'>";
                echo "<h4 class='text-lg font-semibold text-white'>" . htmlspecialchars($item['title']) . "</h4>";
                if (!isset($item['error'])) { // Don't show author/price if info failed
                    echo "<p class='text-gray-300 text-sm'>By: " . htmlspecialchars($item['authors']) . "</p>";
                    echo "<p class='text-yellow-300 font-bold text-md'>Price: " . $priceDisplay . "</p>";
                 } else {
                     echo "<p class='text-red-400 text-sm'>Details unavailable.</p>";
                 }
                echo "<p class='text-white'>Quantity: " . htmlspecialchars($item['quantity']) . "</p>";
                echo "</div>"; // end cart-item-details
                echo "</div>"; // end flex items-center

                // Remove Button Form
                echo "<form method='post' action='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "' class='cart-item-remove-form w-full sm:w-auto text-right'>";
                echo "<input type='hidden' name='action' value='removeFromCart'>";
                echo "<input type='hidden' name='bookId' value='" . htmlspecialchars($item['bookId']) . "'>";
                echo "<button type='submit' class='text-red-400 hover:text-red-600 font-bold py-1 px-3 rounded focus:outline-none focus:shadow-outline transition duration-300 ease-in-out border border-red-400 hover:border-red-600'><i class='fas fa-trash-alt mr-1'></i> Remove</button>";
                echo "</form>";
                echo "</li>";
            }
            echo "</ul>";
             // Display Total - ensure currency symbol is correct
            $finalCurrencySymbol = !empty($cartCurrency) ? htmlspecialchars($cartCurrency) : '₹'; // Use detected or default
            echo "<div class='text-right mt-6'>"; // Right align total section
            echo "<p class='text-xl font-bold text-white'>Subtotal: " . $finalCurrencySymbol . " " . number_format($totalPrice, 2) . "</p>";
             // Add a checkout button that links to cart.html (or a real checkout page)
             // This button doesn't *do* anything server-side here, it just navigates.
             // The actual checkout process would happen on checkout.php/cart.html
            echo "<a href='./cart.html' class='inline-block mt-4 bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-full shadow-md focus:outline-none focus:shadow-outline transition duration-300 ease-in-out'><i class='fas fa-check-circle mr-2'></i> Proceed to Checkout</a>";
            echo "</div>";
            echo "</div>"; // end cart container
        } elseif (isset($_SESSION['cart']) && count($_SESSION['cart']) === 0) {
            // Show empty cart message only if cart exists but is empty
             echo "<h2 class='text-2xl font-semibold mt-10 mb-4 text-white'>Your Shopping Cart:</h2>";
             echo "<div class='bg-white/20 rounded-lg shadow-md p-6 text-center'>";
             echo "<p class='text-gray-300 italic'>Your shopping cart is currently empty.</p>";
             echo "</div>";
        }
        ?>
    </div>

    <!-- Footer -->
    <footer class="footer" id="footer">
        <div class="main-content">
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
            <div class="left box">
                 <!-- Removed empty content div, added heading for clarity -->
                 <h2>Connect</h2>
                 <div class="social">
                     <a href="#footer" aria-label="Facebook"><span class="fab fa-facebook-f"></span></a>
                     <a href="#footer" aria-label="Twitter"><span class="fab fa-twitter"></span></a>
                     <a href="#footer" aria-label="Instagram"><span class="fab fa-instagram"></span></a>
                     <a href="#footer" aria-label="YouTube"><span class="fa-brands fa-youtube"></span></a>
                 </div>
            </div>
             <!-- Maybe add a Right Box here for quick links if needed -->
        </div>
        <div class="bottom text-center">
             <span class="credit">Copyright <span class="far fa-copyright"></span> 2025</span>
             <span> <a href="./index.php">Guys at UU</a> | All rights reserved.</span>
        </div>
    </footer>
</body>
</html>