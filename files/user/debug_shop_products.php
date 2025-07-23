<?php
// Debug the shop page product loading
$host = "localhost";
$username = "root";
$password = "";
$database = "scentoradb";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Shop Page Debug - Product Loading</h2>";

// Test the modified query from shop_user.php
echo "<h3>Testing Simplified Product Query with Ratings:</h3>";
$sql = "SELECT 
    p.Product_ID, p.Product_Name, p.Product_Price, p.Available_Stocks, p.Stock_Level,
    p.Category, p.Image_URL, p.Product_Code, p.Brand, p.Description,
    COALESCE(AVG(pr.Rating), 0) as average_rating,
    COUNT(pr.Review_ID) as total_reviews
    FROM product p
    LEFT JOIN product_reviews pr ON p.Product_ID = pr.Product_ID AND pr.Is_Approved = 1
    WHERE p.Is_Active = 1 AND p.Available_Stocks > 0
    GROUP BY p.Product_ID
    ORDER BY p.Product_ID DESC";

$result = $conn->query($sql);

if (!$result) {
    echo "<p style='color: red;'>SQL Error: " . $conn->error . "</p>";
} else {
    echo "<p style='color: green;'>Query executed successfully!</p>";
    echo "<p>Number of products found: " . $result->num_rows . "</p>";
    
    if ($result->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Product ID</th><th>Product Name</th><th>Price</th><th>Stock</th><th>Category</th><th>Brand</th><th>Avg Rating</th><th>Total Reviews</th></tr>";
        
        $count = 0;
        while ($row = $result->fetch_assoc() && $count < 10) { // Show first 10 products
            echo "<tr>";
            echo "<td>" . $row['Product_ID'] . "</td>";
            echo "<td>" . $row['Product_Name'] . "</td>";
            echo "<td>" . $row['Product_Price'] . "</td>";
            echo "<td>" . $row['Available_Stocks'] . "</td>";
            echo "<td>" . $row['Category'] . "</td>";
            echo "<td>" . ($row['Brand'] ?? 'NULL') . "</td>";
            echo "<td>" . $row['average_rating'] . "</td>";
            echo "<td>" . $row['total_reviews'] . "</td>";
            echo "</tr>";
            $count++;
        }
        echo "</table>";
        
        if ($result->num_rows > 10) {
            echo "<p>... and " . ($result->num_rows - 10) . " more products</p>";
        }
    }
}

// Test the original simple query
echo "<h3>Testing Original Simple Product Query:</h3>";
$simple_sql = "SELECT 
    Product_ID, Product_Name, Product_Price, Available_Stocks, Stock_Level,
    Category, Image_URL, Product_Code, Brand, Description
    FROM product
    WHERE Is_Active = 1 AND Available_Stocks > 0
    ORDER BY Product_ID DESC";

$simple_result = $conn->query($simple_sql);

if (!$simple_result) {
    echo "<p style='color: red;'>SQL Error: " . $conn->error . "</p>";
} else {
    echo "<p style='color: green;'>Simple query executed successfully!</p>";
    echo "<p>Number of products found: " . $simple_result->num_rows . "</p>";
}

$conn->close();
?>
