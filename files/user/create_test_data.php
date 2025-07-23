<?php
// Simple test to create reviewable order data
require_once '../includes/session_config.php';

if (!isLoggedIn()) {
    echo "Please log in first";
    exit;
}

$user_id = getCurrentUserId();

// Database connection
$host = "localhost";
$username = "root";
$password = "";
$database = "scentoradb";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Creating Test Data for Reviews</h2>";

// Check if user has any orders
$check_sql = "SELECT COUNT(*) as order_count FROM `order` WHERE User_ID = ?";
$stmt = $conn->prepare($check_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$order_count = $row['order_count'];
$stmt->close();

echo "<p>User ID: $user_id</p>";
echo "<p>Existing orders: $order_count</p>";

if ($order_count == 0) {
    echo "<h3>Creating a test order...</h3>";
    
    // Create a test order
    $insert_order = "INSERT INTO `order` (User_ID, Order_Date, Total_Amount, Status, Arrival_Date) VALUES (?, NOW(), 500.00, 'Completed', CURDATE())";
    $stmt = $conn->prepare($insert_order);
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        $order_id = $conn->insert_id;
        echo "<p>✅ Created test order ID: $order_id</p>";
        
        // Add a test product to the order
        $insert_details = "INSERT INTO orderdetails (Order_ID, Product_ID, Quantity, Product_Price) VALUES (?, 1, 1, 500.00)";
        $stmt2 = $conn->prepare($insert_details);
        $stmt2->bind_param("i", $order_id);
        
        if ($stmt2->execute()) {
            echo "<p>✅ Added product to order</p>";
        } else {
            echo "<p>❌ Failed to add product: " . $stmt2->error . "</p>";
        }
        $stmt2->close();
    } else {
        echo "<p>❌ Failed to create order: " . $stmt->error . "</p>";
    }
    $stmt->close();
} else {
    echo "<h3>Updating existing completed order...</h3>";
    
    // Find a completed order and update its arrival date to today
    $update_sql = "UPDATE `order` SET Arrival_Date = CURDATE() WHERE User_ID = ? AND Status = 'Completed' LIMIT 1";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo "<p>✅ Updated existing order arrival date to today</p>";
    } else {
        // Try to create a completed order from pending/accepted
        $update_sql2 = "UPDATE `order` SET Status = 'Completed', Arrival_Date = CURDATE() WHERE User_ID = ? LIMIT 1";
        $stmt2 = $conn->prepare($update_sql2);
        $stmt2->bind_param("i", $user_id);
        
        if ($stmt2->execute() && $stmt2->affected_rows > 0) {
            echo "<p>✅ Updated order status to completed with today's date</p>";
        } else {
            echo "<p>❌ No orders found to update</p>";
        }
        $stmt2->close();
    }
    $stmt->close();
}

echo "<h3>Current Reviewable Orders:</h3>";

// Show current reviewable orders
$review_sql = "SELECT DISTINCT o.Order_ID, o.Order_Date, o.Arrival_Date, o.Status,
        od.Product_ID, p.Product_Name, p.Image_URL, p.Brand, od.Product_Price,
        DATEDIFF(CURDATE(), o.Arrival_Date) as days_since_arrival
        FROM `order` o
        INNER JOIN orderdetails od ON o.Order_ID = od.Order_ID
        INNER JOIN product p ON od.Product_ID = p.Product_ID
        LEFT JOIN product_reviews pr ON (o.Order_ID = pr.Order_ID AND od.Product_ID = pr.Product_ID)
        WHERE o.User_ID = ? 
        AND o.Status = 'Completed' 
        AND o.Arrival_Date IS NOT NULL
        AND DATEDIFF(CURDATE(), o.Arrival_Date) <= 7
        AND pr.Review_ID IS NULL
        ORDER BY o.Arrival_Date DESC";

$stmt = $conn->prepare($review_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>Order ID</th><th>Product</th><th>Arrival Date</th><th>Days Since</th></tr>";

$count = 0;
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$row['Order_ID']}</td>";
    echo "<td>{$row['Product_Name']}</td>";
    echo "<td>{$row['Arrival_Date']}</td>";
    echo "<td>{$row['days_since_arrival']}</td>";
    echo "</tr>";
    $count++;
}

echo "</table>";
echo "<p><strong>Reviewable products: $count</strong></p>";

if ($count > 0) {
    echo "<p>✅ You should now be able to see products in the rating modal!</p>";
    echo "<p><a href='orders_user.php'>Go back to Orders page</a></p>";
} else {
    echo "<p>❌ Still no reviewable products. Check if products exist in the database.</p>";
}

$stmt->close();
$conn->close();
?>
