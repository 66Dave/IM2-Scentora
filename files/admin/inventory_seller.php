<?php
session_start();
require_once '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = $_POST['name'] ?? '';
    $code = $_POST['code'] ?? '';
    $price = floatval($_POST['price'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $category = $_POST['category'] ?? '';
    $brand = $_POST['brand'] ?? 'Scentora';
    $description = $_POST['description'] ?? '';
    $user_id = 1; // Default admin user
    $is_active = 1;

    // Handle image upload
    $image_url = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $filename = uniqid() . '_' . basename($_FILES['image']['name']);
        $target_path = $upload_dir . $filename;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
            $image_url = $target_path;
        }
    }

    try {
        // Insert into database
        $sql = "INSERT INTO product (
            User_ID, Product_Name, Product_Price, Product_Code, 
            Category, Image_URL, Date_Added, Date_Updated, 
            Brand, Description, Is_Active, Available_Stocks
        ) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW(), ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "isdsssssiii",
            $user_id, $product_name, $price, $code,
            $category, $image_url, $brand, $description,
            $is_active, $stock
        );

        if ($stmt->execute()) {
            echo "success";
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}

$conn->close();
?>
