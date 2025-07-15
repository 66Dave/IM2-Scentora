<?php
session_start();
header('Content-Type: application/json');

// Error logging function
function logError($message) {
    error_log(date('Y-m-d H:i:s') . " - Remove Cart Error: " . $message . "\n", 3, "../error.log");
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Please login to continue'
    ]);
    exit;
}

try {
    // Database connection
    $conn = new mysqli("localhost", "root", "", "scentoradb");
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Validate cart_id
    if (!isset($_POST['cart_id'])) {
        throw new Exception("Cart ID is required");
    }

    $cart_id = intval($_POST['cart_id']);
    $user_id = $_SESSION['user_id'];

    // Delete the cart item
    $sql = "DELETE FROM cart WHERE Cart_ID = ? AND User_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $cart_id, $user_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to remove item: " . $stmt->error);
    }

    if ($stmt->affected_rows === 0) {
        throw new Exception("Item not found or already removed");
    }

    echo json_encode([
        'success' => true,
        'message' => 'Item removed successfully'
    ]);

} catch (Exception $e) {
    logError($e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    // Close statements and connection
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}
?>