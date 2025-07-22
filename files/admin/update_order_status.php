<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../includes/db_connect.php';

// Include PHPMailer for sending emails
require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
            // If status is changed to "Accepted", send email notification
            if (strtolower($status) === 'accepted') {
                sendOrderAcceptedEmail($conn, $order_id);
            }
            // If status is changed to "Declined", send decline notification
            elseif (strtolower($status) === 'declined') {
                sendOrderDeclinedEmail($conn, $order_id);
            }
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

function sendOrderAcceptedEmail($conn, $order_id) {
    try {
        // Fetch order and user details
        $sql = "SELECT o.*, u.Name, u.Email, u.Address 
                FROM `order` o 
                JOIN user u ON o.User_ID = u.User_ID 
                WHERE o.Order_ID = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $order = $result->fetch_assoc();

        if (!$order) {
            error_log("Order not found for email notification: " . $order_id);
            return false;
        }

        // Get order items
        $itemsSql = "SELECT p.Product_Name, od.Product_Qty, od.Product_Price, od.Subtotal 
                     FROM orderdetails od 
                     JOIN product p ON od.Product_ID = p.Product_ID 
                     WHERE od.Order_ID = ?";    
        $stmt = $conn->prepare($itemsSql);
        $stmt->bind_param("i", $order_id);
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
        $mail->Subject = "Order Accepted - Scentora Order #" . $order_id;

        // Create email body for order acceptance
        $body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #ddd; border-radius: 10px; overflow: hidden;'>
            <div style='background: linear-gradient(135deg, #a182c9, #917489); color: white; padding: 25px; text-align: center;'>
                <h1 style='margin: 0; font-size: 28px;'>🎉 Order Accepted!</h1>
                <p style='margin: 10px 0 0 0; font-size: 16px; opacity: 0.9;'>Order #$order_id</p>
            </div>
            <div style='padding: 30px; background: #fff;'>
                <div style='background: #f8f9ff; padding: 20px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #a182c9;'>
                    <h2 style='color: #a182c9; margin: 0 0 10px 0;'>Great News!</h2>
                    <p style='margin: 0; color: #666; line-height: 1.6;'>Your order has been accepted and is now being processed. We'll prepare your items and arrange delivery soon.</p>
                </div>
                
                <div style='margin-bottom: 25px;'>
                    <h3 style='color: #333; margin: 0 0 15px 0; font-size: 18px;'>Order Details</h3>
                    <p style='margin: 5px 0; color: #666;'><strong>Order Date:</strong> " . date('F j, Y', strtotime($order['Order_Date'])) . "</p>
                    <p style='margin: 5px 0; color: #666;'><strong>Customer:</strong> {$order['Name']}</p>
                    <p style='margin: 5px 0; color: #666;'><strong>Delivery Address:</strong> {$order['Address']}</p>
                    <p style='margin: 5px 0; color: #666;'><strong>Payment Method:</strong> {$order['Payment_Method']}</p>
                    <p style='margin: 5px 0; color: #666;'><strong>Courier:</strong> {$order['Courier']}</p>
                </div>
                
                <div style='background: #fff; border: 1px solid #eee; border-radius: 8px; overflow: hidden;'>
                    <table style='width: 100%; border-collapse: collapse;'>
                        <thead>
                            <tr style='background: #a182c9; color: white;'>
                                <th style='padding: 15px 10px; text-align: left; font-weight: 600;'>Item</th>
                                <th style='padding: 15px 10px; text-align: center; font-weight: 600;'>Qty</th>
                                <th style='padding: 15px 10px; text-align: right; font-weight: 600;'>Price</th>
                                <th style='padding: 15px 10px; text-align: right; font-weight: 600;'>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>";

        foreach ($items as $index => $item) {
            $bgColor = $index % 2 == 0 ? '#f9f9f9' : '#fff';
            $body .= "<tr style='background: $bgColor;'>
                        <td style='padding: 12px 10px; border-bottom: 1px solid #eee; color: #333;'>{$item['Product_Name']}</td>
                        <td style='padding: 12px 10px; border-bottom: 1px solid #eee; text-align: center; color: #666;'>{$item['Product_Qty']}</td>
                        <td style='padding: 12px 10px; border-bottom: 1px solid #eee; text-align: right; color: #666;'>₱" . number_format($item['Product_Price'], 2) . "</td>
                        <td style='padding: 12px 10px; border-bottom: 1px solid #eee; text-align: right; color: #333; font-weight: 600;'>₱" . number_format($item['Subtotal'], 2) . "</td>
                    </tr>";
        }

        $body .= "</tbody>
                    </table>
                </div>
                
                <div style='text-align: right; margin-top: 20px; padding: 15px; background: #f0f8ff; border-radius: 8px;'>
                    <p style='margin: 0; font-size: 20px; font-weight: bold; color: #a182c9;'>Total Amount: ₱" . number_format($order['Total_Amount'], 2) . "</p>
                    <p style='margin: 5px 0 0 0; font-size: 14px; color: #666;'>*Delivery fee will be handled separately by the courier</p>
                </div>
                
                <div style='margin-top: 30px; padding: 20px; background: #f8f9ff; border-radius: 8px; border: 1px solid #e5d6f7;'>
                    <h3 style='color: #a182c9; margin: 0 0 15px 0;'>What's Next?</h3>
                    <ul style='margin: 0; padding-left: 20px; color: #666; line-height: 1.8;'>
                        <li>Our team is preparing your order</li>
                        <li>Your chosen courier will contact you about delivery arrangements</li>
                        <li>You'll receive tracking information once shipped</li>
                        <li>You can track your order status in your account</li>
                    </ul>
                </div>
                
                <div style='text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;'>
                    <p style='color: #666; margin: 10px 0; font-size: 16px;'>Thank you for choosing Scentora! 🌸</p>
                    <p style='color: #999; margin: 0; font-size: 14px;'>
                        Questions? Contact us at sscentora@gmail.com
                    </p>
                </div>
            </div>
        </div>";

        $mail->Body = $body;

        if ($mail->send()) {
            error_log("Order accepted email sent successfully for order: " . $order_id);
            return true;
        } else {
            error_log("Failed to send order accepted email for order: " . $order_id);
            return false;
        }

    } catch (Exception $e) {
        error_log("Email sending error for order " . $order_id . ": " . $e->getMessage());
        return false;
    }
}

