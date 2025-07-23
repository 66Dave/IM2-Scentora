<?php
// Start output buffering to prevent any accidental output
ob_start();

require_once '../includes/api_middleware.php';

try {
    // Set headers and require admin access
    setJsonHeaders();
    apiRequireAdmin();

    $host = "localhost";
    $username = "root";
    $password = "";
    $database = "scentoradb";

    $conn = new mysqli($host, $username, $password, $database);
    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }

$query = "SELECT 
    Product_ID as id,
    Product_Name as name,
    Product_Price as price,
    Product_Code as code,
    Category as category,
    Image_URL as img,
    Date_Added as added,
    Date_Updated as updated,
    Is_Active as active,
    Brand as brand,
    Description as description,
    Available_Stocks as stock
    FROM product 
    ORDER BY Product_Name";

    $result = $conn->query($query);

    if (!$result) {
        throw new Exception('Query failed: ' . $conn->error);
    }

    $products = [];
    while ($row = $result->fetch_assoc()) {
        // Convert numeric values
        $row['price'] = floatval($row['price']);
        $row['stock'] = intval($row['stock']);
        $row['active'] = $row['active'] == 1;
        
        // Ensure dates are formatted
        $row['added'] = date('Y-m-d', strtotime($row['added']));
        $row['updated'] = date('Y-m-d', strtotime($row['updated']));
        
        $products[] = $row;
    }

    $conn->close();

    // Clean output buffer and send JSON response
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode($products);

} catch (Exception $e) {
    // Clean output buffer and send error response
    ob_clean();
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>
