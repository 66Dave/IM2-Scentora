<?php
session_start();
header('Content-Type: application/json');
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

try {
    // Check if product exists and has enough stock
    $check_product = $conn->prepare("SELECT Available_Stocks FROM product WHERE Product_ID = ? AND Is_Active = 1");
    $check_product->bind_param("i", $product_id);
    $check_product->execute();
    $result = $check_product->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Product not found");
    }
    
    $product = $result->fetch_assoc();
    if ($product['Available_Stocks'] < $quantity) {
        throw new Exception("Not enough stock available");
    }

    // Check if item already in cart
    $check_cart = $conn->prepare("SELECT Cart_ID, Quantity FROM cart WHERE User_ID = ? AND Product_ID = ?");
    $check_cart->bind_param("ii", $user_id, $product_id);
    $check_cart->execute();
    $cart_result = $check_cart->get_result();

    if ($cart_result->num_rows > 0) {
        // Update existing cart item
        $cart_item = $cart_result->fetch_assoc();
        $new_quantity = $cart_item['Quantity'] + $quantity;
        
        if ($new_quantity > $product['Available_Stocks']) {
            throw new Exception("Cannot add more items than available stock");
        }

        $update = $conn->prepare("UPDATE cart SET Quantity = ? WHERE Cart_ID = ?");
        $update->bind_param("ii", $new_quantity, $cart_item['Cart_ID']);
        $update->execute();
    } else {
        // Add new cart item
        $insert = $conn->prepare("INSERT INTO cart (User_ID, Product_ID, Quantity, Date_Added) VALUES (?, ?, ?, NOW())");
        $insert->bind_param("iii", $user_id, $product_id, $quantity);
        $insert->execute();
    }

    echo json_encode([
        'success' => true,
        'message' => 'Item added to cart successfully'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>