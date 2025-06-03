<?php
// Set the default timezone to Asia/Kolkata (IST)
date_default_timezone_set('Asia/Kolkata');

$servername = "localhost";
$username = "root";
$passwd = "";
$dbname = "udhhan";

session_start();

// Database connection
$conn = new mysqli($servername, $username, $passwd, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_data = null;
$orders = [];
$fetch_error = null;
$error = null; // For login errors
$upload_error = null; // For profile picture upload errors
$delete_error = null; // For account deletion errors
$upload_success = null; // For successful profile picture upload

// Handle logout action
if (isset($_GET['logout'])) {
    // Clear all session variables
    $_SESSION = array();
    // Destroy the session
    session_destroy();
    // Delete the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    // Delete the remember-me cookies (if you had them - adjust if your implementation is different)
    setcookie('user_id', '', time() - 3600, '/');
    setcookie('auth_token', '', time() - 3600, '/');
    setcookie('username', '', time() - 3600, '/');
    setcookie('email', '', time() - 3600, '/');

    header("Location: login.php");
    exit();
}

// Handle Login Form Submission
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT user_id, password, name, email FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Password is correct, set session
            $_SESSION['user_id'] = $user['user_id'];

            // Update last login timestamp
            $update_last_login_sql = "UPDATE users SET last_login = NOW() WHERE user_id = ?";
            $stmt_update_login = $conn->prepare($update_last_login_sql);
            $stmt_update_login->bind_param("i", $user['user_id']);
            $stmt_update_login->execute();
            $stmt_update_login->close();

            header("Location: login.php"); // Redirect to the dashboard
            exit();
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "User not found.";
    }
    $stmt->close();
}

// Check if user is already logged in via session
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $is_logged_in = true;
}
// Or check if user is logged in via cookies (basic implementation - consider security)
elseif (isset($_COOKIE['user_id']) && isset($_COOKIE['auth_token'])) {
    // ** SECURITY WARNING: This is a very basic and insecure cookie check. **
    // For a secure implementation, you would query the database to verify the 'auth_token'
    // associated with the 'user_id'. This example keeps it simple but is NOT recommended for production.
    $user_id = $_COOKIE['user_id'];
    $is_logged_in = true;
} else {
    $is_logged_in = false;
}

