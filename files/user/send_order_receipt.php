<?php
// Start with a clean output buffer
ob_start();
session_start();

// Ensure no unwanted output
error_reporting(0);
ini_set('display_errors', 0);

// Set JSON header
header('Content-Type: application/json');

require '../admin/phpmailer/src/Exception.php';
require '../admin/phpmailer/src/PHPMailer.php';
require '../admin/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Initialize response array
$response = ['success' => false, 'message' => ''];

try {
    // Check request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Verify session
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not authenticated');
    }

    // Verify order ID
    if (!isset($_POST['order_id'])) {
        throw new Exception('Order ID is required');
    }

    $orderId = intval($_POST['order_id']);
    $userId = $_SESSION['user_id'];
    
    // Connect to the database
    $conn = new mysqli("localhost", "root", "", "scentoradb");
    if ($conn->connect_error) {
        throw new Exception("Database connection failed");
    }

    // Fetch order and user details
    $sql = "SELECT o.*, u.Name, u.Email, u.Address 
            FROM `order` o 
            JOIN user u ON o.User_ID = u.User_ID 
            WHERE o.Order_ID = ? AND o.User_ID = ? 
            AND (o.Status = 'Accepted' OR o.Status = 'Completed')";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $orderId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();

    if (!$order) {
        throw new Exception("Order not found or not eligible for receipt");
    }

    // Get order items
    $itemsSql = "SELECT p.Product_Name, od.Product_Qty, od.Product_Price, od.Subtotal 
                 FROM orderdetails od 
                 JOIN product p ON od.Product_ID = p.Product_ID 
                 WHERE od.Order_ID = ?";    
    $stmt = $conn->prepare($itemsSql);
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Configure PHPMailer
    $mail = new PHPMailer(true);
    $mail->SMTPDebug = 0;
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'sscentora@gmail.com';
    $mail->Password = 'mvcq uvsu otkq iegu';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('sscentora@gmail.com', 'Scentora');
    $mail->addAddress($order['Email']);
    $mail->isHTML(true);
    $mail->Subject = "Scentora - Order Receipt #" . $orderId;

    // Create email body
    $body = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
        <div style='background: #a182c9; color: white; padding: 20px; text-align: center;'>
            <h2>Order Receipt</h2>
            <p>Order #$orderId</p>
        </div>
        <div style='padding: 20px; background: #fff;'>
            <p><strong>Date:</strong> " . date('Y-m-d', strtotime($order['Order_Date'])) . "</p>
            <p><strong>Customer:</strong> {$order['Name']}</p>
            <p><strong>Shipping Address:</strong> {$order['Address']}</p>
            
            <table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
                <tr style='background: #e5d6f7;'>
                    <th style='padding: 10px; text-align: left;'>Item</th>
                    <th style='padding: 10px; text-align: center;'>Quantity</th>
                    <th style='padding: 10px; text-align: right;'>Price</th>
                    <th style='padding: 10px; text-align: right;'>Subtotal</th>
                </tr>";

    foreach ($items as $item) {
        $body .= "<tr>
                    <td style='padding: 10px; border-bottom: 1px solid #eee;'>{$item['Product_Name']}</td>
                    <td style='padding: 10px; border-bottom: 1px solid #eee; text-align: center;'>{$item['Product_Qty']}</td>
                    <td style='padding: 10px; border-bottom: 1px solid #eee; text-align: right;'>₱" . number_format($item['Product_Price'], 2) . "</td>
                    <td style='padding: 10px; border-bottom: 1px solid #eee; text-align: right;'>₱" . number_format($item['Subtotal'], 2) . "</td>
                </tr>";
    }

    $body .= "</table>
            <p style='text-align: right; font-weight: bold;'>Total Amount: ₱" . number_format($order['Total_Amount'], 2) . "</p>
            <p style='color: #666; font-size: 0.9em; margin-top: 20px;'>Thank you for shopping with Scentora!</p>
        </div>
    </div>";

    $mail->Body = $body;

    if ($mail->send()) {
        $response['success'] = true;
        $response['message'] = "Receipt has been sent to your email";
    } else {
        throw new Exception("Failed to send email");
    }

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    error_log("Send receipt error: " . $e->getMessage());
}

echo json_encode($response);
exit;
?>