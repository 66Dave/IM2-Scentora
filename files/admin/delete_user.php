<?php
<?php
header('Content-Type: text/plain');
error_reporting(E_ALL);
ini_set('display_errors', 0);

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

    // Start transaction
    $conn->begin_transaction();

    // Check if user is not admin
    $stmt = $conn->prepare("SELECT User_Type FROM user WHERE User_ID = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user['User_Type'] === 'Admin') {
        throw new Exception('Cannot delete admin user');
    }

    // Delete from related tables
    $tables = ['cart', 'consumerdetails', 'employeedetails', 'order'];
    foreach ($tables as $table) {
        $stmt = $conn->prepare("DELETE FROM $table WHERE User_ID = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
    }

    // Delete the user
    $stmt = $conn->prepare("DELETE FROM user WHERE User_ID = ? AND User_Type != 'Admin'");
    $stmt->bind_param("i", $userId);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to delete user");
    }

    if ($stmt->affected_rows === 0) {
        throw new Exception("User not found or is admin");
    }

    $conn->commit();
    echo "success";

} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
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