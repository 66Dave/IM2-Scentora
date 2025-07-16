<?php
require_once '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    
    if ($id <= 0) {
        die("Invalid product ID");
    }

    try {
        // First get the image URL to delete the file
        $stmt = $conn->prepare("SELECT Image_URL FROM product WHERE Product_ID = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();

        // Delete the product
        $stmt = $conn->prepare("DELETE FROM product WHERE Product_ID = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            // Delete the image file if it exists
            if ($product && $product['Image_URL'] && file_exists($product['Image_URL'])) {
                unlink($product['Image_URL']);
            }
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