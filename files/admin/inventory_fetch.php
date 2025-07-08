<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "scentoradb";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT Product_ID, Product_Name, Product_Code, Category, Product_Price, Stock_Status, Image_URL, Date_Added, Date_Updated, Is_Active
        FROM product ORDER BY Product_ID DESC";

$result = $conn->query($sql);
$products = [];

while ($row = $result->fetch_assoc()) {
    // Try to extract stock number from status
    preg_match('/\((\d+)/', $row['Stock_Status'], $matches);
    $stock = isset($matches[1]) ? (int)$matches[1] : 0;
    $products[] = [
        "id" => $row['Product_ID'],
        "name" => $row['Product_Name'],
        "code" => $row['Product_Code'],
        "price" => $row['Product_Price'],
        "stock" => $stock,
        "img" => $row['Image_URL'],
        "category" => $row['Category'],
        "added" => $row['Date_Added'],
        "updated" => $row['Date_Updated'],
        "active" => (bool)$row['Is_Active'], // Default to active
    ];
}

header("Content-Type: application/json");
echo json_encode($products);

$conn->close();
?>