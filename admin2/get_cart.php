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

$sql = "SELECT 
    c.Cart_ID,
    c.Product_ID,
    c.Quantity,
    p.Product_Name,
    p.Product_Price,
    p.Image_URL,
    p.Stock_Status,
    p.Category,
    (c.Quantity * p.Product_Price) as Subtotal
FROM cart c
JOIN product p ON c.Product_ID = p.Product_ID
WHERE c.User_ID = ? AND p.Is_Active = 1
ORDER BY c.Date_Added DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$cart_items = [];
$total_amount = 0;

while ($row = $result->fetch_assoc()) {
    preg_match('/\((\d+)/', $row['Stock_Status'], $matches);
    $available_stock = isset($matches[1]) ? (int)$matches[1] : 0;
    
    $cart_items[] = [
        'cart_id' => $row['Cart_ID'],
        'product_id' => $row['Product_ID'],
        'name' => $row['Product_Name'],
        'price' => $row['Product_Price'],
        'quantity' => $row['Quantity'],
        'subtotal' => $row['Subtotal'],
        'img' => !empty($row['Image_URL']) ? $row['Image_URL'] : 'uploads/placeholder.jpg',
        'category' => $row['Category'],
        'available_stock' => $available_stock,
        'stock_status' => $row['Stock_Status']
    ];
    
    $total_amount += $row['Subtotal'];
}

header("Content-Type: application/json");
echo json_encode([
    'success' => true,
    'cart_items' => $cart_items,
    'total_amount' => $total_amount,
    'item_count' => count($cart_items)
]);

$conn->close();
?>
