<?php
// Add these at the top of the file
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$response = ['success' => false, 'message' => ''];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['reset_email'])) {
            throw new Exception('Email is required');
        }

        $email = filter_var($_POST['reset_email'], FILTER_SANITIZE_EMAIL);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format');
        }

        $conn = new mysqli("localhost", "root", "", "scentoradb");
        if ($conn->connect_error) {
            throw new Exception("Database connection failed: " . $conn->connect_error);
        }

        // Check if email exists
        $stmt = $conn->prepare("SELECT User_ID FROM user WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            throw new Exception("No account found with this email address");
        }

        // Generate and store reset token
        $token = bin2hex(random_bytes(32));
        // Set expiry to 24 hours instead of 1 hour
        $expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        // Debug logging
        error_log("Generated token: " . $token);
        error_log("Expiry time set to: " . $expiry);

        $stmt = $conn->prepare("UPDATE user SET reset_token = ?, reset_expiry = ? WHERE Email = ?");
        $stmt->bind_param("sss", $token, $expiry, $email);
        if (!$stmt->execute()) {
            throw new Exception("Failed to update reset token");
        }

        // Send email
        $mail = new PHPMailer(true);

        // Server settings
        $mail->SMTPDebug = 2; // Enable debug output
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'sscentora@gmail.com';
        $mail->Password = 'mvcq uvsu otkq iegu';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('sscentora@gmail.com', 'Scentora');
        $mail->addAddress($email);

        // Content
        $reset_link = "http://localhost/IM2-Scentora/files/admin/change_password.php?token=" . $token;
        
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Request - Scentora';
        $mail->Body = "
            <h2>Password Reset Request</h2>
            <p>Click the link below to reset your password:</p>
            <p><a href='{$reset_link}'>{$reset_link}</a></p>
            <p>This link will expire in 1 hour.</p>
            <p>If you didn't request this, please ignore this email.</p>
        ";

        if (!$mail->send()) {
            throw new Exception("Email could not be sent. Mailer Error: " . $mail->ErrorInfo);
        }

        $response['success'] = true;
        $response['message'] = "Reset link has been sent to your email";
    }
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    error_log("Password reset error: " . $e->getMessage());
}

echo json_encode($response);
?>