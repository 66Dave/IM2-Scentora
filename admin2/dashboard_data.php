<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$host = "localhost";
$username = "root";
$password = "";
$database = "scentoradb";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$totalProductsQuery = $conn->query("SELECT COUNT(*) AS total FROM product WHERE Is_Active = 1");
$totalProducts = $totalProductsQuery->fetch_assoc()['total'] ?? 0;

$stockAlertsQuery = $conn->query("SELECT COUNT(*) AS alerts FROM product
    WHERE Stock_Status LIKE '%(0%' OR
          Stock_Status LIKE '%(1%' OR
          Stock_Status LIKE '%(2%' OR
          Stock_Status LIKE '%(3%' OR
          Stock_Status LIKE '%(4%'");
$stockAlerts = $stockAlertsQuery->fetch_assoc()['alerts'] ?? 0;

$totalOrdersQuery = $conn->query("SELECT COUNT(*) AS orders FROM `order`");
$totalOrders = $totalOrdersQuery->fetch_assoc()['orders'] ?? 0;

$stockSummaryQuery = $conn->query("SELECT
    SUM(CASE WHEN Stock_Status LIKE '%(0%' THEN 1 ELSE 0 END) AS outOfStock,
    SUM(CASE WHEN Stock_Status NOT LIKE '%(0%' THEN 1 ELSE 0 END) AS inStock
  FROM product WHERE Is_Active = 1");
$stockSummary = $stockSummaryQuery->fetch_assoc();

$inStock = $stockSummary['inStock'] ?? 0;
$outOfStock = $stockSummary['outOfStock'] ?? 0;

$data = [
    "totalProducts" => $totalProducts,
    "stockAlerts" => $stockAlerts,
    "newOrders" => $totalOrders,
    "inStock" => $inStock,
    "outOfStock" => $outOfStock,
    "totalOrders" => $totalOrders
];

header("Content-Type: application/json");
echo json_encode($data);

$conn->close();