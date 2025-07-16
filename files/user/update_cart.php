<?php
session_start();
header("Content-Type: text/plain");

$host = "localhost";
$username = "root";
$password = "";
$database = "scentoradb";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    http_response_code(500);
    die("Connection failed: " . $conn->connect_error);
}

$cart_id = intval($_POST['cart_id'] ?? 0);
$quantity = intval($_POST['quantity'] ?? 1);
$user_id = $_SESSION['user_id'] ?? 1; // fallback for demo/testing

if ($cart_id < 1 || $quantity < 1) {
    echo "Invalid cart or quantity.";
    exit;
}

//Fetch product ID and check stock
$sql = "SELECT Product_ID FROM cart WHERE Cart_ID = ? AND User_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $cart_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $product_id = (int)$row['Product_ID'];
    $stmt->close();

    $stockSql = "SELECT Available_Stocks FROM product WHERE Product_ID = ?";
    $stockStmt = $conn->prepare($stockSql);
    $stockStmt->bind_param("i", $product_id);
    $stockStmt->execute();
    $stockResult = $stockStmt->get_result();

    if ($stockRow = $stockResult->fetch_assoc()) {
        $available = (int)$stockRow['Available_Stocks'];
        if ($quantity > $available) {
            echo "Only {$available} item(s) available.";
            exit;
        }
    } else {
        echo "Product not found.";
        exit;
    }
    $stockStmt->close();
} else {
    echo "Cart item not found.";
    exit;
}

//Update cart quantity
$updateSql = "UPDATE cart SET Quantity = ? WHERE Cart_ID = ?";
$updateStmt = $conn->prepare($updateSql);
$updateStmt->bind_param("ii", $quantity, $cart_id);
$updateStmt->execute();
echo "updated";
$updateStmt->close();
$conn->close();
?>