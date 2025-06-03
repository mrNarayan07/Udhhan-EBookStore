<?php
// Set the default timezone to Asia/Kolkata (IST)
date_default_timezone_set('Asia/Kolkata');

$servername = "localhost";
$username = "root";
$passwd = ""; 
$dbname = "udhhan";
// Start session
session_start();

// Check if the form is submitted
if (isset($_POST['register'])) {
    // Get user inputs
    $name = $_POST['username']; // Assuming the name field has name="username"
    $email = $_POST['email'];   // Assuming the email field has name="email"
    $password = $_POST['password'];
    $password2 = $_POST['password2'];
    $security_question = $_POST['security_question'];
    $security_answer = $_POST['security_answer'];
    // Validate inputs (you might want to add more robust validation)
    if (empty($name) || empty($email) || empty($password) || empty($password2)) {
        $error = "All fields are required.";
    } elseif ($password !== $password2) {
        $error = "Passwords do not match.";
    } elseif (empty($security_question) || empty($security_answer)) {
        $error = "Please select a security question and provide an answer.";
    }else {
        // Create connection
        $conn = new mysqli($servername, $username, $passwd, $dbname);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Check if the users table exists, if not create it
        $sql_check_table = "SHOW TABLES LIKE 'users'";
        $result_check_table = $conn->query($sql_check_table);

        if ($result_check_table->num_rows == 0) {
            $sql_create_table = "CREATE TABLE users (
            user_id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            password TEXT NOT NULL,
            security_question VARCHAR(255),
            security_answer VARCHAR(255),
            logs TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            profile_picture VARCHAR(255),
            last_login TIMESTAMP DEFAULT '0000-00-00 00:00:00',
            is_active TINYINT(1) DEFAULT 1,
            role VARCHAR(50) DEFAULT 'user',
            reset_token VARCHAR(255),
            reset_token_expiry DATETIME
            )";

            if ($conn->query($sql_create_table) === TRUE) {
                echo "Table users created successfully<br>";
            } else {
                echo "Error creating table: " . $conn->error . "<br>";
                $conn->close();
                exit();
            }
        }

        // Check if the email already exists
        $sql_check_email = "SELECT email FROM users WHERE email='$email'";
        $result_check_email = $conn->query($sql_check_email);

        if ($result_check_email->num_rows > 0) {
            $error = "Email already exists.";
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert new user data
             $sql_insert = "INSERT INTO users (name, email, password, security_question, security_answer) VALUES ('$name', '$email', '$hashed_password', '$security_question', '$security_answer')";

            if ($conn->query($sql_insert) === TRUE) {
                echo "Registration Successful!<br>";

                // Set session variables
                $_SESSION['user_id'] = $conn->insert_id;
                $_SESSION['username'] = $name;
                $_SESSION['email'] = $email;

                // Set cookie for 7 days
                $expiry = time() + (7 * 24 * 60 * 60);
                setcookie('user_id', $conn->insert_id, $expiry, '/', '', false, true);
                setcookie('auth_token', $hashed_password, $expiry, '/', '', false, true); // Store hashed password (not ideal for security, but for basic persistent login)
                setcookie('username', $name, $expiry, '/', '', false, true);
                setcookie('email', $email, $expiry, '/', '', false, true);

                // Redirect to index.php
                header("Location: ./index.php");
                exit();
            } else {
                $error = "Error during registration: " . $conn->error;
            }
        }
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC"
        crossorigin="anonymous"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css"/>
     <!-- <link rel="stylesheet" href="./css/font-awesome.min.css" /> -->
    <!-- Swiper -->
    <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css"/> -->
     <link rel="stylesheet" href="./css/swiper-bundle.min.css">
    <link rel="icon" type="image/svg"  href="./img/bookfavicon.svg" />
    <link rel="stylesheet" href="./css/style.css" />
    <link rel="stylesheet" href="./css/loginReg.css" />
    <script src="script.js" defer></script>
    <title>Register | Udhhan</title>
</head>
<body>
     <!-- Header Start  -->
     <header class="header">
      <!-- Header 1 Start  -->
      <div class="header-1">
        <a href="./index.php" class="logo"><i class="fas fa-book"></i> Udhhan- The flight of education</a>

    </header>

    <div class="container">
        <div class="row">
            <div class="mx-auto mt-2 text-center mt-3">
                <img src="./img/booklogo.png" width="4%" alt="Logo Barber" />
            </div>
        </div>
        <div class="row mt-5">
            <div class="col-md-6">
                <img class="img-register img-fluid" src="./img/img2.svg" width="435px" alt="Register Image"/>
            </div>
            <div class="col-md-6 mt-3">
                <div class="card shadow-sm p-3">
                    <div class="card-body">
                        <h4>Sign Up</h4>
                        <p class="text-muted">Sign Up for the Best Book!</p>
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <form action="register.php" class="mt-4" method="post">
                            <div class="form-group">
                                <input type="text" class="form-control" name="username" id="username" placeholder="Name" required/>
                            </div>
                            <div class="form-group mt-2">
                                <input type="email" class="form-control" placeholder="Email" name="email" id="email" autocomplete="off" required />
                            </div>
                            <div class="input-group mt-2">
                                <input type="password" class="form-control" placeholder="Password" id="password" name="password" required/>
                               <!-- <div class="input-group-append">
                                    <span class="input-group-text" onclick="displayPassword()">
                                
                                    </span>
                                </div>-->
                            </div>
                            <div class="input-group mt-2">
                                <input type="password" class="form-control" placeholder="Confirm Password" id="passwordConfirm" name="password2" required/>
                                <!-- <div class="input-group-append">
                                    <span
                                        class="input-group-text"
                                        onclick="displayPasswordConfirm()">
                                
                                    </span>
                                </div> -->
                            </div>
                            <div class="form-group mt-2">
                                <label for="security_question" class="form-label">Security Question</label>
                                <select class="form-control" id="security_question" name="security_question" required>
                                    <option value="">Select a question</option>
                                    <option value="What is your mother's maiden name?">What is your mother's maiden name?</option>
                                    <option value="What was the name of your first pet?">What was the name of your first pet?</option>
                                    <option value="What is your favorite color?">What is your favorite color?</option>
                                    <option value="What city were you born in?">What city were you born in?</option>
                                </select>
                            </div>
                            <div class="form-group mt-2">
                                <label for="security_answer" class="form-label">Answer</label>
                                <input type="text" class="form-control" id="security_answer" name="security_answer" placeholder="Your Answer" required/>
                            </div>
                            <div class="d-grid gap-2 mt-3">
                                <button
                                    class="btn btn-primary mt-2 btn-color-theme"
                                    type="submit"
                                    name="register"
                                >
                                    Sign Up
                                </button>
                                <p class="text-center">
                                    Already have an account?
                                    <a class="text-theme" href="login.php">Sign In</a>
                                </p>
                                </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Footer Start -->
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
    <!-- Footer End  -->

    <script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.min.js"></script>

    <script src="https://unpkg.com/feather-icons"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script>
        feather.replace();
    </script>
</body>
</html>