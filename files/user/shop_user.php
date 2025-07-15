<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "scentoradb";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

$sql = "SELECT * FROM product WHERE Is_Active = 1";
$result = $conn->query($sql);

if (!$result) {
    die("Query failed: " . $conn->error);
}

$products = [];
while ($row = $result->fetch_assoc()) {
    $imagePath = $row['Image_URL'];
    // Simplify image path handling
    if (!empty($imagePath)) {
        $imagePath = '../admin/' . $imagePath;
    } else {
        $imagePath = '../images/placeholder.jpg';
    }

    $products[] = [
        'Product_ID' => $row['Product_ID'],
        'Product_Name' => $row['Product_Name'],
        'Product_Price' => number_format((float)$row['Product_Price'], 2),
        'Stock_Status' => $row['Stock_Status'],
        'Category' => $row['Category'],
        'Image_URL' => $imagePath,
        'Product_Code' => $row['Product_Code'],
        'Brand' => $row['Brand'] ?? 'No Brand Specified',
        'Description' => $row['Description'] ?? 'No Description Available'
    ];
}

// Debug output
if (empty($products)) {
    error_log("No products found in database");
}

header('Content-Type: application/json');
echo json_encode($products);
$conn->close();
?>
