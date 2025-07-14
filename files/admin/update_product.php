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
$brand = $_POST['brand']; // Add this line
$stock = intval($_POST['stock']);
$image = '';
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = 'uploads/';
    $filename = basename($_FILES['image']['name']);
    $targetPath = $uploadDir . $filename;
    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
        $image = $targetPath;
    }
}

if (empty($image)) {
    $image = $_POST['currentImage'] ?? '';
}
$price = floatval($_POST['price'] ?? 0);
$updated = $_POST['updated'];
$stock_status = $stock > 0 ? "In stock ($stock pcs)" : "Out of stock";

$sql = "UPDATE product
        SET Product_Name=?, Product_Code=?, Category=?, Brand=?, Image_URL=?, Stock_Status=?, Product_Price=?, Date_Updated=?
        WHERE Product_ID=?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssssssi", $name, $code, $category, $brand, $image, $stock_status, $price, $updated, $id);

if ($stmt->execute()) {
    echo "success";
} else {
    echo "error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>