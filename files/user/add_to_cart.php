<?php
session_start();
$host = "localhost";
$username = "root";
$password = "";
$database = "scentoradb";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    http_response_code(500);
    die("Connection failed: " . $conn->connect_error);
}

// You should get the user ID froam session in a real app
$user_id = $_SESSION['user_id'] ?? 1; // fallback for demo
$product_id = intval($_POST['product_id'] ?? 0);
$quantity = intval($_POST['quantity'] ?? 1);

if ($product_id < 1) {
    http_response_code(400);
    echo "Invalid product.";
    exit;
}

// Check if product already in cart
$sql = "SELECT Quantity FROM cart WHERE User_ID=? AND Product_ID=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $product_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    // Update quantity
    $stmt->close();
    $sql = "UPDATE cart SET Quantity = Quantity + ? WHERE User_ID=? AND Product_ID=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $quantity, $user_id, $product_id);
    $stmt->execute();
    echo "updated";
} else {
    // Insert new
    $stmt->close();
    $sql = "INSERT INTO cart (User_ID, Product_ID, Quantity) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $user_id, $product_id, $quantity);
    $stmt->execute();
    echo "added";
}
$stmt->close();
$conn->close();
?>