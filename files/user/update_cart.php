<?php
session_start();
$host = "localhost";
$username = "root";
$password = "";
$database = "scentoradb";
$conn = new mysqli($host, $username, $password, $database);

$cart_id = intval($_POST['cart_id'] ?? 0);
$quantity = intval($_POST['quantity'] ?? 1);

if ($cart_id < 1 || $quantity < 1) {
    echo "Invalid";
    exit;
}

$sql = "UPDATE cart SET Quantity=? WHERE Cart_ID=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $quantity, $cart_id);
$stmt->execute();
echo "updated";
$stmt->close();
$conn->close();
?>