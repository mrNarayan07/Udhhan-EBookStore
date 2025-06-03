<?php
// Set the default timezone to Asia/Kolkata (IST)
date_default_timezone_set('Asia/Kolkata');

$servername = "localhost";
$username = "root";
$passwd = "";
$dbname = "udhhan";

session_start();

// Check for logout action
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
    // Delete the remember-me cookies (if you had them)
    setcookie('user_id', '', time() - 3600, '/');
    setcookie('auth_token', '', time() - 3600, '/');
    setcookie('username', '', time() - 3600, '/');
    setcookie('email', '', time() - 3600, '/');

    header("Location: login.php");
    exit();
}

// Check if user is already logged in via session
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $is_logged_in = true;
}
// Or check if user is logged in via cookies (basic implementation - consider security)
elseif (isset($_COOKIE['user_id']) && isset($_COOKIE['auth_token'])) {
    $user_id = $_COOKIE['user_id'];
    // Ideally, you would verify the auth_token against the stored password hash
    // For simplicity in this example, we'll just assume the cookie is valid.
    $is_logged_in = true;
} else {
    $is_logged_in = false;
}

// Database connection
$conn = new mysqli($servername, $username, $passwd, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_data = null;
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

        // Update last login timestamp
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
        if ($user_data['profile_picture'] && $user_data['profile_picture'] !== './img/profile.png') {
            if (unlink($user_data['profile_picture'])) {
                $update_sql = "UPDATE users SET profile_picture = NULL WHERE user_id = ?";
                $stmt_update = $conn->prepare($update_sql);
                $stmt_update->bind_param("i", $user_id);
                if ($stmt_update->execute()) {
                    header("Location: login.php"); // Refresh
                    exit();
                } else {
                    $upload_error = "Error resetting profile picture in the database.";
                }
                $stmt_update->close();
            } else {
                $upload_error = "Error deleting the profile picture from the directory.";
            }
        }
    }

    // Handle profile picture upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/svg', 'image/heic'];
        $file_type = $_FILES['profile_image']['type'];
        $file_size = $_FILES['profile_image']['size']; // In bytes
        $max_size = 2 * 1024 * 1024; // 2MB

        if (in_array($file_type, $allowed_types) && $file_size <= $max_size) {
            $upload_dir = './uploads/'; // Create this directory if it doesn't exist
            $file_extension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
            $new_file_name = 'profile_' . $user_id . '_' . time() . '.' . $file_extension;
            $destination = $upload_dir . $new_file_name;

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            // Delete previous profile picture if it's not the default
            if ($user_data['profile_picture'] && $user_data['profile_picture'] !== './img/profile.png' && file_exists($user_data['profile_picture'])) {
                unlink($user_data['profile_picture']);
            }

            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $destination)) {
                // Update the database with the new profile picture path
                $update_sql = "UPDATE users SET profile_picture = ? WHERE user_id = ?";
                $stmt_update = $conn->prepare($update_sql);
                $stmt_update->bind_param("si", $destination, $user_id);
                if ($stmt_update->execute()) {
                    header("Location: login.php"); // Refresh
                    exit();
                } else {
                    $upload_error = "Error updating profile picture path in the database.";
                }
                $stmt_update->close();
            } else {
                $upload_error = "Error uploading the file.";
            }
        } else {
            $upload_error = "Invalid file type or file size exceeds the limit (2MB). Allowed types: JPG, JPEG, PNG, SVG, HEIC.";
        }
    } elseif (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 1) {
        $upload_error = "The uploaded file exceeds the upload_max_filesize directive in php.ini.";
    } elseif (isset($_FILES['profile_image']) && $_FILES['profile_image']['size'] > (2 * 1024 * 1024)) {
        $upload_error = "File size exceeds the limit (2MB).";
    }


    // Handle delete account
    if (isset($_POST['delete_account'])) {
        // Add confirmation step in a real application!
        $delete_sql = "DELETE FROM users WHERE user_id = ?";
        $stmt_delete = $conn->prepare($delete_sql);
        $stmt_delete->bind_param("i", $user_id);
        if ($stmt_delete->execute()) {
            // Delete profile picture if it's not the default
            if ($user_data['profile_picture'] && $user_data['profile_picture'] !== './img/profile.png' && file_exists($user_data['profile_picture'])) {
                unlink($user_data['profile_picture']);
            }
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
            // Delete the remember-me cookies
            setcookie('user_id', '', time() - 3600, '/');
            setcookie('auth_token', '', time() - 3600, '/');
            setcookie('username', '', time() - 3600, '/');
            setcookie('email', '', time() - 3600, '/');

            header("Location: login.php?account_deleted=1");
            exit();
        } else {
            $delete_error = "Error deleting your account.";
        }
        $stmt_delete->close();
    }
}

