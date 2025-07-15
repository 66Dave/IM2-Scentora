<?php
session_start();
$host = "localhost";
$username = "root";
$password = "";
$database = "scentoradb";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    http_response_code(500);
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'] ?? 1; // fallaback for demo

$sql = "SELECT c.Cart_ID, c.Product_ID, c.Quantity, p.Product_Name, p.Product_Price, p.Image_URL, p.Brand, p.Stock_Status
        FROM cart c
        JOIN product p ON c.Product_ID = p.Product_ID
        WHERE c.User_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$cart = [];
while ($row = $result->fetch_assoc()) {
    $row['Image_URL'] = !empty($row['Image_URL']) ? '../admin/' . $row['Image_URL'] : '../images/placeholder.jpg';
    // Extract stock number from Stock_Status (e.g. "In stock (12 pcs)")
    if (preg_match('/(\d+)\s*pcs/', $row['Stock_Status'], $m)) {
        $row['Stock'] = intval($m[1]);
    } else {
        $row['Stock'] = 0;
    }
    $cart[] = $row;
}

header('Content-Type: application/json');
echo json_encode($cart);

$stmt->close();
$conn->close();
?>