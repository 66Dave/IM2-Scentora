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
$product_id = intval($_POST['product_id'] ?? 0);
$quantity = intval($_POST['quantity'] ?? 1);

if ($product_id <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product or quantity']);
    exit;
}

$product_check = $conn->prepare("SELECT Product_ID, Product_Name, Product_Price, Stock_Status FROM product WHERE Product_ID = ? AND Is_Active = 1");
$product_check->bind_param("i", $product_id);
$product_check->execute();
$product_result = $product_check->get_result();

if ($product_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Product not found or unavailable']);
    exit;
}

$product = $product_result->fetch_assoc();

preg_match('/\((\d+)/', $product['Stock_Status'], $matches);
$available_stock = isset($matches[1]) ? (int)$matches[1] : 0;

if ($available_stock < $quantity) {
    echo json_encode(['success' => false, 'message' => 'Insufficient stock available']);
    exit;
}

$cart_check = $conn->prepare("SELECT Cart_ID, Quantity FROM cart WHERE User_ID = ? AND Product_ID = ?");
$cart_check->bind_param("ii", $user_id, $product_id);
$cart_check->execute();
$cart_result = $cart_check->get_result();

if ($cart_result->num_rows > 0) {
    $cart_item = $cart_result->fetch_assoc();
    $new_quantity = $cart_item['Quantity'] + $quantity;
    
    if ($new_quantity > $available_stock) {
        echo json_encode(['success' => false, 'message' => 'Cannot add more items. Stock limit reached']);
        exit;
    }
    
    $update_stmt = $conn->prepare("UPDATE cart SET Quantity = ? WHERE Cart_ID = ?");
    $update_stmt->bind_param("ii", $new_quantity, $cart_item['Cart_ID']);
    
    if ($update_stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Cart updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update cart']);
    }
} else {
    $insert_stmt = $conn->prepare("INSERT INTO cart (User_ID, Product_ID, Quantity) VALUES (?, ?, ?)");
    $insert_stmt->bind_param("iii", $user_id, $product_id, $quantity);
    
    if ($insert_stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Item added to cart successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add item to cart']);
    }
}

$conn->close();
?>
