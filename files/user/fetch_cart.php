<?php
session_start();
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "localhost";
$username = "root";
$password = "";
$database = "scentoradb";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Connection failed"]);
    exit;
}

$user_id = $_SESSION['user_id'] ?? 1; // fallback for demo

$sql = "SELECT c.Cart_ID, c.Product_ID, c.Quantity,
               p.Product_Name, p.Product_Price, p.Image_URL, p.Brand,
               p.Available_Stocks, p.Description
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
    $row['Stock'] = (int)$row['Available_Stocks'];
    $cart[] = $row;
}

echo json_encode($cart);

$stmt->close();
$conn->close();
?>