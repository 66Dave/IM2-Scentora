<?php
// Check what's actually in the database
$host = "localhost";
$username = "root";
$password = "";
$database = "scentoradb";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Database Debug: Reviews and Products</h2>";

// Check all reviews with their product IDs
echo "<h3>All Reviews in Database:</h3>";
$sql = "SELECT pr.*, cd.Consumer_Name, p.Product_Name 
        FROM product_reviews pr 
        LEFT JOIN user u ON pr.User_ID = u.User_ID 
        LEFT JOIN consumerdetails cd ON u.User_ID = cd.User_ID 
        LEFT JOIN product p ON pr.Product_ID = p.Product_ID
        ORDER BY pr.Review_Date DESC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Review ID</th><th>Product ID</th><th>Product Name</th><th>User ID</th><th>Consumer Name</th><th>Rating</th><th>Review Text</th><th>Is_Approved</th><th>Review Date</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Review_ID'] . "</td>";
        echo "<td>" . $row['Product_ID'] . "</td>";
        echo "<td>" . ($row['Product_Name'] ?? 'NOT FOUND') . "</td>";
        echo "<td>" . $row['User_ID'] . "</td>";
        echo "<td>" . ($row['Consumer_Name'] ?? 'NULL') . "</td>";
        echo "<td>" . $row['Rating'] . "</td>";
        echo "<td>" . substr($row['Review_Text'], 0, 50) . "...</td>";
        echo "<td>" . ($row['Is_Approved'] ?? 'NULL') . "</td>";
        echo "<td>" . $row['Review_Date'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No reviews found in database.</p>";
}

// Check available products
echo "<h3>Available Products:</h3>";
$sql = "SELECT Product_ID, Product_Name, Is_Active FROM product ORDER BY Product_ID";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Product ID</th><th>Product Name</th><th>Is_Active</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Product_ID'] . "</td>";
        echo "<td>" . $row['Product_Name'] . "</td>";
        echo "<td>" . $row['Is_Active'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No products found in database.</p>";
}

$conn->close();
?>
