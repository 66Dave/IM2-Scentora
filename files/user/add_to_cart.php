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

$user_id = $_SESSION['user_id'] ?? 1; // fallback for demo/testing
$product_id = intval($_POST['product_id'] ?? 0);
$quantity = intval($_POST['quantity'] ?? 1);

if ($product_id < 1 || $quantity < 1) {
    http_response_code(400);
    echo "Invalid product or quantity.";
    exit;
}

//Check current available stock
$stockCheckSql = "SELECT Available_Stocks FROM product WHERE Product_ID = ?";
$stockStmt = $conn->prepare($stockCheckSql);
$stockStmt->bind_param("i", $product_id);
$stockStmt->execute();
$stockResult = $stockStmt->get_result();
if ($stockRow = $stockResult->fetch_assoc()) {
    $availableStock = (int)$stockRow['Available_Stocks'];
    if ($availableStock < $quantity) {
        http_response_code(400);
        echo "Only {$availableStock} item(s) left in stock.";
        exit;
    }
} else {
    http_response_code(404);
    echo "Product not found.";
    exit;
}
$stockStmt->close();

//Check if product is already in cart
$sql = "SELECT Quantity FROM cart WHERE User_ID = ? AND Product_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $product_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    //Fetch existing quantity
    $stmt->bind_result($existingQty);
    $stmt->fetch();
    $totalQty = $existingQty + $quantity;

    if ($totalQty > $availableStock) {
        echo "Cannot add more than {$availableStock} total item(s). Already have {$existingQty} in cart.";
        exit;
    }

    // Update cart quantity
    $stmt->close();
    $updateSql = "UPDATE cart SET Quantity = ? WHERE User_ID = ? AND Product_ID = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("iii", $totalQty, $user_id, $product_id);
    $updateStmt->execute();
    echo "updated";
    $updateStmt->close();
} else {
    $stmt->close();

    //Check if adding initial quantity exceeds stock
    if ($quantity > $availableStock) {
        echo "Only {$availableStock} item(s) available.";
        exit;
    }

    // Insert new item into cart
    $insertSql = "INSERT INTO cart (User_ID, Product_ID, Quantity) VALUES (?, ?, ?)";
    $insertStmt = $conn->prepare($insertSql);
    $insertStmt->bind_param("iii", $user_id, $product_id, $quantity);
    $insertStmt->execute();
    echo "added";
    $insertStmt->close();
}

$conn->close();
?>