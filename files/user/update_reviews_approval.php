<?php
// Update all reviews to approved status
$host = "localhost";
$username = "root";
$password = "";
$database = "scentoradb";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Update all existing reviews to be approved
$sql = "UPDATE product_reviews SET Is_Approved = 1 WHERE Is_Approved IS NULL OR Is_Approved = 0";
$result = $conn->query($sql);

echo "<h2>Review Approval Update</h2>";
echo "<p>Updated " . $conn->affected_rows . " reviews to approved status</p>";

// Verify the update
$sql = "SELECT COUNT(*) as total_reviews, 
               SUM(CASE WHEN Is_Approved = 1 THEN 1 ELSE 0 END) as approved_reviews
        FROM product_reviews";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

echo "<p>Total reviews: " . $row['total_reviews'] . "</p>";
echo "<p>Approved reviews: " . $row['approved_reviews'] . "</p>";

$conn->close();
?>
