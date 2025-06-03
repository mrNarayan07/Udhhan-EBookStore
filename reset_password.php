<?php
$servername = "localhost";
$username = "root";
$passwd = "";
$dbname = "udhhan";

session_start();

$token = $_GET['token'] ?? null;
$error = null;
$success_message = null;

if ($token) {
    $conn = new mysqli($servername, $username, $passwd, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql_check_token = "SELECT user_id FROM users WHERE reset_token='$token' AND reset_token_expiry > NOW()";
    $result_check_token = $conn->query($sql_check_token);

    if ($result_check_token->num_rows == 0) {
        $error = "Invalid or expired reset token.";
    } else {
        $user = $result_check_token->fetch_assoc();
        $user_id = $user['user_id'];

        if (isset($_POST['reset_password'])) {
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];

            if (empty($new_password) || empty($confirm_password)) {
                $error = "Please enter and confirm your new password.";
            } elseif ($new_password !== $confirm_password) {
                $error = "New password and confirm password do not match.";
            } else {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $sql_update_password = "UPDATE users SET password='$hashed_password', reset_token=NULL, reset_token_expiry=NULL WHERE user_id=$user_id";
                if ($conn->query($sql_update_password) === TRUE) {
                    $success_message = "Your password has been successfully reset. You can now <a href='login.php'>login</a> with your new password.";
                } else {
                    $error = "Error updating password: " . $conn->error;
                }
            }
        }
    }
    $conn->close();
} else {
    $error = "No reset token provided.";
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
    <title>Reset Password | Udhhan</title>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="mx-auto mt-5">
                <div class="card shadow-sm p-4">
                    <div class="card-body">
                        <h4 class="card-title text-center">Reset Your Password</h4>
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php elseif ($success_message): ?>
                            <div class="alert alert-success"><?php echo $success_message; ?></div>
                        <?php else: ?>
                            <p class="card-text text-center text-muted">Enter your new password below.</p>
                            <form method="post" action="reset_password.php?token=<?php echo $token; ?>" class="mt-3">
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary btn-color-theme" name="reset_password">Reset Password</button>
                                </div>
                            </form>
                        <?php endif; ?>
                        <div class="mt-3 text-center">
                            <a href="login.php">Back to Login</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>
</html>