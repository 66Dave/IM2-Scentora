<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

require '../admin/phpmailer/src/Exception.php';
require '../admin/phpmailer/src/PHPMailer.php';
require '../admin/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$response = ['success' => false, 'message' => '', 'debug' => ''];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $mail = new PHPMailer(true);
    
    // Enable debugging
    $mail->SMTPDebug = 3; // More verbose debug output
    ob_start(); // Start output buffering
    
    // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'sscentora@gmail.com';
    $mail->Password = 'mvcq uvsu otkq iegu';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Recipients
    $mail->setFrom('sscentora@gmail.com', 'Scentora');
    $mail->addAddress('davelagunda@gmail.com', 'Dave');

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'New Order Notification';
    $mail->Body    = 'A new order has been placed on Scentora.';
    
    $mail->send();
    $debugOutput = ob_get_clean(); // Get debug output
    
    $response['success'] = true;
    $response['message'] = "Order notification sent successfully!";
    $response['debug'] = $debugOutput;

} catch (Exception $e) {
    $debugOutput = ob_get_clean(); // Get debug output
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    $response['debug'] = $debugOutput;
    error_log("Order email error: " . $e->getMessage() . "\nDebug: " . $debugOutput);
}

echo json_encode($response);