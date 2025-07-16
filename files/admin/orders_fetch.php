<?php
require_once '../includes/db_connect.php';

try {
    // Updated query to match your database structure
    $query = "SELECT 
        o.Order_ID as order_id,
        u.Email as buyer_email,
        o.Order_Date as order_date,
        o.Total_Amount as total,
        o.Status as status,
        o.Payment_Proof as payment_proof
        FROM `order` o
        JOIN user u ON o.User_ID = u.User_ID
        ORDER BY o.Order_Date DESC";

    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }

    $orders = [];
    while ($row = $result->fetch_assoc()) {
        // Format the data
        $row['total'] = number_format((float)$row['total'], 2, '.', '');
        $row['order_date'] = date('Y-m-d H:i:s', strtotime($row['order_date']));
        
        // Fix payment proof path
        if ($row['payment_proof']) {
            // Remove any path traversal
            $row['payment_proof'] = basename($row['payment_proof']);
            // Construct proper path for front-end
            $row['payment_proof'] = '../uploads/proofs/' . $row['payment_proof'];
        }

        $orders[] = $row;
    }

    // Debug output
    error_log("Orders fetched: " . json_encode($orders));

    header('Content-Type: application/json');
    echo json_encode($orders);

} catch (Exception $e) {
    error_log("Orders fetch error: " . $e->getMessage());
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>