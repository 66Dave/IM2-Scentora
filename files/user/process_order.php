<?php
<?php
session_start();
header('Content-Type: application/json');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

$host = "localhost";
$username = "root";
$password = "";
$database = "scentoradb";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed']));
}

try {
    $conn->begin_transaction();

    // Get form data
    $user_id = $_SESSION['user_id'];
    $fullname = $_POST['fullname'];
    $address = $_POST['address'];
    $payment_method = $_POST['payment'];
    $courier = $_POST['courier'];
    
    // Handle file upload
    $proof_file = $_FILES['proof'];
    $upload_dir = 'uploads/proofs/';
    $file_ext = strtolower(pathinfo($proof_file['name'], PATHINFO_EXTENSION));
    $file_name = uniqid() . '.' . $file_ext;
    $file_path = $upload_dir . $file_name;
    
    if (!move_uploaded_file($proof_file['tmp_name'], $file_path)) {
        throw new Exception('Failed to upload proof of payment');
    }

    // Create order
    $sql = "INSERT INTO `order` (User_ID, Order_Date, Amount_Paid, Shipping_Address, Payment_Method, Payment_Proof, Status) 
            VALUES (?, CURDATE(), ?, ?, ?, ?, 'Pending')";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("idsss", $user_id, $total_amount, $address, $payment_method, $file_path);
    $stmt->execute();
    
    $order_id = $conn->insert_id;

    // Send email notification
    $mail = new PHPMailer(true);
    
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'your-email@gmail.com'; // Your Gmail
    $mail->Password = 'your-app-password'; // Your Gmail app password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('your-email@gmail.com', 'Scentora');
    $mail->addAddress('davelagunda@gmail.com');
    
    $mail->isHTML(true);
    $mail->Subject = 'New Order Needs Verification - Order #' . $order_id;
    $mail->Body = "
        <h2>New Order Requires Verification</h2>
        <p>Order #$order_id has been placed and needs verification.</p>
        <p><strong>Customer:</strong> $fullname</p>
        <p><strong>Amount:</strong> ₱" . number_format($total_amount, 2) . "</p>
        <p><a href='http://localhost/IM2-Scentora/files/admin/dashboard.html?order=$order_id' 
              style='padding:10px 20px; background:#917489; color:#fff; text-decoration:none; border-radius:5px;'>
           View Order in Dashboard
        </a></p>
    ";

    $mail->send();
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Order placed successfully',
        'order_id' => $order_id
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>