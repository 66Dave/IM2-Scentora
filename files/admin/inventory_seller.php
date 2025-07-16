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
            Category, Image_URL, Brand, Description, 
            Is_Active, Available_Stocks
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        
        // Fixed: Corrected number of parameters and type definition string
        $stmt->bind_param(
            "isdsssssii", 
            $user_id,        // i (integer)
            $product_name,   // s (string)
            $price,         // d (double)
            $code,          // s (string)
            $category,      // s (string)
            $image_url,     // s (string)
            $brand,         // s (string)
            $description,   // s (string)
            $is_active,     // i (integer)
            $stock          // i (integer)
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
