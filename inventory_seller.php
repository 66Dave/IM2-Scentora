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
$image = '';
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = 'uploads/';
    $filename = basename($_FILES['image']['name']);
    $targetPath = $uploadDir . $filename;

    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    if (in_array($ext, $allowed)) {
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $image = $targetPath;
        }
    }
}
$added = $_POST['added'] ?? date("Y-m-d");
$updated = $_POST['updated'] ?? date("Y-m-d");
$user_id = 1;

$stock_status = $stock > 0 ? "In stock ($stock pcs)" : "Out of stock";

$brand = $_POST['brand'] ?? '';
$is_active = $stock > 0 ? 1 : 0;

$sql = "INSERT INTO product (
    User_ID, Product_Name, Product_Price, Stock_Status,
    Product_Code, Category, Image_URL, Date_Added, Date_Updated, Brand, Is_Active
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$price = floatval($_POST['price'] ?? 0); // default price
$stmt->bind_param("isdsssssssi", $user_id, $product_name, $price, $stock_status,
                  $code, $category, $image, $added, $updated, $brand, $is_active);

if ($stmt->execute()) {
    echo "success";
} else {
    http_response_code(500);
    echo "error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>