<?php
$servername = "localhost";
$username = "root";
$passwd = ""; 
$dbname = "udhhan";
session_start();

$show_security_question = false;
$email = $_POST['email'] ?? null;
$error = null;
$security_question_text = null;

if (isset($_POST['forgot_password_email'])) {
    if (empty($email)) {
        $error = "Please enter your email address.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        $conn = new mysqli($servername, $username, $passwd, $dbname);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $sql_get_question = "SELECT user_id, name, security_question FROM users WHERE email='$email'";
        $result_get_question = $conn->query($sql_get_question);

        if ($result_get_question->num_rows == 1) {
            $user = $result_get_question->fetch_assoc();
            $_SESSION['forgot_password_user_id'] = $user['user_id'];
            $_SESSION['forgot_password_name'] = $user['name'];
            $security_question_text = $user['security_question'];
            $show_security_question = true;
        } else {
            $error = "No user found with that email address.";
        }
        $conn->close();
    }
}

if (isset($_POST['forgot_password_answer']) && isset($_SESSION['forgot_password_user_id'])) {
    $security_answer = $_POST['security_answer'];
    $user_id = $_SESSION['forgot_password_user_id'];

    if (empty($security_answer)) {
        $error = "Please enter your security answer.";
    } else {
        $conn = new mysqli($servername, $username, $passwd, $dbname);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $sql_check_answer = "SELECT user_id FROM users WHERE user_id=$user_id AND security_answer='$security_answer'"; // Consider hashing the stored answer
        $result_check_answer = $conn->query($sql_check_answer);

        if ($result_check_answer->num_rows == 1) {
            // Security answer is correct, now generate and send reset link
            $reset_token = bin2hex(random_bytes(32));
            $expiry_time = date("Y-m-d H:i:s", time() + (30 * 60)); // Token expires in 30 minutes

            $sql_update_token = "UPDATE users SET reset_token='$reset_token', reset_token_expiry='$expiry_time' WHERE user_id=$user_id";
            if ($conn->query($sql_update_token) === TRUE) {
                $reset_link = "reset_password.php?token=$reset_token";
                $subject = "Password Reset Request for Udhhan";
                $message = "Dear " . $_SESSION['forgot_password_name'] . ",\n\nYou have correctly answered your security question. Please click on the following link to reset your password:\n\n$reset_link\n\nThis link will expire in 30 minutes.\n\nIf you did not request a password reset, please ignore this email.\n\nSincerely,\nThe Udhhan Team";
                $headers = "From: your_email@example.com"; // Replace with your actual sending email address

                if (mail($email, $subject, $message, $headers)) {
                    $success_message = "A password reset link has been sent to your email address. Please check your inbox (and spam folder).";
                    unset($_SESSION['forgot_password_user_id']); // Clear session data
                    unset($_SESSION['forgot_password_name']);
                    $show_security_question = false; // Hide the security question form
                } else {
                    $error = "Failed to send the password reset email. Please try again later.";
                }
            } else {
                $error = "Error updating reset token in the database: " . $conn->error;
            }
        } else {
            $error = "Incorrect security answer.";
            $show_security_question = true; // Re-show the question form
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous"/>
    <link rel="stylesheet" href="./css/loginReg.css" />
    <title>Forgot Password | Udhhan</title>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="mx-auto mt-5">
                <div class="card shadow-sm p-4">
                    <div class="card-body">
                        <h4 class="card-title text-center">Forgot Your Password?</h4>
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <?php if (isset($success_message)): ?>
                            <div class="alert alert-success"><?php echo $success_message; ?></div>
                        <?php endif; ?>

                        <?php if (!$show_security_question && !isset($success_message)): ?>
                            <p class="card-text text-center text-muted">Enter your email address below to verify your identity.</p>
                            <form method="post" action="forgot_password.php" class="mt-3">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" required value="<?php echo htmlspecialchars($email ?? ''); ?>">
                                </div>
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary btn-color-theme" name="forgot_password_email">Verify Email</button>
                                </div>
                                <div class="mt-3 text-center">
                                    <a href="login.php">Back to Login</a>
                                </div>
                            </form>
                        <?php endif; ?>

                        <?php if ($show_security_question): ?>
                            <p class="card-text mt-3">Please answer the following security question:</p>
                                <p class="card-text lead fw-bold"><?php echo htmlspecialchars($security_question_text); ?></p>
                                <form method="post" action="forgot_password.php" class="mt-3">
                                    <div class="mb-3">
                                        <label for="security_answer" class="form-label">Your Answer</label>
                                        <input type="text" class="form-control" id="security_answer" name="security_answer" required>
                                    </div>
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary btn-color-theme" name="forgot_password_answer">Submit Answer</button>
                                    </div>
                                    <div class="mt-3 text-center">
                                        <a href="login.php">Back to Login</a>
                                    </div>
                                </form>
                            <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>
</html>