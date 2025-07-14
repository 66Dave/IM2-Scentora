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

if ($cart_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid cart item']);
    exit;
}

$delete_stmt = $conn->prepare("DELETE FROM cart WHERE Cart_ID = ? AND User_ID = ?");
$delete_stmt->bind_param("ii", $cart_id, $user_id);

if ($delete_stmt->execute()) {
    if ($delete_stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Item removed from cart']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Cart item not found']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to remove item']);
}

$conn->close();
?>
