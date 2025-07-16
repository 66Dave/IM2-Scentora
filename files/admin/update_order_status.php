<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
    $status = isset($_POST['status']) ? $_POST['status'] : '';
    
    // Validate inputs
    if ($order_id <= 0 || empty($status)) {
        echo "Invalid order ID or status";
        exit;
    }

    // Validate status values
    $valid_statuses = ['Pending', 'Accepted', 'Declined', 'Cancelled', 'Completed'];
    if (!in_array($status, $valid_statuses)) {
        echo "Invalid status value";
        exit;
    }

    try {
        // Check current status
        $stmt = $conn->prepare("SELECT Status FROM `order` WHERE Order_ID = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $stmt->bind_result($current_status);
        $stmt->fetch();
        $stmt->close();

        // If changing to Declined and wasn't already Declined, restore stock
        if (strtolower($status) === 'declined' && strtolower($current_status) !== 'declined') {
            // Get all products and quantities in this order
            $details = $conn->query("SELECT Product_ID, Product_Qty FROM orderdetails WHERE Order_ID = $order_id");
            while ($row = $details->fetch_assoc()) {
                $conn->query("UPDATE product SET Available_Stocks = Available_Stocks + {$row['Product_Qty']} WHERE Product_ID = {$row['Product_ID']}");
            }
        }

        // If changing from Declined to something else, deduct stock again
        if (strtolower($current_status) === 'declined' && strtolower($status) !== 'declined') {
            $details = $conn->query("SELECT Product_ID, Product_Qty FROM orderdetails WHERE Order_ID = $order_id");
            while ($row = $details->fetch_assoc()) {
                $conn->query("UPDATE product SET Available_Stocks = Available_Stocks - {$row['Product_Qty']} WHERE Product_ID = {$row['Product_ID']}");
            }
        }

        // Update order status
        $stmt = $conn->prepare("UPDATE `order` SET Status = ? WHERE Order_ID = ?");
        $stmt->bind_param("si", $status, $order_id);
        
        if ($stmt->execute()) {
            echo "updated";
        } else {
            echo "Failed to update order status: " . $stmt->error;
        }
        
        $stmt->close();
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Invalid request";
}

$conn->close();
?>
