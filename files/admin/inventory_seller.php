<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "scentoradb";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$product_name = $_POST['name'] ?? '';
$code = $_POST['code'] ?? '';
$category = $_POST['category'] ?? '';
$stock = intval($_POST['stock'] ?? 0);
$image = $_POST['image'] ?? '';
$added = $_POST['added'] ?? date("Y-m-d");
$updated = $_POST['updated'] ?? date("Y-m-d");
$user_id = 1;

$stock_status = $stock > 0 ? "In stock ($stock pcs)" : "Out of stock";

$sql = "INSERT INTO product (User_ID, Product_Name, Product_Price, Stock_Status, Product_Code, Category, Image_URL, Date_Added, Date_Updated)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$price = floatval($_POST['price'] ?? 0); // default price
$stmt->bind_param("isdssssss", $user_id, $product_name, $price, $stock_status, $code, $category, $image, $added, $updated);

if ($stmt->execute()) {
    echo "success";
} else {
    http_response_code(500);
    echo "error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>