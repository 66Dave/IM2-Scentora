<?php
// Test the get_product_reviews API with different product IDs
echo "<h2>Testing get_product_reviews.php API</h2>";

// Test with different product IDs to see what's returned
$test_product_ids = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

foreach ($test_product_ids as $product_id) {
    echo "<h3>Testing Product ID: $product_id</h3>";
    
    $url = "http://localhost/IM2-Scentora/files/user/get_product_reviews.php?product_id=$product_id";
    $response = file_get_contents($url);
    
    echo "<strong>Raw Response:</strong><br>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
    
    $data = json_decode($response, true);
    if ($data) {
        echo "<strong>Parsed Data:</strong><br>";
        echo "Success: " . ($data['success'] ? 'true' : 'false') . "<br>";
        if ($data['success']) {
            echo "Total Reviews: " . $data['total_reviews'] . "<br>";
            echo "Average Rating: " . $data['average_rating'] . "<br>";
            echo "Number of Reviews Returned: " . count($data['reviews']) . "<br>";
        } else {
            echo "Error: " . ($data['error'] ?? 'Unknown error') . "<br>";
        }
    } else {
        echo "<strong>Failed to parse JSON</strong><br>";
    }
    echo "<hr>";
}
?>
