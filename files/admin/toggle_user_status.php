<?php
header('Content-Type: text/plain');
error_reporting(0);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Invalid request method');
}

try {
    $conn = new mysqli("localhost", "root", "", "scentoradb");
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    $userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
    $status = isset($_POST['status']) ? (int)$_POST['status'] : 0;

    // Don't allow modifying admin accounts
    $stmt = $conn->prepare("UPDATE user SET is_active = ? WHERE User_ID = ? AND User_Type != 'Admin'");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("ii", $status, $userId);
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    if ($stmt->affected_rows > 0) {
        echo "success";
    } else {
        echo "no changes";
    }

} catch (Exception $e) {
    http_response_code(500);
    echo "error: " . $e->getMessage();
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}
?>