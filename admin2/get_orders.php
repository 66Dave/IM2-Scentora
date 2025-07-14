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

$orders_sql = "SELECT 
    o.Order_ID,
    o.Total_Amount,
    o.Order_Status,
    o.Shipping_Address,
    o.Payment_Method,
    o.Order_Date
FROM `order` o
WHERE o.User_ID = ?
ORDER BY o.Order_Date DESC";

$orders_stmt = $conn->prepare($orders_sql);
$orders_stmt->bind_param("i", $user_id);
$orders_stmt->execute();
$orders_result = $orders_stmt->get_result();

$orders = [];

while ($order = $orders_result->fetch_assoc()) {
    $items_sql = "SELECT 
        Product_ID,
        Product_Name,
        Product_Price,
        Quantity,
        Subtotal
    FROM order_items
    WHERE Order_ID = ?";
    
    $items_stmt = $conn->prepare($items_sql);
    $items_stmt->bind_param("i", $order['Order_ID']);
    $items_stmt->execute();
    $items_result = $items_stmt->get_result();
    
    $order_items = [];
    while ($item = $items_result->fetch_assoc()) {
        $order_items[] = $item;
    }
    
    $order['items'] = $order_items;
    $orders[] = $order;
}

echo json_encode([
    'success' => true,
    'orders' => $orders
]);

$conn->close();
?>