if ($is_logged_in) {
    // Fetch user data
    $sql = "SELECT name, email, created_at, last_login, profile_picture FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user_data = $result->fetch_assoc();
        $name = htmlspecialchars($user_data['name']);
        $email = htmlspecialchars($user_data['email']);
        $created_at = date('Y-m-d H:i:s', strtotime($user_data['created_at']));
        $last_login = date('Y-m-d H:i:s', strtotime($user_data['last_login']));
        $profile_picture = htmlspecialchars($user_data['profile_picture']) ? htmlspecialchars($user_data['profile_picture']) : './img/profile.png';

        // Update last login timestamp (already done during login form submission, but doing it here too for session/cookie login)
        $update_last_login_sql = "UPDATE users SET last_login = NOW() WHERE user_id = ?";
        $stmt_update_login = $conn->prepare($update_last_login_sql);
        $stmt_update_login->bind_param("i", $user_id);
        $stmt_update_login->execute();
        $stmt_update_login->close();

    } else {
        echo "Error: Could not retrieve user information.";
        exit();
    }
    $stmt->close();

    // Handle remove profile picture
    if (isset($_POST['remove_profile'])) {
        $sql_get_old_picture = "SELECT profile_picture FROM users WHERE user_id = ?";
        $stmt_get_old = $conn->prepare($sql_get_old_picture);
        $stmt_get_old->bind_param("i", $user_id);
        $stmt_get_old->execute();
        $result_old = $stmt_get_old->get_result();
        if ($result_old->num_rows === 1) {
            $old_data = $result_old->fetch_assoc();
            $old_picture = $old_data['profile_picture'];
            if ($old_picture && $old_picture !== './img/profile.png' && file_exists($old_picture)) {
                unlink($old_picture); // Attempt to delete the old file
            }
        }
        $stmt_get_old->close();

        $sql_remove_picture = "UPDATE users SET profile_picture = NULL WHERE user_id = ?";
        $stmt_remove = $conn->prepare($sql_remove_picture);
        $stmt_remove->bind_param("i", $user_id);
        if ($stmt_remove->execute()) {
            $profile_picture = './img/profile.png'; // Reset the displayed picture
        } else {
            $error = "Error removing profile picture.";
        }
        $stmt_remove->close();
    }

    // Handle profile picture upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
        $file = $_FILES['profile_image'];
        $max_size = 2 * 1024 * 1024; // 2MB
        $allowed_types = ['image/png', 'image/jpeg', 'image/jpg', 'image/svg', 'image/heic'];

        if ($file['size'] > $max_size) {
            $upload_error = "File size exceeds the limit (2MB).";
        } elseif (!in_array($file['type'], $allowed_types)) {
            $upload_error = "Invalid file type. Allowed types: png, jpg, jpeg, svg, heic.";
        } else {
            $upload_dir = './img/profile_pics/'; // Ensure this directory exists and is writable
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true); // Create directory if it doesn't exist
            }
            $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $new_file_name = uniqid() . '.' . $file_ext; // Unique filename
            $upload_path = $upload_dir . $new_file_name;

            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                // Get the old profile picture to delete
                $sql_get_old_picture = "SELECT profile_picture FROM users WHERE user_id = ?";
                $stmt_get_old = $conn->prepare($sql_get_old_picture);
                $stmt_get_old->bind_param("i", $user_id);
                $stmt_get_old->execute();
                $result_old = $stmt_get_old->get_result();
                if ($result_old->num_rows === 1) {
                    $old_data = $result_old->fetch_assoc();
                    $old_picture = $old_data['profile_picture'];
                    if ($old_picture && $old_picture !== './img/profile.png' && file_exists($old_picture) && $old_picture !== $upload_path) {
                        unlink($old_picture); // Attempt to delete the old file
                    }
                }
                $stmt_get_old->close();

                // Update database with the new file path
                $sql_update_picture = "UPDATE users SET profile_picture = ? WHERE user_id = ?";
                $stmt_update = $conn->prepare($sql_update_picture);
                $stmt_update->bind_param("si", $upload_path, $user_id);
                if ($stmt_update->execute()) {
                    $profile_picture = $upload_path; // Update the displayed picture
                    $upload_success = "Profile picture updated successfully!";
                } else {
                    $upload_error = "Error updating profile picture path in database.";
                    // If database update fails, you might want to delete the uploaded file
                    if (file_exists($upload_path)) {
                        unlink($upload_path);
                    }
                }
                $stmt_update->close();
            } else {
                $upload_error = "Error uploading file.";
            }
        }
    }

    // Handle delete account
    if (isset($_POST['delete_account'])) {
        // Add confirmation logic here if not already in the HTML
        $sql_delete_orders = "DELETE FROM orders WHERE user_id = ?";
        $stmt_delete_orders = $conn->prepare($sql_delete_orders);
        $stmt_delete_orders->bind_param("i", $user_id);
        $stmt_delete_orders->execute();
        $stmt_delete_orders->close();

        $sql_delete_user = "DELETE FROM users WHERE user_id = ?";
        $stmt_delete_user = $conn->prepare($sql_delete_user);
        $stmt_delete_user->bind_param("i", $user_id);
        if ($stmt_delete_user->execute()) {
            // Account deleted, log out the user
            $_SESSION = array();
            session_destroy();
            header("Location: login.php?account_deleted=1");
            exit();
        } else {
            $delete_error = "Error deleting account.";
        }
        $stmt_delete_user->close();
    }

    // Fetch order history for the logged-in user (same logic as order_history.php)
    try {
        $stmt_orders = $conn->prepare("SELECT order_id, order_date, total_amount, payment_method, item_ids FROM orders WHERE user_id = ? ORDER BY order_date DESC LIMIT 3"); // Limit to 3 recent orders
        $stmt_orders->bind_param("i", $user_id);
        $stmt_orders->execute();
        $orders_result = $stmt_orders->get_result();
        $orders = $orders_result->fetch_all(MYSQLI_ASSOC);
        $stmt_orders->close();
    } catch (mysqli_sql_exception $e) {
        error_log("Error fetching order history: " . $e->getMessage());
        $orders = [];
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
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous"/>
    <link rel="icon" href="./img/bookfavicon.svg">
    <link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" href="loginReg.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title><?php echo $is_logged_in ? 'Dashboard' : 'Login'; ?> | Udhhan</title>
    <style>
        :root {
            --primaryColor: #386c5c;
            --secondaryColor: #b3aaaa70;
            --thirdColor: #ff745c;
            --black: #444;
            --light-color: #666;
            --border: 0.1rem solid rgba(0, 0, 0, 0.1);
            --border-hover: 0.1rem solid var(--secondaryColor);
            --box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        }
        body {
            background-color: #f8f9fa;
        }
        .aibtn {
            background: linear-gradient(to right, #74512D, #4E1F00);
            transition: ease 400ms;
        }
        .login-container {
            display: flex;
            flex-direction: column; /* Stack dashboard and order history */
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 1rem;
        }
        .login-card {
            width: 100%;
            max-width: 450px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border-radius: 0.5rem;
            overflow: hidden;
            background-color: #fff;
            margin-bottom: 20px; /* Add margin below the dashboard card */
        }
        .login-card-body {
            padding: 2rem;
        }
        .profile-img-container {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            overflow: hidden;
            margin: 1rem auto 1.5rem;
            border: 0.2rem solid var(--secondaryColor);
            box-shadow: var(--box-shadow);
        }
        .profile-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: contrast(1.15);
            border: solid 2px rgba(52, 34, 18, 0.52);
            border-radius: 50%;
        }
        .dashboard-info {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .dashboard-info h5 {
            margin-bottom: 0.7rem;
            color: var(--light-color);
        }
        .dashboard-actions {
            display: flex;
            flex-direction: column;
            gap: 0.7rem;
            margin-top: 1.5rem;
        }
        .dashboard-actions button, .dashboard-actions a {
            padding: 0.8rem 1.7rem;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            color: var(--black);
            background-color: #f0f0f0;
            transition: background-color 0.3s ease;
            font-size: 1.15rem;
            color: white;
            /* New gradient style for buttons */
            background-color: var(--primaryColor);
            /* background-image: linear-gradient(to right, #41295a 0%, #2F0743  51%, #41295a  100%); */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .dashboard-actions button:hover, .dashboard-actions a:hover {
            color: white;
            background-image: none; /* Remove gradient on hover */
        }
        .upload-form {
            margin-top: 1.5rem;
            padding: 1.2rem;
            border: var(--border);
            border-radius: 0.5rem;
            background-color: #f9f9f9;
        }
        .upload-form label {
            display: block;
            margin-bottom: 0.6rem;
            font-weight: bold;
            color: var(--black);
        }
        .upload-form input[type="file"] {
            margin-bottom: 1.2rem;
        }
        .upload-form button {
            padding: 0.8rem 1.7rem;
            border: none;
            border-radius: 0.5rem;
            background-color: var(--primaryColor);
            color: white;
            cursor: pointer;
            transition: background-color 0.3s ease;
            font-size: 1rem;
        }
        .upload-form button:hover {
            background-color: #2a5246;
        }
        .error-message {
            color: var(--thirdColor);
            margin-top: 0.6rem;
            text-align: center;
        }
        .success-message {
            color: var(--primaryColor);
            margin-top: 0.6rem;
            text-align: center;
        }
        .form-group {
            margin-bottom: 1.8rem;
        }
        .form-control {
            border-radius: 0.3rem;
            padding: 0.7rem 1rem;
            border: 0.15rem solid #ced4da;
        }
        .dash-art {
            background: url(./img/dashboard_art.png);
            background-repeat: repeat;
        }
        .btn {
            transition: ease 400ms;
        }
        .btn-color-theme {
            background-color: var(--primaryColor);
            border-color: var(--primaryColor);
            font-size: 1.1rem;
            padding: 0.8rem 1.5rem;
        }
        .btn-color-theme:hover {
            background-color: #2a5246;
            border-color: #2a5246;
        }
        .text-theme {
            color: var(--primaryColor);
        }
        .card-header.bg-primary {
            background-color: #2a5246;
            border-color: #2a5246;
            text-align: center;
            border-radius-top-left: 0.5rem;
            border-radius-top-right: 0.5rem;
            padding: 1rem 0;
            font-size: 1.2rem;
            font-weight: bold;
        }

        /* Order History Styles (moved from order_history.php) */
        .order-history-container {
            width: 100%;
            max-width: 900px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #f9f9f9;
            margin-top: 20px; /* Add top margin to separate from dashboard */
        }
        .order-history-container h3 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
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
            align-items: center;
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
        .order-error-message {
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

        <div class="icons">

            <input type="file" id="profile_image" name="profile_image" accept="image/png, image/jpeg, image/jpg, image/svg, image/heic" style="display: none;">
            <label for="profile_image" style="cursor: pointer;">
                </label>

            <a href="#" class="fas fa-heart-circle-check" aria-label="Favorites"></a>

            <a href="./cart.html" class="fas fa-shopping-cart" aria-label="Shopping Cart"></a>

            <?php if ($is_logged_in): ?>
                <a id="login-btn" class="fa-solid fa-user active" href="./login.php" aria-label="Dashboard"></a>
            <?php else: ?>
                <a id="login-btn" class="fa-solid fa-user" href="./login.php" aria-label="Login"></a>
            <?php endif; ?>
        </div>
    </div>

    <div class="bottom-navbar">
        <a href="./index.php" class="fas fa-home"></a>
    </div>
    </header>
    <section class="dash-art">
    <div class="login-container">
        <div class="login-card">
            <?php if ($is_logged_in && $user_data): ?>
                <div class="card-header bg-primary text-white aibtn">
                    <h3> Dashboard </h3>
                </div>
                <div class="card-body text-center">
                    <h3 class="mb-3">Hi, <?php echo $name; ?>!</h3>
                    <div class="profile-img-container">
                        <img id="profile-preview" src="<?php echo $profile_picture; ?>" alt="Profile Picture" class="profile-img">
                    </div>
                    <div class="dashboard-info">
                        <h5><strong>Email:</strong> <?php echo $email; ?></h5>
                        <h5><strong>Joining Date:</strong> <?php echo $created_at; ?></h5>
                        <h5><strong>Last Login:</strong> <?php echo $last_login; ?></h5>
                    </div>

                    <div class="dashboard-actions">
                        <form id="upload-profile-form" action="login.php" method="post" enctype="multipart/form-data" class="upload-form">
                            <div class="mb-3">
                                <label for="profile_image_upload" class="form-label">Select an image file (png, jpg, jpeg, svg, heic, max 2MB):</label>
                                <input type="file" class="form-control" id="profile_image_upload" name="profile_image" accept="image/png, image/jpeg, image/jpg, image/svg, image/heic">
                                <small class="form-text text-muted">Maximum file size: 2MB</small>
                                <?php if (isset($upload_error)): ?>
                                    <p class="error-message"><?php echo $upload_error; ?></p>
                                <?php endif; ?>
                                <?php if (isset($upload_success)): ?>
                                    <p class="success-message"><?php echo $upload_success; ?></p>
                                <?php endif; ?>
                                <div id="upload-warning" class="error-message" style="display:none;">File size exceeds the limit (2MB).</div>
                            </div>
                           <center><button type="submit" class="btn btn-secondary" style="display:none;" id="upload_profile_button" name="upload_profile">Upload/Change Picture</button></center>
                        </form>

                        <form action="login.php" method="post">
                            <button type="submit" class="btn btn-secondary" name="remove_profile">Remove Profile Picture</button>
                            <?php if (isset($error)): ?>
                                <p class="error-message"><?php echo $error; ?></p>
                            <?php endif; ?>
                        </form>

                        <form action="login.php" method="post" onsubmit="return confirm('Are you sure you want to delete your account? This action cannot be undone.');">
                            <?php if (isset($delete_error)): ?>
                                <p class="error-message"><?php echo $delete_error; ?></p>
                            <?php endif; ?>
                            <button type="submit" class="btn btn-danger" name="delete_account">Delete Account</button>
                        </form>

                        <a href="login.php?logout=1" class="btn btn-dark">Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="card-header bg-primary text-white">
                    Sign In
                </div>
                <div class="card-body">
                    <p class="text-muted text-center mb-3">Get the Best Book Here!</p>
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <?php if (isset($_GET['account_deleted'])): ?>
                        <div class="alert alert-success">Your account has been successfully deleted.</div>
                    <?php endif; ?>
                    <form action="login.php" method="post" class="mt-4">
                        <div class="form-group">
                            <input type="email" class="form-control" placeholder="Email" name="email" id="email" autocomplete="off" required />
                        </div>
                        <div class="form-group">
                            <input type="password" class="form-control" placeholder="Password" id="password" name="password" required/>
                        </div>
                        <div class="d-grid gap-2 mt-3">
                            <button class="btn btn-primary btn-color-theme" type="submit" name="login">
                                Sign In
                            </button>
                        </div>

                        <div class="d-grid gap-2 mt-3">
                            <p class="text-center">
                                <a href="forgot_password.php" class="text-theme">Forgot Password?</a>
                            </p>
                            <p class="text-center">Don't have an account yet?
                                <a class="text-theme" href="./register.php">Sign Up</a></p>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($is_logged_in && !empty($orders)): ?>

            <div class="order-history-container">
                <h3>Your Recent Orders</h3>
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
                <?php if (count($orders) === 3): ?>
                    <div class="text-center mt-3">
                        <a href="order_history.php" class="btn btn-secondary">View All Order History</a>
                    </div>
                <?php endif; ?>
            </div>
        <?php elseif ($is_logged_in && empty($orders) && !isset($fetch_error)): ?>
            <div class="order-history-container">
                <h3>Your Recent Orders</h3>
                <p class="no-orders">You haven't placed any orders yet.</p>
            </div>
        <?php elseif ($is_logged_in && isset($fetch_error)): ?>
            <div class="order-history-container">
                <h3>Your Recent Orders</h3>
                <p class="order-error-message"><?php echo $fetch_error; ?></p>
            </div>
        <?php endif; ?>
    </div>
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
                        <span class="fas fa-envelope">
                        </span>
                        <span class="text" style="text-transform: lowercase;">udhhan-india@edu.com</span>
                    </div>
                </div>
            </div>
            <div class="right box">
                <h2>Contact us</h2>
                <div class="content">
                    <form action="#">
                        <div class="email">
                            <div class="text">Email *</div>
                            <input type="email" placeholder="Email address..." required />
                        </div>
                        <div class="msg">
                            <div class="text">Message *</div>
                            <textarea rows="2" cols="25" placeholder="Your message..." required></textarea>
                        </div>
                        <div class="btn">
                            <button style="width: 46.5rem;" type="submit">Send</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="bottom">
            <span class="credit">Copyright <span class="far fa-copyright"></span> 2025</span>
            <span><a href="#">Guys at UU</a> | All rights reserved.</span>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script>
        const profileImageInput = document.getElementById('profile_image_upload');
        const profilePreview = document.getElementById('profile-preview');
        const uploadButton = document.getElementById('upload_profile_button');
        const uploadWarning = document.getElementById('upload-warning');

        if (profileImageInput) {
            profileImageInput.addEventListener('change', function() {
                const file = this.files[0];
                const maxSize = 2 * 1024 * 1024; // 2MB

                if (file && file.size > maxSize) {
                    uploadWarning.style.display = 'block';
                    uploadButton.style.display = 'none';
                    profilePreview.src = "<?php echo $profile_picture ?? './img/profile.png'; ?>"; // Reset preview
                    this.value = ''; // Clear the file input
                } else if (file) {
                    uploadWarning.style.display = 'none';
                    uploadButton.style.display = 'block';
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        profilePreview.src = e.target.result;
                    }
                    reader.readAsDataURL(file);
                } else {
                    uploadButton.style.display = 'none';
                    profilePreview.src = "<?php echo $profile_picture ?? './img/profile.png'; ?>"; // Keep existing or default
                }
            });
        }
    </script>
</body>
</html>