<?php

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

// Debug logging
function logError($message) {
    error_log(date('Y-m-d H:i:s') . " - " . $message . "\n", 3, "../error.log");
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

$host = "localhost";
$username = "root";
$password = "";
$database = "scentoradb";

try {
    $conn = new mysqli($host, $username, $password, $database);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    $conn->begin_transaction();

    $user_id = $_SESSION['user_id'];
    $fullname = $_POST['fullname'] ?? '';
    $address = $_POST['address'] ?? '';
    $payment_method = $_POST['payment'] ?? '';
    $courier = $_POST['courier'] ?? '';

    // Validate inputs
    if (empty($fullname) || empty($address) || empty($payment_method)) {
        throw new Exception("All fields are required");
    }

    // Handle file upload
    if (!isset($_FILES['proof']) || $_FILES['proof']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Payment proof is required");
    }

    $upload_dir = '../uploads/proofs/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $file = $_FILES['proof'];
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $file_name = 'proof_' . time() . '_' . uniqid() . '.' . $file_ext;
    $file_path = $upload_dir . $file_name;

    if (!move_uploaded_file($file['tmp_name'], $file_path)) {
        throw new Exception("Failed to upload proof of payment");
    }

    // Calculate total from cart
    $cart_sql = "SELECT SUM(c.Quantity * p.Product_Price) as total 
                 FROM cart c 
                 JOIN product p ON c.Product_ID = p.Product_ID 
                 WHERE c.User_ID = ?";
    
    $stmt = $conn->prepare($cart_sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $total = $result->fetch_assoc()['total'] ?? 0;

    if ($total <= 0) {
        throw new Exception("Cart is empty");
    }

    // Insert order
    $order_sql = "INSERT INTO `order` (User_ID, Total_Amount, Shipping_Address, Payment_Method, Payment_Proof, Status) 
                  VALUES (?, ?, ?, ?, ?, 'Pending')";
    
    $stmt = $conn->prepare($order_sql);
    $stmt->bind_param("idsss", $user_id, $total, $address, $payment_method, $file_path);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to create order: " . $stmt->error);
    }
    
    $order_id = $conn->insert_id;

    // Move cart items to order details
    $details_sql = "INSERT INTO orderdetails (Order_ID, Product_ID, Product_Price, Product_Qty) 
                    SELECT ?, c.Product_ID, p.Product_Price, c.Quantity 
                    FROM cart c 
                    JOIN product p ON c.Product_ID = p.Product_ID 
                    WHERE c.User_ID = ?";
    
    $stmt = $conn->prepare($details_sql);
    $stmt->bind_param("ii", $order_id, $user_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to create order details");
    }

    // Clear cart
    $clear_sql = "DELETE FROM cart WHERE User_ID = ?";
    $stmt = $conn->prepare($clear_sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Order placed successfully',
        'order_id' => $order_id
    ]);

} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    logError($e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

if (isset($conn)) {
    $conn->close();
}
?>