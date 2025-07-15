<?php
$conn = new mysqli("localhost", "root", "", "scentoradb");
if ($conn->connect_error) die("Connection failed");

$order_id = intval($_POST['order_id'] ?? 0);
$status = $_POST['status'] ?? '';

if ($order_id < 1 || !in_array($status, ['Accepted', 'Declined'])) {
    echo "Invalid request";
    exit;
}

$conn->begin_transaction();

try {
    $sql = "UPDATE `order` SET Status = ? WHERE Order_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $order_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to update order status");
    }
    
    if ($status === 'Accepted') {
        $items_sql = "SELECT Product_ID, Product_Qty FROM orderdetails WHERE Order_ID = ?";
        $items_stmt = $conn->prepare($items_sql);
        $items_stmt->bind_param("i", $order_id);
        $items_stmt->execute();
        $items_result = $items_stmt->get_result();
        
        while ($item = $items_result->fetch_assoc()) {
            $product_id = $item['Product_ID'];
            $ordered_qty = $item['Product_Qty'];
            
            $stock_sql = "SELECT Stock_Status FROM product WHERE Product_ID = ?";
            $stock_stmt = $conn->prepare($stock_sql);
            $stock_stmt->bind_param("i", $product_id);
            $stock_stmt->execute();
            $stock_result = $stock_stmt->get_result();
            
            if ($stock_row = $stock_result->fetch_assoc()) {
                $current_stock_status = $stock_row['Stock_Status'];
                
                preg_match('/\((\d+)/', $current_stock_status, $matches);
                $current_stock = isset($matches[1]) ? (int)$matches[1] : 0;
                
                $new_stock = max(0, $current_stock - $ordered_qty);
                
                $new_stock_status = $new_stock > 0 ? "In stock ($new_stock pcs)" : "Out of stock";
                
                $update_sql = "UPDATE product SET Stock_Status = ? WHERE Product_ID = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("si", $new_stock_status, $product_id);
                
                if (!$update_stmt->execute()) {
                    throw new Exception("Failed to update stock for product ID: $product_id");
                }
                
                $update_stmt->close();
            }
            
            $stock_stmt->close();
        }
        
        $items_stmt->close();
    }
    
    $conn->commit();
    echo "updated";
    
} catch (Exception $e) {
    $conn->rollback();
    echo "error: " . $e->getMessage();
}

$stmt->close();
$conn->close();
?>
