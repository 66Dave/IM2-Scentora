<?php
// Start output buffering to catch any unwanted output
ob_start();

// Set JSON header first
header('Content-Type: application/json');

try {
    require_once '../includes/session_config.php';
    
    // Since user can access shop_user.php, they're already authenticated
    // Skip complex session checking for this API endpoint

    // Database connection
    $host = "localhost";
    $username = "root";
    $password = "";
    $database = "scentoradb";

    $conn = new mysqli($host, $username, $password, $database);
    if ($conn->connect_error) {
        echo json_encode(['success' => false, 'error' => 'Database connection failed']);
        exit;
    }

    // Get top rated products (products with highest average ratings and at least 1 review)
    $sql = "SELECT 
        p.Product_ID, 
        p.Product_Name, 
        p.Product_Price, 
        p.Available_Stocks, 
        p.Stock_Level,
        p.Category, 
        p.Image_URL, 
        p.Product_Code, 
        p.Brand, 
        p.Description,
        AVG(pr.Rating) as average_rating,
        COUNT(pr.Review_ID) as total_reviews
        FROM product p
        INNER JOIN product_reviews pr ON p.Product_ID = pr.Product_ID
        WHERE p.Is_Active = 1 
        AND p.Available_Stocks > 0
        GROUP BY p.Product_ID
        HAVING total_reviews > 0
        ORDER BY average_rating DESC, total_reviews DESC
        LIMIT 20";

    $result = $conn->query($sql);
    
    if (!$result) {
        echo json_encode(['success' => false, 'error' => 'Query failed: ' . $conn->error]);
        exit;
    }
    
    $products = [];

    while ($row = $result->fetch_assoc()) {
        // Handle image path same as shop_user.php
        $imagePath = !empty($row['Image_URL']) ? '../admin/' . $row['Image_URL'] : '../images/placeholder.jpg';
        
        $products[] = [
            'Product_ID' => (int)$row['Product_ID'],
            'Product_Name' => $row['Product_Name'],
            'Product_Price' => (float)$row['Product_Price'],
            'Available_Stocks' => (int)$row['Available_Stocks'],
            'Stock_Level' => (int)$row['Stock_Level'],
            'Category' => $row['Category'],
            'Image_URL' => $imagePath,
            'Product_Code' => $row['Product_Code'],
            'Brand' => $row['Brand'] ?? 'Scentora',
            'Description' => $row['Description'] ?? 'No description available',
            'average_rating' => round((float)$row['average_rating'], 1),
            'total_reviews' => (int)$row['total_reviews']
        ];
    }

    $conn->close();
    
    // Clear any unwanted output
    ob_clean();
    
    echo json_encode([
        'success' => true,
        'products' => $products,
        'total_products' => count($products)
    ]);
    
} catch (Exception $e) {
    // Clear any unwanted output
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}
?>
