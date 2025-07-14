<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "scentoradb";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all active products (added by admin)
$sql = "SELECT Product_ID, Product_Name, Product_Price, Stock_Status, Category, Image_URL FROM product WHERE Is_Active = 1";
$result = $conn->query($sql);

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}
header("Content-Type: application/json");
echo json_encode($products);
$conn->close();
?>