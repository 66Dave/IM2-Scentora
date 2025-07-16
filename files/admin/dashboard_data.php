<?php
// Error visibility (optional for debugging)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database credentials
$host = "localhost";
$username = "root";
$password = "";
$database = "scentoradb";

// Connect to database
$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
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

// Package all data
$data = [
    "totalProducts" => $totalProducts,
    "stockAlerts" => $stockAlerts,
    "pendingOrders" => $pendingOrders,
    "inStock" => $inStock,
    "outOfStock" => $outOfStock,
    "totalOrders" => $totalOrders
];

// Output JSON
header("Content-Type: application/json");
echo json_encode($data);

// Close connection
$conn->close();