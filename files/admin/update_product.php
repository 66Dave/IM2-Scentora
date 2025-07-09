<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "scentoradb";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = intval($_POST['id']);
$name = $_POST['name'];
$code = $_POST['code'];
$category = $_POST['category'];
$stock = intval($_POST['stock']);
$image = $_POST['image'];
$price = floatval($_POST['price'] ?? 0);
$updated = $_POST['updated'];
$stock_status = $stock > 0 ? "In stock ($stock pcs)" : "Out of stock";

$sql = "UPDATE product
        SET Product_Name=?, Product_Code=?, Category=?, Image_URL=?, Stock_Status=?, Product_Price=?, Date_Updated=?
        WHERE Product_ID=?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssssi", $name, $code, $category, $image, $stock_status, $price, $updated, $id);

if ($stmt->execute()) {
    echo "success";
} else {
    echo "error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>