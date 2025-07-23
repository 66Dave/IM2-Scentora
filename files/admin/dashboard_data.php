<?php
// Start output buffering to prevent any accidental output
ob_start();

require_once '../includes/session_config.php';

// Require admin access
requireAdmin();

// Check session timeout
checkSessionTimeout();

// Error visibility (optional for debugging)
ini_set('display_errors', 0); // Disable display errors for clean JSON
error_reporting(E_ALL);

try {
    // Database credentials
    $host = "localhost";
    $username = "root";
    $password = "";
    $database = "scentoradb";

    // Connect to database
    $conn = new mysqli($host, $username, $password, $database);
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

// Total products (only active products)
$totalProductsQuery = $conn->query("SELECT COUNT(*) AS total FROM product WHERE Is_Active = 1");
$totalProducts = $totalProductsQuery->fetch_assoc()['total'] ?? 0;

// Stock alerts (products with low stock - less than 5)
$stockAlertsQuery = $conn->query("SELECT COUNT(*) AS alerts FROM product 
    WHERE Is_Active = 1 AND Available_Stocks < 5");
$stockAlerts = $stockAlertsQuery->fetch_assoc()['alerts'] ?? 0;

// Pending orders count
$pendingOrdersQuery = $conn->query("SELECT COUNT(*) AS pending FROM `order` WHERE Status = 'Pending'");
$pendingOrders = $pendingOrdersQuery->fetch_assoc()['pending'] ?? 0;

// Total orders
$totalOrdersQuery = $conn->query("SELECT COUNT(*) AS orders FROM `order`");
$totalOrders = $totalOrdersQuery->fetch_assoc()['orders'] ?? 0;

// Stock summary (in stock vs out of stock)
$stockSummaryQuery = $conn->query("SELECT 
    SUM(CASE WHEN Available_Stocks = 0 THEN 1 ELSE 0 END) AS outOfStock,
    SUM(CASE WHEN Available_Stocks > 0 THEN 1 ELSE 0 END) AS inStock
    FROM product 
    WHERE Is_Active = 1");
$stockSummary = $stockSummaryQuery->fetch_assoc();

$inStock = $stockSummary['inStock'] ?? 0;
$outOfStock = $stockSummary['outOfStock'] ?? 0;

// Total sales from completed orders
$totalSalesQuery = $conn->query("SELECT COALESCE(SUM(Total_Amount), 0) AS totalSales 
    FROM `order` WHERE Status = 'Completed'");
$totalSales = $totalSalesQuery->fetch_assoc()['totalSales'] ?? 0;

    // Package all data
    $data = [
        "totalProducts" => $totalProducts,
        "stockAlerts" => $stockAlerts,
        "pendingOrders" => $pendingOrders,
        "inStock" => $inStock,
        "outOfStock" => $outOfStock,
        "totalOrders" => $totalOrders,
        "totalSales" => number_format($totalSales, 2, '.', '')
    ];

    // Close connection
    $conn->close();

    // Clean output buffer and send JSON response
    ob_clean();
    header("Content-Type: application/json");
    echo json_encode($data);

} catch (Exception $e) {
    // Clean output buffer and send error response
    ob_clean();
    http_response_code(500);
    header("Content-Type: application/json");
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>