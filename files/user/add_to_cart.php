<?php
session_start();
header('Content-Type: text/plain');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "Please login first";
    exit;
}

$host = "localhost";
$username = "root";
$password = "";
$database = "scentoradb";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    echo "Database connection failed";
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = intval($_POST['product_id'] ?? 0);
$quantity = intval($_POST['quantity'] ?? 1);

if ($product_id < 1) {
    echo "Invalid product";
    exit;
}

// Check product stock first
$stock_check = $conn->prepare("SELECT Stock_Status FROM product WHERE Product_ID = ?");
$stock_check->bind_param("i", $product_id);
$stock_check->execute();
$result = $stock_check->get_result();
$product = $result->fetch_assoc();

// Extract number from "In stock (X pcs)"
if (preg_match('/\((\d+)\s*pcs\)/', $product['Stock_Status'], $matches)) {
    $available_stock = intval($matches[1]);
    if ($available_stock < $quantity) {
        echo "Not enough stock available";
        exit;
    }
}

// Check if product already in cart
$check = $conn->prepare("SELECT Cart_ID, Quantity FROM cart WHERE User_ID = ? AND Product_ID = ?");
$check->bind_param("ii", $user_id, $product_id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    // Update existing cart item
    $cart_item = $result->fetch_assoc();
    $new_qty = $cart_item['Quantity'] + $quantity;
    
    // Check if new quantity exceeds stock
    if ($new_qty > $available_stock) {
        echo "Cannot add more of this item (stock limit reached)";
        exit;
    }
    
    $update = $conn->prepare("UPDATE cart SET Quantity = ? WHERE Cart_ID = ?");
    $update->bind_param("ii", $new_qty, $cart_item['Cart_ID']);
    $update->execute();
    echo "updated";
} else {
    // Add new cart item
    $insert = $conn->prepare("INSERT INTO cart (User_ID, Product_ID, Quantity) VALUES (?, ?, ?)");
    $insert->bind_param("iii", $user_id, $product_id, $quantity);
    $insert->execute();
    echo "added";
}

$conn->close();
?>