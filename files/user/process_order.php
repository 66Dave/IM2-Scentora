<?php
session_start();
header("Content-Type: application/json");

// Debug logs
file_put_contents("form_debug.txt", print_r($_POST, true));
file_put_contents("files_debug.txt", print_r($_FILES, true));

// Database connection
$conn = new mysqli("localhost", "root", "", "scentoradb");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Connection failed"]);
    exit;
}

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo json_encode(["success" => false, "message" => "User not logged in"]);
    exit;
}

// Collect form data
$address = trim($_POST['address'] ?? '');
$paymentMethod = $_POST['payment'] ?? '';
$courier = $_POST['courier'] ?? '';
$totalAmount = floatval($_POST['total_amount'] ?? 0);

// Validate required fields
if (!$address || !$paymentMethod || !$courier || $totalAmount <= 0) {
    echo json_encode(["success" => false, "message" => "Missing or invalid form data"]);
    exit;
}

// Upload proof file
$proofFilename = '';
if (isset($_FILES['proof']) && $_FILES['proof']['error'] === UPLOAD_ERR_OK) {
    $ext = pathinfo($_FILES['proof']['name'], PATHINFO_EXTENSION);
    $proofFilename = 'proof_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
    $targetPath = 'uploads/proofs/' . $proofFilename;

    file_put_contents("path_debug.txt", $targetPath);
    file_put_contents("upload_debug.txt", print_r($_FILES['proof'], true));

    if (!is_writable(dirname($targetPath))) {
        error_log("Folder is not writable");
        echo json_encode(["success" => false, "message" => "Proofs folder not writable"]);
        exit;
    }

    if (!move_uploaded_file($_FILES['proof']['tmp_name'], $targetPath)) {
        echo json_encode(["success" => false, "message" => "Failed to upload proof of payment"]);
        exit;
    }
}

// Fetch cart items
$cartSql = "SELECT c.Product_ID, c.Quantity, p.Product_Price, p.Available_Stocks
            FROM cart c JOIN product p ON c.Product_ID = p.Product_ID
            WHERE c.User_ID = ?";
$cartStmt = $conn->prepare($cartSql);
$cartStmt->bind_param("i", $user_id);
$cartStmt->execute();
$result = $cartStmt->get_result();

$cartItems = [];
while ($row = $result->fetch_assoc()) {
    if ($row['Available_Stocks'] < $row['Quantity']) {
        echo json_encode(["success" => false, "message" => "Insufficient stock for product ID {$row['Product_ID']}"]);
        exit;
    }
    $cartItems[] = $row;
}
$cartStmt->close();

if (empty($cartItems)) {
    echo json_encode(["success" => false, "message" => "Cart is empty"]);
    exit;
}

// Create order
$orderSql = "INSERT INTO `order`
(User_ID, Shipping_Address, Payment_Method, Courier, Total_Amount, Payment_Proof, Status)
VALUES (?, ?, ?, ?, ?, ?, 'Pending')";
$orderStmt = $conn->prepare($orderSql);
$orderStmt->bind_param("isssds", $user_id, $address, $paymentMethod, $courier, $totalAmount, $proofFilename);
$orderStmt->execute();
$orderId = $orderStmt->insert_id;
$orderStmt->close();

// Insert order details and deduct stock
foreach ($cartItems as $item) {
    $product_id = $item['Product_ID'];
    $quantity = (int)$item['Quantity'];
    $price = floatval($item['Product_Price']);
    $subtotal = $price * $quantity;

    $detailStmt = $conn->prepare("INSERT INTO orderdetails
    (Order_ID, Product_ID, Product_Price, Product_Qty, Subtotal)
    VALUES (?, ?, ?, ?, ?)");
    $detailStmt->bind_param("iidid", $orderId, $product_id, $price, $quantity, $subtotal);
    $detailStmt->execute();
    $detailStmt->close();

    // Deduct stock
    $deductStmt = $conn->prepare("UPDATE product
        SET Available_Stocks = Available_Stocks - ?
        WHERE Product_ID = ?");
    $deductStmt->bind_param("ii", $quantity, $product_id);
    $deductStmt->execute();
    $deductStmt->close();
}

// Clear cart
$conn->query("DELETE FROM cart WHERE User_ID = $user_id");

// Return success
echo json_encode(["success" => true, "order_id" => $orderId]);
$conn->close();
?>