function sendOrderDeclinedEmail($conn, $order_id) {
    try {
        // Fetch order and user details
        $sql = "SELECT o.*, u.Name, u.Email, u.Address 
                FROM `order` o 
                JOIN user u ON o.User_ID = u.User_ID 
                WHERE o.Order_ID = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $order = $result->fetch_assoc();

        if (!$order) {
            error_log("Order not found for decline email notification: " . $order_id);
            return false;
        }

        // Get order items
        $itemsSql = "SELECT p.Product_Name, od.Product_Qty, od.Product_Price, od.Subtotal 
                     FROM orderdetails od 
                     JOIN product p ON od.Product_ID = p.Product_ID 
                     WHERE od.Order_ID = ?";    
        $stmt = $conn->prepare($itemsSql);
        $stmt->bind_param("i", $order_id);
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
        $mail->Subject = "Order Update - Scentora Order #" . $order_id;

        // Create email body for order decline
        $body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #ddd; border-radius: 10px; overflow: hidden;'>
            <div style='background: linear-gradient(135deg, #e57373, #f06292); color: white; padding: 25px; text-align: center;'>
                <h1 style='margin: 0; font-size: 28px;'>⚠️ Order Update Required</h1>
                <p style='margin: 10px 0 0 0; font-size: 16px; opacity: 0.9;'>Order #$order_id</p>
            </div>
            <div style='padding: 30px; background: #fff;'>
                <div style='background: #ffebee; padding: 20px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #e57373;'>
                    <h2 style='color: #d32f2f; margin: 0 0 10px 0;'>Payment Verification Issue</h2>
                    <p style='margin: 0; color: #666; line-height: 1.6;'>We're sorry, but your order has been declined due to <strong>invalid payment proof</strong>. The payment proof you submitted could not be verified or does not match our records.</p>
                </div>
                
                <div style='margin-bottom: 25px;'>
                    <h3 style='color: #333; margin: 0 0 15px 0; font-size: 18px;'>Order Details</h3>
                    <p style='margin: 5px 0; color: #666;'><strong>Order Date:</strong> " . date('F j, Y', strtotime($order['Order_Date'])) . "</p>
                    <p style='margin: 5px 0; color: #666;'><strong>Customer:</strong> {$order['Name']}</p>
                    <p style='margin: 5px 0; color: #666;'><strong>Total Amount:</strong> ₱" . number_format($order['Total_Amount'], 2) . "</p>
                    <p style='margin: 5px 0; color: #666;'><strong>Payment Method:</strong> {$order['Payment_Method']}</p>
                </div>
                
                <div style='background: #fff; border: 1px solid #eee; border-radius: 8px; overflow: hidden; margin-bottom: 20px;'>
                    <table style='width: 100%; border-collapse: collapse;'>
                        <thead>
                            <tr style='background: #f5f5f5; color: #333;'>
                                <th style='padding: 12px 10px; text-align: left; font-weight: 600; border-bottom: 1px solid #ddd;'>Item</th>
                                <th style='padding: 12px 10px; text-align: center; font-weight: 600; border-bottom: 1px solid #ddd;'>Qty</th>
                                <th style='padding: 12px 10px; text-align: right; font-weight: 600; border-bottom: 1px solid #ddd;'>Price</th>
                            </tr>
                        </thead>
                        <tbody>";

        foreach ($items as $index => $item) {
            $bgColor = $index % 2 == 0 ? '#f9f9f9' : '#fff';
            $body .= "<tr style='background: $bgColor;'>
                        <td style='padding: 10px; border-bottom: 1px solid #eee; color: #333;'>{$item['Product_Name']}</td>
                        <td style='padding: 10px; border-bottom: 1px solid #eee; text-align: center; color: #666;'>{$item['Product_Qty']}</td>
                        <td style='padding: 10px; border-bottom: 1px solid #eee; text-align: right; color: #333;'>₱" . number_format($item['Product_Price'], 2) . "</td>
                    </tr>";
        }

        $body .= "</tbody>
                    </table>
                </div>
                
                <div style='background: #e3f2fd; padding: 20px; border-radius: 8px; border: 1px solid #2196f3; margin-bottom: 20px;'>
                    <h3 style='color: #1976d2; margin: 0 0 15px 0;'>How to Resolve This Issue</h3>
                    <ol style='margin: 0; padding-left: 20px; color: #666; line-height: 1.8;'>
                        <li><strong>Check your payment:</strong> Ensure your payment was completed successfully</li>
                        <li><strong>Verify payment proof:</strong> Make sure the screenshot/receipt shows:
                            <ul style='margin: 10px 0; padding-left: 20px;'>
                                <li>Clear transaction details</li>
                                <li>Correct amount (₱" . number_format($order['Total_Amount'], 2) . ")</li>
                                <li>Transaction timestamp</li>
                                <li>Reference number (if applicable)</li>
                            </ul>
                        </li>
                        <li><strong>Resubmit your order:</strong> Place a new order with valid payment proof</li>
                        <li><strong>Contact us:</strong> If you believe this is an error, contact our support team</li>
                    </ol>
                </div>
                
                <div style='background: #fff3e0; padding: 20px; border-radius: 8px; border-left: 4px solid #ff9800;'>
                    <h3 style='color: #f57c00; margin: 0 0 10px 0;'>Important Notes</h3>
                    <ul style='margin: 0; padding-left: 20px; color: #666; line-height: 1.8;'>
                        <li>Your payment has been restored to your account if it was successfully processed</li>
                        <li>Product stock has been released back to inventory</li>
                        <li>You may place a new order anytime with valid payment proof</li>
                        <li>All payments must match the exact order total</li>
                    </ul>
                </div>
                
                <div style='text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;'>
                    <p style='color: #666; margin: 10px 0; font-size: 16px;'>Questions about this decision? 🤔</p>
                    <p style='color: #999; margin: 0; font-size: 14px;'>
                        Contact us at <a href='mailto:sscentora@gmail.com' style='color: #2196f3;'>sscentora@gmail.com</a><br>
                        We're here to help resolve any payment issues!
                    </p>
                </div>
            </div>
        </div>";

        $mail->Body = $body;

        if ($mail->send()) {
            error_log("Order declined email sent successfully for order: " . $order_id);
            return true;
        } else {
            error_log("Failed to send order declined email for order: " . $order_id);
            return false;
        }

    } catch (Exception $e) {
        error_log("Decline email sending error for order " . $order_id . ": " . $e->getMessage());
        return false;
    }
}
?>
