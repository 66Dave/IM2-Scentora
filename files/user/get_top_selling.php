<?php
require_once '../includes/api_middleware.php';

// Set JSON headers and require consumer access
setJsonHeaders();
apiRequireConsumer();

// Database connection (same as shop_user.php)
$host = "localhost";
$username = "root";
$password = "";
$database = "scentoradb";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database connection failed',
        'message' => $conn->connect_error
    ]);
    exit();
}

header('Content-Type: application/json');

try {
    // Calculate date range for the last 3 months to ensure we have data
    $current_date = new DateTime();
    $start_of_period = new DateTime();
    $start_of_period->modify('-3 months')->modify('first day of this month');
    $end_of_period = new DateTime();
    $end_of_period->modify('last day of this month');
    
    $start_date = $start_of_period->format('Y-m-d');
    $end_date = $end_of_period->format('Y-m-d');
    
    // Query to get ALL products sorted by sales (top selling first) - ONLY completed orders
    $query = "
        SELECT 
            p.Product_ID,
            p.Product_Name,
            p.Brand,
            p.Category,
            p.Product_Price,
            p.Available_Stocks,
            p.Stock_Level,
            p.Image_URL,
            COALESCE(SUM(CASE WHEN o.Status = 'Completed' THEN od.Product_Qty ELSE 0 END), 0) as total_sold
        FROM product p
        LEFT JOIN orderdetails od ON p.Product_ID = od.Product_ID
        LEFT JOIN `order` o ON od.Order_ID = o.Order_ID 
        WHERE p.Is_Active = 1 AND p.Available_Stocks > 0
        AND (o.Order_Date IS NULL OR (o.Order_Date BETWEEN ? AND ? AND o.Status = 'Completed'))
        GROUP BY p.Product_ID, p.Product_Name, p.Brand, p.Category, p.Product_Price, p.Available_Stocks, p.Stock_Level, p.Image_URL
        ORDER BY total_sold DESC, p.Product_Name ASC
        LIMIT 10
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        // Handle image path same as shop_user.php
        $imagePath = !empty($row['Image_URL']) ? '../admin/' . $row['Image_URL'] : '../images/placeholder.jpg';
        
        $products[] = [
            'Product_ID' => (int)$row['Product_ID'],
            'Product_Name' => $row['Product_Name'],
            'Brand' => $row['Brand'] ?? 'Scentora',
            'Category' => $row['Category'],
            'Product_Price' => (float)$row['Product_Price'],
            'Available_Stocks' => (int)$row['Available_Stocks'],
            'Stock_Level' => $row['Stock_Level'],
            'Image_URL' => $imagePath,
            'total_sold' => (int)$row['total_sold']
        ];
    }
    
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'products' => $products,
        'period' => [
            'start_date' => $start_date,
            'end_date' => $end_date,
            'period_name' => 'Last 3 Months'
        ],
        'total_products' => count($products)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error occurred',
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>