// Handle login form submission
if (isset($_POST['login']) && !$is_logged_in) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        $sql_login = "SELECT user_id, name, email, password FROM users WHERE email=?";
        $stmt_login = $conn->prepare($sql_login);
        $stmt_login->bind_param("s", $email);
        $stmt_login->execute();
        $result_login = $stmt_login->get_result();

        if ($result_login->num_rows == 1) {
            $row = $result_login->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                echo "Logged In!<br>";

                // Set session variables
                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['username'] = $row['name'];
                $_SESSION['email'] = $row['email'];

                // Set cookie for 7 days (basic implementation)
                $expiry = time() + (7 * 24 * 60 * 60);
                setcookie('user_id', $row['user_id'], $expiry, '/');
                setcookie('auth_token', $row['password'], $expiry, '/'); // Storing plain hashed password in cookie - SECURITY RISK!
                setcookie('username', $row['name'], $expiry, '/');
                setcookie('email', $row['email'], $expiry, '/');

                // Redirect to index.php after successful login
                header("Location: index.php");
                exit();
            } else {
                $error = "Incorrect password.";
            }
        } else {
            $error = "Invalid email address.";
        }
        $stmt_login->close();
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
    <title>Login | Udhhan</title>
    <style>
            :root {
    --primaryColor: #386c5c;
    --secondaryColor: #b3aaaa70;
    --thirdColor: #ff745c;
    --black: #444;
    --light-color: #666;
    --border: 0.1rem solid rgba(0, 0, 0, 0.1);
    --border-hover: 0.1rem solid var(--secondaryColor);
    --box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
    }
        body {
            background-color: #f8f9fa;
            }
        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 1rem;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border-radius: 0.5rem;
            overflow: hidden;
        }
        .login-card-body {
            padding: 2rem;
        }
        .profile-img-container {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            overflow: hidden;
            margin: 1rem auto;
            border: 0.2rem solid var(--secondaryColor);
            box-shadow: var(--box-shadow);
        }
        .profile-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .dashboard-info {
            text-align: center;
            margin-bottom: 1rem;
        }
        .dashboard-info p {
            margin-bottom: 0.5rem;
            color: var(--light-color);
        }
        .dashboard-actions {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        .dashboard-actions button, .dashboard-actions a {
            padding: 0.7rem 1.5rem;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            color: var(--black);
            background-color: #f0f0f0;
            transition: background-color 0.3s ease;
        }
        .dashboard-actions button:hover, .dashboard-actions a:hover {
            background-color: var(--secondaryColor);
            color: white;
        }
        .upload-form {
            margin-top: 1rem;
            padding: 1rem;
            border: var(--border);
            border-radius: 0.5rem;
            background-color: #f9f9f9;
        }
        .upload-form label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
            color: var(--black);
        }
        .upload-form input[type="file"] {
            margin-bottom: 1rem;
        }
        .upload-form button {
            padding: 0.7rem 1.5rem;
            border: none;
            border-radius: 0.5rem;
            background-color: var(--primaryColor);
            color: white;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .upload-form button:hover {
            background-color: #2a5246;
        }
        .error-message {
            color: var(--thirdColor);
            margin-top: 0.5rem;
            text-align: center;
        }
        .success-message {
            color: var(--primaryColor);
            margin-top: 0.5rem;
            text-align: center;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-control {
            border-radius: 0.3rem;
        }
        .btn-color-theme {
            background-color: var(--primaryColor);
            border-color: var(--primaryColor);
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
                <!-- <i class="fas fa-camera" aria-label="Upload Profile Picture"></i> -->
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

    <div class="login-container">
        <div class="login-card">
            <?php if ($is_logged_in && $user_data): ?>
                <div class="card-header bg-primary text-white">
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
                                <div id="upload-warning" class="error-message" style="display:none;">File size exceeds the limit (2MB).</div>
                            </div>
                            <button type="submit" style="display:none;" id="upload_profile_button" name="upload_profile">Upload/Change Picture</button>
                        </form>

                        <form action="login.php" method="post">
                            <button type="submit" class="btn btn-secondary" name="remove_profile">Remove Profile Picture</button>
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
    </div>

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
                            <input type="email" placeholder="Email address..." required />
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script>
        $(document).ready(function() {
            // Profile picture upload on change
            $('#profile_image_upload').change(function() {
                const file = this.files[0];
                const maxSize = 2 * 1024 * 1024; // 2MB

                if (file) {
                    if (file.size > maxSize) {
                        $('#upload-warning').show();
                        this.value = ''; // Clear the file input
                        $('#profile-preview').attr('src', '<?php echo $profile_picture; ?>'); // Revert preview
                    } else {
                        $('#upload-warning').hide();
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            $('#profile-preview').attr('src', e.target.result);
                        }
                        reader.readAsDataURL(file);
                        $('#upload_profile_button').click(); // Automatically submit the form
                    }
                } else {
                    $('#profile-preview').attr('src', '<?php echo $profile_picture; ?>'); // Revert if no file selected
                }
            });
        });
    </script>
</body>
</html>