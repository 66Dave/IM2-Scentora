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
