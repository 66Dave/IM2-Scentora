<?php
// Start output buffering to prevent any accidental output
ob_start();

// Enable error reporting but capture errors
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to prevent HTML output

require_once '../includes/session_config.php';
require_once '../includes/api_middleware.php';

try {
    // Require consumer login for API
    apiRequireConsumer();
    
    // Get user ID from session using the same method as orders_user.php
    $user_id = getCurrentUserId();

    if (!$user_id) {
        throw new Exception('User ID not found in session');
    }
} catch (Exception $e) {
    ob_clean();
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Authentication failed: ' . $e->getMessage()]);
    exit;
}

// Database connection
$host = "localhost";
$username = "root";
$password = "";
$database = "scentoradb";

try {
    $conn = new mysqli($host, $username, $password, $database);
    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }
} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit;
}

try {
    // Get orders that are completed and within review period (7 days after arrival)
    $sql = "SELECT DISTINCT o.Order_ID, o.Order_Date, o.Arrival_Date, o.Status,
            od.Product_ID, p.Product_Name, p.Image_URL, p.Brand, od.Product_Price,
            DATEDIFF(CURDATE(), o.Arrival_Date) as days_since_arrival,
            pr.Review_ID,
            CASE 
                WHEN pr.Review_ID IS NOT NULL THEN 1 
                ELSE 0 
            END as has_reviewed
            FROM `order` o
            INNER JOIN orderdetails od ON o.Order_ID = od.Order_ID
            INNER JOIN product p ON od.Product_ID = p.Product_ID
            LEFT JOIN product_reviews pr ON (o.Order_ID = pr.Order_ID AND od.Product_ID = pr.Product_ID)
            WHERE o.User_ID = ? 
            AND o.Status = 'Completed' 
            AND o.Arrival_Date IS NOT NULL
            AND DATEDIFF(CURDATE(), o.Arrival_Date) <= 7
            ORDER BY o.Arrival_Date DESC, o.Order_ID DESC";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('SQL prepare failed: ' . $conn->error);
    }

    $stmt->bind_param("i", $user_id);
    if (!$stmt->execute()) {
        throw new Exception('SQL execute failed: ' . $stmt->error);
    }

    $result = $stmt->get_result();

    $reviewableItems = [];
    while ($row = $result->fetch_assoc()) {
        // Handle image path same as shop_user.php
        $imagePath = !empty($row['Image_URL']) ? '../admin/' . $row['Image_URL'] : '../images/placeholder.jpg';
        
        $reviewableItems[] = [
            'Order_ID' => $row['Order_ID'],
            'Product_ID' => $row['Product_ID'],
            'Product_Name' => $row['Product_Name'],
            'Brand' => $row['Brand'] ?? 'Scentora',
            'Product_Price' => (float)$row['Product_Price'],
            'Image_URL' => $imagePath,
            'Order_Date' => $row['Order_Date'],
            'Arrival_Date' => $row['Arrival_Date'],
            'days_since_arrival' => (int)$row['days_since_arrival'],
            'has_reviewed' => (bool)$row['has_reviewed'],
            'days_left' => 7 - (int)$row['days_since_arrival']
        ];
    }

    $stmt->close();
    $conn->close();

    // Clean output buffer and send JSON response
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'reviewable_items' => $reviewableItems,
        'total_items' => count($reviewableItems),
        'debug_user_id' => $user_id
    ]);

} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>
