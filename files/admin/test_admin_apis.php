<?php
// Test script to check admin APIs
session_start();

echo "<h2>Admin API Test</h2>";

// Test if admin session exists
if (isset($_SESSION['admin_logged_in'])) {
    echo "<p>✅ Admin session exists</p>";
} else {
    echo "<p>❌ No admin session found</p>";
    echo "<p>Setting test admin session...</p>";
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_email'] = 'test@admin.com';
}

echo "<h3>1. Testing Dashboard Data API</h3>";
$dashboard_url = 'http://localhost/IM2-Scentora/files/admin/dashboard_data.php';
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => 'Cookie: ' . $_SERVER['HTTP_COOKIE']
    ]
]);

$dashboard_response = file_get_contents($dashboard_url, false, $context);
echo "<h4>Dashboard API Response:</h4>";
echo "<pre>" . htmlspecialchars($dashboard_response) . "</pre>";

$dashboard_json = json_decode($dashboard_response, true);
if ($dashboard_json) {
    echo "<h4>✅ Dashboard JSON Parsed Successfully:</h4>";
    echo "<pre>" . print_r($dashboard_json, true) . "</pre>";
} else {
    echo "<h4>❌ Dashboard API returned invalid JSON</h4>";
}

echo "<h3>2. Testing Inventory Data API</h3>";
$inventory_url = 'http://localhost/IM2-Scentora/files/admin/inventory_fetch.php';
$inventory_response = file_get_contents($inventory_url, false, $context);
echo "<h4>Inventory API Response:</h4>";
echo "<pre>" . htmlspecialchars(substr($inventory_response, 0, 500)) . "...</pre>";

$inventory_json = json_decode($inventory_response, true);
if ($inventory_json) {
    echo "<h4>✅ Inventory JSON Parsed Successfully (showing first 3 items):</h4>";
    echo "<pre>" . print_r(array_slice($inventory_json, 0, 3), true) . "</pre>";
    echo "<p><strong>Total products: " . count($inventory_json) . "</strong></p>";
} else {
    echo "<h4>❌ Inventory API returned invalid JSON</h4>";
}

echo "<h3>3. Direct Database Check</h3>";
$host = "localhost";
$username = "root";
$password = "";
$database = "scentoradb";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    echo "<p>❌ Database connection failed: " . $conn->connect_error . "</p>";
} else {
    echo "<p>✅ Database connected successfully</p>";
    
    // Check products table
    $result = $conn->query("SELECT COUNT(*) as count FROM product");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<p>Products in database: " . $row['count'] . "</p>";
    }
    
    // Check orders table
    $result = $conn->query("SELECT COUNT(*) as count FROM `order`");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<p>Orders in database: " . $row['count'] . "</p>";
    }
    
    $conn->close();
}
?>
