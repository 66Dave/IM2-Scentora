<?php
session_start();
require_once '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $product_name = $_POST['name'];
    $code = $_POST['code'];
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $category = $_POST['category'];
    $brand = $_POST['brand'];
    $description = $_POST['description'];
    $current_image = $_POST['currentImage'];

    // Handle new image upload
    $image_url = $current_image;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $filename = uniqid() . '_' . basename($_FILES['image']['name']);
        $target_path = $upload_dir . $filename;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
            $image_url = $target_path;
            // Delete old image if it exists
            if ($current_image && file_exists($current_image)) {
                unlink($current_image);
            }
        }
    }

    try {
        $sql = "UPDATE product SET 
            Product_Name = ?, 
            Product_Price = ?, 
            Product_Code = ?, 
            Category = ?, 
            Image_URL = ?, 
            Date_Updated = NOW(), 
            Brand = ?, 
            Description = ?, 
            Available_Stocks = ? 
            WHERE Product_ID = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "sdsssssii",
            $product_name, $price, $code, $category, 
            $image_url, $brand, $description, $stock, $id
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
