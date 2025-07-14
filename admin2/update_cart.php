<?php
session_start();

$host = "localhost";
$username = "root";
$password = "";
$database = "scentoradb";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

$user_id = $_SESSION['user_id'];
$cart_id = intval($_POST['cart_id'] ?? 0);
$quantity = intval($_POST['quantity'] ?? 1);

if ($cart_id <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid cart item or quantity']);
    exit;
}

$verify_stmt = $conn->prepare("
    SELECT c.Product_ID, p.Stock_Status 
    FROM cart c 
    JOIN product p ON c.Product_ID = p.Product_ID 
    WHERE c.Cart_ID = ? AND c.User_ID = ?
");
$verify_stmt->bind_param("ii", $cart_id, $user_id);
$verify_stmt->execute();
$verify_result = $verify_stmt->get_result();

if ($verify_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Cart item not found']);
    exit;
}

$cart_data = $verify_result->fetch_assoc();

preg_match('/\((\d+)/', $cart_data['Stock_Status'], $matches);
$available_stock = isset($matches[1]) ? (int)$matches[1] : 0;

if ($quantity > $available_stock) {
    echo json_encode(['success' => false, 'message' => 'Insufficient stock available']);
    exit;
}

$update_stmt = $conn->prepare("UPDATE cart SET Quantity = ? WHERE Cart_ID = ? AND User_ID = ?");
$update_stmt->bind_param("iii", $quantity, $cart_id, $user_id);

if ($update_stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Cart updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update cart']);
}

$conn->close();
?>
