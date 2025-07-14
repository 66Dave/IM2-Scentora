<?php
session_start();
header('Content-Type: application/json');

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
$shipping_address = $_POST['shipping_address'] ?? '';
$payment_method = $_POST['payment_method'] ?? 'Cash on Delivery';

if (empty($shipping_address)) {
    echo json_encode(['success' => false, 'message' => 'Shipping address is required']);
    exit;
}

$conn->begin_transaction();

try {
    $cart_sql = "SELECT 
        c.Product_ID,
        c.Quantity,
        p.Product_Name,
        p.Product_Price,
        p.Stock_Status,
        (c.Quantity * p.Product_Price) as Subtotal
    FROM cart c
    JOIN product p ON c.Product_ID = p.Product_ID
    WHERE c.User_ID = ? AND p.Is_Active = 1";
    
    $cart_stmt = $conn->prepare($cart_sql);
    $cart_stmt->bind_param("i", $user_id);
    $cart_stmt->execute();
    $cart_result = $cart_stmt->get_result();
    
    if ($cart_result->num_rows === 0) {
        throw new Exception('Cart is empty');
    }
    
    $cart_items = [];
    $total_amount = 0;
    
    while ($row = $cart_result->fetch_assoc()) {
        preg_match('/\((\d+)/', $row['Stock_Status'], $matches);
        $available_stock = isset($matches[1]) ? (int)$matches[1] : 0;
        
        if ($available_stock < $row['Quantity']) {
            throw new Exception("Insufficient stock for {$row['Product_Name']}");
        }
        
        $cart_items[] = $row;
        $total_amount += $row['Subtotal'];
    }
    
    $order_sql = "INSERT INTO `order` (User_ID, Total_Amount, Order_Status, Shipping_Address, Payment_Method) VALUES (?, ?, 'Pending', ?, ?)";
    $order_stmt = $conn->prepare($order_sql);
    $order_stmt->bind_param("idss", $user_id, $total_amount, $shipping_address, $payment_method);
    $order_stmt->execute();
    
    $order_id = $conn->insert_id;
    
    foreach ($cart_items as $item) {
        $order_item_sql = "INSERT INTO order_items (Order_ID, Product_ID, Product_Name, Product_Price, Quantity, Subtotal) VALUES (?, ?, ?, ?, ?, ?)";
        $order_item_stmt = $conn->prepare($order_item_sql);
        $order_item_stmt->bind_param("iisdid", $order_id, $item['Product_ID'], $item['Product_Name'], $item['Product_Price'], $item['Quantity'], $item['Subtotal']);
        $order_item_stmt->execute();
        
        preg_match('/\((\d+)/', $item['Stock_Status'], $matches);
        $current_stock = isset($matches[1]) ? (int)$matches[1] : 0;
        $new_stock = $current_stock - $item['Quantity'];
        $new_stock_status = $new_stock > 0 ? "In stock ({$new_stock} pcs)" : "Out of stock";
        
        $update_stock_sql = "UPDATE product SET Stock_Status = ?, Date_Updated = ? WHERE Product_ID = ?";
        $today = date("Y-m-d");
        $update_stock_stmt = $conn->prepare($update_stock_sql);
        $update_stock_stmt->bind_param("ssi", $new_stock_status, $today, $item['Product_ID']);
        $update_stock_stmt->execute();
    }
    
    $clear_cart_sql = "DELETE FROM cart WHERE User_ID = ?";
    $clear_cart_stmt = $conn->prepare($clear_cart_sql);
    $clear_cart_stmt->bind_param("i", $user_id);
    $clear_cart_stmt->execute();
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Order placed successfully',
        'order_id' => $order_id,
        'total_amount' => $total_amount
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>
