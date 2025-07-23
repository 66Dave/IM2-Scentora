<?php
// Debug script to check what's in the database and what the API returns
require_once '../includes/session_config.php';

echo "<h2>Debug: Review System Data</h2>";

// Check if user is logged in
if (!isLoggedIn()) {
    echo "<p>❌ Not logged in</p>";
    exit;
}

$user_id = getCurrentUserId();
echo "<p>✅ User ID: $user_id</p>";

// Database connection
$host = "localhost";
$username = "root";
$password = "";
$database = "scentoradb";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    echo "<p>❌ Database connection failed</p>";
    exit;
}

echo "<h3>1. All User Orders</h3>";
$sql = "SELECT Order_ID, Order_Date, Arrival_Date, Status, Total_Amount FROM `order` WHERE User_ID = ? ORDER BY Order_Date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

echo "<table border='1' style='border-collapse:collapse; margin: 10px 0;'>";
echo "<tr><th>Order ID</th><th>Order Date</th><th>Arrival Date</th><th>Status</th><th>Total</th><th>Days Since Arrival</th></tr>";

while ($row = $result->fetch_assoc()) {
    $days_since = $row['Arrival_Date'] ? floor((time() - strtotime($row['Arrival_Date'])) / (60 * 60 * 24)) : 'N/A';
    echo "<tr>";
    echo "<td>{$row['Order_ID']}</td>";
    echo "<td>{$row['Order_Date']}</td>";
    echo "<td>{$row['Arrival_Date']}</td>";
    echo "<td>{$row['Status']}</td>";
    echo "<td>₱{$row['Total_Amount']}</td>";
    echo "<td>$days_since</td>";
    echo "</tr>";
}
echo "</table>";
$stmt->close();

echo "<h3>2. Completed Orders with Products</h3>";
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
        ORDER BY o.Arrival_Date DESC, o.Order_ID DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

echo "<table border='1' style='border-collapse:collapse; margin: 10px 0;'>";
echo "<tr><th>Order ID</th><th>Product</th><th>Arrival Date</th><th>Days Since</th><th>Has Review</th><th>Within 7 Days?</th></tr>";

$reviewable_count = 0;
while ($row = $result->fetch_assoc()) {
    $within_window = $row['days_since_arrival'] <= 7 ? 'YES' : 'NO';
    if ($within_window === 'YES' && !$row['has_reviewed']) {
        $reviewable_count++;
    }
    
    echo "<tr>";
    echo "<td>{$row['Order_ID']}</td>";
    echo "<td>{$row['Product_Name']}</td>";
    echo "<td>{$row['Arrival_Date']}</td>";
    echo "<td>{$row['days_since_arrival']}</td>";
    echo "<td>" . ($row['has_reviewed'] ? 'YES' : 'NO') . "</td>";
    echo "<td>$within_window</td>";
    echo "</tr>";
}
echo "</table>";
echo "<p><strong>Reviewable products found: $reviewable_count</strong></p>";
$stmt->close();

echo "<h3>3. Test API Response</h3>";
$url = 'http://localhost/IM2-Scentora/files/user/get_reviewable_orders.php';
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => 'Cookie: ' . $_SERVER['HTTP_COOKIE']
    ]
]);

$api_response = file_get_contents($url, false, $context);
echo "<h4>Raw API Response:</h4>";
echo "<pre>" . htmlspecialchars($api_response) . "</pre>";

$json_data = json_decode($api_response, true);
if ($json_data) {
    echo "<h4>Parsed JSON:</h4>";
    echo "<pre>" . print_r($json_data, true) . "</pre>";
} else {
    echo "<p>❌ API response is not valid JSON</p>";
}

echo "<h3>4. Create Test Data (if needed)</h3>";
echo "<p>If no reviewable products found, you can:</p>";
echo "<ol>";
echo "<li>Create a test order by shopping and completing it</li>";
echo "<li>Or manually update an existing completed order's arrival_date to today</li>";
echo "</ol>";

echo "<form method='post'>";
echo "<button type='submit' name='create_test_data'>Create Test Data for Reviews</button>";
echo "</form>";

if (isset($_POST['create_test_data'])) {
    // Find a completed order and update its arrival date to today
    $update_sql = "UPDATE `order` SET Arrival_Date = CURDATE() WHERE User_ID = ? AND Status = 'Completed' LIMIT 1";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("i", $user_id);
    if ($update_stmt->execute()) {
        echo "<p>✅ Test data created! Refresh the page to see changes.</p>";
    } else {
        echo "<p>❌ Failed to create test data</p>";
    }
    $update_stmt->close();
}

$conn->close();
?>
