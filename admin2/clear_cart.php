<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

$host = "localhost";
$username = "root";
$password = "";
$database = "scentoradb";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$user_id = $_SESSION['user_id'];

$clear_stmt = $conn->prepare("DELETE FROM cart WHERE User_ID = ?");
$clear_stmt->bind_param("i", $user_id);

if ($clear_stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Cart cleared successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to clear cart']);
}

$conn->close();
?>
