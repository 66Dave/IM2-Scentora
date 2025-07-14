<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "scentoradb";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT 
  Product_ID, Product_Name, Product_Price, Stock_Status,
  Category, Image_URL, Is_Active
  FROM product 
  WHERE Is_Active = 1 
  ORDER BY Product_ID DESC";

$result = $conn->query($sql);

$products = [];

while ($row = $result->fetch_assoc()) {
    $products[] = [
        "id" => $row['Product_ID'],
        "name" => $row['Product_Name'],
        "price" => $row['Product_Price'],
        "stock" => $row['Stock_Status'],
        "category" => $row['Category'],
        "img" => !empty($row['Image_URL']) ? $row['Image_URL'] : 'uploads/placeholder.jpg'
    ];
}

header("Content-Type: application/json");
echo json_encode($products);
$conn->close();
?>