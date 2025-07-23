<?php
// Test submit_review.php API
session_start();

echo "<h2>Submit Review API Test</h2>";

// Set test session data
$_SESSION['user_id'] = 1;
$_SESSION['consumer_id'] = 1;
$_SESSION['Consumer_ID'] = 1;

echo "<h3>Test 1: Direct API Call</h3>";
$url = 'http://localhost/IM2-Scentora/files/user/submit_review.php';

// Test data for batch review
$test_data = [
    'reviews' => [
        [
            'order_id' => 1,
            'product_id' => 1,
            'rating' => 5,
            'review_text' => 'Great product!'
        ]
    ]
];

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json' . "\r\n" . 'Cookie: ' . $_SERVER['HTTP_COOKIE'],
        'content' => json_encode($test_data)
    ]
]);

$response = file_get_contents($url, false, $context);
echo "<h4>Response:</h4>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";

$json_data = json_decode($response, true);
if ($json_data) {
    echo "<h4>✅ Parsed JSON:</h4>";
    echo "<pre>" . print_r($json_data, true) . "</pre>";
} else {
    echo "<h4>❌ API returned invalid JSON</h4>";
}

echo "<h3>Test 2: Check if there are any reviewable orders</h3>";
$reviewable_url = 'http://localhost/IM2-Scentora/files/user/get_reviewable_orders.php';
$reviewable_context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => 'Cookie: ' . $_SERVER['HTTP_COOKIE']
    ]
]);

$reviewable_response = file_get_contents($reviewable_url, false, $reviewable_context);
echo "<h4>Reviewable Orders Response:</h4>";
echo "<pre>" . htmlspecialchars($reviewable_response) . "</pre>";
?>
