<?php
date_default_timezone_set('Asia/Kolkata');
session_start();

// Database connection details
$servername = "localhost";
$username = "root";
$passwd = "";
$dbname = "udhhan";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $passwd);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    error_log("Database connection failed in process_order.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error occurred.']);
    exit();
}

// Check if the 'orders' table exists
try {
    $result = $conn->query("SHOW TABLES LIKE 'orders'");
    if ($result->rowCount() == 0) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'The "orders" table does not exist. Please create it using the following SQL:',
            'sql' => "
            CREATE TABLE orders (
                order_id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                item_ids TEXT NOT NULL,
                total_amount DECIMAL(10, 2) NOT NULL,
                payment_method VARCHAR(50) NOT NULL,
                order_date DATETIME NOT NULL
            );
            "
        ]);
        $conn = null;
        exit();
    }
} catch(PDOException $e) {
    error_log("Error checking for 'orders' table: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error checking database tables.']);
    $conn = null;
    exit();
}

// Get the JSON data sent from the checkout page
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

if ($data && isset($_SESSION['user_id']) && isset($data['items']) && is_array($data['items']) && isset($data['total_amount']) && isset($data['payment_method'])) {
    $user_id = $_SESSION['user_id'];
    $item_ids_json = json_encode($data['items']); // Store item IDs as JSON
    $total_amount = $data['total_amount'];
    $payment_method = $data['payment_method'];
    $order_date = date("Y-m-d H:i:s");

    try {
        $stmt = $conn->prepare("INSERT INTO orders (user_id, item_ids, total_amount, payment_method, order_date) VALUES (:user_id, :item_ids, :total_amount, :payment_method, :order_date)");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':item_ids', $item_ids_json);
        $stmt->bindParam(':total_amount', $total_amount);
        $stmt->bindParam(':payment_method', $payment_method);
        $stmt->bindParam(':order_date', $order_date);
        $stmt->execute();

        echo json_encode(['success' => true]);

    } catch(PDOException $e) {
        error_log("Error inserting order into database: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error saving order. Please try again.']);
    }

} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid order data received.']);
}

$conn = null; // Close the database connection
?>