<?php
// Test script to verify database connection and product table structure
echo "<h2>Database Connection Test</h2>";

try {
    $host = "localhost";
    $username = "root";
    $password = "";
    $database = "scentoradb";

    $conn = new mysqli($host, $username, $password, $database);
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    
    echo "<p>✅ Database connection successful</p>";
    
    // Test product table structure
    $result = $conn->query("DESCRIBE product");
    echo "<h3>Product Table Structure:</h3>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Test count of existing products
    $result = $conn->query("SELECT COUNT(*) as count FROM product");
    $count = $result->fetch_assoc()['count'];
    echo "<p>Current products in database: <strong>$count</strong></p>";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>
