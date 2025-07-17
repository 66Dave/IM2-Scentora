<?php
header("Content-Type: application/json");
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "localhost";
$username = "root";
$password = "";
$database = "scentoradb";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed"]));
}

$sql = "SELECT 
    Product_ID, Product_Name, Product_Price, Available_Stocks, Stock_Level,
    Category, Image_URL, Product_Code, Brand, Description
    FROM product
    WHERE Is_Active = 1 AND Available_Stocks > 0
    ORDER BY Product_ID DESC";

$result = $conn->query($sql);
if (!$result) {
    echo json_encode(["error" => "Query failed"]);
    exit;
}

$products = [];
while ($row = $result->fetch_assoc()) {
    $imagePath = !empty($row['Image_URL']) ? '../admin/' . $row['Image_URL'] : '../images/placeholder.jpg';

    $products[] = [
        'Product_ID' => $row['Product_ID'],
        'Product_Name' => $row['Product_Name'],
        'Product_Price' => (float)$row['Product_Price'],
        'Available_Stocks' => (int)$row['Available_Stocks'],
        'Stock_Level' => (int)$row['Stock_Level'],
        'Category' => $row['Category'],
        'Image_URL' => $imagePath,
        'Product_Code' => $row['Product_Code'],
        'Brand' => $row['Brand'] ?? 'Scentora',
        'Description' => $row['Description'] ?? 'No description available'
    ];
}

if (empty($products)) {
    error_log("No active products with stock found");
}

echo json_encode($products);
$conn->close();
?>