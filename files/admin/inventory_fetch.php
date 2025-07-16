<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "scentoradb";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
}

$query = "SELECT 
    Product_ID as id,
    Product_Name as name,
    Product_Price as price,
    Product_Code as code,
    Category as category,
    Image_URL as img,
    Date_Added as added,
    Date_Updated as updated,
    Is_Active as active,
    Brand as brand,
    Description as description,
    Available_Stocks as stock
    FROM product 
    ORDER BY Product_Name";

$result = $conn->query($query);

if (!$result) {
    die(json_encode(['error' => 'Query failed: ' . $conn->error]));
}

$products = [];
while ($row = $result->fetch_assoc()) {
    // Convert numeric values
    $row['price'] = floatval($row['price']);
    $row['stock'] = intval($row['stock']);
    $row['active'] = $row['active'] == 1;
    
    // Ensure dates are formatted
    $row['added'] = date('Y-m-d', strtotime($row['added']));
    $row['updated'] = date('Y-m-d', strtotime($row['updated']));
    
    $products[] = $row;
}

header('Content-Type: application/json');
echo json_encode($products);

$conn->close();
?>
